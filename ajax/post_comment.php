<?php
require_once '../config/session.php';
require_student(); // Enforces logged in student
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
$content = trim($_POST['content'] ?? '');
$parent_id = isset($_POST['parent_id']) && $_POST['parent_id'] !== '' ? (int)$_POST['parent_id'] : null;
$user_id = $_SESSION['user_id'];

if ($announcement_id <= 0 || empty($content)) {
    echo json_encode(['success' => false, 'message' => 'Announcement ID and content are required']);
    exit;
}

try {
    // Validate announcement exists
    $stmt = $pdo->prepare("SELECT id FROM announcements WHERE id = :aid");
    $stmt->execute(['aid' => $announcement_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Announcement not found']);
        exit;
    }

    // Insert comment
    $stmt = $pdo->prepare("INSERT INTO comments (announcement_id, user_id, comment, status) VALUES (:aid, :uid, :content, 'approved')");
    $stmt->execute([
        'aid' => $announcement_id,
        'uid' => $user_id,
        
        'content' => $content
    ]);

    $comment_id = $pdo->lastInsertId();
    log_activity($user_id, 'comment', "Posted comment ID $comment_id on announcement ID $announcement_id");

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
