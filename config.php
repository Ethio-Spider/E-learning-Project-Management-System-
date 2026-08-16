<?php
/**
 * E-Learning Management System - Configuration
 *
 * Runtime configuration values for the application.
 */

declare(strict_types=1);

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
        'version' => '1.0',
        'base_path' => '/api/',
        'rate_limit' => (int) (getenv('RATE_LIMIT') ?: '100'),
        'timeout' => (int) (getenv('API_TIMEOUT') ?: '30'),
    ],
    'SECURITY' => [
        'enable_cors' => filter_var(getenv('ENABLE_CORS') ?: 'true', FILTER_VALIDATE_BOOLEAN),
        'cors_origins' => array_filter(array_map('trim', explode(',', getenv('CORS_ORIGINS') ?: '*'))),
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
    ],
    'PAGINATION' => [
        'default_limit' => (int) (getenv('DEFAULT_LIMIT') ?: '20'),
        'max_limit' => (int) (getenv('MAX_LIMIT') ?: '100'),
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
