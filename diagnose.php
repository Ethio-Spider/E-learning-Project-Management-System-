<?php
declare(strict_types=1);

header('Content-Type: text/plain; charset=utf-8');

echo "E-Learning Project Diagnostic\n";
echo "============================\n";
echo "PHP: " . PHP_VERSION . "\n";
echo "PDO: " . (extension_loaded('pdo') ? 'OK' : 'MISSING') . "\n";
echo "PDO MySQL: " . (extension_loaded('pdo_mysql') ? 'OK' : 'MISSING') . "\n\n";

try {
    require_once __DIR__ . '/db.php';
    echo "Database connection: OK\n";

    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    $required = [
        'users', 'projects', 'resources', 'assignments', 'submissions',
        'enrollments', 'activity_logs', 'audit_logs', 'rate_limits',
        'email_verifications', 'password_resets', 'payments', 'certificates'
    ];

    foreach ($required as $table) {
        echo sprintf("%-24s %s\n", $table, in_array($table, $tables, true) ? 'OK' : 'MISSING');
    }

    if (in_array('users', $tables, true)) {
        $count = (int)$pdo->query('SELECT COUNT(*) FROM users WHERE deleted_at IS NULL')->fetchColumn();
        echo "\nUsers: {$count}\n";
    }

    if (in_array('projects', $tables, true)) {
        $count = (int)$pdo->query('SELECT COUNT(*) FROM projects WHERE deleted_at IS NULL')->fetchColumn();
        echo "Courses: {$count}\n";
    }

    echo "\nNext checks:\n";
    echo "1. Open http://localhost:8000/api.php?action=csrf-token\n";
    echo "2. Open http://localhost:8000/login.html\n";
    echo "3. Check the PHP terminal if an API error appears.\n";
} catch (Throwable $e) {
    http_response_code(500);
    echo "\nDATABASE ERROR\n";
    echo $e->getMessage() . "\n";
}
