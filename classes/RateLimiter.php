<?php
/**
 * RateLimiter - simple in-memory/file-backed request throttling.
 */

declare(strict_types=1);

class RateLimiter
{
    private ?PDO $pdo;
    private string $storagePath;

    public function __construct(?PDO $pdo = null, ?string $storagePath = null)
    {
        $this->pdo = $pdo;
        $this->storagePath = $storagePath ?? (defined('APP_ROOT') ? APP_ROOT : __DIR__ . '/../') . '/logs/rate_limits.json';
    }

    public function allow(string $identifier, string $endpoint, int $limit = 60, int $windowSeconds = 60): array
    {
        if ($this->pdo instanceof PDO) {
            $driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
            if ($driver === 'mysql') {
                return $this->allowMysql($identifier, $endpoint, $limit, $windowSeconds);
            }
        }

        return $this->allowFile($identifier, $endpoint, $limit, $windowSeconds);
    }

    private function allowFile(string $identifier, string $endpoint, int $limit, int $windowSeconds): array
    {
        $now = time();
        $windowStart = $now - $windowSeconds;
        $key = md5($identifier . '|' . $endpoint);
        $file = $this->storagePath;
        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $entries = is_file($file) ? json_decode((string) file_get_contents($file), true) : [];
        $entries = is_array($entries) ? $entries : [];

        $valid = [];
        foreach (($entries[$key] ?? []) as $timestamp) {
            $value = (int) $timestamp;
            if ($value >= $windowStart) {
                $valid[] = $value;
            }
        }

        if (count($valid) >= $limit) {
            return ['allowed' => false, 'remaining' => 0, 'reset_in' => max(0, $windowSeconds - ($now - min($valid)))];
        }

        $valid[] = $now;
        $entries[$key] = $valid;
        file_put_contents($file, json_encode($entries, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);

        return ['allowed' => true, 'remaining' => max(0, $limit - count($valid)), 'reset_in' => $windowSeconds];
    }

    private function allowMysql(string $identifier, string $endpoint, int $limit, int $windowSeconds): array
    {
        $now = time();
        $stmt = $this->pdo->prepare('SELECT request_count, window_end FROM rate_limits WHERE identifier = ? AND endpoint = ? AND window_end > NOW() ORDER BY created_at DESC LIMIT 1');
        $stmt->execute([$identifier, $endpoint]);
        $row = $stmt->fetch();

        if ($row) {
            $count = (int) ($row['request_count'] ?? 0);
            if ($count >= $limit) {
                return ['allowed' => false, 'remaining' => 0, 'reset_in' => max(0, strtotime((string) $row['window_end']) - $now)];
            }

            $update = $this->pdo->prepare('UPDATE rate_limits SET request_count = request_count + 1, window_end = DATE_ADD(NOW(), INTERVAL ? SECOND) WHERE identifier = ? AND endpoint = ?');
            $update->execute([$windowSeconds, $identifier, $endpoint]);

            return ['allowed' => true, 'remaining' => $limit - ($count + 1), 'reset_in' => $windowSeconds];
        }

        $insert = $this->pdo->prepare('INSERT INTO rate_limits (identifier, endpoint, request_count, window_start, window_end) VALUES (?, ?, 1, NOW(), DATE_ADD(NOW(), INTERVAL ? SECOND))');
        $insert->execute([$identifier, $endpoint, $windowSeconds]);

        return ['allowed' => true, 'remaining' => $limit - 1, 'reset_in' => $windowSeconds];
    }
}
