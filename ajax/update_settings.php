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

$section = $_POST['section'] ?? '';

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (:key, :val) ON DUPLICATE KEY UPDATE setting_value = :val");
    
    $logo_url = null;

    if ($section === 'general') {
        $stmt->execute(['key' => 'site_name', 'val' => $_POST['site_name'] ?? '']);
        $stmt->execute(['key' => 'primary_color', 'val' => $_POST['primary_color'] ?? '#2563eb']);
        
        // Handle Logo Upload
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['logo'];
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
            if (!in_array($file['type'], $allowed_types)) {
                throw new Exception('Invalid logo format.');
            }
            if ($file['size'] > 2 * 1024 * 1024) {
                throw new Exception('Logo size exceeds 2MB limit.');
            }
            
            $upload_dir = '../../assets/images/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $new_name = 'logo_' . time() . '.' . $ext;
            $destination = $upload_dir . $new_name;
            $db_path = 'assets/images/' . $new_name;
            
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $stmt->execute(['key' => 'logo_path', 'val' => $db_path]);
                $logo_url = $db_path;
            }
        }
    } elseif ($section === 'contact') {
        $stmt->execute(['key' => 'contact_email', 'val' => $_POST['contact_email'] ?? '']);
        $stmt->execute(['key' => 'contact_phone', 'val' => $_POST['contact_phone'] ?? '']);
        $stmt->execute(['key' => 'contact_address', 'val' => $_POST['contact_address'] ?? '']);
    } elseif ($section === 'social') {
        $stmt->execute(['key' => 'social_facebook', 'val' => $_POST['social_facebook'] ?? '']);
        $stmt->execute(['key' => 'social_twitter', 'val' => $_POST['social_twitter'] ?? '']);
        $stmt->execute(['key' => 'social_linkedin', 'val' => $_POST['social_linkedin'] ?? '']);
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'logo_url' => $logo_url]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
