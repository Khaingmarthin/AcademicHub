<?php
// config/session.php

if (session_status() === PHP_SESSION_NONE) {
    // Set secure session parameters before starting
    ini_set('session.use_only_cookies', 1);
    ini_set('session.use_strict_mode', 1);
    
    session_start();
}

// Auto login from Remember Me Cookie
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
    $token = $_COOKIE['remember_me'];
    
    // We need database access here. We assume db.php is available or included.
    // If not, we try to include it.
    if (!isset($pdo)) {
        if (file_exists(__DIR__ . '/db.php')) {
            require_once __DIR__ . '/db.php';
        }
    }
    
    if (isset($pdo)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE remember_token = :token LIMIT 1");
        $stmt->execute(['token' => $token]);
        $user = $stmt->fetch();
        
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            // Regenerate session id for security
            session_regenerate_id(true);
        } else {
            // Invalid token, delete cookie
            setcookie('remember_me', '', time() - 3600, '/');
        }
    }
}

// Initialize current academic year if logged in but not set
if (isset($_SESSION['user_id']) && !isset($_SESSION['current_academic_year_id'])) {
    if (!isset($pdo)) {
        if (file_exists(__DIR__ . '/db.php')) {
            require_once __DIR__ . '/db.php';
        }
    }
    if (isset($pdo)) {
        $stmt = $pdo->query("SELECT id FROM academic_years WHERE status = 'Active' LIMIT 1");
        $active_year = $stmt->fetch();
        if ($active_year) {
            $_SESSION['current_academic_year_id'] = $active_year['id'];
        }
    }
}

/**
 * Check if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Require a logged in user, otherwise redirect to login
 */
function require_login() {
    if (!is_logged_in()) {
        header("Location: /auth/login.php");
        exit();
    }
}

/**
 * Require a logged in admin user
 */
function require_admin() {
    require_login();
    if ($_SESSION['user_role'] !== 'admin') {
        // Redirect to a safe page if not admin
        header("Location: /student/dashboard.php"); 
        exit();
    }
}

/**
 * Require a logged in student user
 */
function require_student() {
    require_login();
    if ($_SESSION['user_role'] !== 'student') {
        // Redirect to admin dashboard if not student
        header("Location: /admin/dashboard.php");
        exit();
    }
}
?>
