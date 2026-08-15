<?php
/**
 * E-Learning Management System - API
 * 
 * RESTful API for course management, enrollments, and resources
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

// Enable CORS for development
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Load configuration and classes
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/classes/Logger.php';
require_once __DIR__ . '/classes/Response.php';
require_once __DIR__ . '/classes/Validator.php';
require_once __DIR__ . '/classes/CourseRepository.php';
require_once __DIR__ . '/classes/EnrollmentRepository.php';
require_once __DIR__ . '/classes/ResourceRepository.php';

// Initialize logger
$logger = new Logger(config('LOGGING.log_dir'), config('LOGGING.log_level'));

try {
    $pdo = getDatabase();
    $courseRepo = new CourseRepository($pdo);
    $enrollmentRepo = new EnrollmentRepository($pdo);
    $resourceRepo = new ResourceRepository($pdo);
    
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? '';
    
    // Parse request body
    $input = [];
    if (in_array($method, ['POST', 'PUT'])) {
        $raw = file_get_contents('php://input');
        if (!empty(trim($raw))) {
            $input = json_decode($raw, true) ?? [];
        }
    }
    
    // Route handlers
    switch (true) {
        // ========== COURSES ==========
        case $method === 'GET' && $action === '':
            handleGetAllCourses($courseRepo, $logger);
            
        case $method === 'GET' && $action === 'get':
            handleGetCourse($courseRepo, $logger);
            
        case $method === 'GET' && $action === 'search':
            handleSearchCourses($courseRepo, $logger);
            
        case $method === 'POST' && $action === 'create':
            handleCreateCourse($courseRepo, $input, $pdo, $logger);
            
        case $method === 'PUT' && $action === 'update':
            handleUpdateCourse($courseRepo, $input, $logger);
            
        case $method === 'DELETE' && $action === 'delete':
            handleDeleteCourse($courseRepo, $logger);
        
        // ========== RESOURCES ==========
        case $method === 'GET' && $action === 'resources':
            handleGetResources($resourceRepo, $logger);
            
        case $method === 'POST' && $action === 'create-resource':
            handleCreateResource($resourceRepo, $input, $logger);
            
        case $method === 'PUT' && $action === 'update-resource':
            handleUpdateResource($resourceRepo, $input, $logger);
            
        case $method === 'DELETE' && $action === 'delete-resource':
            handleDeleteResource($resourceRepo, $logger);
        
        // ========== ENROLLMENTS ==========
        case $method === 'GET' && $action === 'enrollments':
            handleGetEnrollments($enrollmentRepo, $logger);
            
        case $method === 'POST' && $action === 'enroll':
            handleCreateEnrollment($enrollmentRepo, $courseRepo, $input, $pdo, $logger);
            
        case $method === 'PUT' && $action === 'update-enrollment':
            handleUpdateEnrollment($enrollmentRepo, $input, $logger);
            
        case $method === 'DELETE' && $action === 'delete-enrollment':
            handleDeleteEnrollment($enrollmentRepo, $logger);
            
        case $method === 'GET' && $action === 'enrollment-stats':
            handleGetEnrollmentStats($enrollmentRepo, $logger);
            
        // ========== ERROR ==========
        default:
            Response::error('Invalid API endpoint', null, 404)->send();
    }

} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    $logger->error('Database error', ['error' => $e->getMessage()]);
    Response::serverError('Database error occurred')->send();
} catch (Throwable $e) {
    error_log('API error: ' . $e->getMessage());
    $logger->error('API error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
    Response::serverError(isDebug() ? $e->getMessage() : 'An error occurred')->send();
}

// ========== COURSE HANDLERS ==========

function handleGetAllCourses(CourseRepository $courseRepo, Logger $logger): never
{
    $limit = min((int)($_GET['limit'] ?? 20), 100);
    $offset = (int)($_GET['offset'] ?? 0);
    $filters = [
        'category' => $_GET['category'] ?? '',
        'level' => $_GET['level'] ?? '',
        'status' => $_GET['status'] ?? '',
    ];
    
    $courses = $courseRepo->getAll($filters, $limit, $offset);
    $total = $courseRepo->count($filters);
    
    $logger->info('Fetched all courses', ['count' => count($courses)]);
    Response::success('Courses loaded', [
        'courses' => $courses,
        'pagination' => [
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset,
        ]
    ])->send();
}

function handleGetCourse(CourseRepository $courseRepo, Logger $logger): never
{
    $id = (int)($_GET['id'] ?? 0);
    
    if (!Validator::positiveInt($id)) {
        Response::error('Valid course ID is required', null, 400)->send();
    }
    
    $course = $courseRepo->getWithResources($id);
    if (!$course) {
        $logger->info('Course not found', ['course_id' => $id]);
        Response::notFound('Course not found')->send();
    }
    
    $logger->info('Fetched course', ['course_id' => $id]);
    Response::success('Course loaded', $course)->send();
}

function handleSearchCourses(CourseRepository $courseRepo, Logger $logger): never
{
    $query = trim($_GET['q'] ?? '');
    
    if (strlen($query) < 2) {
        Response::error('Search query must be at least 2 characters', null, 400)->send();
    }
    
    $limit = min((int)($_GET['limit'] ?? 20), 100);
    $offset = (int)($_GET['offset'] ?? 0);
    
    $courses = $courseRepo->search($query, $limit, $offset);
    $logger->info('Searched courses', ['query' => $query, 'results' => count($courses)]);
    
    Response::success('Search completed', ['courses' => $courses])->send();
}

function handleCreateCourse(CourseRepository $courseRepo, array $input, PDO $pdo, Logger $logger): never
{
    $validated = Validator::validateCourse($input);
    $validator = $validated['validator'];
    
    if ($validator->hasErrors()) {
        Response::validationError('Validation failed', $validator->getErrors())->send();
    }
    
    try {
        $id = $courseRepo->create($validated);
        
        // Log activity
        $stmt = $pdo->prepare('INSERT INTO activity_logs (project_id, action, ip_address, user_agent) VALUES (?, ?, ?, ?)');
        $stmt->execute([$id, 'COURSE_CREATED', $_SERVER['REMOTE_ADDR'] ?? null, $_SERVER['HTTP_USER_AGENT'] ?? null]);
        
        $logger->info('Course created', ['course_id' => $id, 'title' => $validated['title']]);
        Response::created('Course created successfully', ['id' => $id])->send();
    } catch (PDOException $e) {
        $logger->error('Failed to create course', ['error' => $e->getMessage()]);
        Response::serverError('Failed to create course')->send();
    }
}

function handleUpdateCourse(CourseRepository $courseRepo, array $input, Logger $logger): never
{
    $id = (int)($_GET['id'] ?? 0);
    
    if (!Validator::positiveInt($id)) {
        Response::error('Valid course ID is required', null, 400)->send();
    }
    
    if (!$courseRepo->getById($id)) {
        Response::notFound('Course not found')->send();
    }
    
    $validated = Validator::validateCourse($input);
    $validator = $validated['validator'];
    
    if ($validator->hasErrors()) {
        Response::validationError('Validation failed', $validator->getErrors())->send();
    }
    
    if ($courseRepo->update($id, $validated)) {
        $logger->info('Course updated', ['course_id' => $id]);
        Response::success('Course updated successfully')->send();
    } else {
        Response::serverError('Failed to update course')->send();
    }
}

function handleDeleteCourse(CourseRepository $courseRepo, Logger $logger): never
{
    $id = (int)($_GET['id'] ?? 0);
    
    if (!Validator::positiveInt($id)) {
        Response::error('Valid course ID is required', null, 400)->send();
    }
    
    if ($courseRepo->delete($id)) {
        $logger->info('Course deleted', ['course_id' => $id]);
        Response::success('Course deleted successfully')->send();
    } else {
        Response::notFound('Course not found')->send();
    }
}

// ========== RESOURCE HANDLERS ==========

function handleGetResources(ResourceRepository $resourceRepo, Logger $logger): never
{
    $projectId = (int)($_GET['project_id'] ?? 0);
    
    if (!Validator::positiveInt($projectId)) {
        Response::error('Valid course ID is required', null, 400)->send();
    }
    
    $resources = $resourceRepo->getByCourse($projectId);
    $logger->info('Fetched resources', ['project_id' => $projectId, 'count' => count($resources)]);
    
    Response::success('Resources loaded', ['resources' => $resources])->send();
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

