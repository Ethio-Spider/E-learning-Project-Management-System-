<?php
/**
 * AssignmentRepository - Handles assignment and submission operations
 */

declare(strict_types=1);

class AssignmentRepository
{
    private PDO $pdo;
    
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    
    /**
     * Get all assignments for a course
     */
    public function getByCourse(int $projectId): array
    {
        $sql = 'SELECT id, project_id, title, description, due_date, max_score, status, created_at 
                FROM assignments 
                WHERE project_id = ? AND deleted_at IS NULL 
                ORDER BY due_date ASC, created_at DESC';
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$projectId]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get assignment by ID
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM assignments WHERE id = ? AND deleted_at IS NULL');
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        
        return $result ?: null;
    }
    
    /**
     * Create assignment
     */
    public function create(array $data): int
    {
        $sql = 'INSERT INTO assignments (project_id, title, description, due_date, max_score, status) 
                VALUES (?, ?, ?, ?, ?, ?)';
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['project_id'],
            $data['title'],
            $data['description'],
            $data['due_date'],
            $data['max_score'] ?? 100,
            $data['status'] ?? 'Open',
        ]);
        
        return (int)$this->pdo->lastInsertId();
    }
    
    /**
     * Submit assignment
     */
    public function submitAssignment(int $assignmentId, int $enrollmentId, string $studentEmail, string $submissionText = '', string $fileUrl = ''): int
    {
        // Check if already submitted
        $checkStmt = $this->pdo->prepare('SELECT id FROM submissions WHERE assignment_id = ? AND enrollment_id = ?');
        $checkStmt->execute([$assignmentId, $enrollmentId]);
        
        if ($checkStmt->fetch()) {
            throw new Exception('Already submitted this assignment.');
        }
        
        $sql = 'INSERT INTO submissions (assignment_id, enrollment_id, student_email, submission_text, file_url) 
                VALUES (?, ?, ?, ?, ?)';
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$assignmentId, $enrollmentId, $studentEmail, $submissionText, $fileUrl]);
        
        return (int)$this->pdo->lastInsertId();
    }
    
    /**
     * Get student's submission for assignment
     */
    public function getSubmission(int $assignmentId, int $enrollmentId): ?array
    {
        $sql = 'SELECT id, assignment_id, enrollment_id, student_email, submission_text, file_url, submitted_at, score, feedback, graded_at 
                FROM submissions 
                WHERE assignment_id = ? AND enrollment_id = ? AND deleted_at IS NULL';
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$assignmentId, $enrollmentId]);
        $result = $stmt->fetch();
        
        return $result ?: null;
    }
    
    /**
     * Get all submissions for an assignment
     */
    public function getAssignmentSubmissions(int $assignmentId): array
    {
        $sql = 'SELECT s.id, s.assignment_id, s.enrollment_id, s.student_email, s.submission_text, s.file_url, 
                        s.submitted_at, s.score, s.feedback, s.graded_at, e.student_name
                FROM submissions s
                JOIN enrollments e ON s.enrollment_id = e.id
                WHERE s.assignment_id = ? AND s.deleted_at IS NULL
                ORDER BY s.submitted_at DESC';
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$assignmentId]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Grade submission
     */
    public function gradeSubmission(int $submissionId, int $score, string $feedback = '', string $gradedBy = ''): bool
    {
        $sql = 'UPDATE submissions SET score = ?, feedback = ?, graded_at = CURRENT_TIMESTAMP, graded_by = ? 
                WHERE id = ?';
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$score, $feedback, $gradedBy, $submissionId]);
    }
}
