<?php
/**
 * ResourceRepository - Handles course resource operations
 */

declare(strict_types=1);

class ResourceRepository
{
    private PDO $pdo;
    
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    
    /**
     * Get all resources for a course
     */
    public function getByCourse(int $projectId): array
    {
        $sql = 'SELECT id, project_id, title, type, file_url, description, position, is_required, created_at 
                FROM resources 
                WHERE project_id = ? AND deleted_at IS NULL 
                ORDER BY position ASC, created_at DESC';
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$projectId]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get resource by ID
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM resources WHERE id = ? AND deleted_at IS NULL');
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        
        return $result ?: null;
    }
    
    /**
     * Create resource
     */
    public function create(array $data): int
    {
        $sql = 'INSERT INTO resources (project_id, title, type, file_url, description, position, is_required) 
                VALUES (?, ?, ?, ?, ?, ?, ?)';
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['project_id'],
            $data['title'],
            $data['type'] ?? 'Link',
            $data['file_url'],
            $data['description'] ?? null,
            $data['position'] ?? 0,
            $data['is_required'] ?? true,
        ]);
        
        return (int)$this->pdo->lastInsertId();
    }
    
    /**
     * Update resource
     */
    public function update(int $id, array $data): bool
    {
        $fields = ['updated_at = CURRENT_TIMESTAMP'];
        $values = [];
        
        if (isset($data['title'])) {
            $fields[] = 'title = ?';
            $values[] = $data['title'];
        }
        
        if (isset($data['type'])) {
            $fields[] = 'type = ?';
            $values[] = $data['type'];
        }
        
        if (isset($data['file_url'])) {
            $fields[] = 'file_url = ?';
            $values[] = $data['file_url'];
        }
        
        if (isset($data['description'])) {
            $fields[] = 'description = ?';
            $values[] = $data['description'];
        }
        
        if (isset($data['position'])) {
            $fields[] = 'position = ?';
            $values[] = $data['position'];
        }
        
        if (isset($data['is_required'])) {
            $fields[] = 'is_required = ?';
            $values[] = $data['is_required'];
        }
        
        $values[] = $id;
        
        $sql = 'UPDATE resources SET ' . implode(', ', $fields) . ' WHERE id = ? AND deleted_at IS NULL';
        
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute($values);
        
        return $result && $stmt->rowCount() > 0;
    }
    
    /**
     * Soft delete resource
     */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('UPDATE resources SET deleted_at = CURRENT_TIMESTAMP WHERE id = ? AND deleted_at IS NULL');
        $result = $stmt->execute([$id]);
        
        return $result && $stmt->rowCount() > 0;
    }
    
    /**
     * Reorder resources
     */
    public function reorder(array $resources): bool
    {
        foreach ($resources as $position => $id) {
            $stmt = $this->pdo->prepare('UPDATE resources SET position = ? WHERE id = ?');
            $stmt->execute([$position, $id]);
        }
        
        return true;
    }
    
    /**
     * Count resources for a course
     */
    public function count(int $projectId): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM resources WHERE project_id = ? AND deleted_at IS NULL');
        $stmt->execute([$projectId]);
        
        return (int)$stmt->fetchColumn();
    }
}
