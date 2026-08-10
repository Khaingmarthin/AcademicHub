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
$title = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');
$category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
$academic_year_id = !empty($_POST['academic_year_id']) ? (int)$_POST['academic_year_id'] : (get_global_active_academic_year($pdo)['id'] ?? null);
$publish_date = !empty($_POST['publish_date']) ? $_POST['publish_date'] : null;
$is_urgent = isset($_POST['is_urgent']) ? 1 : 0;

$status = get_calculated_status($pdo, $publish_date, $academic_year_id);

if (empty($id) || empty($title) || empty($content)) {
    echo json_encode(['success' => false, 'message' => 'ID, title and content are required']);
    exit;
}

// Check existing to retain attachment if not overwritten
$stmt = $pdo->prepare("SELECT file_name as attachment_path FROM attachments WHERE announcement_id = :id LIMIT 1");
$stmt->execute(['id' => $id]);

$existing = $stmt->fetch();
$attachment_path = $existing['attachment_path'] ?? null;

// File Upload
if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = '../../assets/uploads/announcements/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
    
    $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9.\-_]/', '', basename($_FILES['attachment']['name']));
    $target_file = $upload_dir . $filename;
    
    if (move_uploaded_file($_FILES['attachment']['tmp_name'], $target_file)) {
        $attachment_path = 'assets/uploads/announcements/' . $filename;
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to upload attachment']);
        exit;
    }
}

try {
    $stmt = $pdo->prepare("UPDATE announcements 
                           SET title = :title, content = :content, category_id = :category_id, 
                               academic_year_id = :academic_year_id, status = :status, 
                               publish_date = :publish_date, expire_date = NULL,
                               is_urgent = :is_urgent, is_featured = 0
                           WHERE id = :id");
    
    $stmt->execute([
        'title' => $title,
        'content' => $content,
        'category_id' => $category_id,
        'academic_year_id' => $academic_year_id,
        'status' => $status,
        'publish_date' => $publish_date,
        'is_urgent' => $is_urgent,
        'id' => $id
    ]);

    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK && $attachment_path) {
        $pdo->prepare("DELETE FROM attachments WHERE announcement_id = ?")->execute([$id]);
        $att_stmt = $pdo->prepare("INSERT INTO attachments (announcement_id, file_name, file_type, file_size) VALUES (?, ?, ?, ?)");
        $att_stmt->execute([$id, basename($attachment_path), mime_content_type("../../" . $attachment_path) ?: "application/octet-stream", filesize("../../" . $attachment_path) ?: 0]);
    }
    
    
    
    log_activity($_SESSION['user_id'], 'update_announcement', "Updated announcement: ID $id");
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
