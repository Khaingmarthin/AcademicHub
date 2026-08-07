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

$student_id = trim($_POST['student_id'] ?? '');
if (empty($student_id)) {
    $student_id = 'STU-' . strtoupper(uniqid());
}
$roll_number = trim($_POST['roll_number'] ?? '');
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$gender = trim($_POST['gender'] ?? '');
$dob = trim($_POST['date_of_birth'] ?? '');
$classroom_id = (int)($_POST['classroom_id'] ?? 0);
$status = trim($_POST['status'] ?? 'Active');
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';

if (empty($student_id) || empty($roll_number) || empty($username) || empty($email) || empty($classroom_id) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
    exit;
}

if ($password !== $password_confirm) {
    echo json_encode(['success' => false, 'message' => 'Passwords do not match.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
    exit;
}

try {
    // Check duplicate email
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email address is already in use.']);
        exit;
    }

    // Check duplicate student_id
    $stmt = $pdo->prepare("SELECT id FROM users WHERE student_id = :sid");
    $stmt->execute(['sid' => $student_id]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Student ID is already assigned.']);
        exit;
    }

    // Check duplicate roll_number
    $stmt = $pdo->prepare("SELECT id FROM users WHERE roll_number = :roll");
    $stmt->execute(['roll' => $roll_number]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Roll Number is already in use.']);
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $dob_val = empty($dob) ? null : $dob;
    
    $stmt = $pdo->prepare("INSERT INTO users (student_id, roll_number, classroom_id, username, email, password, role, status, phone, gender, date_of_birth) VALUES (:sid, :roll, :cid, :uname, :email, :pass, 'student', :status, :phone, :gender, :dob)");
    
    $stmt->execute([
        'sid' => $student_id,
        'roll' => $roll_number,
        'cid' => $classroom_id,
        'uname' => $username,
        'email' => $email,
        'pass' => $hashed_password,
        'status' => $status,
        'phone' => empty($phone) ? null : $phone,
        'gender' => empty($gender) ? null : $gender,
        'dob' => $dob_val
    ]);
    
    $new_user_id = $pdo->lastInsertId();

    // Default Notification Settings for new student
    $stmt = $pdo->prepare("INSERT INTO notification_settings (user_id) VALUES (:uid)");
    $stmt->execute(['uid' => $new_user_id]);

    log_activity($_SESSION['user_id'], 'Created Student', "Created student account for $username ($student_id)");

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
