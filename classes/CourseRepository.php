<?php
/**
 * CourseRepository - Handles course data operations
 */

declare(strict_types=1);

class CourseRepository
{
    private PDO $pdo;
    
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    
    /**
     * Get all courses with optional filters
     */
    public function getAll(array $filters = [], int $limit = 20, int $offset = 0): array
    {
        $query = 'SELECT id, title, description, category, instructor, duration, level, status, image_url, price, rating, total_ratings, created_at, updated_at FROM projects WHERE deleted_at IS NULL';
        $params = [];
        
        if (!empty($filters['category'])) {
            $query .= ' AND category = ?';
            $params[] = $filters['category'];
        }
        
        if (!empty($filters['level'])) {
            $query .= ' AND level = ?';
            $params[] = $filters['level'];
        }
        
        if (!empty($filters['status'])) {
            $query .= ' AND status = ?';
            $params[] = $filters['status'];
        }
        
        $query .= ' ORDER BY created_at DESC, id DESC LIMIT ? OFFSET ?';
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Search courses by query
     */
    public function search(string $query, int $limit = 20, int $offset = 0): array
    {
        $term = '%' . $query . '%';
        
        $sql = 'SELECT id, title, description, category, instructor, duration, level, status, image_url, price, rating, total_ratings, created_at, updated_at 
                FROM projects 
                WHERE deleted_at IS NULL AND (
                    title LIKE ? OR 
                    description LIKE ? OR 
                    category LIKE ? OR 
                    instructor LIKE ?
                )
                ORDER BY created_at DESC, id DESC 
                LIMIT ? OFFSET ?';
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$term, $term, $term, $term, $limit, $offset]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get course by ID
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM projects WHERE id = ? AND deleted_at IS NULL');
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        
        return $result ?: null;
    }
    
    /**
     * Get course with resources
     */
    public function getWithResources(int $id): ?array
    {
        $course = $this->getById($id);
        
        if (!$course) {
            return null;
        }
        
        $stmt = $this->pdo->prepare('SELECT * FROM resources WHERE project_id = ? AND deleted_at IS NULL ORDER BY position ASC');
        $stmt->execute([$id]);
        $course['resources'] = $stmt->fetchAll();
        
        return $course;
    }
    
    /**
     * Create new course
     */
    public function create(array $data): int
    {
        $sql = 'INSERT INTO projects (title, description, category, instructor, duration, level, status, image_url) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['title'],
            $data['description'],
            $data['category'],
            $data['instructor'],
            $data['duration'],
            $data['level'],
            $data['status'],
            $data['image_url'],
        ]);
        
        return (int)$this->pdo->lastInsertId();
    }
    
    /**
     * Update course
     */
    public function update(int $id, array $data): bool
    {
        $sql = 'UPDATE projects 
                SET title = ?, description = ?, category = ?, instructor = ?, duration = ?, level = ?, status = ?, image_url = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ? AND deleted_at IS NULL';
        
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([
            $data['title'],
            $data['description'],
            $data['category'],
            $data['instructor'],
            $data['duration'],
            $data['level'],
            $data['status'],
            $data['image_url'],
            $id,
        ]);
        
        return $result && $stmt->rowCount() > 0;
    }
    
    /**
     * Soft delete course
     */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('UPDATE projects SET deleted_at = CURRENT_TIMESTAMP WHERE id = ? AND deleted_at IS NULL');
        $result = $stmt->execute([$id]);
        
        return $result && $stmt->rowCount() > 0;
    }
    
    /**
     * Count courses matching criteria
     */
    public function count(array $filters = []): int
    {
        $query = 'SELECT COUNT(*) as total FROM projects WHERE deleted_at IS NULL';
        $params = [];
        
        if (!empty($filters['category'])) {
            $query .= ' AND category = ?';
            $params[] = $filters['category'];
        }
        
        if (!empty($filters['level'])) {
            $query .= ' AND level = ?';
            $params[] = $filters['level'];
        }
        
        if (!empty($filters['status'])) {
            $query .= ' AND status = ?';
            $params[] = $filters['status'];
        }
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get lessons (resources) for a course
     */
    public function getLessonsByCourse(int $courseId): ?array
    {
        $stmt = $this->pdo->prepare('
            SELECT id, project_id as course_id, title, type, file_url, description, position, is_required, created_at 
            FROM resources 
            WHERE project_id = ? AND deleted_at IS NULL 
            ORDER BY position ASC
        ');
        $stmt->execute([$courseId]);
        $results = $stmt->fetchAll();
        
        return $results ?: [];
    }
}
