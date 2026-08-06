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

if (empty($id) || empty($name)) {
    echo json_encode(['success' => false, 'message' => 'ID and Name are required']);
    exit;
}

try {
    // Check for duplicate
    $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM academic_years WHERE year_name = :name AND id != :id");
    $check_stmt->execute(['name' => $name, 'id' => $id]);
    if ($check_stmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'message' => 'Academic year already exists']);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE academic_years SET year_name = :name WHERE id = :id");
    $stmt->execute(['name' => $name, 'id' => $id]);
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
