<?php
/**
 * Validator - Input validation and sanitization
 */

declare(strict_types=1);

class Validator
{
    private array $errors = [];
    
    /**
     * Validate an email address
     */
    public static function email(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false && strlen($email) <= 255;
    }
    
    /**
     * Validate a URL
     */
    public static function url(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false && strlen($url) <= 1000;
    }
    
    /**
     * Validate a positive integer
     */
    public static function positiveInt($value): bool
    {
        $id = filter_var($value, FILTER_VALIDATE_INT);
        return $id !== false && $id > 0;
    }

    public static function validDeadline(string $dueDate, ?int $now = null): bool
    {
        $timestamp = strtotime($dueDate);
        return $timestamp !== false && $timestamp >= ($now ?? time());
    }
    
    /**
     * Validate string length
     */
    public static function stringLength(string $value, int $min, int $max): bool
    {
        $len = mb_strlen($value);
        return $len >= $min && $len <= $max;
    }
    
    /**
     * Validate enum value
     */
    public static function enum(string $value, array $allowed): bool
    {
        return in_array($value, $allowed, true);
    }
    
    /**
     * Sanitize a string
     */
    public static function sanitizeString(string $value): string
    {
        return trim((string)strip_tags($value));
    }
    
    /**
     * Sanitize HTML (allow basic tags)
     */
    public static function sanitizeHtml(string $value): string
    {
        return trim((string)$value);
    }
    
    /**
     * Add validation error
     */
    public function addError(string $field, string $message): void
    {
        $this->errors[$field] = $message;
    }
    
    /**
     * Get all validation errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
    
    /**
     * Check if there are any errors
     */
    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }
    
    /**
     * Get first error message
     */
    public function getFirstError(): string
    {
        return array_values($this->errors)[0] ?? 'Validation error';
    }
    
    /**
     * Validate course data
     */
    public static function validateCourse(array $data): array
    {
        $validator = new self();
        
        $title = trim($data['title'] ?? '');
        $description = trim($data['description'] ?? '');
        $category = trim($data['category'] ?? 'General');
        $instructor = trim($data['instructor'] ?? 'TBD');
        $duration = trim($data['duration'] ?? 'Self-paced');
        $level = $data['level'] ?? 'Beginner';
        $status = $data['status'] ?? 'Active';
        $imageUrl = trim($data['image_url'] ?? '');
        
        // Validate title
        if (!self::stringLength($title, 3, 255)) {
            $validator->addError('title', 'Title must be between 3 and 255 characters');
        }
        
        // Validate description
        if (!self::stringLength($description, 10, 5000)) {
            $validator->addError('description', 'Description must be between 10 and 5000 characters');
        }
        
        // Validate category
        if (!self::stringLength($category, 1, 100)) {
            $validator->addError('category', 'Category must be between 1 and 100 characters');
        }
        
        // Validate instructor
        if (!self::stringLength($instructor, 1, 255)) {
            $validator->addError('instructor', 'Instructor must be between 1 and 255 characters');
        }
        
        // Validate duration
        if (!self::stringLength($duration, 1, 100)) {
            $validator->addError('duration', 'Duration must be between 1 and 100 characters');
        }
        
        // Validate level
        if (!self::enum($level, ['Beginner', 'Intermediate', 'Advanced'])) {
            $validator->addError('level', 'Invalid course level');
        }
        
        // Validate status
        if (!self::enum($status, ['Active', 'Draft', 'Archived'])) {
            $validator->addError('status', 'Invalid course status');
        }
        
        // Validate image URL if provided
        if ($imageUrl && !self::url($imageUrl)) {
            $validator->addError('image_url', 'Image URL must be a valid URL');
        }
        
        return [
            'title' => $title,
            'description' => $description,
            'category' => $category ?: 'General',
            'instructor' => $instructor ?: 'TBD',
            'duration' => $duration ?: 'Self-paced',
            'level' => $level,
            'status' => $status,
            'image_url' => $imageUrl ?: null,
            'validator' => $validator,
        ];
    }
    
    /**
     * Validate enrollment data
     */
    public static function validateEnrollment(array $data): array
    {
        $validator = new self();
        
        $projectId = filter_var($data['project_id'] ?? null, FILTER_VALIDATE_INT);
        $studentName = trim($data['student_name'] ?? '');
        $email = trim($data['email'] ?? '');
        
        if (!self::positiveInt($projectId)) {
            $validator->addError('project_id', 'Valid course ID is required');
        }
        
        if (!self::stringLength($studentName, 2, 255)) {
            $validator->addError('student_name', 'Student name must be between 2 and 255 characters');
        }
        
        if (!self::email($email)) {
            $validator->addError('email', 'Valid email address is required');
        }
        
        return [
            'project_id' => $projectId,
            'student_name' => $studentName,
            'email' => $email,
            'validator' => $validator,
        ];
    }
}
