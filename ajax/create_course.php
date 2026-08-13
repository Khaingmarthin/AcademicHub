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

$name = trim($_POST['name'] ?? '');
$code = trim($_POST['code'] ?? '');
$faculty_id = (int)($_POST['faculty_id'] ?? 0);
$major = trim($_POST['major'] ?? '');
$year_level = trim($_POST['year_level'] ?? '');
$semester = trim($_POST['semester'] ?? '');
$credits = trim($_POST['credits'] ?? '');
$status = trim($_POST['status'] ?? 'Active');
$description = trim($_POST['description'] ?? '');

if (empty($name) || empty($code) || $faculty_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Course Name, Code, and Faculty are required.']);
    exit;
}

$academic_year_id = isset($_POST['academic_year_id']) ? (int)$_POST['academic_year_id'] : (get_global_active_academic_year($pdo)['id'] ?? null);
if (!$academic_year_id) {
    echo json_encode(['success' => false, 'message' => 'No active academic year selected. Please select an academic year from the header.']);
    exit;
}

try {
    // Check for duplicate code
    $stmt = $pdo->prepare("SELECT id FROM courses WHERE course_code = :code");
    $stmt->execute(['code' => $code]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Course with this code already exists.']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO courses (academic_year_id, faculty_id, course_name, course_code, major, year_level, semester, credits, description, status) VALUES (:academic_year_id, :faculty_id, :name, :code, :major, :year_level, :semester, :credits, :desc, :status)");
    $stmt->execute([
        'academic_year_id' => $academic_year_id,
        'faculty_id' => $faculty_id,
        'name' => $name,
        'code' => $code,
        'major' => $major ?: null,
        'year_level' => $year_level ?: null,
        'semester' => $semester ?: null,
        'credits' => $credits !== '' ? (int)$credits : null,
        'desc' => $description,
        'status' => $status
    ]);

    log_activity($_SESSION['user_id'], 'Created Course', "Created new course: $name ($code)");

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
