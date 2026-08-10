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
$classroom_id = (int)($_POST['classroom_id'] ?? 0);
$semester = $_POST['semester'] ?? '';

if ($id <= 0 || $classroom_id === 0 || !in_array($semester, ['first', 'second'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data provided']);
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

// Fetch existing timetable
$stmt = $pdo->prepare("SELECT * FROM timetables WHERE id = :id");
$stmt->execute(['id' => $id]);
$existing = $stmt->fetch();

if (!$existing) {
    echo json_encode(['success' => false, 'message' => 'Timetable not found']);
    exit;
}

// Fetch classroom details
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

$db_path = $existing['file_path'];
$title = ucfirst($semester) . " Semester Timetable";

// Handle File Upload if provided
if (isset($_FILES['timetable_file']) && $_FILES['timetable_file']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['timetable_file'];
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    $allowed_exts = ['pdf', 'jpg', 'jpeg', 'png'];
    if (!in_array($file_ext, $allowed_exts)) {
        echo json_encode(['success' => false, 'message' => 'Only PDF, JPG, and PNG files are allowed']);
        exit;
    }

    if ($file['size'] > 10 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'File size exceeds 10MB limit']);
        exit;
    }

    $upload_dir = '../../assets/uploads/timetables/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $new_file_name = 'timetable_cls_' . $classroom_id . '_' . $active_ay_id . '_' . $semester . '_' . time() . '.' . $file_ext;
    $destination = $upload_dir . $new_file_name;
    
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        echo json_encode(['success' => false, 'message' => 'Failed to save the uploaded file']);
        exit;
    }
    
    // Delete old file
    if (file_exists('../../' . $existing['file_path'])) {
        unlink('../../' . $existing['file_path']);
    }
    
    $db_path = 'assets/uploads/timetables/' . $new_file_name;
}

try {
    $stmt = $pdo->prepare("UPDATE timetables SET 
                           academic_year_id = :ayid, 
                           academic_year_level_id = :aylid, 
                           course_id = :courseid, 
                           major_id = :mid, 
                           classroom_id = :cid, 
                           semester = :sem, 
                           title = :title, 
                           file_path = :path, 
                           uploaded_by = :uid 
                           WHERE id = :id");
    $stmt->execute([
        'ayid' => $active_ay_id,
        'aylid' => $ay_level_id,
        'courseid' => $course_id,
        'mid' => $major_id,
        'cid' => $classroom_id,
        'sem' => $semester,
        'title' => $title,
        'path' => $db_path,
        'uid' => $_SESSION['user_id'],
        'id' => $id
    ]);
    
    log_activity($_SESSION['user_id'], 'Updated Timetable', "Updated timetable ID: $id");

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
