<?php
/**
 * Email Verification Repository
 *
 * Manages email verification tokens and verification status.
 */

declare(strict_types=1);

class EmailVerificationRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function createVerificationToken(int $userId, string $email): string
    {
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + config('EMAIL.verification_expiry', 86400));

        $stmt = $this->pdo->prepare(
            'INSERT INTO email_verifications (user_id, email, verification_token, expires_at)
             VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$userId, $email, $token, $expiresAt]);

        return $token;
    }

    public function verifyEmail(string $token): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT user_id FROM email_verifications
             WHERE verification_token = ? AND verified_at IS NULL AND expires_at > NOW()'
        );
        $stmt->execute([$token]);
        $verification = $stmt->fetch();

        if (!$verification) {
            return false;
        }

        $userId = (int) $verification['user_id'];

        // Mark token as verified
        $updateVerification = $this->pdo->prepare(
            'UPDATE email_verifications SET verified_at = NOW() WHERE verification_token = ?'
        );
        $updateVerification->execute([$token]);

        // Update user record
        $updateUser = $this->pdo->prepare(
            'UPDATE users SET email_verified = TRUE, email_verified_at = NOW() WHERE id = ?'
        );
        $updateUser->execute([$userId]);

        return true;
    }

    public function isEmailVerified(int $userId): bool
    {
        $stmt = $this->pdo->prepare('SELECT email_verified FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $result = $stmt->fetch();

        return $result && (bool) $result['email_verified'];
    }

    public function getVerificationToken(int $userId): ?string
    {
        $stmt = $this->pdo->prepare(
            'SELECT verification_token FROM email_verifications
             WHERE user_id = ? AND verified_at IS NULL AND expires_at > NOW()
             ORDER BY created_at DESC LIMIT 1'
        );
        $stmt->execute([$userId]);
        $result = $stmt->fetch();

        return $result ? $result['verification_token'] : null;
    }

    public function cleanupExpiredTokens(): int
    {
        $stmt = $this->pdo->prepare('DELETE FROM email_verifications WHERE expires_at < NOW()');
        $stmt->execute();
        return $stmt->rowCount();
    }
}
