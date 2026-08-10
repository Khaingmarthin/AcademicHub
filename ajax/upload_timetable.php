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

$classroom_id = (int)($_POST['classroom_id'] ?? 0);
$semester = $_POST['semester'] ?? '';

if ($classroom_id === 0 || !in_array($semester, ['first', 'second'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid classroom or semester selection']);
    exit;
}

// Ensure there is an active academic year based on session
$active_ay_id = get_global_active_academic_year($pdo)['id'] ?? 0;
if (!$active_ay_id) {
    $stmt = $pdo->query("SELECT id FROM academic_years WHERE status = 'Active' LIMIT 1");
    $active_ay_id = $stmt->fetchColumn();
}

if (!$active_ay_id) {
    echo json_encode(['success' => false, 'message' => 'No academic year selected']);
    exit;
}

// Fetch classroom details to get level, course, major
$stmt = $pdo->prepare("SELECT academic_year_level_id, course_id FROM classrooms WHERE id = :cid LIMIT 1");
$stmt->execute(['cid' => $classroom_id]);
$classroom = $stmt->fetch();
if (!$classroom) {
    echo json_encode(['success' => false, 'message' => 'Invalid classroom']);
    exit;
}

$ay_level_id = $classroom['academic_year_level_id'];
$course_id = $classroom['course_id'];

// Get major_id from course
$stmt = $pdo->prepare("SELECT id FROM majors WHERE course_id = :course_id LIMIT 1");
$stmt->execute(['course_id' => $course_id]);
$major_id = $stmt->fetchColumn() ?: null;

// Handle File Upload
if (!isset($_FILES['timetable_file']) || $_FILES['timetable_file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Failed to upload PDF file']);
    exit;
}

$file = $_FILES['timetable_file'];
$file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

$allowed_exts = ['pdf', 'jpg', 'jpeg', 'png'];
if (!in_array($file_ext, $allowed_exts)) {
    echo json_encode(['success' => false, 'message' => 'Only PDF, JPG, and PNG files are allowed']);
    exit;
}

// Validate file size (10MB max)
if ($file['size'] > 10 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'File size exceeds 10MB limit']);
    exit;
}

// Define upload path
$upload_dir = '../../assets/uploads/timetables/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Generate unique file name
$new_file_name = 'timetable_cls_' . $classroom_id . '_' . $active_ay_id . '_' . $semester . '_' . time() . '.' . $file_ext;
$destination = $upload_dir . $new_file_name;
$db_path = 'assets/uploads/timetables/' . $new_file_name;

if (!move_uploaded_file($file['tmp_name'], $destination)) {
    echo json_encode(['success' => false, 'message' => 'Failed to save the uploaded file']);
    exit;
}

try {
    $title = ucfirst($semester) . " Semester Timetable";
    
    // Insert new record
    $stmt = $pdo->prepare("INSERT INTO timetables (academic_year_id, academic_year_level_id, course_id, major_id, classroom_id, semester, title, file_path, uploaded_by) VALUES (:ayid, :aylid, :courseid, :mid, :cid, :sem, :title, :path, :uid)");
    $stmt->execute([
        'ayid' => $active_ay_id,
        'aylid' => $ay_level_id,
        'courseid' => $course_id,
        'mid' => $major_id,
        'cid' => $classroom_id,
        'sem' => $semester,
        'title' => $title,
        'path' => $db_path,
        'uid' => $_SESSION['user_id']
    ]);
    // Notification Generation
    $notif_stmt = $pdo->prepare("
        INSERT INTO notifications (user_id, title, message, link, type)
        SELECT u.id, 'Timetable Update', 'A new timetable has been posted for your classroom.', '../../student/timetable.php', 'timetable'
        FROM users u
        WHERE u.role = 'student' AND u.classroom_id = :cid
    ");
    $notif_stmt->execute(['cid' => $classroom_id]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
