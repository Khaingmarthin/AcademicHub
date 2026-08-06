<?php
require_once '../config/db.php';
require_once '../config/functions.php';

header('Content-Type: application/json');

$q = $_GET['q'] ?? '';
$category_id = isset($_GET['category']) && $_GET['category'] !== '' ? (int)$_GET['category'] : null;
$type = $_GET['type'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if ($page < 1) $page = 1;
$limit = 9; // 9 cards per page looks good in a 3-column grid
$offset = ($page - 1) * $limit;

try {
    // Force active academic year and published
    $stmt_active_ay = $pdo->query("SELECT id FROM academic_years WHERE status = 'active' LIMIT 1");
    $active_ay_id = $stmt_active_ay->fetchColumn();

    $where_clauses = [
        "(ay.status != 'archived' OR ay.status IS NULL)",
        "(a.publish_date <= NOW() OR a.publish_date IS NULL)"
    ];
    $params = [];

    if ($active_ay_id) {
        $where_clauses[] = "a.academic_year_id = :ay";
        $params['ay'] = $active_ay_id;
    }

    if (!empty($q)) {
        $where_clauses[] = "(a.title LIKE :q OR a.content LIKE :q)";
        $params['q'] = '%' . $q . '%';
    }

    if ($category_id) {
        $where_clauses[] = "a.category_id = :cat";
        $params['cat'] = $category_id;
    }

    if ($type === 'normal') {
        $where_clauses[] = "a.is_urgent = 0";
    } elseif ($type === 'urgent') {
        $where_clauses[] = "a.is_urgent = 1";
    }

    if (!empty($date_from)) {
        $where_clauses[] = "DATE(COALESCE(a.publish_date, a.created_at)) >= :date_from";
        $params['date_from'] = $date_from;
    }

    if (!empty($date_to)) {
        $where_clauses[] = "DATE(COALESCE(a.publish_date, a.created_at)) <= :date_to";
        $params['date_to'] = $date_to;
    }

    $where_sql = implode(' AND ', $where_clauses);

    // Get total count
    $count_sql = "SELECT COUNT(*) FROM announcements a LEFT JOIN academic_years ay ON a.academic_year_id = ay.id WHERE $where_sql";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total_items = $count_stmt->fetchColumn();
    $total_pages = ceil($total_items / $limit);

    // Get results
    $sql = "SELECT a.id, a.title, a.content, a.publish_date, a.created_at, a.is_urgent, c.category_name as category_name,
                   (SELECT CONCAT('assets/uploads/attachments/', file_name) FROM attachments WHERE announcement_id = a.id AND file_type LIKE 'image/%' LIMIT 1) as image_path
            FROM announcements a
            LEFT JOIN categories c ON a.category_id = c.id
            LEFT JOIN academic_years ay ON a.academic_year_id = ay.id
            WHERE $where_sql 
            ORDER BY a.publish_date DESC, a.created_at DESC 
            LIMIT $limit OFFSET $offset";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format content snippet (remove tags and limit length)
    foreach ($results as &$r) {
        $clean_content = strip_tags($r['content']);
        if (mb_strlen($clean_content) > 100) {
            $r['snippet'] = mb_substr($clean_content, 0, 100) . '...';
        } else {
            $r['snippet'] = $clean_content;
        }
        unset($r['content']); // Don't send full content
        
        $r['formatted_date'] = date('M d, Y', strtotime($r['publish_date'] ?? $r['created_at']));
    }

    echo json_encode([
        'success' => true,
        'results' => $results,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_items' => $total_items
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
