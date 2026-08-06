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

$name = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');
$color = trim($_POST['color'] ?? '#2563EB');
$icon = trim($_POST['icon'] ?? 'folder');

if (empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Category name is required.']);
    exit;
}

try {
    // Check for duplicates
    $stmt = $pdo->prepare("SELECT id FROM categories WHERE category_name = :name");
    $stmt->execute(['name' => $name]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Category with this name already exists.']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO categories (category_name, description, color, icon) VALUES (:name, :desc, :color, :icon)");
    $stmt->execute([
        'name' => $name,
        'desc' => $description,
        'color' => $color,
        'icon' => $icon
    ]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
