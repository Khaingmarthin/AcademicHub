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
$status = $_POST['status'] ?? '';

if (empty($id) || !in_array($status, ['active', 'archived', 'preparation'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

try {
    $pdo->beginTransaction();

    if ($status === 'active') {
        // Enforce rule: Only one active year
        $pdo->exec("UPDATE academic_years SET status = 'archived' WHERE status = 'active'");
        
        $stmt = $pdo->prepare("UPDATE academic_years SET status = 'active' WHERE id = :id");
        $stmt->execute(['id' => $id]);
    } else {
        // Enforce rule: Cannot archive the currently active year directly
        $stmt = $pdo->prepare("SELECT status FROM academic_years WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $current_status = $stmt->fetchColumn();
        
        if ($current_status === 'active') {
            echo json_encode(['success' => false, 'message' => 'You cannot archive the currently active academic year. Please set another year as active first.']);
            $pdo->rollBack();
            exit;
        }
        
        $stmt = $pdo->prepare("UPDATE academic_years SET status = :status WHERE id = :id");
        $stmt->execute(['status' => $status, 'id' => $id]);
    }

    $pdo->commit();
    log_activity($_SESSION['user_id'], 'academic_year_change', "Changed academic year ID $id status to $status");
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
