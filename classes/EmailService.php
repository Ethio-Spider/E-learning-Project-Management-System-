<?php
/**
 * Email Service
 *
 * Handles email sending for verification, password reset, and notifications.
 */

declare(strict_types=1);

class EmailService
{
    private array $config;
    private Logger $logger;

    public function __construct()
    {
        $this->config = [
            'from' => config('EMAIL.from'),
            'from_name' => config('EMAIL.from_name'),
            'smtp_host' => config('EMAIL.smtp_host'),
            'smtp_port' => config('EMAIL.smtp_port'),
            'smtp_user' => config('EMAIL.smtp_user'),
            'smtp_password' => config('EMAIL.smtp_password'),
        ];
        $this->logger = new Logger();
    }

    public function sendVerificationEmail(string $email, string $token, string $userName): bool
    {
        $verificationUrl = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/verify-email?token=' . urlencode($token);

        $subject = 'Verify Your Email Address';
        $body = $this->buildHtmlEmail(
            'Email Verification',
            "Hi {$userName},\n\nPlease verify your email address to complete your registration.",
            'Verify Email',
            $verificationUrl
        );

        return $this->send($email, $subject, $body);
    }

    public function sendPasswordResetEmail(string $email, string $token, string $userName): bool
    {
        $resetUrl = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/reset-password?token=' . urlencode($token);

        $subject = 'Password Reset Request';
        $body = $this->buildHtmlEmail(
            'Password Reset',
            "Hi {$userName},\n\nYou requested a password reset. Click the button below to set a new password.",
            'Reset Password',
            $resetUrl
        );

        return $this->send($email, $subject, $body);
    }

    public function send2FACode(string $email, string $code): bool
    {
        $subject = '2FA Code';
        $body = "<p>Your 2FA code is: <strong>{$code}</strong></p><p>This code expires in 5 minutes.</p>";

        return $this->send($email, $subject, $body);
    }

    public function sendWelcomeEmail(string $email, string $userName): bool
    {
        $subject = 'Welcome to ' . config('name');
        $body = $this->buildHtmlEmail(
            'Welcome!',
            "Hi {$userName},\n\nWelcome to " . config('name') . "! Start learning today.",
            'Get Started',
            'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/dashboard'
        );

        return $this->send($email, $subject, $body);
    }

    private function buildHtmlEmail(string $title, string $message, string $buttonText, string $buttonUrl): string
    {
        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #050b17; color: #e5eefc; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .button { display: inline-block; padding: 12px 24px; background: #6d7cff; color: white; text-decoration: none; border-radius: 6px; margin: 20px 0; }
                .footer { text-align: center; font-size: 0.9em; color: #666; padding-top: 20px; border-top: 1px solid #ddd; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>{$title}</h1>
                </div>
                <div class="content">
                    <p>{$message}</p>
                    <p><a href="{$buttonUrl}" class="button">{$buttonText}</a></p>
                </div>
                <div class="footer">
                    <p>© 2026 E-Learning Platform. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        HTML;
    }

    private function send(string $to, string $subject, string $body): bool
    {
        try {
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
            $headers .= "From: {$this->config['from_name']} <{$this->config['from']}>" . "\r\n";

            $result = mail($to, $subject, $body, $headers);

            if ($result) {
                $this->logger->log('Email sent to ' . $to, 'info');
            } else {
                $this->logger->log('Failed to send email to ' . $to, 'error');
            }

            return $result;
        } catch (Exception $e) {
            $this->logger->log('Email error: ' . $e->getMessage(), 'error');
            return false;
        }
    }
}
