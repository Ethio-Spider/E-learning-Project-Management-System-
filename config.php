<?php
/**
 * E-Learning Management System - Configuration
 * 
 * Handles all configuration settings for the application
 * Uses environment variables with fallback defaults
 */

declare(strict_types=1);

// Define application constants
define('APP_NAME', 'E-Learning Resource Repository');
define('APP_VERSION', '2.0.0');
define('APP_ROOT', dirname(__FILE__));
define('APP_DEBUG', filter_var(getenv('APP_DEBUG') ?: 'false', FILTER_VALIDATE_BOOLEAN));

// Database Configuration
const DATABASE = [
    'host' => getenv('DB_HOST') ?: '127.0.0.1',
    'port' => (int)(getenv('DB_PORT') ?: '3306'),
    'name' => getenv('DB_NAME') ?: 'elearning_db',
    'user' => getenv('DB_USER') ?: 'root',
    'password' => getenv('DB_PASSWORD') ?: '',
    'charset' => 'utf8mb4',
    'timezone' => getenv('DB_TIMEZONE') ?: 'UTC',
];

// API Configuration
const API = [
    'version' => '1.0',
    'base_path' => '/api/',
    'rate_limit' => (int)(getenv('RATE_LIMIT') ?: '100'),
    'timeout' => (int)(getenv('API_TIMEOUT') ?: '30'),
];

// Security Configuration
const SECURITY = [
    'enable_cors' => filter_var(getenv('ENABLE_CORS') ?: 'true', FILTER_VALIDATE_BOOLEAN),
    'cors_origins' => explode(',', getenv('CORS_ORIGINS') ?: '*'),
    'enable_csrf' => filter_var(getenv('ENABLE_CSRF') ?: 'true', FILTER_VALIDATE_BOOLEAN),
    'session_timeout' => (int)(getenv('SESSION_TIMEOUT') ?: '3600'),
];

// File Upload Configuration
const UPLOAD = [
    'max_size' => (int)(getenv('MAX_UPLOAD_SIZE') ?: '52428800'), // 50MB
    'allowed_types' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'],
    'upload_dir' => APP_ROOT . '/uploads/',
];

// Logging Configuration
const LOGGING = [
    'enable_logs' => filter_var(getenv('ENABLE_LOGS') ?: 'true', FILTER_VALIDATE_BOOLEAN),
    'log_dir' => APP_ROOT . '/logs/',
    'log_level' => getenv('LOG_LEVEL') ?: 'INFO', // ERROR, WARNING, INFO, DEBUG
];

// Email Configuration (for notifications)
const EMAIL = [
    'from' => getenv('EMAIL_FROM') ?: 'noreply@elearning.local',
    'from_name' => getenv('EMAIL_FROM_NAME') ?: APP_NAME,
    'smtp_host' => getenv('SMTP_HOST') ?: 'localhost',
    'smtp_port' => (int)(getenv('SMTP_PORT') ?: '587'),
    'smtp_user' => getenv('SMTP_USER') ?: '',
    'smtp_password' => getenv('SMTP_PASSWORD') ?: '',
];

// Pagination Configuration
const PAGINATION = [
    'default_limit' => (int)(getenv('DEFAULT_LIMIT') ?: '20'),
    'max_limit' => (int)(getenv('MAX_LIMIT') ?: '100'),
];

// Validation Configuration
const VALIDATION = [
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
];

// Ensure required directories exist
if (!is_dir(LOGGING['log_dir']) && LOGGING['enable_logs']) {
    mkdir(LOGGING['log_dir'], 0755, true);
}

if (!is_dir(UPLOAD['upload_dir'])) {
    mkdir(UPLOAD['upload_dir'], 0755, true);
}

/**
 * Get a configuration value
 * 
 * @param string $key Configuration key (e.g., 'DATABASE.host')
 * @param mixed $default Default value if key doesn't exist
 * @return mixed Configuration value
 */
function config(string $key, mixed $default = null): mixed
{
    $parts = explode('.', $key);
    $const = strtoupper(array_shift($parts));
    
    if (!defined($const)) {
        return $default;
    }
    
    $value = constant($const);
    
    foreach ($parts as $part) {
        if (is_array($value) && isset($value[$part])) {
            $value = $value[$part];
        } else {
            return $default;
        }
    }
    
    return $value;
}

/**
 * Check if application is in debug mode
 */
function isDebug(): bool
{
    return APP_DEBUG;
}

/**
 * Get application root path
 */
function appPath(string $path = ''): string
{
    return APP_ROOT . ($path ? '/' . ltrim($path, '/') : '');
}

return [
    'name' => APP_NAME,
    'version' => APP_VERSION,
    'debug' => APP_DEBUG,
];
