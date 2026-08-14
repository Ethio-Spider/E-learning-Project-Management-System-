<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db.php';

// Get request method
$method = $_SERVER['REQUEST_METHOD'];
$request = isset($_GET['action']) ? $_GET['action'] : '';

try {
    switch($method) {
        case 'GET':
            if ($request === 'list') {
                getAllProjects();
            } elseif ($request === 'get' && isset($_GET['id'])) {
                getProject($_GET['id']);
            } elseif ($request === 'search' && isset($_GET['q'])) {
                searchProjects($_GET['q']);
            } elseif ($request === 'resources' && isset($_GET['id'])) {
                getProjectResources($_GET['id']);
            } else {
                getAllProjects();
            }
            break;

        case 'POST':
            if ($request === 'create') {
                createProject();
            } elseif ($request === 'enroll') {
                enrollStudent();
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
            }
            break;

        case 'PUT':
            if ($request === 'update' && isset($_GET['id'])) {
                updateProject($_GET['id']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
            }
            break;

        case 'DELETE':
            if ($request === 'delete' && isset($_GET['id'])) {
                deleteProject($_GET['id']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

// ============= FUNCTIONS =============

function getAllProjects() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT * FROM projects ORDER BY created_at DESC");
        $projects = $stmt->fetchAll();
        echo json_encode(['success' => true, 'data' => $projects]);
    } catch (PDOException $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function getProject($id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
        $stmt->execute([$id]);
        $project = $stmt->fetch();
        
        if (!$project) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Project not found']);
            return;
        }
        
        echo json_encode(['success' => true, 'data' => $project]);
    } catch (PDOException $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function searchProjects($query) {
    global $pdo;
    try {
        $search = "%$query%";
        $stmt = $pdo->prepare("
            SELECT * FROM projects 
            WHERE title LIKE ? OR description LIKE ? OR category LIKE ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$search, $search, $search]);
        $projects = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'data' => $projects, 'count' => count($projects)]);
    } catch (PDOException $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function createProject() {
    global $pdo;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validation
    if (!isset($input['title']) || empty(trim($input['title']))) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Title is required']);
        return;
    }
    
    if (!isset($input['description']) || empty(trim($input['description']))) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Description is required']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO projects (title, description, category, instructor, duration, level, status, image_url)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $result = $stmt->execute([
            trim($input['title']),
            trim($input['description']),
            $input['category'] ?? 'General',
            $input['instructor'] ?? 'TBD',
            $input['duration'] ?? 'Self-paced',
            $input['level'] ?? 'Beginner',
            $input['status'] ?? 'Active',
            $input['image_url'] ?? ''
        ]);
        
        if ($result) {
            $id = $pdo->lastInsertId();
            http_response_code(201);
            echo json_encode([
                'success' => true, 
                'message' => 'Project created successfully',
                'id' => $id
            ]);
        } else {
            throw new Exception('Failed to create project');
        }
    } catch (PDOException $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function updateProject($id) {
    global $pdo;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    try {
        // Check if project exists
        $check = $pdo->prepare("SELECT id FROM projects WHERE id = ?");
        $check->execute([$id]);
        
        if (!$check->fetch()) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Project not found']);
            return;
        }
        
        $stmt = $pdo->prepare("
            UPDATE projects 
            SET title = ?, description = ?, category = ?, instructor = ?, 
                duration = ?, level = ?, status = ?, image_url = ?
            WHERE id = ?
        ");
        
        $result = $stmt->execute([
            trim($input['title'] ?? ''),
            trim($input['description'] ?? ''),
            $input['category'] ?? 'General',
            $input['instructor'] ?? 'TBD',
            $input['duration'] ?? 'Self-paced',
            $input['level'] ?? 'Beginner',
            $input['status'] ?? 'Active',
            $input['image_url'] ?? '',
            $id
        ]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Project updated successfully']);
        } else {
            throw new Exception('Failed to update project');
        }
    } catch (PDOException $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function deleteProject($id) {
    global $pdo;
    
    try {
        // Check if project exists
        $check = $pdo->prepare("SELECT id FROM projects WHERE id = ?");
        $check->execute([$id]);
        
        if (!$check->fetch()) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Project not found']);
            return;
        }
        
        $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
        
        if ($stmt->execute([$id])) {
            echo json_encode(['success' => true, 'message' => 'Project deleted successfully']);
        } else {
            throw new Exception('Failed to delete project');
        }
    } catch (PDOException $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function getProjectResources($projectId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM resources WHERE project_id = ? ORDER BY created_at DESC");
        $stmt->execute([$projectId]);
        $resources = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'data' => $resources]);
    } catch (PDOException $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function enrollStudent() {
    global $pdo;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validation
    if (!isset($input['project_id']) || empty($input['project_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Project ID is required']);
        return;
    }
    
    if (!isset($input['student_name']) || empty(trim($input['student_name']))) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Student name is required']);
        return;
    }
    
    if (!isset($input['email']) || empty(trim($input['email'])) || !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Valid email is required']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO enrollments (project_id, student_name, email, status)
            VALUES (?, ?, ?, ?)
        ");
        
        $result = $stmt->execute([
            $input['project_id'],
            trim($input['student_name']),
            trim($input['email']),
            'Enrolled'
        ]);
        
        if ($result) {
            http_response_code(201);
            echo json_encode([
                'success' => true, 
                'message' => 'Enrollment successful'
            ]);
        } else {
            throw new Exception('Failed to enroll student');
        }
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Student already enrolled in this project']);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
?>
