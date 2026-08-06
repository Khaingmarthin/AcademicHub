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
    echo json_encode(['success' => false, 'message' => 'Invalid timetable ID.']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT file_path FROM timetables WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $timetable = $stmt->fetch();

    if ($timetable) {
        if (file_exists('../../' . $timetable['file_path'])) {
            unlink('../../' . $timetable['file_path']);
        }
        
        $stmt = $pdo->prepare("DELETE FROM timetables WHERE id = :id");
        $stmt->execute(['id' => $id]);
        
        logActivity($pdo, $_SESSION['user_id'], 'Deleted Timetable', "Deleted timetable ID: $id");
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Timetable not found.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
