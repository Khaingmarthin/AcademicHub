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
$name = trim($_POST['name'] ?? '');
$code = trim($_POST['code'] ?? '');
$faculty_id = (int)($_POST['faculty_id'] ?? 0);
$major = trim($_POST['major'] ?? '');
$year_level = trim($_POST['year_level'] ?? '');
$semester = trim($_POST['semester'] ?? '');
$credits = trim($_POST['credits'] ?? '');
$status = trim($_POST['status'] ?? 'Active');
$description = trim($_POST['description'] ?? '');

if ($id <= 0 || empty($name) || empty($code) || $faculty_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data. Name, Code, and Faculty are required.']);
    exit;
}

try {
    // Check for duplicate code (excluding current)
    $stmt = $pdo->prepare("SELECT id FROM courses WHERE course_code = :code AND id != :id");
    $stmt->execute(['code' => $code, 'id' => $id]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Another course with this code already exists.']);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE courses SET faculty_id = :faculty_id, course_name = :name, course_code = :code, major = :major, year_level = :year_level, semester = :semester, credits = :credits, description = :desc, status = :status WHERE id = :id");
    $stmt->execute([
        'faculty_id' => $faculty_id,
        'name' => $name,
        'code' => $code,
        'major' => $major ?: null,
        'year_level' => $year_level ?: null,
        'semester' => $semester ?: null,
        'credits' => $credits !== '' ? (int)$credits : null,
        'desc' => $description,
        'status' => $status,
        'id' => $id
    ]);

    logActivity($pdo, $_SESSION['user_id'], 'Updated Course', "Updated course: $name ($code)");

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
