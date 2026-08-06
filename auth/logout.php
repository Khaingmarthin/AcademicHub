<?php
require_once '../config/session.php';
require_once '../config/functions.php';

require_once '../config/db.php';

// Log logout before unsetting session
if (isset($_SESSION['user_id'])) {
    log_activity($_SESSION['user_id'], 'logout', 'User logged out successfully');
}

// Unset all session variables
$_SESSION = array();

// Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Clear remember me cookie
if (isset($_COOKIE['remember_me'])) {
    // If we wanted to be super secure, we could also clear the token in DB, but we'd need DB connection here
    setcookie('remember_me', '', time() - 3600, '/');
}

// Redirect to login page
header("Location: login.php");
exit();
?>
