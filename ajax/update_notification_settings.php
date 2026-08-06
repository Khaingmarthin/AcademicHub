<?php
require_once '../config/session.php';
require_student();
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

$user_id = $_SESSION['user_id'];
$notify_new = isset($_POST['notify_new_announcement']) ? 1 : 0;
$notify_urgent = isset($_POST['notify_urgent_announcement']) ? 1 : 0;
$notify_timetable = isset($_POST['notify_timetable_update']) ? 1 : 0;

try {
    $stmt = $pdo->prepare("
        INSERT INTO notification_settings (user_id, general_enabled, urgent_enabled, timetable_enabled)
        VALUES (:uid, :nna, :nua, :ntu)
        ON DUPLICATE KEY UPDATE 
            general_enabled = :nna, 
            urgent_enabled = :nua, 
            timetable_enabled = :ntu
    ");
    $stmt->execute([
        'nna' => $notify_new,
        'nua' => $notify_urgent,
        'ntu' => $notify_timetable,
        'uid' => $user_id
    ]);

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
