<?php
/**
 * EnrollmentRepository - Handles enrollment data operations
 */

declare(strict_types=1);

class EnrollmentRepository
{
    private PDO $pdo;
    
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    
    /**
     * Get all enrollments for a course
     */
    public function getByCourse(int $projectId, int $limit = 100, int $offset = 0): array
    {
        $sql = 'SELECT id, project_id, student_name, email, enrollment_date, status, progress, completed_at 
                FROM enrollments 
                WHERE project_id = ? AND deleted_at IS NULL 
                ORDER BY enrollment_date DESC 
                LIMIT ? OFFSET ?';
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$projectId, $limit, $offset]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get all enrollments for a student email
     */
    public function getByEmail(string $email, int $limit = 50, int $offset = 0): array
    {
        $sql = 'SELECT id, project_id, student_name, email, enrollment_date, status, progress, completed_at 
                FROM enrollments 
                WHERE email = ? AND deleted_at IS NULL 
                ORDER BY enrollment_date DESC 
                LIMIT ? OFFSET ?';
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$email, $limit, $offset]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get enrollment by ID
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM enrollments WHERE id = ? AND deleted_at IS NULL');
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        
        return $result ?: null;
    }
    
    /**
     * Check if student is enrolled in course
     */
    public function isEnrolled(int $projectId, string $email): bool
    {
        $stmt = $this->pdo->prepare('SELECT id FROM enrollments WHERE project_id = ? AND email = ? AND deleted_at IS NULL');
        $stmt->execute([$projectId, $email]);
        
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Create enrollment
     */
    public function create(int $projectId, string $studentName, string $email): int
    {
        // Check if already enrolled
        if ($this->isEnrolled($projectId, $email)) {
            throw new Exception('Student is already enrolled in this course');
        }
        
        $sql = 'INSERT INTO enrollments (project_id, student_name, email, status) VALUES (?, ?, ?, ?)';
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$projectId, $studentName, $email, 'Enrolled']);
        
        return (int)$this->pdo->lastInsertId();
    }
    
    /**
     * Update enrollment
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $values = [];
        
        if (isset($data['status'])) {
            $fields[] = 'status = ?';
            $values[] = $data['status'];
        }
        
        if (isset($data['progress'])) {
            $fields[] = 'progress = ?';
            $values[] = $data['progress'];
        }
        
        if (isset($data['completed_at'])) {
            $fields[] = 'completed_at = ?';
            $values[] = $data['completed_at'];
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $fields[] = 'updated_at = CURRENT_TIMESTAMP';
        $values[] = $id;
        
        $sql = 'UPDATE enrollments SET ' . implode(', ', $fields) . ' WHERE id = ? AND deleted_at IS NULL';
        
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute($values);
        
        return $result && $stmt->rowCount() > 0;
    }
    
    /**
     * Soft delete enrollment
     */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('UPDATE enrollments SET deleted_at = CURRENT_TIMESTAMP WHERE id = ? AND deleted_at IS NULL');
        $result = $stmt->execute([$id]);
        
        return $result && $stmt->rowCount() > 0;
    }
    
    /**
     * Count enrollments for a course
     */
    public function countByCourse(int $projectId): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM enrollments WHERE project_id = ? AND deleted_at IS NULL');
        $stmt->execute([$projectId]);
        
        return (int)$stmt->fetchColumn();
    }
    
    /**
     * Get enrollment statistics
     */
    public function getStats(int $projectId): array
    {
        $sql = 'SELECT 
                COUNT(*) as total_enrollments,
                SUM(CASE WHEN status = "Enrolled" THEN 1 ELSE 0 END) as active_enrollments,
                SUM(CASE WHEN status = "Completed" THEN 1 ELSE 0 END) as completed_enrollments,
                SUM(CASE WHEN status = "Cancelled" THEN 1 ELSE 0 END) as cancelled_enrollments,
                AVG(progress) as average_progress
                FROM enrollments 
                WHERE project_id = ? AND deleted_at IS NULL';
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$projectId]);
        
        return $stmt->fetch() ?: [];
    }
}
