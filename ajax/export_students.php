<?php
require_once '../config/session.php';
require_admin();
require_once '../config/db.php';
require_once '../config/functions.php';

$stmt = $pdo->query("
    SELECT 
        u.student_id, 
        u.username as full_name, 
        u.email, 
        c.classroom_name, 
        u.status,
        m.major_name as major,
        ay.year_name as academic_year
    FROM users u
    LEFT JOIN classrooms c ON u.classroom_id = c.id
    LEFT JOIN majors m ON c.major_id = m.id
    LEFT JOIN academic_years ay ON c.academic_year_id = ay.id
    WHERE u.role = 'student'
    ORDER BY u.created_at DESC
");

$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=students_export_' . date('Ymd_His') . '.csv');

$output = fopen('php://output', 'w');

// Output CSV headers
fputcsv($output, ['Student ID', 'Full Name', 'Email', 'Classroom', 'Major', 'Academic Year', 'Status']);

foreach ($students as $s) {
    fputcsv($output, [
        $s['student_id'],
        $s['full_name'],
        $s['email'],
        $s['classroom_name'] ?: 'None',
        $s['major'] ?: 'None',
        $s['academic_year'] ?: 'None',
        $s['status']
    ]);
}

fclose($output);
exit;
