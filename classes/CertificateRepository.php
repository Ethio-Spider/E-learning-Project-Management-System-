<?php
/**
 * CertificateRepository - Handles certificate generation and tracking
 */

declare(strict_types=1);

class CertificateRepository
{
    private PDO $pdo;
    
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    
    /**
     * Generate certificate when course is completed
     */
    public function generateCertificate(int $enrollmentId, int $courseId, string $studentName): string
    {
        $certificateId = 'CERT-' . strtoupper(bin2hex(random_bytes(8)));
        
        // Get course info
        $stmt = $this->pdo->prepare('SELECT title FROM projects WHERE id = ?');
        $stmt->execute([$courseId]);
        $course = $stmt->fetch();
        
        if (!$course) {
            throw new Exception('Course not found.');
        }
        
        $issuedDate = new DateTime();
        $expiryDate = (clone $issuedDate)->modify('+2 years');
        
        // Insert certificate record
        $sql = 'INSERT INTO certificates (enrollment_id, certificate_id, course_title, student_name, issued_date, expiry_date, status)
                VALUES (?, ?, ?, ?, ?, ?, ?)';
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $enrollmentId,
            $certificateId,
            $course['title'],
            $studentName,
            $issuedDate->format('Y-m-d H:i:s'),
            $expiryDate->format('Y-m-d H:i:s'),
            'Active'
        ]);
        
        return $certificateId;
    }
    
    /**
     * Get student's certificates
     */
    public function getStudentCertificates(string $email): array
    {
        $sql = 'SELECT c.id, c.certificate_id, c.course_title, c.student_name, c.issued_date, c.expiry_date, c.status
                FROM certificates c
                JOIN enrollments e ON c.enrollment_id = e.id
                WHERE e.email = ? AND c.status = "Active"
                ORDER BY c.issued_date DESC';
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$email]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Verify certificate
     */
    public function verifyCertificate(string $certificateId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM certificates WHERE certificate_id = ? AND status = "Active"');
        $stmt->execute([$certificateId]);
        $result = $stmt->fetch();
        
        return $result ?: null;
    }
}
