<?php
$base_dir = __DIR__;

// 1. admin/dashboard.php
$path = $base_dir . '/admin/dashboard.php';
$content = file_get_contents($path);
$content = str_replace("al.action", "al.activity as action", $content);
file_put_contents($path, $content);
echo "Updated dashboard.php\n";

// 2. admin/activity_logs.php
$path = $base_dir . '/admin/activity_logs.php';
$content = file_get_contents($path);
$content = str_replace("al.action", "al.activity as action", $content);
$content = str_replace("ORDER BY action ", "ORDER BY activity ", $content);
file_put_contents($path, $content);
echo "Updated activity_logs.php\n";

// 3. config/functions.php
$path = $base_dir . '/config/functions.php';
$content = file_get_contents($path);
$content = str_replace(
    'INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (:uid, :action, :desc, :ip)',
    'INSERT INTO activity_logs (user_id, activity, description) VALUES (:uid, :action, :desc)'
);
$content = preg_replace('/\'ip\' => \$ip_address\s*/', '', $content);
file_put_contents($path, $content);
echo "Updated functions.php\n";
