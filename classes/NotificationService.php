<?php
/**
 * NotificationService - Handles email and push notifications
 */

declare(strict_types=1);

class NotificationService
{
    private string $smtpHost;
    private int $smtpPort;
    private string $smtpUser;
    private string $smtpPassword;
    private string $fromEmail;
    private string $fromName;
    
    public function __construct(array $config = [])
    {
        // Load from environment or config
        $this->smtpHost = $config['smtp_host'] ?? $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com';
        $this->smtpPort = (int)($config['smtp_port'] ?? $_ENV['SMTP_PORT'] ?? 587);
        $this->smtpUser = $config['smtp_user'] ?? $_ENV['SMTP_USER'] ?? '';
        $this->smtpPassword = $config['smtp_password'] ?? $_ENV['SMTP_PASSWORD'] ?? '';
        $this->fromEmail = $config['from_email'] ?? $_ENV['FROM_EMAIL'] ?? 'noreply@learnflow.app';
        $this->fromName = $config['from_name'] ?? $_ENV['FROM_NAME'] ?? 'LearnFlow Pro';
    }
    
    /**
     * Send email notification
     */
    public function sendEmail(string $toEmail, string $subject, string $htmlBody, string $plainTextBody = ''): bool
    {
        if (empty($this->smtpUser)) {
            // Log instead of sending if SMTP not configured
            error_log("Email would be sent to: $toEmail - Subject: $subject");
            return true;
        }
        
        $plainTextBody = $plainTextBody ?: strip_tags($htmlBody);
        
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: {$this->fromName} <{$this->fromEmail}>\r\n";
        $headers .= "Reply-To: {$this->fromEmail}\r\n";
        
        return mail($toEmail, $subject, $htmlBody, $headers);
    }
    
    /**
     * Send assignment submission confirmation
     */
    public function sendSubmissionConfirmation(string $studentEmail, string $studentName, string $assignmentTitle, string $courseTitle): bool
    {
        $subject = "Assignment Submitted: $assignmentTitle";
        
        $htmlBody = $this->getEmailTemplate('submission-confirmation', [
            'studentName' => $studentName,
            'assignmentTitle' => $assignmentTitle,
            'courseTitle' => $courseTitle,
            'submittedAt' => date('F j, Y g:i A'),
        ]);
        
        return $this->sendEmail($studentEmail, $subject, $htmlBody);
    }
    
    /**
     * Send assignment graded notification
     */
    public function sendGradeNotification(string $studentEmail, string $studentName, string $assignmentTitle, float $score, int $maxScore, string $feedback = ''): bool
    {
        $percentage = round(($score / $maxScore) * 100, 1);
        $subject = "Grade Posted: $assignmentTitle";
        
        $htmlBody = $this->getEmailTemplate('grade-notification', [
            'studentName' => $studentName,
            'assignmentTitle' => $assignmentTitle,
            'score' => $score,
            'maxScore' => $maxScore,
            'percentage' => $percentage,
            'feedback' => $feedback,
            'gradedAt' => date('F j, Y g:i A'),
        ]);
        
        return $this->sendEmail($studentEmail, $subject, $htmlBody);
    }
    
    /**
     * Send course enrollment confirmation
     */
    public function sendEnrollmentConfirmation(string $studentEmail, string $studentName, string $courseTitle, string $instructorName): bool
    {
        $subject = "Welcome to $courseTitle";
        
        $htmlBody = $this->getEmailTemplate('enrollment-confirmation', [
            'studentName' => $studentName,
            'courseTitle' => $courseTitle,
            'instructorName' => $instructorName,
            'enrolledAt' => date('F j, Y'),
        ]);
        
        return $this->sendEmail($studentEmail, $subject, $htmlBody);
    }
    
    /**
     * Send assignment due date reminder
     */
    public function sendDueReminderEmail(string $studentEmail, string $studentName, string $assignmentTitle, string $dueDate): bool
    {
        $subject = "Reminder: $assignmentTitle is due soon";
        
        $htmlBody = $this->getEmailTemplate('assignment-reminder', [
            'studentName' => $studentName,
            'assignmentTitle' => $assignmentTitle,
            'dueDate' => $dueDate,
        ]);
        
        return $this->sendEmail($studentEmail, $subject, $htmlBody);
    }
    
    /**
     * Send certificate issued notification
     */
    public function sendCertificateNotification(string $studentEmail, string $studentName, string $courseTitle, string $certificateId): bool
    {
        $subject = "Congratulations! Certificate Issued for $courseTitle";
        
        $htmlBody = $this->getEmailTemplate('certificate-issued', [
            'studentName' => $studentName,
            'courseTitle' => $courseTitle,
            'certificateId' => $certificateId,
            'issuedAt' => date('F j, Y'),
        ]);
        
        return $this->sendEmail($studentEmail, $subject, $htmlBody);
    }
    
    /**
     * Send password reset link
     */
    public function sendPasswordResetEmail(string $userEmail, string $userName, string $resetToken): bool
    {
        $subject = "Reset Your Password";
        $resetLink = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'learnflow.app') . '/reset-password.html?token=' . urlencode($resetToken);
        
        $htmlBody = $this->getEmailTemplate('password-reset', [
            'userName' => $userName,
            'resetLink' => $resetLink,
            'expiresIn' => '24 hours',
        ]);
        
        return $this->sendEmail($userEmail, $subject, $htmlBody);
    }
    
    /**
     * Send new user welcome email
     */
    public function sendWelcomeEmail(string $userEmail, string $userName, string $userRole): bool
    {
        $subject = "Welcome to LearnFlow Pro!";
        
        $htmlBody = $this->getEmailTemplate('welcome', [
            'userName' => $userName,
            'userRole' => ucfirst($userRole),
            'loginUrl' => 'https://' . ($_SERVER['HTTP_HOST'] ?? 'learnflow.app') . '/login.html',
        ]);
        
        return $this->sendEmail($userEmail, $subject, $htmlBody);
    }
    
    /**
     * Get email template
     */
    private function getEmailTemplate(string $template, array $vars = []): string
    {
        // Extract variables
        extract($vars, EXTR_SKIP);
        
        ob_start();
        
        switch ($template) {
            case 'submission-confirmation':
                echo $this->renderSubmissionConfirmation($studentName, $assignmentTitle, $courseTitle, $submittedAt);
                break;
            case 'grade-notification':
                echo $this->renderGradeNotification($studentName, $assignmentTitle, $score, $maxScore, $percentage, $feedback, $gradedAt);
                break;
            case 'enrollment-confirmation':
                echo $this->renderEnrollmentConfirmation($studentName, $courseTitle, $instructorName, $enrolledAt);
                break;
            case 'assignment-reminder':
                echo $this->renderAssignmentReminder($studentName, $assignmentTitle, $dueDate);
                break;
            case 'certificate-issued':
                echo $this->renderCertificateIssued($studentName, $courseTitle, $certificateId, $issuedAt);
                break;
            case 'password-reset':
                echo $this->renderPasswordReset($userName, $resetLink, $expiresIn);
                break;
            case 'welcome':
                echo $this->renderWelcome($userName, $userRole, $loginUrl);
                break;
        }
        
        return ob_get_clean() ?: '';
    }
    
    private function renderSubmissionConfirmation($studentName, $assignmentTitle, $courseTitle, $submittedAt): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #4f46e5, #7c3aed); color: white; padding: 30px; border-radius: 8px; text-align: center; }
        .content { padding: 30px; background: #f9fafb; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        .button { background: #4f46e5; color: white; padding: 12px 24px; border-radius: 6px; text-decoration: none; display: inline-block; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Assignment Submitted Successfully! ✓</h1>
        </div>
        <div class="content">
            <p>Hi <strong>$studentName</strong>,</p>
            <p>Your assignment for <strong>$courseTitle</strong> has been successfully submitted:</p>
            <div style="background: white; padding: 15px; border-radius: 6px; margin: 20px 0; border-left: 4px solid #4f46e5;">
                <p><strong>Assignment:</strong> $assignmentTitle</p>
                <p><strong>Submitted:</strong> $submittedAt</p>
            </div>
            <p>Your instructor will review your submission and provide feedback shortly. You'll receive an email notification when your assignment is graded.</p>
            <a href="https://learnflow.app" class="button">View Your Dashboard</a>
        </div>
        <div class="footer">
            <p>LearnFlow Pro - Modern E-Learning Platform</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
    
    private function renderGradeNotification($studentName, $assignmentTitle, $score, $maxScore, $percentage, $feedback, $gradedAt): string
    {
        $gradeColor = $percentage >= 80 ? '#10b981' : ($percentage >= 60 ? '#f59e0b' : '#ef4444');
        
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #4f46e5, #7c3aed); color: white; padding: 30px; border-radius: 8px; text-align: center; }
        .content { padding: 30px; background: #f9fafb; }
        .grade-box { background: white; border-left: 4px solid $gradeColor; padding: 20px; border-radius: 6px; margin: 20px 0; }
        .grade-display { font-size: 32px; font-weight: bold; color: $gradeColor; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Your Assignment Has Been Graded!</h1>
        </div>
        <div class="content">
            <p>Hi <strong>$studentName</strong>,</p>
            <p>Your instructor has graded your assignment:</p>
            <div class="grade-box">
                <p><strong>Assignment:</strong> $assignmentTitle</p>
                <p><strong>Score:</strong> <span class="grade-display">$score / $maxScore</span></p>
                <p><strong>Percentage:</strong> $percentage%</p>
                <p><strong>Graded:</strong> $gradedAt</p>
            </div>
            <h3>Feedback from your instructor:</h3>
            <p>$feedback</p>
        </div>
        <div class="footer">
            <p>LearnFlow Pro - Modern E-Learning Platform</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
    
    private function renderEnrollmentConfirmation($studentName, $courseTitle, $instructorName, $enrolledAt): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #4f46e5, #7c3aed); color: white; padding: 30px; border-radius: 8px; text-align: center; }
        .content { padding: 30px; background: #f9fafb; }
        .button { background: #4f46e5; color: white; padding: 12px 24px; border-radius: 6px; text-decoration: none; display: inline-block; margin: 20px 0; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to $courseTitle! 🎓</h1>
        </div>
        <div class="content">
            <p>Hi <strong>$studentName</strong>,</p>
            <p>Congratulations! You have successfully enrolled in <strong>$courseTitle</strong>.</p>
            <p><strong>Instructor:</strong> $instructorName<br>
            <strong>Enrollment Date:</strong> $enrolledAt</p>
            <p>Your course is now available in your dashboard. Start learning today!</p>
            <a href="https://learnflow.app" class="button">Go to Dashboard</a>
        </div>
        <div class="footer">
            <p>LearnFlow Pro - Modern E-Learning Platform</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
    
    private function renderAssignmentReminder($studentName, $assignmentTitle, $dueDate): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #f59e0b, #ef4444); color: white; padding: 30px; border-radius: 8px; text-align: center; }
        .content { padding: 30px; background: #f9fafb; }
        .button { background: #4f46e5; color: white; padding: 12px 24px; border-radius: 6px; text-decoration: none; display: inline-block; margin: 20px 0; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⏰ Assignment Due Reminder</h1>
        </div>
        <div class="content">
            <p>Hi <strong>$studentName</strong>,</p>
            <p>This is a friendly reminder that your assignment is due soon:</p>
            <div style="background: white; padding: 15px; border-radius: 6px; margin: 20px 0; border-left: 4px solid #f59e0b;">
                <p><strong>Assignment:</strong> $assignmentTitle</p>
                <p><strong>Due Date:</strong> $dueDate</p>
            </div>
            <p>Make sure to submit your work before the due date. Click below to view the assignment details.</p>
            <a href="https://learnflow.app" class="button">Submit Assignment</a>
        </div>
        <div class="footer">
            <p>LearnFlow Pro - Modern E-Learning Platform</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
    
    private function renderCertificateIssued($studentName, $courseTitle, $certificateId, $issuedAt): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #10b981, #06b6d4); color: white; padding: 30px; border-radius: 8px; text-align: center; }
        .content { padding: 30px; background: #f9fafb; }
        .button { background: #10b981; color: white; padding: 12px 24px; border-radius: 6px; text-decoration: none; display: inline-block; margin: 20px 0; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Certificate Earned!</h1>
        </div>
        <div class="content">
            <p>Hi <strong>$studentName</strong>,</p>
            <p>Congratulations on completing <strong>$courseTitle</strong>!</p>
            <p>Your certificate has been issued and is now available in your dashboard.</p>
            <div style="background: white; padding: 15px; border-radius: 6px; margin: 20px 0; border-left: 4px solid #10b981;">
                <p><strong>Course:</strong> $courseTitle</p>
                <p><strong>Certificate ID:</strong> $certificateId</p>
                <p><strong>Issued:</strong> $issuedAt</p>
            </div>
            <p>You can download, share, or verify this certificate using the ID above.</p>
            <a href="https://learnflow.app" class="button">View Certificate</a>
        </div>
        <div class="footer">
            <p>LearnFlow Pro - Modern E-Learning Platform</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
    
    private function renderPasswordReset($userName, $resetLink, $expiresIn): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #4f46e5, #7c3aed); color: white; padding: 30px; border-radius: 8px; text-align: center; }
        .content { padding: 30px; background: #f9fafb; }
        .button { background: #4f46e5; color: white; padding: 12px 24px; border-radius: 6px; text-decoration: none; display: inline-block; margin: 20px 0; }
        .warning { background: #fef3c7; border: 1px solid #fcd34d; padding: 15px; border-radius: 6px; margin: 20px 0; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Reset Your Password</h1>
        </div>
        <div class="content">
            <p>Hi <strong>$userName</strong>,</p>
            <p>We received a request to reset your password. Click the button below to set a new password:</p>
            <a href="$resetLink" class="button">Reset Password</a>
            <div class="warning">
                <strong>⚠️ Important:</strong> This link will expire in $expiresIn. If you did not request this, please ignore this email.
            </div>
        </div>
        <div class="footer">
            <p>LearnFlow Pro - Modern E-Learning Platform</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
    
    private function renderWelcome($userName, $userRole, $loginUrl): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #4f46e5, #7c3aed); color: white; padding: 30px; border-radius: 8px; text-align: center; }
        .content { padding: 30px; background: #f9fafb; }
        .button { background: #4f46e5; color: white; padding: 12px 24px; border-radius: 6px; text-decoration: none; display: inline-block; margin: 20px 0; }
        .features { margin: 20px 0; }
        .feature-item { padding: 10px 0; border-bottom: 1px solid #e5e7eb; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to LearnFlow Pro! 🚀</h1>
        </div>
        <div class="content">
            <p>Hi <strong>$userName</strong>,</p>
            <p>Welcome to LearnFlow Pro! Your account as a <strong>$userRole</strong> has been created successfully.</p>
            <a href="$loginUrl" class="button">Sign In Now</a>
            <h3>What you can do:</h3>
            <div class="features">
                <div class="feature-item">📚 Browse and enroll in courses</div>
                <div class="feature-item">✍️ Submit assignments and get feedback</div>
                <div class="feature-item">📊 Track your progress and analytics</div>
                <div class="feature-item">🎓 Earn certificates upon completion</div>
                <div class="feature-item">💬 Participate in discussions</div>
            </div>
        </div>
        <div class="footer">
            <p>LearnFlow Pro - Modern E-Learning Platform</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
