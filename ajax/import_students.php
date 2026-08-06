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

if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Failed to upload CSV file']);
    exit;
}

$file = $_FILES['import_file'];
$file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if ($file_ext !== 'csv') {
    echo json_encode(['success' => false, 'message' => 'Only CSV files are allowed for import']);
    exit;
}

// Fetch classrooms mapping
// Map by classroom_name -> id
$classrooms_map = [];
$stmt = $pdo->query("SELECT id, classroom_name FROM classrooms");
while ($row = $stmt->fetch()) {
    $classrooms_map[strtolower($row['classroom_name'])] = $row['id'];
}

$success_count = 0;
$error_count = 0;
$errors = [];

if (($handle = fopen($file['tmp_name'], "r")) !== FALSE) {
    // Read header
    $header = fgetcsv($handle, 1000, ",");
    // Expected format: Student ID, Full Name, Email, Classroom
    
    $pdo->beginTransaction();
    try {
        $insert_stmt = $pdo->prepare("INSERT INTO users (student_id, classroom_id, username, email, password, role, status) VALUES (:sid, :cid, :uname, :email, :pass, 'student', 'Active')");
        $notif_stmt = $pdo->prepare("INSERT INTO notification_settings (user_id) VALUES (:uid)");

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if (count($data) < 4) continue;
            
            $sid = trim($data[0]);
            $uname = trim($data[1]);
            $email = trim($data[2]);
            $class_name = strtolower(trim($data[3]));
            
            if (empty($sid) || empty($uname) || empty($email) || empty($class_name)) {
                $error_count++;
                $errors[] = "Row with empty required fields skipped.";
                continue;
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error_count++;
                $errors[] = "Invalid email format for $email.";
                continue;
            }
            
            if (!isset($classrooms_map[$class_name])) {
                $error_count++;
                $errors[] = "Classroom '$class_name' not found in system.";
                continue;
            }
            
            $cid = $classrooms_map[$class_name];
            
            // Check duplicates
            $chk = $pdo->prepare("SELECT id FROM users WHERE email = ? OR student_id = ?");
            $chk->execute([$email, $sid]);
            if ($chk->fetch()) {
                $error_count++;
                $errors[] = "Student ID $sid or Email $email already exists.";
                continue;
            }
            
            // Generate default password (e.g. Student ID)
            $password = password_hash($sid, PASSWORD_DEFAULT);
            
            $insert_stmt->execute([
                'sid' => $sid,
                'cid' => $cid,
                'uname' => $uname,
                'email' => $email,
                'pass' => $password
            ]);
            
            $new_user_id = $pdo->lastInsertId();
            $notif_stmt->execute(['uid' => $new_user_id]);
            
            $success_count++;
        }
        $pdo->commit();
        
        if ($success_count > 0) {
            logActivity($pdo, $_SESSION['user_id'], 'Imported Students', "Imported $success_count students via CSV.");
        }
        
        $msg = "Import complete. $success_count imported successfully. $error_count failed.";
        
        echo json_encode(['success' => true, 'message' => $msg, 'errors' => array_slice($errors, 0, 5)]); // Return top 5 errors
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Database error during import: ' . $e->getMessage()]);
    }
    fclose($handle);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to read CSV file']);
}
