<?php
declare(strict_types=1);

// Load the existing domain implementations before the namespaced application layer.
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

$legacyClasses = [
    'CourseRepository',
    'EnrollmentRepository',
    'AssignmentRepository',
    'ProgressRepository',
    'CertificateRepository',
    'UserRepository',
    'FileUploadHandler',
    'NotificationService',
    'PaymentService',
    'EmailVerificationRepository',
    'PasswordResetRepository',
    'Logger',
    'EmailService',
    'AuditLogger',
    'RateLimiter',
    'TwoFactorService',
    'ErrorMonitor',
    'Validator',
    'Response',
];

foreach ($legacyClasses as $class) {
    require_once __DIR__ . '/../classes/' . $class . '.php';
}

require_once __DIR__ . '/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/Middleware/RoleMiddleware.php';
require_once __DIR__ . '/Middleware/RateLimitMiddleware.php';
require_once __DIR__ . '/Repositories/UserRepository.php';
require_once __DIR__ . '/Repositories/CourseRepository.php';
require_once __DIR__ . '/Services/AuthService.php';
require_once __DIR__ . '/Services/PaymentService.php';
require_once __DIR__ . '/Services/NotificationService.php';
require_once __DIR__ . '/Controllers/AuthController.php';
require_once __DIR__ . '/Controllers/CourseController.php';
require_once __DIR__ . '/Controllers/AssignmentController.php';
require_once __DIR__ . '/Controllers/UserController.php';
require_once __DIR__ . '/Controllers/AdminController.php';

function appControllers(PDO $pdo): array
{
    return [
        'auth' => new \App\Controllers\AuthController(new \App\Services\AuthService(new \App\Repositories\UserRepository($pdo))),
        'courses' => new \App\Controllers\CourseController(new \App\Repositories\CourseRepository($pdo)),
        'assignments' => new \App\Controllers\AssignmentController(new \AssignmentRepository($pdo)),
        'users' => new \App\Controllers\UserController(new \App\Repositories\UserRepository($pdo)),
        'admin' => new \App\Controllers\AdminController(
            new \App\Repositories\UserRepository($pdo),
            new \App\Repositories\CourseRepository($pdo)
        ),
    ];
}
