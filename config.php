<?php
declare(strict_types=1);
$envFile = __DIR__ . '/.env';

if (is_file($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);

        $key = trim($key);
        $value = trim($value);

        if ($key !== '') {
            putenv($key . '=' . $value);
        }
    }
}
/**
 * E-Learning Management System - Configuration
 *
 * Runtime configuration values for the application.
 */



define('APP_NAME', 'E-Learning Resource Repository');
define('APP_VERSION', '2.0.0');
define('APP_ROOT', __DIR__);
define('APP_DEBUG', filter_var(getenv('APP_DEBUG') ?: 'false', FILTER_VALIDATE_BOOLEAN));

$_APP_CONFIG = [
    'DATABASE' => [
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => (int) (getenv('DB_PORT') ?: '3306'),
        'name' => getenv('DB_NAME') ?: 'elearning_db',
        'user' => getenv('DB_USER') ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: '',
        'charset' => 'utf8mb4',
        'timezone' => getenv('DB_TIMEZONE') ?: 'UTC',
    ],
    'API' => [
        'version' => '2.0',
        'base_path' => '/api/',
        'timeout' => (int) (getenv('API_TIMEOUT') ?: '30'),
    ],
    'SECURITY' => [
        'enable_cors' => filter_var(getenv('ENABLE_CORS') ?: 'true', FILTER_VALIDATE_BOOLEAN),
        'cors_origins' => array_filter(array_map('trim', explode(',', getenv('CORS_ORIGINS') ?: 'http://localhost:3000,http://127.0.0.1:8000,http://127.0.0.1:8080'))),
        'enable_csrf' => filter_var(getenv('ENABLE_CSRF') ?: 'true', FILTER_VALIDATE_BOOLEAN),
        'session_timeout' => (int) (getenv('SESSION_TIMEOUT') ?: '3600'),
    ],
    'UPLOAD' => [
        'max_size' => (int) (getenv('MAX_UPLOAD_SIZE') ?: '52428800'),
        'allowed_types' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'],
        'upload_dir' => APP_ROOT . '/uploads/',
    ],
    'LOGGING' => [
        'enable_logs' => filter_var(getenv('ENABLE_LOGS') ?: 'true', FILTER_VALIDATE_BOOLEAN),
        'log_dir' => APP_ROOT . '/logs/',
        'log_level' => getenv('LOG_LEVEL') ?: 'INFO',
    ],
    'EMAIL' => [
        'from' => getenv('EMAIL_FROM') ?: 'noreply@elearning.local',
        'from_name' => getenv('EMAIL_FROM_NAME') ?: APP_NAME,
        'smtp_host' => getenv('SMTP_HOST') ?: 'localhost',
        'smtp_port' => (int) (getenv('SMTP_PORT') ?: '587'),
        'smtp_user' => getenv('SMTP_USER') ?: '',
        'smtp_password' => getenv('SMTP_PASSWORD') ?: '',
        'verification_enabled' => filter_var(getenv('EMAIL_VERIFICATION') ?: 'true', FILTER_VALIDATE_BOOLEAN),
        'verification_expiry' => (int) (getenv('EMAIL_VERIFICATION_EXPIRY') ?: '86400'),
    ],
    'PAYMENT' => [
        'stripe_key' => getenv('STRIPE_SECRET_KEY') ?: '',
        'stripe_public_key' => getenv('STRIPE_PUBLIC_KEY') ?: '',
        'paypal_client_id' => getenv('PAYPAL_CLIENT_ID') ?: '',
        'paypal_secret' => getenv('PAYPAL_SECRET') ?: '',
        'paypal_mode' => getenv('PAYPAL_MODE') ?: 'sandbox',
        'webhook_secret' => getenv('PAYMENT_WEBHOOK_SECRET') ?: '',
    ],
    '2FA' => [
        'enabled' => filter_var(getenv('TWO_FA_ENABLED') ?: 'true', FILTER_VALIDATE_BOOLEAN),
        'totp_window' => (int) (getenv('TOTP_WINDOW') ?: '1'),
        'backup_codes_count' => (int) (getenv('BACKUP_CODES_COUNT') ?: '10'),
    ],
    'RATE_LIMIT' => [
        'enabled' => filter_var(getenv('RATE_LIMIT_ENABLED') ?: 'true', FILTER_VALIDATE_BOOLEAN),
        'requests_per_minute' => (int) (getenv('RATE_LIMIT_RPM') ?: '60'),
        'storage' => getenv('RATE_LIMIT_STORAGE') ?: 'file',
    ],
    'AUDIT' => [
        'enabled' => filter_var(getenv('AUDIT_LOGGING') ?: 'true', FILTER_VALIDATE_BOOLEAN),
        'log_sensitive' => filter_var(getenv('AUDIT_LOG_SENSITIVE') ?: 'false', FILTER_VALIDATE_BOOLEAN),
    ],
    'MONITORING' => [
        'sentry_dsn' => getenv('SENTRY_DSN') ?: '',
        'error_tracking' => filter_var(getenv('ERROR_TRACKING') ?: 'false', FILTER_VALIDATE_BOOLEAN),
    ],
    'PAGINATION' => [
        'default_limit' => (int) (getenv('DEFAULT_LIMIT') ?: '20'),
        'max_limit' => (int) (getenv('MAX_LIMIT') ?: '100'),
    ],
    'BACKUP' => [
        'enabled' => filter_var(getenv('BACKUP_ENABLED') ?: 'true', FILTER_VALIDATE_BOOLEAN),
        'backup_dir' => getenv('BACKUP_DIR') ?: APP_ROOT . '/backups/',
        'retention_days' => (int) (getenv('BACKUP_RETENTION') ?: '30'),
        'schedule_frequency' => getenv('BACKUP_FREQUENCY') ?: 'daily',
    ],
    'VALIDATION' => [
        'title_min' => 3,
        'title_max' => 255,
        'description_min' => 10,
        'description_max' => 5000,
        'category_max' => 100,
        'instructor_max' => 255,
        'duration_max' => 100,
        'url_max' => 1000,
        'name_max' => 255,
        'email_max' => 255,
    ],
];

$GLOBALS['_APP_CONFIG'] = $_APP_CONFIG;

if (!is_dir($_APP_CONFIG['LOGGING']['log_dir']) && $_APP_CONFIG['LOGGING']['enable_logs']) {
    mkdir($_APP_CONFIG['LOGGING']['log_dir'], 0755, true);
}

if (!is_dir($_APP_CONFIG['UPLOAD']['upload_dir'])) {
    mkdir($_APP_CONFIG['UPLOAD']['upload_dir'], 0755, true);
}

if (!is_dir($_APP_CONFIG['BACKUP']['backup_dir']) && $_APP_CONFIG['BACKUP']['enabled']) {
    mkdir($_APP_CONFIG['BACKUP']['backup_dir'], 0755, true);
}

function config(string $key, mixed $default = null): mixed
{
    $config = $GLOBALS['_APP_CONFIG'] ?? [];
    $parts = explode('.', $key);

    foreach ($parts as $part) {
        if (!is_array($config) || !array_key_exists($part, $config)) {
            if (defined($part)) {
                return constant($part);
            }
            return $default;
        }
        $config = $config[$part];
    }

    return $config;
}

function isDebug(): bool
{
    return APP_DEBUG;
}

function appPath(string $path = ''): string
{
    return APP_ROOT . ($path ? '/' . ltrim($path, '/') : '');
}

return [
    'name' => APP_NAME,
    'version' => APP_VERSION,
    'debug' => APP_DEBUG,
];
