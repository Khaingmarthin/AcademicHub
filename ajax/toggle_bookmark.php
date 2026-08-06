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

$announcement_id = (int)($_POST['announcement_id'] ?? 0);
$user_id = $_SESSION['user_id'];

if ($announcement_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid announcement ID']);
    exit;
}

try {
    // Check if bookmark exists
    $stmt = $pdo->prepare("SELECT id FROM bookmarks WHERE user_id = :uid AND announcement_id = :aid");
    $stmt->execute(['uid' => $user_id, 'aid' => $announcement_id]);
    $bookmark = $stmt->fetch();

    if ($bookmark) {
        // Remove it
        $stmt = $pdo->prepare("DELETE FROM bookmarks WHERE id = :id");
        $stmt->execute(['id' => $bookmark['id']]);
        log_activity($user_id, 'bookmark', "Removed bookmark for announcement ID $announcement_id");
        echo json_encode(['success' => true, 'action' => 'removed']);
    } else {
        // Add it
        $stmt = $pdo->prepare("INSERT INTO bookmarks (user_id, announcement_id) VALUES (:uid, :aid)");
        $stmt->execute(['uid' => $user_id, 'aid' => $announcement_id]);
        log_activity($user_id, 'bookmark', "Added bookmark for announcement ID $announcement_id");
        echo json_encode(['success' => true, 'action' => 'added']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
