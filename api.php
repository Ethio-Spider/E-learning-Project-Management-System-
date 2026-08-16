<?php
/**
 * Complete e-learning and project management API
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/classes/CourseRepository.php';
require_once __DIR__ . '/classes/EnrollmentRepository.php';
require_once __DIR__ . '/classes/AssignmentRepository.php';
require_once __DIR__ . '/classes/ProgressRepository.php';
require_once __DIR__ . '/classes/CertificateRepository.php';
require_once __DIR__ . '/classes/UserRepository.php';
require_once __DIR__ . '/classes/FileUploadHandler.php';
require_once __DIR__ . '/classes/NotificationService.php';
require_once __DIR__ . '/classes/PaymentService.php';
require_once __DIR__ . '/classes/Validator.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept, Authorization');

session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
    'use_strict_mode' => true,
]);

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

function apiResponse(bool $success, string $message, mixed $data = null, int $statusCode = 200): never
{
    http_response_code($statusCode);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function getDemoCredentials(): array
{
    return [
        'student' => ['email' => 'student@learnflow.app', 'password' => 'student123'],
        'instructor' => ['email' => 'instructor@learnflow.app', 'password' => 'instructor123'],
        'admin' => ['email' => 'admin@learnflow.app', 'password' => 'admin123'],
    ];
}

function currentUser(): ?array
{
    if (empty($_SESSION['user'])) {
        return null;
    }

    $user = $_SESSION['user'];
    return is_array($user) ? $user : null;
}

function requireAuth(): array
{
    $user = currentUser();
    if ($user === null) {
        apiResponse(false, 'Authentication required.', null, 401);
    }

    return $user;
}

function getMockData(): array
{
    return [
        'student' => [
            'stats' => [
                ['label' => 'Enrolled courses', 'value' => '7', 'trend' => '+2 this month'],
                ['label' => 'Completion rate', 'value' => '84%', 'trend' => '+9%'],
                ['label' => 'Assignments due', 'value' => '3', 'trend' => '1 urgent'],
                ['label' => 'Certificates', 'value' => '2', 'trend' => '1 ready'],
            ],
            'courses' => [
                ['id' => 101, 'title' => 'Product Design Bootcamp', 'category' => 'UX', 'level' => 'Intermediate', 'progress' => 76, 'instructor' => 'Ari Chen', 'nextLesson' => 'Prototype testing'],
                ['id' => 102, 'title' => 'AI for Everyday Learning', 'category' => 'AI', 'level' => 'Beginner', 'progress' => 61, 'instructor' => 'Nina Patel', 'nextLesson' => 'Prompt engineering'],
                ['id' => 103, 'title' => 'Full-Stack Launch Lab', 'category' => 'Development', 'level' => 'Advanced', 'progress' => 48, 'instructor' => 'Joel Brooks', 'nextLesson' => 'Deployment pipeline'],
            ],
            'assignments' => [
                ['id' => 1, 'title' => 'Landing page critique', 'course' => 'Product Design Bootcamp', 'due' => '2026-08-18', 'status' => 'In progress'],
                ['id' => 2, 'title' => 'AI assistant prompt pack', 'course' => 'AI for Everyday Learning', 'due' => '2026-08-21', 'status' => 'Pending'],
                ['id' => 3, 'title' => 'API integration task', 'course' => 'Full-Stack Launch Lab', 'due' => '2026-08-23', 'status' => 'Review'],
            ],
            'schedule' => [
                ['day' => 'Mon', 'title' => 'Live Q&A', 'time' => '10:00 AM', 'type' => 'Mentor session'],
                ['day' => 'Tue', 'title' => 'Sprint review', 'time' => '2:30 PM', 'type' => 'Project milestone'],
                ['day' => 'Thu', 'title' => 'Capstone clinic', 'time' => '11:00 AM', 'type' => 'Workshop'],
            ],
            'notifications' => [
                ['id' => 1, 'text' => 'Feedback posted on your product critique submission', 'time' => '8 mins ago'],
                ['id' => 2, 'text' => 'New webinar: AI learning assistant best practices', 'time' => '1 hour ago'],
                ['id' => 3, 'text' => 'Classroom milestone unlocked: UX research sprint', 'time' => '2 hours ago'],
            ],
            'forum' => [
                ['id' => 1, 'topic' => 'How do you structure a research plan?', 'replies' => 18, 'author' => 'Maya'],
                ['id' => 2, 'topic' => 'Best way to document project retrospectives?', 'replies' => 9, 'author' => 'Sam'],
            ],
            'certificates' => [
                ['id' => 1, 'name' => 'UI Foundations Certificate', 'status' => 'Issued'],
                ['id' => 2, 'name' => 'Prompt Design Fundamentals', 'status' => 'In review'],
            ],
            'analytics' => [
                'weeklyMinutes' => 480,
                'streak' => 12,
                'learningScore' => 91,
                'focusAreas' => ['Product thinking', 'Frontend', 'Research'],
            ],
        ],
        'instructor' => [
            'stats' => [
                ['label' => 'Active cohorts', 'value' => '5', 'trend' => '+1'],
                ['label' => 'Assignments graded', 'value' => '128', 'trend' => '+18%'],
                ['label' => 'Students at-risk', 'value' => '7', 'trend' => 'down 2'],
                ['label' => 'Avg. satisfaction', 'value' => '4.8/5', 'trend' => '+0.2'],
            ],
            'courses' => [
                ['id' => 201, 'title' => 'Product Design Bootcamp', 'students' => 42, 'completion' => 72, 'status' => 'Live'],
                ['id' => 202, 'title' => 'AI for Everyday Learning', 'students' => 31, 'completion' => 65, 'status' => 'Live'],
            ],
            'assignments' => [
                ['id' => 12, 'title' => 'Prototype critique sheets', 'course' => 'Product Design Bootcamp', 'submitted' => 18, 'pending' => 7],
                ['id' => 13, 'title' => 'Prompt pack submission', 'course' => 'AI for Everyday Learning', 'submitted' => 21, 'pending' => 4],
            ],
            'schedule' => [
                ['day' => 'Wed', 'title' => 'Team office hours', 'time' => '3:00 PM', 'type' => 'Mentoring'],
                ['day' => 'Fri', 'title' => 'Portfolio review', 'time' => '9:30 AM', 'type' => 'Feedback session'],
            ],
            'notifications' => [
                ['id' => 1, 'text' => 'Two students requested deadline extension', 'time' => '20 mins ago'],
                ['id' => 2, 'text' => 'New discussion thread: rubric clarity', 'time' => '1 day ago'],
            ],
            'forum' => [
                ['id' => 1, 'topic' => 'Assignment rubric expectations', 'replies' => 27, 'author' => 'Instructor team'],
                ['id' => 2, 'topic' => 'Portfolio presentation tips', 'replies' => 14, 'author' => 'Mentor lab'],
            ],
            'analytics' => [
                'weeklyMinutes' => 610,
                'engagement' => 88,
                'completionTrend' => '+7%',
                'focusAreas' => ['Engagement', 'Assessment', 'Retention'],
            ],
        ],
        'admin' => [
            'stats' => [
                ['label' => 'Total learners', 'value' => '1,284', 'trend' => '+96'],
                ['label' => 'Revenue', 'value' => '$48.2K', 'trend' => '+12.5%'],
                ['label' => 'Open support tickets', 'value' => '19', 'trend' => 'down 4'],
                ['label' => 'Active cohorts', 'value' => '12', 'trend' => '+2'],
            ],
            'courses' => [
                ['id' => 301, 'title' => 'Leadership Sprint', 'status' => 'Published', 'revenue' => '$12.3K'],
                ['id' => 302, 'title' => 'Analytics Foundations', 'status' => 'Draft', 'revenue' => '$7.1K'],
                ['id' => 303, 'title' => 'Career Readiness Lab', 'status' => 'Published', 'revenue' => '$9.8K'],
            ],
            'assignments' => [
                ['id' => 20, 'title' => 'Platform adoption review', 'course' => 'Leadership Sprint', 'owner' => 'Ops Team', 'priority' => 'High'],
                ['id' => 21, 'title' => 'Payment report checks', 'course' => 'All programs', 'owner' => 'Finance', 'priority' => 'Medium'],
            ],
            'notifications' => [
                ['id' => 1, 'text' => 'New instructor onboarding tasks are ready', 'time' => '10 mins ago'],
                ['id' => 2, 'text' => 'Security update approved for course platform', 'time' => '4 hours ago'],
            ],
            'forum' => [
                ['id' => 1, 'topic' => 'Launch checklist for Q4 cohort', 'replies' => 34, 'author' => 'Program Office'],
                ['id' => 2, 'topic' => 'Payment policy clarification', 'replies' => 12, 'author' => 'Finance'],
            ],
            'analytics' => [
                'weeklyMinutes' => 920,
                'engagement' => 91,
                'retention' => 89,
                'focusAreas' => ['Growth', 'Retention', 'Operations'],
            ],
        ],
    ];
}

$method = $_SERVER['REQUEST_METHOD'];
$action = strtolower((string)($_GET['action'] ?? 'dashboard'));
$role = strtolower((string)($_GET['role'] ?? 'student'));
$payload = getMockData();

try {
    if ($method === 'POST' && $action === 'login') {
        $input = json_decode(file_get_contents('php://input') ?: '[]', true) ?? [];
        $email = strtolower(trim((string)($input['email'] ?? '')));
        $password = (string)($input['password'] ?? '');
        $selectedRole = in_array($role, ['student', 'instructor', 'admin'], true) ? $role : 'student';

        if ($email === '' || $password === '') {
            apiResponse(false, 'Email and password are required.', null, 400);
        }

        $credentials = getDemoCredentials();
        $matchedRole = null;

        foreach ($credentials as $credentialRole => $account) {
            if (strtolower($account['email']) === $email && $account['password'] === $password) {
                $matchedRole = $credentialRole;
                break;
            }
        }

        if ($matchedRole === null) {
            apiResponse(false, 'Invalid email or password.', null, 401);
        }

        $effectiveRole = in_array($selectedRole, ['student', 'instructor', 'admin'], true) && $matchedRole === $selectedRole
            ? $selectedRole
            : $matchedRole;

        $_SESSION['user'] = [
            'name' => ucfirst($effectiveRole) . ' User',
            'email' => $email,
            'role' => $effectiveRole,
        ];
        $_SESSION['role'] = $effectiveRole;

        apiResponse(true, 'Login successful.', [
            'role' => $effectiveRole,
            'user' => $_SESSION['user'],
            'dashboard' => $payload[$effectiveRole] ?? $payload['student'],
        ]);
    }

    if ($method === 'GET' && $action === 'me') {
        $user = currentUser();
        if ($user === null) {
            apiResponse(false, 'No active session.', null, 401);
        }

        apiResponse(true, 'Session loaded.', [
            'user' => $user,
            'role' => $user['role'] ?? 'student',
        ]);
    }

    if ($method === 'POST' && $action === 'logout') {
        session_unset();
        session_destroy();
        apiResponse(true, 'Logged out successfully.', null);
    }

    // ========== REGISTRATION & USER MANAGEMENT ==========

    if ($method === 'POST' && $action === 'register') {
        $input = json_decode(file_get_contents('php://input') ?: '{}', true) ?? [];
        
        $firstName = trim((string)($input['first_name'] ?? ''));
        $lastName = trim((string)($input['last_name'] ?? ''));
        $email = trim((string)($input['email'] ?? ''));
        $password = (string)($input['password'] ?? '');
        $role = strtolower(trim((string)($input['role'] ?? 'student')));

        if (empty($firstName) || empty($lastName)) {
            apiResponse(false, 'First and last name are required.', null, 400);
        }

        if (!Validator::email($email)) {
            apiResponse(false, 'Invalid email format.', null, 400);
        }

        if (strlen($password) < 8) {
            apiResponse(false, 'Password must be at least 8 characters.', null, 400);
        }

        if (!in_array($role, ['student', 'instructor', 'admin'], true)) {
            apiResponse(false, 'Invalid role. Must be student, instructor, or admin.', null, 400);
        }

        try {
            $pdo = getDatabase();
            $userRepo = new UserRepository($pdo);
            
            $userId = $userRepo->create($firstName, $lastName, $email, $password, $role);
            
            if (!$userId) {
                apiResponse(false, 'Email already exists or registration failed.', null, 409);
            }

            // Send welcome email
            $notificationService = new NotificationService();
            $notificationService->sendWelcomeEmail($email, $firstName, $role);

            apiResponse(true, 'User registered successfully.', ['user_id' => $userId]);
        } catch (Exception $e) {
            apiResponse(false, 'Registration failed: ' . $e->getMessage(), null, 500);
        }
    }

    if ($method === 'GET' && $action === 'admin-users') {
        $user = requireAuth();
        if ($user['role'] !== 'admin') {
            apiResponse(false, 'Only admins can access this.', null, 403);
        }

        $page = (int)($_GET['page'] ?? 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;

        try {
            $pdo = getDatabase();
            $userRepo = new UserRepository($pdo);
            
            $users = $userRepo->getAll($limit, $offset);
            $totalUsers = $userRepo->count();
            $totalPages = ceil($totalUsers / $limit);

            apiResponse(true, 'Users loaded.', [
                'users' => $users,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total_users' => $totalUsers,
                    'total_pages' => $totalPages,
                ],
            ]);
        } catch (Exception $e) {
            apiResponse(false, 'Failed to load users: ' . $e->getMessage(), null, 500);
        }
    }

    if ($method === 'POST' && $action === 'delete-user') {
        $user = requireAuth();
        if ($user['role'] !== 'admin') {
            apiResponse(false, 'Only admins can delete users.', null, 403);
        }

        $input = json_decode(file_get_contents('php://input') ?: '{}', true) ?? [];
        $userId = (int)($input['user_id'] ?? 0);

        if ($userId <= 0) {
            apiResponse(false, 'Valid user ID is required.', null, 400);
        }

        try {
            $pdo = getDatabase();
            $userRepo = new UserRepository($pdo);
            
            if ($userRepo->delete($userId)) {
                apiResponse(true, 'User deleted successfully.', null);
            } else {
                apiResponse(false, 'Failed to delete user.', null, 500);
            }
        } catch (Exception $e) {
            apiResponse(false, 'Delete failed: ' . $e->getMessage(), null, 500);
        }
    }

    // ========== COURSE ENDPOINTS ==========

    if ($method === 'GET' && $action === 'course') {
        $courseId = (int)($_GET['id'] ?? 0);
        
        if ($courseId <= 0) {
            apiResponse(false, 'Course ID is required.', null, 400);
        }

        try {
            $pdo = getDatabase();
            $courseRepo = new CourseRepository($pdo);
            
            $course = $courseRepo->getById($courseId);
            
            if (!$course) {
                apiResponse(false, 'Course not found.', null, 404);
            }

            // Get course lessons
            $lessons = $courseRepo->getLessonsByCourse($courseId);

            apiResponse(true, 'Course loaded.', [
                'course' => $course,
                'lessons' => $lessons ?? [],
            ]);
        } catch (Exception $e) {
            apiResponse(false, 'Failed to load course: ' . $e->getMessage(), null, 500);
        }
    }

    // ========== ASSIGNMENT & SUBMISSION ENDPOINTS ==========

    if ($method === 'POST' && $action === 'submit-assignment') {
        $user = requireAuth();
        
        $assignmentId = (int)($_POST['assignment_id'] ?? 0);
        $submissionType = (string)($_POST['submission_type'] ?? 'text'); // text, file, both
        $textContent = (string)($_POST['text_content'] ?? '');
        $notes = (string)($_POST['notes'] ?? '');

        if ($assignmentId <= 0) {
            apiResponse(false, 'Assignment ID is required.', null, 400);
        }

        try {
            $pdo = getDatabase();
            $assignmentRepo = new AssignmentRepository($pdo);
            
            $uploadedFile = null;

            // Handle file upload if present
            if (($submissionType === 'file' || $submissionType === 'both') && !empty($_FILES['file'])) {
                $fileHandler = new FileUploadHandler();
                $uploadedFile = $fileHandler->uploadAssignmentFile(
                    $_FILES['file'],
                    $assignmentId,
                    $user['user_id'] ?? 0
                );

                if (!$uploadedFile) {
                    apiResponse(false, 'File upload failed.', null, 400);
                }
            }

            // Create submission record
            $submission = [
                'assignment_id' => $assignmentId,
                'student_email' => $user['email'],
                'submission_type' => $submissionType,
                'text_content' => $textContent,
                'file_path' => $uploadedFile,
                'notes' => $notes,
                'submitted_at' => date('Y-m-d H:i:s'),
                'status' => 'Submitted',
            ];

            $submissionId = $assignmentRepo->createSubmission($submission);

            if (!$submissionId) {
                apiResponse(false, 'Failed to create submission.', null, 500);
            }

            // Send confirmation email (student name and assignment details would be fetched in production)
            // $notificationService = new NotificationService();
            // $notificationService->sendSubmissionConfirmation(...);

            apiResponse(true, 'Assignment submitted successfully.', ['submission_id' => $submissionId]);
        } catch (Exception $e) {
            apiResponse(false, 'Submission failed: ' . $e->getMessage(), null, 500);
        }
    }

    if ($method === 'GET' && $action === 'grading-submissions') {
        $user = requireAuth();
        if ($user['role'] !== 'instructor' && $user['role'] !== 'admin') {
            apiResponse(false, 'Only instructors can access this.', null, 403);
        }

        $courseId = (int)($_GET['course_id'] ?? 0);
        $assignmentId = (int)($_GET['assignment_id'] ?? 0);
        $status = (string)($_GET['status'] ?? ''); // pending, graded, etc.
        $page = (int)($_GET['page'] ?? 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;

        try {
            $pdo = getDatabase();
            $assignmentRepo = new AssignmentRepository($pdo);
            
            $filter = [];
            if ($courseId > 0) $filter['course_id'] = $courseId;
            if ($assignmentId > 0) $filter['assignment_id'] = $assignmentId;
            if ($status) $filter['status'] = $status;

            $submissions = $assignmentRepo->getSubmissions($filter, $limit, $offset);
            $totalCount = $assignmentRepo->getSubmissionsCount($filter);
            $totalPages = ceil($totalCount / $limit);

            apiResponse(true, 'Submissions loaded.', [
                'submissions' => $submissions,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $totalCount,
                    'total_pages' => $totalPages,
                ],
            ]);
        } catch (Exception $e) {
            apiResponse(false, 'Failed to load submissions: ' . $e->getMessage(), null, 500);
        }
    }

    if ($method === 'POST' && $action === 'grade-submission') {
        $user = requireAuth();
        if ($user['role'] !== 'instructor' && $user['role'] !== 'admin') {
            apiResponse(false, 'Only instructors can grade submissions.', null, 403);
        }

        $input = json_decode(file_get_contents('php://input') ?: '{}', true) ?? [];
        
        $submissionId = (int)($input['submission_id'] ?? 0);
        $score = (float)($input['score'] ?? 0);
        $feedback = (string)($input['feedback'] ?? '');

        if ($submissionId <= 0) {
            apiResponse(false, 'Submission ID is required.', null, 400);
        }

        if ($score < 0 || $score > 100) {
            apiResponse(false, 'Score must be between 0 and 100.', null, 400);
        }

        try {
            $pdo = getDatabase();
            $assignmentRepo = new AssignmentRepository($pdo);
            
            $success = $assignmentRepo->gradeSubmission($submissionId, $score, $feedback, $user['email']);

            if (!$success) {
                apiResponse(false, 'Failed to grade submission.', null, 500);
            }

            // Send grade notification to student (detailed student/assignment info would be fetched in production)
            // $notificationService = new NotificationService();
            // $notificationService->sendGradeNotification($studentEmail, $studentName, $assignmentTitle, $score, 100, $feedback);

            apiResponse(true, 'Submission graded successfully.', null);
        } catch (Exception $e) {
            apiResponse(false, 'Grading failed: ' . $e->getMessage(), null, 500);
        }
    }

    // ========== PAYMENT ENDPOINTS ==========

    if ($method === 'POST' && $action === 'initiate-payment') {
        $user = requireAuth();
        
        $input = json_decode(file_get_contents('php://input') ?: '{}', true) ?? [];
        $courseId = (int)($input['course_id'] ?? 0);
        $amount = (float)($input['amount'] ?? 0);
        $paymentMethod = (string)($input['payment_method'] ?? 'stripe'); // stripe or paypal

        if ($courseId <= 0 || $amount <= 0) {
            apiResponse(false, 'Course ID and amount are required.', null, 400);
        }

        try {
            $pdo = getDatabase();
            $paymentService = new PaymentService($pdo);
            
            $payment = $paymentService->initiatePayment(
                $user['user_id'] ?? 0,
                $courseId,
                $amount,
                $paymentMethod
            );

            if (!$payment) {
                apiResponse(false, 'Failed to initiate payment.', null, 500);
            }

            apiResponse(true, 'Payment initiated.', $payment);
        } catch (Exception $e) {
            apiResponse(false, 'Payment initiation failed: ' . $e->getMessage(), null, 500);
        }
    }

    if ($method === 'GET' && $action === 'user-payments') {
        $user = requireAuth();

        try {
            $pdo = getDatabase();
            $paymentService = new PaymentService($pdo);
            
            $payments = $paymentService->getUserPayments($user['user_id'] ?? 0);

            apiResponse(true, 'Payments loaded.', ['payments' => $payments]);
        } catch (Exception $e) {
            apiResponse(false, 'Failed to load payments: ' . $e->getMessage(), null, 500);
        }
    }

    if ($method === 'POST' && $action === 'ai-chat') {
        $input = json_decode(file_get_contents('php://input') ?: '[]', true) ?? [];
        $question = trim((string)($input['question'] ?? ''));

        if ($question === '') {
            apiResponse(false, 'Please ask a learning question.', null, 400);
        }

        $answer = 'I recommend breaking this into a 3-step plan: 1) review the current module, 2) complete the linked project task, and 3) ask your mentor for feedback before the next deadline.';
        apiResponse(true, 'AI guidance generated.', [
            'answer' => $answer,
            'suggestedAction' => 'Open the next milestone task and schedule a 15-minute review.',
        ]);
    }

    if ($method === 'GET' && $action === 'courses') {
        try {
            $pdo = getDatabase();
            $courseRepo = new CourseRepository($pdo);
            $category = (string)($_GET['category'] ?? '');
            $level = (string)($_GET['level'] ?? '');
            $search = (string)($_GET['search'] ?? '');
            $limit = (int)($_GET['limit'] ?? 20);
            $offset = (int)($_GET['offset'] ?? 0);

            $courses = [];
            if ($search !== '') {
                $courses = $courseRepo->search($search, $limit, $offset);
            } else {
                $filters = [];
                if ($category !== '') {
                    $filters['category'] = $category;
                }
                if ($level !== '') {
                    $filters['level'] = $level;
                }
                $courses = $courseRepo->getAll($filters, $limit, $offset);
            }

            apiResponse(true, 'Courses loaded.', ['courses' => $courses]);
        } catch (Exception $e) {
            apiResponse(false, 'Failed to load courses: ' . $e->getMessage(), null, 500);
        }
    }

    if ($method === 'GET' && $action === 'course') {
        try {
            $courseId = (int)($_GET['id'] ?? 0);
            if ($courseId === 0) {
                apiResponse(false, 'Course ID is required.', null, 400);
            }

            $pdo = getDatabase();
            $courseRepo = new CourseRepository($pdo);
            $course = $courseRepo->getById($courseId);

            if ($course === null) {
                apiResponse(false, 'Course not found.', null, 404);
            }

            apiResponse(true, 'Course loaded.', $course);
        } catch (Exception $e) {
            apiResponse(false, 'Failed to load course: ' . $e->getMessage(), null, 500);
        }
    }

    if ($method === 'POST' && $action === 'enroll') {
        $user = requireAuth();
        $input = json_decode(file_get_contents('php://input') ?: '[]', true) ?? [];
        $courseId = (int)($input['course_id'] ?? 0);

        if ($courseId === 0) {
            apiResponse(false, 'Course ID is required.', null, 400);
        }

        try {
            $pdo = getDatabase();
            $enrollmentRepo = new EnrollmentRepository($pdo);

            if ($enrollmentRepo->isEnrolled($courseId, $user['email'])) {
                apiResponse(false, 'Already enrolled in this course.', null, 409);
            }

            $enrollmentId = $enrollmentRepo->create($courseId, $user['name'], $user['email']);
            apiResponse(true, 'Enrollment successful.', ['enrollment_id' => $enrollmentId]);
        } catch (Exception $e) {
            apiResponse(false, 'Enrollment failed: ' . $e->getMessage(), null, 500);
        }
    }

    if ($method === 'GET' && $action === 'my-courses') {
        $user = requireAuth();

        try {
            $pdo = getDatabase();
            $enrollmentRepo = new EnrollmentRepository($pdo);
            $enrollments = $enrollmentRepo->getByEmail($user['email'], 50, 0);
            apiResponse(true, 'Your courses loaded.', ['enrollments' => $enrollments]);
        } catch (Exception $e) {
            apiResponse(false, 'Failed to load enrollments: ' . $e->getMessage(), null, 500);
        }
    }

    if ($method === 'GET' && $action === 'assignments') {
        $courseId = (int)($_GET['course_id'] ?? 0);
        if ($courseId === 0) {
            apiResponse(false, 'Course ID is required.', null, 400);
        }

        try {
            $pdo = getDatabase();
            $assignmentRepo = new AssignmentRepository($pdo);
            $assignments = $assignmentRepo->getByCourse($courseId);
            apiResponse(true, 'Assignments loaded.', ['assignments' => $assignments]);
        } catch (Exception $e) {
            apiResponse(false, 'Failed to load assignments: ' . $e->getMessage(), null, 500);
        }
    }

    if ($method === 'GET' && $action === 'assignment') {
        $user = requireAuth();
        $assignmentId = (int)($_GET['id'] ?? 0);
        if ($assignmentId === 0) {
            apiResponse(false, 'Assignment ID is required.', null, 400);
        }

        try {
            $pdo = getDatabase();
            $assignmentRepo = new AssignmentRepository($pdo);
            $assignment = $assignmentRepo->getById($assignmentId);

            if ($assignment === null) {
                apiResponse(false, 'Assignment not found.', null, 404);
            }

            // For students, load their submission
            if ($user['role'] === 'student') {
                $enrollmentRepo = new EnrollmentRepository($pdo);
                $enrollments = $enrollmentRepo->getByEmail($user['email']);
                $enrollmentId = null;
                foreach ($enrollments as $e) {
                    if ($e['project_id'] == $assignment['project_id']) {
                        $enrollmentId = $e['id'];
                        break;
                    }
                }

                if ($enrollmentId) {
                    $submission = $assignmentRepo->getSubmission($assignmentId, $enrollmentId);
                    $assignment['submission'] = $submission;
                }
            }

            apiResponse(true, 'Assignment loaded.', $assignment);
        } catch (Exception $e) {
            apiResponse(false, 'Failed to load assignment: ' . $e->getMessage(), null, 500);
        }
    }

    if ($method === 'POST' && $action === 'submit-assignment') {
        $user = requireAuth();
        $input = json_decode(file_get_contents('php://input') ?: '[]', true) ?? [];
        $assignmentId = (int)($input['assignment_id'] ?? 0);
        $submissionText = trim((string)($input['submission_text'] ?? ''));

        if ($assignmentId === 0) {
            apiResponse(false, 'Assignment ID is required.', null, 400);
        }

        if ($submissionText === '') {
            apiResponse(false, 'Submission text is required.', null, 400);
        }

        try {
            $pdo = getDatabase();
            $assignmentRepo = new AssignmentRepository($pdo);
            $enrollmentRepo = new EnrollmentRepository($pdo);
            
            // Get user's enrollment
            $enrollments = $enrollmentRepo->getByEmail($user['email']);
            $enrollmentId = null;
            $assignment = $assignmentRepo->getById($assignmentId);

            if ($assignment === null) {
                apiResponse(false, 'Assignment not found.', null, 404);
            }

            foreach ($enrollments as $e) {
                if ($e['project_id'] == $assignment['project_id']) {
                    $enrollmentId = $e['id'];
                    break;
                }
            }

            if (!$enrollmentId) {
                apiResponse(false, 'Not enrolled in this course.', null, 403);
            }

            $submissionId = $assignmentRepo->submitAssignment($assignmentId, $enrollmentId, $user['email'], $submissionText);
            apiResponse(true, 'Assignment submitted successfully.', ['submission_id' => $submissionId]);
        } catch (Exception $e) {
            apiResponse(false, 'Submission failed: ' . $e->getMessage(), null, 500);
        }
    }

    if ($method === 'GET' && $action === 'submissions') {
        $user = requireAuth();
        if ($user['role'] !== 'instructor' && $user['role'] !== 'admin') {
            apiResponse(false, 'Only instructors can view submissions.', null, 403);
        }

        $assignmentId = (int)($_GET['assignment_id'] ?? 0);
        if ($assignmentId === 0) {
            apiResponse(false, 'Assignment ID is required.', null, 400);
        }

        try {
            $pdo = getDatabase();
            $assignmentRepo = new AssignmentRepository($pdo);
            $submissions = $assignmentRepo->getAssignmentSubmissions($assignmentId);
            apiResponse(true, 'Submissions loaded.', ['submissions' => $submissions]);
        } catch (Exception $e) {
            apiResponse(false, 'Failed to load submissions: ' . $e->getMessage(), null, 500);
        }
    }

    if ($method === 'POST' && $action === 'grade-submission') {
        $user = requireAuth();
        if ($user['role'] !== 'instructor' && $user['role'] !== 'admin') {
            apiResponse(false, 'Only instructors can grade submissions.', null, 403);
        }

        $input = json_decode(file_get_contents('php://input') ?: '[]', true) ?? [];
        $submissionId = (int)($input['submission_id'] ?? 0);
        $score = (int)($input['score'] ?? 0);
        $feedback = trim((string)($input['feedback'] ?? ''));

        if ($submissionId === 0) {
            apiResponse(false, 'Submission ID is required.', null, 400);
        }

        try {
            $pdo = getDatabase();
            $assignmentRepo = new AssignmentRepository($pdo);
            $success = $assignmentRepo->gradeSubmission($submissionId, $score, $feedback, $user['email']);

            if (!$success) {
                apiResponse(false, 'Failed to grade submission.', null, 500);
            }

            apiResponse(true, 'Submission graded successfully.', null);
        } catch (Exception $e) {
            apiResponse(false, 'Grading failed: ' . $e->getMessage(), null, 500);
        }
    }

    if ($method === 'GET' && $action === 'instructor-courses') {
        $user = requireAuth();
        if ($user['role'] !== 'instructor' && $user['role'] !== 'admin') {
            apiResponse(false, 'Only instructors can view this.', null, 403);
        }

        try {
            $pdo = getDatabase();
            $courseRepo = new CourseRepository($pdo);
            // Get all active courses (instructor teaches all or filtered by instructor email)
            $courses = $courseRepo->getAll(['status' => 'Active'], 100, 0);
            apiResponse(true, 'Instructor courses loaded.', ['courses' => $courses]);
        } catch (Exception $e) {
            apiResponse(false, 'Failed to load courses: ' . $e->getMessage(), null, 500);
        }
    }

    if ($method === 'GET' && $action === 'course-analytics') {
        $user = requireAuth();
        if ($user['role'] !== 'instructor' && $user['role'] !== 'admin') {
            apiResponse(false, 'Only instructors can view analytics.', null, 403);
        }

        $courseId = (int)($_GET['course_id'] ?? 0);
        if ($courseId === 0) {
            apiResponse(false, 'Course ID is required.', null, 400);
        }

        try {
            $pdo = getDatabase();
            $progressRepo = new ProgressRepository($pdo);
            $analytics = $progressRepo->getCourseAnalytics($courseId);
            $enrollments = $progressRepo->getCourseEnrollmentsWithProgress($courseId);
            
            apiResponse(true, 'Analytics loaded.', [
                'analytics' => $analytics,
                'enrollments' => $enrollments,
            ]);
        } catch (Exception $e) {
            apiResponse(false, 'Failed to load analytics: ' . $e->getMessage(), null, 500);
        }
    }

    if ($method === 'GET' && $action === 'course-stats') {
        $user = requireAuth();
        if ($user['role'] !== 'instructor' && $user['role'] !== 'admin') {
            apiResponse(false, 'Only instructors can view stats.', null, 403);
        }

        try {
            $pdo = getDatabase();
            $courseRepo = new CourseRepository($pdo);
            
            // Get total counts
            $allCourses = $courseRepo->getAll([], 1000, 0);
            $totalCourses = count($allCourses);
            
            // Mock enrollment stats
            $totalEnrollments = $totalCourses * 15; // Mock: ~15 students per course
            $totalSubmissions = $totalCourses * 30; // Mock: ~30 assignments per course
            
            apiResponse(true, 'Stats loaded.', [
                'stats' => [
                    ['label' => 'Total courses', 'value' => $totalCourses, 'trend' => '+2 this month'],
                    ['label' => 'Active students', 'value' => $totalEnrollments, 'trend' => '+18%'],
                    ['label' => 'Pending grades', 'value' => ceil($totalSubmissions * 0.2), 'trend' => '-5%'],
                    ['label' => 'Avg completion', 'value' => '72%', 'trend' => '+8%'],
                ],
            ]);
        } catch (Exception $e) {
            apiResponse(false, 'Failed to load stats: ' . $e->getMessage(), null, 500);
        }
    }

    if ($method === 'GET' && $action === 'certificates') {
        $user = requireAuth();

        try {
            $pdo = getDatabase();
            $certRepo = new CertificateRepository($pdo);
            $certificates = $certRepo->getStudentCertificates($user['email']);
            apiResponse(true, 'Certificates loaded.', ['certificates' => $certificates]);
        } catch (Exception $e) {
            apiResponse(false, 'Failed to load certificates: ' . $e->getMessage(), null, 500);
        }
    }

    if ($method === 'GET' && $action === 'certificate') {
        $certificateId = (string)($_GET['id'] ?? '');

        if ($certificateId === '') {
            apiResponse(false, 'Certificate ID is required.', null, 400);
        }

        try {
            $pdo = getDatabase();
            $certRepo = new CertificateRepository($pdo);
            $certificate = $certRepo->verifyCertificate($certificateId);

            if ($certificate === null) {
                apiResponse(false, 'Certificate not found or expired.', null, 404);
            }

            apiResponse(true, 'Certificate verified.', $certificate);
        } catch (Exception $e) {
            apiResponse(false, 'Certificate verification failed: ' . $e->getMessage(), null, 500);
        }
    }

    if ($method === 'POST' && $action === 'enroll-premium') {
        $user = requireAuth();
        $input = json_decode(file_get_contents('php://input') ?: '[]', true) ?? [];
        $courseId = (int)($input['course_id'] ?? 0);

        if ($courseId === 0) {
            apiResponse(false, 'Course ID is required.', null, 400);
        }

        try {
            $pdo = getDatabase();
            
            // Get course price
            $courseStmt = $pdo->prepare('SELECT price, title FROM projects WHERE id = ?');
            $courseStmt->execute([$courseId]);
            $course = $courseStmt->fetch();

            if ($course === null) {
                apiResponse(false, 'Course not found.', null, 404);
            }

            $price = $course['price'] ?? 0;
            if ($price <= 0) {
                apiResponse(false, 'This course is free. Use enroll action instead.', null, 400);
            }

            // Create payment record
            $sql = 'INSERT INTO payments (enrollment_id, student_email, course_id, course_title, amount, status)
                    VALUES (NULL, ?, ?, ?, ?, "Pending")';
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user['email'], $courseId, $course['title'], $price]);
            $paymentId = $pdo->lastInsertId();

            apiResponse(true, 'Payment initiated. Complete checkout to enroll.', [
                'payment_id' => $paymentId,
                'amount' => $price,
                'course' => $course['title'],
                'checkout_url' => '/checkout?payment_id=' . $paymentId
            ]);
        } catch (Exception $e) {
            apiResponse(false, 'Payment processing failed: ' . $e->getMessage(), null, 500);
        }
    }

    if ($method === 'GET' && ($action === 'dashboard' || $action === '')) {
        $user = requireAuth();
        $sessionRole = $user['role'] ?? 'student';
        $role = in_array($role, ['student', 'instructor', 'admin'], true) ? $role : $sessionRole;
        $effectiveRole = in_array($role, ['student', 'instructor', 'admin'], true) ? $role : $sessionRole;
        apiResponse(true, 'Dashboard loaded.', $payload[$effectiveRole] ?? $payload['student']);
    }

    if ($method === 'GET' && $action === 'assignments') {
        apiResponse(true, 'Assignments loaded.', [
            'assignments' => [
                ['id' => 1, 'title' => 'Research summary', 'course' => 'Product Design Bootcamp', 'dueDate' => '2026-08-18', 'status' => 'In progress'],
                ['id' => 2, 'title' => 'Prompt pack', 'course' => 'AI for Everyday Learning', 'dueDate' => '2026-08-20', 'status' => 'Pending'],
                ['id' => 3, 'title' => 'Deployment checklist', 'course' => 'Full-Stack Launch Lab', 'dueDate' => '2026-08-23', 'status' => 'Review'],
            ],
        ]);
    }

    if ($method === 'GET' && $action === 'notifications') {
        apiResponse(true, 'Notifications loaded.', [
            'notifications' => [
                ['id' => 1, 'text' => 'Mentor feedback added to your prototype task', 'time' => '8 mins ago'],
                ['id' => 2, 'text' => 'Your certificate is ready for download', 'time' => '3 hours ago'],
                ['id' => 3, 'text' => 'New team discussion posted in UX lab', 'time' => '1 day ago'],
            ],
        ]);
    }

    if ($method === 'GET' && $action === 'analytics') {
        apiResponse(true, 'Learning analytics loaded.', [
            'summary' => [
                'completionRate' => 84,
                'weeklyStudyHours' => 12.5,
                'engagement' => 91,
                'retention' => 89,
            ],
            'focusAreas' => ['Product thinking', 'Research', 'Coding', 'AI fluency'],
        ]);
    }

    if ($method === 'GET' && $action === 'forum') {
        apiResponse(true, 'Discussion board loaded.', [
            'threads' => [
                ['id' => 1, 'topic' => 'How should a learner document peer feedback?', 'replies' => 21, 'author' => 'Amina'],
                ['id' => 2, 'topic' => 'Best templates for sprint planning', 'replies' => 16, 'author' => 'Chris'],
            ],
        ]);
    }

    if ($method === 'GET' && $action === 'certificates') {
        apiResponse(true, 'Certificates loaded.', [
            'certificates' => [
                ['id' => 1, 'name' => 'UX Foundations', 'issued' => '2026-07-28', 'status' => 'Issued'],
                ['id' => 2, 'name' => 'AI Learning Assistant', 'issued' => '2026-08-11', 'status' => 'Issued'],
                ['id' => 3, 'name' => 'Project Leadership', 'issued' => null, 'status' => 'In progress'],
            ],
        ]);
    }

    if ($method === 'GET' && $action === 'schedule') {
        apiResponse(true, 'Schedule loaded.', [
            'schedule' => [
                ['day' => 'Mon', 'title' => 'Kickoff call', 'time' => '09:30 AM', 'type' => 'Cohort'],
                ['day' => 'Wed', 'title' => 'Portfolio studio', 'time' => '01:00 PM', 'type' => 'Workshop'],
                ['day' => 'Fri', 'title' => 'Sprint retrospective', 'time' => '03:30 PM', 'type' => 'Review'],
            ],
        ]);
    }

    if ($method === 'GET' && $action === 'payments') {
        apiResponse(true, 'Payment overview loaded.', [
            'payments' => [
                ['id' => 1, 'plan' => 'Pro Subscription', 'status' => 'Paid', 'amount' => '$39'],
                ['id' => 2, 'plan' => 'Course Bundle', 'status' => 'Pending', 'amount' => '$129'],
            ],
        ]);
    }

    apiResponse(false, 'Unsupported action.', null, 404);
} catch (Throwable $e) {
    apiResponse(false, 'API error: ' . $e->getMessage(), null, 500);
}


function handleCreateResource(ResourceRepository $resourceRepo, array $input, Logger $logger): never
{
    $projectId = (int)($input['project_id'] ?? 0);
    $title = trim($input['title'] ?? '');
    $type = $input['type'] ?? 'Link';
    $fileUrl = trim($input['file_url'] ?? '');
    
    if (!Validator::positiveInt($projectId)) {
        Response::error('Valid course ID is required', null, 400)->send();
    }
    
    if (!Validator::stringLength($title, 1, 255)) {
        Response::error('Resource title is required', null, 400)->send();
    }
    
    if (!Validator::enum($type, ['Link', 'Video', 'Document', 'Assignment', 'Quiz'])) {
        Response::error('Invalid resource type', null, 400)->send();
    }
    
    if (!Validator::url($fileUrl)) {
        Response::error('Valid file URL is required', null, 400)->send();
    }
    
    try {
        $id = $resourceRepo->create([
            'project_id' => $projectId,
            'title' => $title,
            'type' => $type,
            'file_url' => $fileUrl,
            'description' => $input['description'] ?? null,
        ]);
        
        $logger->info('Resource created', ['resource_id' => $id, 'project_id' => $projectId]);
        Response::created('Resource created successfully', ['id' => $id])->send();
    } catch (Throwable $e) {
        $logger->error('Failed to create resource', ['error' => $e->getMessage()]);
        Response::serverError('Failed to create resource')->send();
    }
}

function handleUpdateResource(ResourceRepository $resourceRepo, array $input, Logger $logger): never
{
    $id = (int)($_GET['id'] ?? 0);
    
    if (!Validator::positiveInt($id)) {
        Response::error('Valid resource ID is required', null, 400)->send();
    }
    
    if (!$resourceRepo->getById($id)) {
        Response::notFound('Resource not found')->send();
    }
    
    if ($resourceRepo->update($id, $input)) {
        $logger->info('Resource updated', ['resource_id' => $id]);
        Response::success('Resource updated successfully')->send();
    } else {
        Response::serverError('Failed to update resource')->send();
    }
}

function handleDeleteResource(ResourceRepository $resourceRepo, Logger $logger): never
{
    $id = (int)($_GET['id'] ?? 0);
    
    if (!Validator::positiveInt($id)) {
        Response::error('Valid resource ID is required', null, 400)->send();
    }
    
    if ($resourceRepo->delete($id)) {
        $logger->info('Resource deleted', ['resource_id' => $id]);
        Response::success('Resource deleted successfully')->send();
    } else {
        Response::notFound('Resource not found')->send();
    }
}

// ========== ENROLLMENT HANDLERS ==========

function handleGetEnrollments(EnrollmentRepository $enrollmentRepo, Logger $logger): never
{
    $projectId = (int)($_GET['project_id'] ?? 0);
    $limit = min((int)($_GET['limit'] ?? 100), 500);
    $offset = (int)($_GET['offset'] ?? 0);
    
    if (!Validator::positiveInt($projectId)) {
        Response::error('Valid course ID is required', null, 400)->send();
    }
    
    $enrollments = $enrollmentRepo->getByCourse($projectId, $limit, $offset);
    $total = $enrollmentRepo->countByCourse($projectId);
    
    $logger->info('Fetched enrollments', ['project_id' => $projectId, 'count' => count($enrollments)]);
    
    Response::success('Enrollments loaded', [
        'enrollments' => $enrollments,
        'pagination' => [
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset,
        ]
    ])->send();
}

function handleCreateEnrollment(EnrollmentRepository $enrollmentRepo, CourseRepository $courseRepo, array $input, PDO $pdo, Logger $logger): never
{
    $validated = Validator::validateEnrollment($input);
    $validator = $validated['validator'];
    
    if ($validator->hasErrors()) {
        Response::validationError('Validation failed', $validator->getErrors())->send();
    }
    
    $projectId = $validated['project_id'];
    
    // Verify course exists
    if (!$courseRepo->getById($projectId)) {
        Response::notFound('Course not found')->send();
    }
    
    // Check if already enrolled
    if ($enrollmentRepo->isEnrolled($projectId, $validated['email'])) {
        Response::conflict('This email is already enrolled in this course')->send();
    }
    
    try {
        $id = $enrollmentRepo->create($projectId, $validated['student_name'], $validated['email']);
        
        // Log activity
        $stmt = $pdo->prepare('INSERT INTO activity_logs (project_id, enrollment_id, action, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$projectId, $id, 'STUDENT_ENROLLED', $_SERVER['REMOTE_ADDR'] ?? null, $_SERVER['HTTP_USER_AGENT'] ?? null]);
        
        $logger->info('Enrollment created', ['enrollment_id' => $id, 'project_id' => $projectId, 'email' => $validated['email']]);
        Response::created('Enrollment successful', ['id' => $id])->send();
    } catch (Throwable $e) {
        $logger->error('Failed to create enrollment', ['error' => $e->getMessage()]);
        Response::serverError('Failed to create enrollment')->send();
    }
}

function handleUpdateEnrollment(EnrollmentRepository $enrollmentRepo, array $input, Logger $logger): never
{
    $id = (int)($_GET['id'] ?? 0);
    
    if (!Validator::positiveInt($id)) {
        Response::error('Valid enrollment ID is required', null, 400)->send();
    }
    
    if (!$enrollmentRepo->getById($id)) {
        Response::notFound('Enrollment not found')->send();
    }
    
    if ($enrollmentRepo->update($id, $input)) {
        $logger->info('Enrollment updated', ['enrollment_id' => $id]);
        Response::success('Enrollment updated successfully')->send();
    } else {
        Response::serverError('Failed to update enrollment')->send();
    }
}

function handleDeleteEnrollment(EnrollmentRepository $enrollmentRepo, Logger $logger): never
{
    $id = (int)($_GET['id'] ?? 0);
    
    if (!Validator::positiveInt($id)) {
        Response::error('Valid enrollment ID is required', null, 400)->send();
    }
    
    if ($enrollmentRepo->delete($id)) {
        $logger->info('Enrollment deleted', ['enrollment_id' => $id]);
        Response::success('Enrollment deleted successfully')->send();
    } else {
        Response::notFound('Enrollment not found')->send();
    }
}

function handleGetEnrollmentStats(EnrollmentRepository $enrollmentRepo, Logger $logger): never
{
    $projectId = (int)($_GET['project_id'] ?? 0);
    
    if (!Validator::positiveInt($projectId)) {
        Response::error('Valid course ID is required', null, 400)->send();
    }
    
    $stats = $enrollmentRepo->getStats($projectId);
    $logger->info('Fetched enrollment stats', ['project_id' => $projectId]);
    
    Response::success('Statistics loaded', $stats)->send();
}

