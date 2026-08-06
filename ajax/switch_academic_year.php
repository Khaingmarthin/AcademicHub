<?php
require_once '../config/session.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_SESSION['user_role'] === 'admin' && isset($_POST['academic_year_id'])) {
        $_SESSION['current_academic_year_id'] = (int) $_POST['academic_year_id'];
    }
    
    $return_url = $_POST['return_url'] ?? '/admin/dashboard.php';
    header("Location: " . $return_url);
    exit();
}
header("Location: /admin/dashboard.php");
exit();
?>
