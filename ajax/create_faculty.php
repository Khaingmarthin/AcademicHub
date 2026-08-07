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
$vision = trim($_POST['vision'] ?? '');
$mission = trim($_POST['mission'] ?? '');
$description = trim($_POST['description'] ?? '');

if (empty($name) || empty($code)) {
    echo json_encode(['success' => false, 'message' => 'Faculty Name and Code are required.']);
    exit;
}

try {
    // Check for duplicate name or code
    $stmt = $pdo->prepare("SELECT id FROM faculties WHERE faculty_name = :name OR faculty_code = :code");
    $stmt->execute(['name' => $name, 'code' => $code]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Faculty with this name or code already exists.']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO faculties (faculty_name, faculty_code, vision, mission, description) VALUES (:name, :code, :vision, :mission, :desc)");
    $stmt->execute([
        'name' => $name,
        'code' => $code,
        'vision' => $vision,
        'mission' => $mission,
        'desc' => $description
    ]);


    log_activity($_SESSION['user_id'], 'Created Faculty', "Created new faculty: $name ($code)");

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
