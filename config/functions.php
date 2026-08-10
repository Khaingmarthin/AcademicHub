<?php
// config/functions.php

/**
 * Sanitize user input to prevent XSS
 */
function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

/**
 * Redirect to a specific URL
 */
function redirect($url) {
    header("Location: " . base_url($url));
    exit();
}

/**
 * Get base URL for assets and links
 */
function base_url($path = '') {
    $base = (strpos($_SERVER['REQUEST_URI'], '/academichub') === 0) ? '/academichub' : '';
    return $base . '/' . ltrim($path, '/');
}

/**
 * Generate CSRF Token
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF Token
 */
function verify_csrf_token($token) {
    if (isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
        return true;
    }
    return false;
}

/**
 * Set a flash message
 */
function set_flash_message($type, $message) {
    $_SESSION['flash'][$type] = $message;
}

/**
 * Display flash messages
 */
function display_flash_messages() {
    if (isset($_SESSION['flash'])) {
        foreach ($_SESSION['flash'] as $type => $message) {
            $bgColor = 'bg-blue-100 border-blue-500 text-blue-700';
            if ($type === 'error') $bgColor = 'bg-red-100 border-red-500 text-red-700';
            if ($type === 'success') $bgColor = 'bg-green-100 border-green-500 text-green-700';
            
            echo '<div class="border-l-4 p-4 mb-4 ' . $bgColor . '" role="alert">';
            echo '<p>' . htmlspecialchars($message) . '</p>';
            echo '</div>';
        }
        unset($_SESSION['flash']);
    }
}

/**
 * Get a system setting from the database.
 * Caches settings statically to avoid multiple queries per page load.
 */
function get_setting($key, $default = '') {
    global $pdo;
    static $settings_cache = null;

    if ($settings_cache === null) {
        try {
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $settings_cache = [];
            foreach ($rows as $row) {
                $settings_cache[$row['setting_key']] = $row['setting_value'];
            }
        } catch (PDOException $e) {
            $settings_cache = []; // Fallback to empty on error
        }
    }

    return isset($settings_cache[$key]) ? $settings_cache[$key] : $default;
}

/**
 * Log user activity
 */
function log_activity($user_id, $action, $description = null) {
    global $pdo;
    if (!$pdo) return false;
    
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, activity, description) VALUES (:uid, :action, :desc)");
        return $stmt->execute([
            'uid' => $user_id,
            'action' => $action,
            'desc' => $description
        ]);
    } catch (PDOException $e) {
        return false; // Fail silently for logs
    }
}

/**
 * Calculate announcement status based on publish date and academic year status
 */
function get_calculated_status($pdo, $publish_date, $academic_year_id) {
    if ($academic_year_id) {
        $stmt = $pdo->prepare("SELECT status FROM academic_years WHERE id = :ay_id");
        $stmt->execute(['ay_id' => $academic_year_id]);
        $ay_status = $stmt->fetchColumn();
        if ($ay_status && strtolower($ay_status) === 'archived') {
            return 'archived';
        }
    }
    
    if (!empty($publish_date)) {
        $pub_time = strtotime($publish_date);
        if ($pub_time > time()) {
            return 'draft';
        }
    }
    return 'published';
}

/**
 * Get the global active academic year from the database.
 * Caches the result statically per request to avoid redundant queries.
 */
function get_global_active_academic_year($pdo) {
    static $active_year = null;
    if ($active_year === null) {
        try {
            $stmt = $pdo->query("SELECT id, year_name as name, status FROM academic_years WHERE status = 'active' LIMIT 1");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $active_year = $row;
            } else {
                $active_year = ['id' => 0, 'name' => 'Not Set', 'status' => ''];
            }
        } catch (PDOException $e) {
            $active_year = ['id' => 0, 'name' => 'Not Set', 'status' => ''];
        }
    }
    return $active_year;
}
?>
