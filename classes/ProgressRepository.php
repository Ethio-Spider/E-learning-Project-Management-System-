<?php
/**
 * ProgressRepository - Handles student progress and analytics
 */

declare(strict_types=1);

class ProgressRepository
{
    private PDO $pdo;
    
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    
    /**
     * Get student's progress in a course
     */
    public function getCourseProgress(int $enrollmentId): array
    {
        $sql = 'SELECT e.id, e.project_id, e.student_name, e.email, e.enrollment_date, e.status, e.progress,
                       COUNT(DISTINCT s.id) as submitted_count,
                       COUNT(DISTINCT CASE WHEN s.score IS NOT NULL THEN s.id END) as graded_count,
                       AVG(CASE WHEN s.score IS NOT NULL THEN s.score END) as avg_score,
                       MAX(s.submitted_at) as last_submission_date
                FROM enrollments e
                LEFT JOIN assignments a ON a.project_id = e.project_id
                LEFT JOIN submissions s ON s.assignment_id = a.id AND s.enrollment_id = e.id
                WHERE e.id = ?
                GROUP BY e.id';
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$enrollmentId]);
        $result = $stmt->fetch();
        
        return $result ?: [];
    }
    
    /**
     * Get course analytics (instructor view)
     */
    public function getCourseAnalytics(int $courseId): array
    {
        $sql = 'SELECT 
                    COUNT(DISTINCT e.id) as total_students,
                    COUNT(DISTINCT CASE WHEN e.status = "Completed" THEN e.id END) as completed_count,
                    COUNT(DISTINCT CASE WHEN e.status = "Enrolled" THEN e.id END) as active_count,
                    AVG(e.progress) as avg_completion,
                    COUNT(DISTINCT s.id) as total_submissions,
                    COUNT(DISTINCT CASE WHEN s.score IS NOT NULL THEN s.id END) as graded_submissions
                FROM enrollments e
                LEFT JOIN assignments a ON a.project_id = e.project_id
                LEFT JOIN submissions s ON s.assignment_id = a.id
                WHERE e.project_id = ? AND e.deleted_at IS NULL';
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$courseId]);
        
        return $stmt->fetch() ?: [];
    }
    
    /**
     * Get all course enrollments with progress
     */
    public function getCourseEnrollmentsWithProgress(int $courseId): array
    {
        $sql = 'SELECT e.id, e.project_id, e.student_name, e.email, e.enrollment_date, e.status, e.progress,
                       COUNT(DISTINCT s.id) as submitted_count,
                       COUNT(DISTINCT CASE WHEN s.score IS NOT NULL THEN s.id END) as graded_count,
                       AVG(CASE WHEN s.score IS NOT NULL THEN s.score END) as avg_score
                FROM enrollments e
                LEFT JOIN assignments a ON a.project_id = e.project_id
                LEFT JOIN submissions s ON s.assignment_id = a.id AND s.enrollment_id = e.id
                WHERE e.project_id = ? AND e.deleted_at IS NULL
                GROUP BY e.id
                ORDER BY e.enrollment_date DESC';
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$courseId]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Update student progress
     */
    public function updateProgress(int $enrollmentId, float $progress): bool
    {
        $sql = 'UPDATE enrollments SET progress = ? WHERE id = ?';
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$progress, $enrollmentId]);
    }
}
