<?php
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['csrf_token'] = 'test';
$_POST['student_id'] = 'STU-TEST-999';
$_POST['roll_number'] = '1CS-999';
$_POST['username'] = 'Test Student';
$_POST['email'] = 'teststudent999@example.com';
$_POST['classroom_id'] = 1;
$_POST['password'] = 'password123';
$_POST['password_confirm'] = 'password123';

// Mock session
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';
$_SESSION['csrf_token'] = 'test';

require 'c:/wamp64/www/academichub/ajax/create_student.php';
?>
