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
$roll_number = trim($_POST['roll_number'] ?? '');
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$gender = trim($_POST['gender'] ?? '');
$dob = trim($_POST['date_of_birth'] ?? '');
$classroom_id = (int)($_POST['classroom_id'] ?? 0);
$status = trim($_POST['status'] ?? 'Active');

if ($id <= 0 || empty($roll_number) || empty($username) || empty($email) || empty($classroom_id)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
    exit;
}

try {
    // Check duplicate email (excluding this user)
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email AND id != :id");
    $stmt->execute(['email' => $email, 'id' => $id]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email address is already in use by another student.']);
        exit;
    }

    // Check duplicate roll_number (excluding this user)
    $stmt = $pdo->prepare("SELECT id FROM users WHERE roll_number = :roll AND id != :id");
    $stmt->execute(['roll' => $roll_number, 'id' => $id]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Roll Number is already in use by another student.']);
        exit;
    }

    $dob_val = empty($dob) ? null : $dob;
    $password = $_POST['password'] ?? '';
    
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET roll_number = :roll, classroom_id = :cid, username = :uname, email = :email, status = :status, phone = :phone, gender = :gender, date_of_birth = :dob, password = :pass WHERE id = :id AND role = 'student'");
        $stmt->execute([
            'roll' => $roll_number,
            'cid' => $classroom_id,
            'uname' => $username,
            'email' => $email,
            'status' => $status,
            'phone' => empty($phone) ? null : $phone,
            'gender' => empty($gender) ? null : $gender,
            'dob' => $dob_val,
            'pass' => $hashed_password,
            'id' => $id
        ]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET roll_number = :roll, classroom_id = :cid, username = :uname, email = :email, status = :status, phone = :phone, gender = :gender, date_of_birth = :dob WHERE id = :id AND role = 'student'");
        $stmt->execute([
            'roll' => $roll_number,
            'cid' => $classroom_id,
            'uname' => $username,
            'email' => $email,
            'status' => $status,
            'phone' => empty($phone) ? null : $phone,
            'gender' => empty($gender) ? null : $gender,
            'dob' => $dob_val,
            'id' => $id
        ]);
    }

    log_activity($_SESSION['user_id'], 'Updated Student', "Updated profile for student: $username");

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
