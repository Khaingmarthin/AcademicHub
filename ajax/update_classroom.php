<?php
require_once '../config/session.php';
require_admin();
require_once '../config/db.php';
require_once '../config/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$csrf_token = $_POST['csrf_token'] ?? '';
if (!verify_csrf_token($csrf_token)) {
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$ay_id = (int)($_POST['academic_year_id'] ?? 0);
$major_id = (int)($_POST['major_id'] ?? 0);
$ay_level_id = (int)($_POST['academic_year_level_id'] ?? 0);
$section_input = trim($_POST['section'] ?? '');
$status = trim($_POST['status'] ?? 'Active');

if ($id <= 0 || $ay_id <= 0 || $major_id <= 0 || $ay_level_id <= 0 || empty($section_input)) {
    echo json_encode(['success' => false, 'message' => 'All required fields must be filled.']);
    exit;
}

try {
    // Get Major Name
    $stmt = $pdo->prepare("SELECT major_name FROM majors WHERE id = :id");
    $stmt->execute(['id' => $major_id]);
    $major_name = $stmt->fetchColumn();

    // Get Year Level Name
    $stmt = $pdo->prepare("SELECT level_name FROM academic_year_levels WHERE id = :id");
    $stmt->execute(['id' => $ay_level_id]);
    $level_name = $stmt->fetchColumn();
    
    if (!$major_name || !$level_name) {
        echo json_encode(['success' => false, 'message' => 'Invalid Major or Year Level.']);
        exit;
    }

    // Determine section string
    $section = ($section_input === 'None') ? '' : $section_input;

    // Generate Classroom Name
    $classroom_name = '';
    
    if (strtolower($major_name) === 'common') {
        if ($section) {
            $classroom_name = "{$level_name} ({$section})";
        } else {
            $classroom_name = $level_name;
        }
    } else if (strtolower($major_name) === 'computer science') {
        if ($section) {
            $classroom_name = "{$level_name} CS ({$section})";
        } else {
            $classroom_name = "{$level_name} CS";
        }
    } else if (strtolower($major_name) === 'computer technology') {
        if ($section) {
            $classroom_name = "{$level_name} CT ({$section})";
        } else {
            $classroom_name = "{$level_name} CT";
        }
    } else {
        // Fallback for other potential majors
        $abbr = strtoupper(substr($major_name, 0, 2));
        if ($section) {
            $classroom_name = "{$level_name} {$abbr} ({$section})";
        } else {
            $classroom_name = "{$level_name} {$abbr}";
        }
    }

    // Check for duplicate within the same academic year (excluding this classroom)
    $stmt = $pdo->prepare("SELECT id FROM classrooms WHERE classroom_name = :name AND academic_year_id = :ay_id AND id != :id");
    $stmt->execute(['name' => $classroom_name, 'ay_id' => $ay_id, 'id' => $id]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => "Another classroom '{$classroom_name}' already exists in this academic year."]);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE classrooms SET academic_year_id = :ay, academic_year_level_id = :ay_level, major_id = :major, section = :sec, classroom_name = :name, status = :status WHERE id = :id");
    $stmt->execute([
        'ay' => $ay_id,
        'ay_level' => $ay_level_id,
        'major' => $major_id,
        'sec' => $section_input,
        'name' => $classroom_name,
        'status' => $status,
        'id' => $id
    ]);

    log_activity($_SESSION['user_id'], 'Updated Classroom', "Updated classroom: $classroom_name");

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
