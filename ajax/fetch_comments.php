<?php
require_once '../config/db.php';
require_once '../config/functions.php';

header('Content-Type: application/json');

$announcement_id = isset($_GET['announcement_id']) ? (int)$_GET['announcement_id'] : 0;

if ($announcement_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid announcement ID']);
    exit;
}

try {
    // Fetch all approved comments for this announcement
    // Also fetch the user's name
    $stmt = $pdo->prepare("
        SELECT c.id,  c.comment as content, c.created_at, u.username as author_name 
        FROM comments c
        JOIN users u ON c.user_id = u.id
        WHERE c.announcement_id = :aid AND c.status = 'approved'
        ORDER BY c.created_at DESC
    ");
    $stmt->execute(['aid' => $announcement_id]);
    $all_comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Organize into a tree structure
    $comments_by_id = [];
    $tree = [];

    // First pass: put all into associative array and format fields
    foreach ($all_comments as $c) {
        $c['children'] = [];
        $comments_by_id[$c['id']] = $c;
    }

    // Second pass: build the tree
    foreach ($comments_by_id as $id => &$c) {
        if (isset($c['parent_id']) && $c['parent_id'] !== null && isset($comments_by_id[$c['parent_id']])) {
            // Append to parent's children (we prepend to keep replies chronological if parent is DESC, but let's sort them later)
            $comments_by_id[$c['parent_id']]['children'][] = &$c;
        } else {
            // It's a root comment
            $tree[] = &$c;
        }
    }

    // Sort children ascending (oldest first)
    function sort_children(&$node) {
        if (!empty($node['children'])) {
            usort($node['children'], function($a, $b) {
                return strtotime($a['created_at']) - strtotime($b['created_at']);
            });
            foreach ($node['children'] as &$child) {
                sort_children($child);
            }
        }
    }

    foreach ($tree as &$root) {
        sort_children($root);
    }

    echo json_encode([
        'success' => true, 
        'total' => count($all_comments),
        'comments' => $tree
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
