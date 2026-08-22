<?php
/**
 * Automated database backup job.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config.php';

$backupDir = config('BACKUP.backup_dir', __DIR__ . '/../backups/');
$database = config('DATABASE');
$now = date('Y-m-d_H-i-s');
$filename = $backupDir . '/backup_' . $now . '.sql';

if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

$command = sprintf(
    'mysqldump --host=%s --port=%d --user=%s --password=%s %s > %s',
    escapeshellarg($database['host']),
    (int) $database['port'],
    escapeshellarg($database['user']),
    escapeshellarg($database['password']),
    escapeshellarg($database['name']),
    escapeshellarg($filename)
);

$result = shell_exec($command);
$exitCode = $result === null ? 1 : 0;

if ($exitCode !== 0 || !is_file($filename)) {
    fwrite(STDERR, "Backup failed. Ensure mysqldump is installed and DB credentials are valid.\n");
    exit(1);
}

$days = (int) config('BACKUP.retention_days', 30);
foreach (glob($backupDir . '/backup_*.sql') as $file) {
    $age = time() - filemtime($file);
    if ($age > ($days * 86400)) {
        unlink($file);
    }
}

echo "Backup created: {$filename}\n";
