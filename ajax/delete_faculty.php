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

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid faculty ID.']);
    exit;
}

try {
    // Check if courses are attached
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM courses WHERE faculty_id = :id");
    $stmt->execute(['id' => $id]);
    $courseCount = $stmt->fetchColumn();
    
    if ($courseCount > 0) {
        echo json_encode(['success' => false, 'message' => 'This faculty contains courses and cannot be deleted.']);
        exit;
    }
    
    // Get name for logging
    $stmt = $pdo->prepare("SELECT faculty_name FROM faculties WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $facultyName = $stmt->fetchColumn();

    $stmt = $pdo->prepare("DELETE FROM faculties WHERE id = :id");
    $stmt->execute(['id' => $id]);

    if ($facultyName) {
        log_activity($_SESSION['user_id'], 'Deleted Faculty', "Deleted faculty: $facultyName");
    }

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
