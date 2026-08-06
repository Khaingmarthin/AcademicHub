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

$action_type = $_POST['action_type'] ?? '';
$student_ids = $_POST['student_ids'] ?? [];
$target_classroom_id = (int)($_POST['target_classroom_id'] ?? 0);

if (empty($student_ids) || !is_array($student_ids)) {
    echo json_encode(['success' => false, 'message' => 'No students selected.']);
    exit;
}

if ($action_type !== 'promote' && $action_type !== 'graduate') {
    echo json_encode(['success' => false, 'message' => 'Invalid action type.']);
    exit;
}

if ($action_type === 'promote' && $target_classroom_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Target classroom is required for promotion.']);
    exit;
}

try {
    $pdo->beginTransaction();
    $count = 0;

    if ($action_type === 'promote') {
        $stmt = $pdo->prepare("UPDATE users SET classroom_id = :cid WHERE id = :id AND role = 'student'");
        foreach ($student_ids as $sid) {
            $stmt->execute(['cid' => $target_classroom_id, 'id' => (int)$sid]);
            $count += $stmt->rowCount();
        }
        $logMsg = "Promoted $count students to classroom ID $target_classroom_id";
    } else {
        $stmt = $pdo->prepare("UPDATE users SET status = 'Graduated', classroom_id = NULL WHERE id = :id AND role = 'student'");
        foreach ($student_ids as $sid) {
            $stmt->execute(['id' => (int)$sid]);
            $count += $stmt->rowCount();
        }
        $logMsg = "Graduated $count students";
    }

    $pdo->commit();

    logActivity($pdo, $_SESSION['user_id'], 'Student Batch Action', $logMsg);

    echo json_encode(['success' => true, 'message' => "Successfully processed $count student(s)."]);
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
