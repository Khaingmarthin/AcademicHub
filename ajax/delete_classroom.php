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
    echo json_encode(['success' => false, 'message' => 'Invalid classroom ID.']);
    exit;
}

try {
    // Check if students are attached to this classroom
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE classroom_id = :id AND role = 'student'");
    $stmt->execute(['id' => $id]);
    $studentCount = $stmt->fetchColumn();
    
    if ($studentCount > 0) {
        echo json_encode(['success' => false, 'message' => 'This classroom contains students and cannot be deleted.']);
        exit;
    }
    
    // Get name for logging
    $stmt = $pdo->prepare("SELECT classroom_name FROM classrooms WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $classroomName = $stmt->fetchColumn();

    $stmt = $pdo->prepare("DELETE FROM classrooms WHERE id = :id");
    $stmt->execute(['id' => $id]);

    if ($classroomName) {
        log_activity($_SESSION['user_id'], 'Deleted Classroom', "Deleted classroom: $classroomName");
    }

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
