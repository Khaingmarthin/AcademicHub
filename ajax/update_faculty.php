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
$vision = trim($_POST['vision'] ?? '');
$mission = trim($_POST['mission'] ?? '');
$description = trim($_POST['description'] ?? '');

if ($id <= 0 || empty($name) || empty($code)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data. Name and Code are required.']);
    exit;
}

try {
    // Check for duplicates (excluding current)
    $stmt = $pdo->prepare("SELECT id FROM faculties WHERE (faculty_name = :name OR faculty_code = :code) AND id != :id");
    $stmt->execute(['name' => $name, 'code' => $code, 'id' => $id]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Another faculty with this name or code already exists.']);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE faculties SET faculty_name = :name, faculty_code = :code, vision = :vision, mission = :mission, description = :desc WHERE id = :id");
    $stmt->execute([
        'name' => $name,
        'code' => $code,
        'vision' => $vision,
        'mission' => $mission,
        'desc' => $description,
        'id' => $id
    ]);


    log_activity($_SESSION['user_id'], 'Updated Faculty', "Updated faculty: $name ($code)");

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
