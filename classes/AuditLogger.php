<?php
/**
 * AuditLogger - stores security and operational actions for traceability.
 */

declare(strict_types=1);

class AuditLogger
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function log(
        string $action,
        ?int $userId = null,
        ?string $entityType = null,
        ?int $entityId = null,
        array $oldValues = [],
        array $newValues = [],
        ?string $ipAddress = null,
        ?string $userAgent = null,
        string $status = 'success',
        ?string $details = null
    ): bool {
        $sql = 'INSERT INTO audit_logs (user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent, status, details)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            $userId,
            $action,
            $entityType,
            $entityId,
            $oldValues === [] ? null : json_encode($oldValues, JSON_THROW_ON_ERROR),
            $newValues === [] ? null : json_encode($newValues, JSON_THROW_ON_ERROR),
            $ipAddress ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            $userAgent ?? ($_SERVER['HTTP_USER_AGENT'] ?? ''),
            $status,
            $details,
        ]);
    }

    public function getRecent(int $limit = 50): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM audit_logs ORDER BY created_at DESC LIMIT ?');
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}
