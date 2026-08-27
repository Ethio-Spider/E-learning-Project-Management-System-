<?php
/**
 * FileUploadHandler - Handles file uploads for assignments and resources
 */

declare(strict_types=1);

class FileUploadHandler
{
    private string $uploadsDir;
    private array $allowedExtensions = ['pdf', 'doc', 'docx', 'txt', 'jpg', 'jpeg', 'png', 'gif', 'zip', 'rar', '7z'];
    private int $maxFileSize = 50 * 1024 * 1024; // 50MB
    
    public function __construct(string $uploadsDir = '/uploads')
    {
        $this->uploadsDir = __DIR__ . '/../' . trim($uploadsDir, '/');
        
        // Create directory if it doesn't exist
        if (!is_dir($this->uploadsDir)) {
            mkdir($this->uploadsDir, 0755, true);
        }
    }
    
    /**
     * Upload file for assignment submission
     */
    public function uploadAssignmentFile(array $file, int $assignmentId, int $studentId): string
    {
        $this->validateFile($file);
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedExtensions, true)) {
            throw new Exception('File type not allowed');
        }
        
        $fileName = sprintf('assignment_%d_student_%d_%s.%s', $assignmentId, $studentId, time(), $extension);
        $filePath = $this->uploadsDir . '/submissions/' . $fileName;
        
        // Create submissions directory if needed
        $submissionsDir = $this->uploadsDir . '/submissions';
        if (!is_dir($submissionsDir)) {
            mkdir($submissionsDir, 0755, true);
        }
        
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            throw new Exception('Failed to move uploaded file');
        }
        
        chmod($filePath, 0644);
        
        return '/uploads/submissions/' . $fileName;
    }
    
    /**
     * Upload course resource
     */
    public function uploadCourseResource(array $file, int $courseId): string
    {
        $this->validateFile($file);
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedExtensions, true)) {
            throw new Exception('File type not allowed');
        }
        
        $fileName = sprintf('course_%d_resource_%s.%s', $courseId, time(), $extension);
        $filePath = $this->uploadsDir . '/resources/' . $fileName;
        
        // Create resources directory if needed
        $resourcesDir = $this->uploadsDir . '/resources';
        if (!is_dir($resourcesDir)) {
            mkdir($resourcesDir, 0755, true);
        }
        
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            throw new Exception('Failed to move uploaded file');
        }
        
        chmod($filePath, 0644);
        
        return '/uploads/resources/' . $fileName;
    }
    
    /**
     * Upload user avatar
     */
    public function uploadAvatar(array $file, int $userId): string
    {
        $this->validateFile($file);
        
        // Only allow image files for avatars
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($extension, $imageExtensions, true)) {
            throw new Exception('Only image files are allowed for avatars');
        }
        
        $fileName = sprintf('user_%d_avatar.%s', $userId, $extension);
        $filePath = $this->uploadsDir . '/avatars/' . $fileName;
        
        // Create avatars directory if needed
        $avatarsDir = $this->uploadsDir . '/avatars';
        if (!is_dir($avatarsDir)) {
            mkdir($avatarsDir, 0755, true);
        }
        
        // Delete old avatar if exists
        $oldAvatars = glob($this->uploadsDir . '/avatars/user_' . $userId . '_avatar.*');
        foreach ($oldAvatars as $oldFile) {
            @unlink($oldFile);
        }
        
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            throw new Exception('Failed to move uploaded file');
        }
        
        chmod($filePath, 0644);
        
        return '/uploads/avatars/' . $fileName;
    }
    
    /**
     * Delete file
     */
    public function deleteFile(string $filePath): bool
    {
        $fullPath = __DIR__ . '/..' . $filePath;
        
        if (file_exists($fullPath)) {
            return @unlink($fullPath);
        }
        
        return false;
    }
    
    /**
     * Validate uploaded file
     */
    private function validateFile(array $file): void
    {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            throw new Exception('No valid file uploaded');
        }
        
        if ($file['size'] > $this->maxFileSize) {
            throw new Exception('File size exceeds maximum allowed size (50MB)');
        }
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload error: ' . $file['error']);
        }
    }
    
    /**
     * Get file size in human readable format
     */
    public static function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    /**
     * Check if file exists
     */
    public function fileExists(string $filePath): bool
    {
        return file_exists(__DIR__ . '/..' . $filePath);
    }
}
