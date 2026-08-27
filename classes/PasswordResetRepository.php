<?php
/**
 * Password Reset Repository
 *
 * Manages password reset tokens.
 */

declare(strict_types=1);

class PasswordResetRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function createResetToken(int $userId): string
    {
        // Delete old unused tokens
        $delete = $this->pdo->prepare('DELETE FROM password_resets WHERE user_id = ? AND used_at IS NULL');
        $delete->execute([$userId]);

        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + 3600);

        $stmt = $this->pdo->prepare(
            'INSERT INTO password_resets (user_id, reset_token, expires_at) VALUES (?, ?, ?)'
        );
        $stmt->execute([$userId, $token, $expiresAt]);

        return $token;
    }

    public function validateResetToken(string $token): ?int
    {
        $stmt = $this->pdo->prepare(
            'SELECT user_id FROM password_resets
             WHERE reset_token = ? AND used_at IS NULL AND expires_at > NOW()'
        );
        $stmt->execute([$token]);
        $result = $stmt->fetch();

        return $result ? (int) $result['user_id'] : null;
    }

    public function resetPassword(string $token, string $passwordHash): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT user_id FROM password_resets
             WHERE reset_token = ? AND used_at IS NULL AND expires_at > NOW()'
        );
        $stmt->execute([$token]);
        $reset = $stmt->fetch();

        if (!$reset) {
            return false;
        }

        $userId = (int) $reset['user_id'];

        // Update password and mark token as used
        $updateReset = $this->pdo->prepare('UPDATE password_resets SET used_at = NOW() WHERE reset_token = ?');
        $updateReset->execute([$token]);

        $updateUser = $this->pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
        return $updateUser->execute([$passwordHash, $userId]);
    }

    public function cleanupExpiredTokens(): int
    {
        $stmt = $this->pdo->prepare('DELETE FROM password_resets WHERE expires_at < NOW()');
        $stmt->execute();
        return $stmt->rowCount();
    }
}
