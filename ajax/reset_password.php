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
    echo json_encode(['success' => false, 'message' => 'Invalid student ID.']);
    exit;
}

try {
    // Generate a secure 8-character temporary password
    $temp_password = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 8);
    $hashed_password = password_hash($temp_password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("UPDATE users SET password = :pass WHERE id = :id AND role = 'student'");
    $stmt->execute(['pass' => $hashed_password, 'id' => $id]);

    if ($stmt->rowCount() > 0) {
        $stmt = $pdo->prepare("SELECT username FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $username = $stmt->fetchColumn();
        
        logActivity($pdo, $_SESSION['user_id'], 'Password Reset', "Reset password for student: $username");
        
        echo json_encode([
            'success' => true, 
            'message' => 'Password reset successfully.', 
            'temp_password' => $temp_password
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Student not found or failed to update.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
