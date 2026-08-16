<?php
/**
 * UserRepository - Handles user data operations
 */

declare(strict_types=1);

class UserRepository
{
    private PDO $pdo;
    
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    
    /**
     * Get user by email
     */
    public function getByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = ? AND deleted_at IS NULL');
        $stmt->execute([$email]);
        $result = $stmt->fetch();
        
        return $result ?: null;
    }
    
    /**
     * Get user by ID
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, first_name, last_name, email, role, created_at, updated_at FROM users WHERE id = ? AND deleted_at IS NULL');
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        
        return $result ?: null;
    }
    
    /**
     * Create a new user
     */
    public function create(string $firstName, string $lastName, string $email, string $password, string $role = 'student'): int
    {
        // Check if user exists
        if ($this->getByEmail($email)) {
            throw new Exception('Email already registered');
        }
        
        // Validate role
        $validRoles = ['student', 'instructor', 'admin'];
        if (!in_array($role, $validRoles, true)) {
            throw new Exception('Invalid role');
        }
        
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        
        $sql = 'INSERT INTO users (first_name, last_name, email, password, role) 
                VALUES (?, ?, ?, ?, ?)';
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$firstName, $lastName, $email, $hashedPassword, $role]);
        
        return (int)$this->pdo->lastInsertId();
    }
    
    /**
     * Verify password
     */
    public function verifyPassword(string $email, string $password): bool
    {
        $user = $this->getByEmail($email);
        
        if (!$user) {
            return false;
        }
        
        return password_verify($password, $user['password']);
    }
    
    /**
     * Update user profile
     */
    public function updateProfile(int $id, array $data): bool
    {
        $fields = [];
        $values = [];
        
        if (isset($data['first_name'])) {
            $fields[] = 'first_name = ?';
            $values[] = $data['first_name'];
        }
        
        if (isset($data['last_name'])) {
            $fields[] = 'last_name = ?';
            $values[] = $data['last_name'];
        }
        
        if (isset($data['bio'])) {
            $fields[] = 'bio = ?';
            $values[] = $data['bio'];
        }
        
        if (isset($data['avatar_url'])) {
            $fields[] = 'avatar_url = ?';
            $values[] = $data['avatar_url'];
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $fields[] = 'updated_at = CURRENT_TIMESTAMP';
        $values[] = $id;
        
        $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ? AND deleted_at IS NULL';
        
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute($values);
        
        return $result && $stmt->rowCount() > 0;
    }
    
    /**
     * Update password
     */
    public function updatePassword(int $id, string $newPassword): bool
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        
        $stmt = $this->pdo->prepare('UPDATE users SET password = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND deleted_at IS NULL');
        $result = $stmt->execute([$hashedPassword, $id]);
        
        return $result && $stmt->rowCount() > 0;
    }
    
    /**
     * Soft delete user
     */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('UPDATE users SET deleted_at = CURRENT_TIMESTAMP WHERE id = ? AND deleted_at IS NULL');
        $result = $stmt->execute([$id]);
        
        return $result && $stmt->rowCount() > 0;
    }
    
    /**
     * Get all users (admin)
     */
    public function getAll(int $limit = 50, int $offset = 0): array
    {
        $sql = 'SELECT id, first_name, last_name, email, role, created_at 
                FROM users 
                WHERE deleted_at IS NULL 
                ORDER BY created_at DESC 
                LIMIT ? OFFSET ?';
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$limit, $offset]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Count total users
     */
    public function count(): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM users WHERE deleted_at IS NULL');
        $stmt->execute();
        
        return (int)$stmt->fetchColumn();
    }
    
    /**
     * Get users by role
     */
    public function getByRole(string $role, int $limit = 50): array
    {
        $sql = 'SELECT id, first_name, last_name, email, role, created_at 
                FROM users 
                WHERE role = ? AND deleted_at IS NULL 
                ORDER BY created_at DESC 
                LIMIT ?';
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$role, $limit]);
        
        return $stmt->fetchAll();
    }
}
