<?php
/**
 * E-Learning Platform - Quick Start Verification
 * Place this file in the root directory and access it via browser
 * Example: http://localhost/E-learning-Project-Management-System--main/E-learning-Project-Management-System--main/verify-setup.php
 */

declare(strict_types=1);

$checks = [];
$warnings = [];
$errors = [];

echo "<!DOCTYPE html>
<html lang=\"en\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>E-Learning Setup Verification</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .container {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        h1 {
            color: #172033;
            margin-top: 0;
        }
        .section {
            margin-bottom: 30px;
        }
        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #4f46e5;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #dfe7ff;
        }
        .check-item {
            display: flex;
            align-items: center;
            padding: 12px;
            margin-bottom: 8px;
            border-radius: 8px;
            background: #f8fafc;
        }
        .status {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            margin-right: 12px;
            font-weight: 700;
            color: white;
            font-size: 14px;
        }
        .status.ok {
            background: #10b981;
        }
        .status.warning {
            background: #f59e0b;
        }
        .status.error {
            background: #ef4444;
        }
        .check-text {
            flex: 1;
        }
        .check-detail {
            font-size: 12px;
            color: #5d6b82;
            margin-top: 4px;
        }
        .summary {
            background: #eef2ff;
            border-left: 4px solid #4f46e5;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .summary.error {
            background: #fee2e2;
            border-left-color: #dc2626;
        }
        .summary.warning {
            background: #fef3c7;
            border-left-color: #f59e0b;
        }
        code {
            background: #1f2937;
            color: #10b981;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
        }
        .button-group {
            display: flex;
            gap: 12px;
            margin-top: 20px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary {
            background: #4f46e5;
            color: white;
        }
        .btn-primary:hover {
            background: #4338ca;
        }
        .btn-secondary {
            background: #dfe7ff;
            color: #4f46e5;
        }
        .btn-secondary:hover {
            background: #c7d2fe;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 13px;
        }
        table th {
            background: #eef2ff;
            padding: 10px;
            text-align: left;
            font-weight: 700;
            color: #172033;
        }
        table td {
            padding: 10px;
            border-bottom: 1px solid #dfe7ff;
        }
        table tr:hover {
            background: #f8fafc;
        }
    </style>
</head>
<body>
    <div class=\"container\">
        <h1>🚀 E-Learning Platform Setup Verification</h1>";

// ==================== CHECK 1: PHP Version ====================
$php_version = phpversion();
if (version_compare($php_version, '8.1', '>=')) {
    echo "<div class=\"section\">
        <div class=\"section-title\">✅ PHP Version</div>
        <div class=\"check-item\">
            <div class=\"status ok\">✓</div>
            <div class=\"check-text\">
                PHP {$php_version} installed
                <div class=\"check-detail\">Required: 8.1+</div>
            </div>
        </div>
    </div>";
} else {
    echo "<div class=\"section\">
        <div class=\"section-title\">❌ PHP Version</div>
        <div class=\"check-item\">
            <div class=\"status error\">✗</div>
            <div class=\"check-text\">
                PHP {$php_version} is too old
                <div class=\"check-detail\">Required: 8.1+ | Please upgrade PHP</div>
            </div>
        </div>
    </div>";
    $errors[] = "PHP version is too old";
}

// ==================== CHECK 2: Extensions ====================
echo "<div class=\"section\">
    <div class=\"section-title\">📦 Required Extensions</div>";

$extensions = [
    'pdo' => 'PDO Database',
    'pdo_mysql' => 'MySQL PDO Driver',
    'json' => 'JSON Support',
    'mbstring' => 'Multibyte String',
];

foreach ($extensions as $ext => $name) {
    if (extension_loaded($ext)) {
        echo "<div class=\"check-item\">
            <div class=\"status ok\">✓</div>
            <div class=\"check-text\">{$name} ({$ext})</div>
        </div>";
    } else {
        echo "<div class=\"check-item\">
            <div class=\"status error\">✗</div>
            <div class=\"check-text\">{$name} ({$ext}) - MISSING</div>
        </div>";
        $errors[] = "{$name} extension is missing";
    }
}
echo "</div>";

// ==================== CHECK 3: Configuration File ====================
echo "<div class=\"section\">
    <div class=\"section-title\">⚙️ Configuration</div>";

if (file_exists(__DIR__ . '/.env')) {
    echo "<div class=\"check-item\">
        <div class=\"status ok\">✓</div>
        <div class=\"check-text\">
            .env file exists
            <div class=\"check-detail\">Located: " . __DIR__ . "/.env</div>
        </div>
    </div>";
} else {
    echo "<div class=\"check-item\">
        <div class=\"status error\">✗</div>
        <div class=\"check-text\">
            .env file missing
            <div class=\"check-detail\">Copy .env.example to .env and update credentials</div>
        </div>
    </div>";
    $errors[] = ".env file not found";
}

if (file_exists(__DIR__ . '/config.php')) {
    echo "<div class=\"check-item\">
        <div class=\"status ok\">✓</div>
        <div class=\"check-text\">config.php exists</div>
    </div>";
} else {
    echo "<div class=\"check-item\">
        <div class=\"status error\">✗</div>
        <div class=\"check-text\">config.php missing</div>
    </div>";
    $errors[] = "config.php not found";
}

echo "</div>";

// ==================== CHECK 4: Files & Directories ====================
echo "<div class=\"section\">
    <div class=\"section-title\">📁 Files & Directories</div>";

$files = [
    'login.html' => 'Login Page',
    'register.html' => 'Registration Page',
    'course-detail.html' => 'Course Detail Page',
    'submit-assignment.html' => 'Assignment Submission',
    'instructor-grading.html' => 'Instructor Grading',
    'admin-users.html' => 'Admin User Management',
    'api.php' => 'API Endpoints',
    'index.php' => 'Dashboard',
];

foreach ($files as $file => $name) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "<div class=\"check-item\">
            <div class=\"status ok\">✓</div>
            <div class=\"check-text\">{$name} ({$file})</div>
        </div>";
    } else {
        echo "<div class=\"check-item\">
            <div class=\"status error\">✗</div>
            <div class=\"check-text\">{$name} ({$file}) - MISSING</div>
        </div>";
        $errors[] = "{$file} is missing";
    }
}

echo "</div>";

// ==================== CHECK 5: Directories ====================
echo "<div class=\"section\">
    <div class=\"section-title\">📂 Upload Directories</div>";

$dirs = [
    'uploads' => 'Main upload directory',
    'uploads/submissions' => 'Assignment submissions',
    'uploads/resources' => 'Course resources',
    'uploads/avatars' => 'User avatars',
];

foreach ($dirs as $dir => $name) {
    if (is_dir(__DIR__ . '/' . $dir)) {
        $perms = substr(sprintf('%o', fileperms(__DIR__ . '/' . $dir)), -3);
        echo "<div class=\"check-item\">
            <div class=\"status ok\">✓</div>
            <div class=\"check-text\">
                {$name}
                <div class=\"check-detail\">Permissions: {$perms}</div>
            </div>
        </div>";
    } else {
        echo "<div class=\"check-item\">
            <div class=\"status warning\">⚠</div>
            <div class=\"check-text\">
                {$name} - MISSING
                <div class=\"check-detail\">Run: mkdir -p {$dir}</div>
            </div>
        </div>";
        $warnings[] = "{$dir} directory is missing";
    }
}

echo "</div>";

// ==================== CHECK 6: Database Connection ====================
echo "<div class=\"section\">
    <div class=\"section-title\">🗄️ Database Connection</div>";

try {
    if (file_exists(__DIR__ . '/config.php') && file_exists(__DIR__ . '/.env')) {
        require_once __DIR__ . '/config.php';
        require_once __DIR__ . '/db.php';
        
        $pdo = getDatabase();
        
        echo "<div class=\"check-item\">
            <div class=\"status ok\">✓</div>
            <div class=\"check-text\">
                Database connection successful
                <div class=\"check-detail\">Connected to elearning_db</div>
            </div>
        </div>";
        
        // Check tables
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<div class=\"check-item\">
            <div class=\"status ok\">✓</div>
            <div class=\"check-text\">
                Database tables found: " . count($tables) . "
                <div class=\"check-detail\">" . implode(", ", $tables) . "</div>
            </div>
        </div>";
        
    } else {
        echo "<div class=\"check-item\">
            <div class=\"status error\">✗</div>
            <div class=\"check-text\">
                Cannot test database - config/env files missing
            </div>
        </div>";
        $errors[] = "Cannot test database connection";
    }
} catch (Exception $e) {
    echo "<div class=\"check-item\">
        <div class=\"status error\">✗</div>
        <div class=\"check-text\">
            Database connection failed
            <div class=\"check-detail\">" . htmlspecialchars($e->getMessage()) . "</div>
        </div>
    </div>";
    $errors[] = "Database connection failed: " . $e->getMessage();
}

echo "</div>";

// ==================== CHECK 7: Classes ====================
echo "<div class=\"section\">
    <div class=\"section-title\">🔧 Backend Classes</div>";

$classes = [
    'classes/UserRepository.php' => 'UserRepository',
    'classes/CourseRepository.php' => 'CourseRepository',
    'classes/AssignmentRepository.php' => 'AssignmentRepository',
    'classes/FileUploadHandler.php' => 'FileUploadHandler',
    'classes/NotificationService.php' => 'NotificationService',
    'classes/PaymentService.php' => 'PaymentService',
    'classes/Validator.php' => 'Validator',
];

foreach ($classes as $file => $name) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "<div class=\"check-item\">
            <div class=\"status ok\">✓</div>
            <div class=\"check-text\">{$name}</div>
        </div>";
    } else {
        echo "<div class=\"check-item\">
            <div class=\"status error\">✗</div>
            <div class=\"check-text\">{$name} - MISSING</div>
        </div>";
        $errors[] = "{$file} is missing";
    }
}

echo "</div>";

// ==================== SUMMARY ====================
echo "<div class=\"section\">";

if (empty($errors)) {
    echo "<div class=\"summary\">
        <strong>✅ Setup Verified Successfully!</strong><br>
        All required components are in place. You can now test the platform.
    </div>";
    
    echo "<div class=\"button-group\">
        <a href=\"login.html\" class=\"btn btn-primary\">Go to Login Page</a>
        <a href=\"register.html\" class=\"btn btn-secondary\">Go to Registration</a>
    </div>";
} else {
    echo "<div class=\"summary error\">
        <strong>❌ Setup Issues Found</strong><br>
        Please resolve the following issues:
        <ul>";
    foreach ($errors as $error) {
        echo "<li>{$error}</li>";
    }
    echo "</ul>
    </div>";
}

if (!empty($warnings)) {
    echo "<div class=\"summary warning\">
        <strong>⚠️ Warnings</strong><br>
        The following items should be configured:
        <ul>";
    foreach ($warnings as $warning) {
        echo "<li>{$warning}</li>";
    }
    echo "</ul>
    </div>";
}

echo "</div>";

// ==================== NEXT STEPS ====================
echo "<div class=\"section\">
    <div class=\"section-title\">📋 Next Steps</div>
    <table>
        <tr>
            <th>Step</th>
            <th>Action</th>
            <th>Link</th>
        </tr>
        <tr>
            <td>1</td>
            <td>Review Testing Guide</td>
            <td><code>TESTING_GUIDE.md</code></td>
        </tr>
        <tr>
            <td>2</td>
            <td>Test Login Page</td>
            <td><a href=\"login.html\" style=\"color: #4f46e5; text-decoration: none;\">login.html</a></td>
        </tr>
        <tr>
            <td>3</td>
            <td>Test Registration</td>
            <td><a href=\"register.html\" style=\"color: #4f46e5; text-decoration: none;\">register.html</a></td>
        </tr>
        <tr>
            <td>4</td>
            <td>Test Admin Panel</td>
            <td><a href=\"admin-users.html\" style=\"color: #4f46e5; text-decoration: none;\">admin-users.html</a></td>
        </tr>
        <tr>
            <td>5</td>
            <td>View API Documentation</td>
            <td><code>API_DOCUMENTATION.md</code></td>
        </tr>
    </table>
</div>";

echo "</div>
</body>
</html>";
?>
