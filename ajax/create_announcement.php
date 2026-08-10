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

$title = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');
$category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
$academic_year_id = !empty($_POST['academic_year_id']) ? (int)$_POST['academic_year_id'] : (get_global_active_academic_year($pdo)['id'] ?? null);
$publish_date = !empty($_POST['publish_date']) ? $_POST['publish_date'] : null;
$is_urgent = isset($_POST['is_urgent']) ? 1 : 0;

$status = get_calculated_status($pdo, $publish_date, $academic_year_id);
$author_id = $_SESSION['user_id'];

if (empty($title) || empty($content)) {
    echo json_encode(['success' => false, 'message' => 'Title and content are required']);
    exit;
}

// File Upload
$attachment_path = null;
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
    $stmt = $pdo->prepare("INSERT INTO announcements (title, content, user_id, category_id, academic_year_id, status, publish_date, expire_date, is_urgent, is_featured) 
                           VALUES (:title, :content, :user_id, :category_id, :academic_year_id, :status, :publish_date, NULL, :is_urgent, 0)");
    
    $stmt->execute([
        'title' => $title,
        'content' => $content,
        'user_id' => $author_id,
        'category_id' => $category_id,
        'academic_year_id' => $academic_year_id,
        'status' => $status,
        'publish_date' => $publish_date,
        'is_urgent' => $is_urgent
    ]);
    
    $announcement_id = $pdo->lastInsertId();
    if ($attachment_path) {
        $att_stmt = $pdo->prepare("INSERT INTO attachments (announcement_id, file_name, file_type, file_size) VALUES (?, ?, ?, ?)");
        $att_stmt->execute([$announcement_id, basename($attachment_path), mime_content_type("../../" . $attachment_path) ?: "application/octet-stream", filesize("../../" . $attachment_path) ?: 0]);
    }
    
    
    // Notification Generation
    if ($status === 'published') {
        $notify_msg = $is_urgent ? "Urgent Announcement: $title" : "New Announcement: $title";
        $link = "../../public/announcement.php?id=$announcement_id";
        
        // Build the query to select users to notify based on preferences
        $pref_column = $is_urgent ? 'urgent_enabled' : 'general_enabled';
        
        // We only notify students for now, but we could notify everyone. 
        // The prompt says "Students receive notifications".
        $notif_stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, title, message, link, type)
            SELECT u.id, :title, :message, :link, :type 
            FROM users u
            LEFT JOIN notification_settings ns ON u.id = ns.user_id
            WHERE u.role = 'student' AND (ns.$pref_column = 1 OR ns.$pref_column IS NULL)
        ");
        
        $notif_stmt->execute([
            'title' => $is_urgent ? 'Urgent Announcement' : 'New Announcement',
            'message' => $notify_msg,
            'link' => $link,
            'type' => $is_urgent ? 'urgent' : 'general'
        ]);
    }
    
    
    log_activity($author_id, 'create_announcement', "Created announcement: ID $announcement_id");
    
    echo json_encode(['success' => true, 'id' => $announcement_id]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
