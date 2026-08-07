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
    echo json_encode(['success' => false, 'message' => 'Invalid course ID.']);
    exit;
}

try {
    // Check if students/classrooms are attached to this course
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM classrooms WHERE course_id = :id");
    $stmt->execute(['id' => $id]);
    $classroomCount = $stmt->fetchColumn();
    
    if ($classroomCount > 0) {
        echo json_encode(['success' => false, 'message' => 'Cannot delete: students are currently assigned to classrooms using this course.']);
        exit;
    }
    
    // Get name for logging
    $stmt = $pdo->prepare("SELECT course_name, course_code FROM courses WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $course = $stmt->fetch();

    $stmt = $pdo->prepare("DELETE FROM courses WHERE id = :id");
    $stmt->execute(['id' => $id]);

    if ($course) {
        log_activity($_SESSION['user_id'], 'Deleted Course', "Deleted course: {$course['course_name']} ({$course['course_code']})");
    }

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
