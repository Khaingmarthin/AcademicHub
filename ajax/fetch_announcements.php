<?php
require_once '../config/session.php';
require_admin();
require_once '../config/db.php';
require_once '../config/functions.php';

header('Content-Type: application/json');

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
if (!in_array($limit, [10, 20, 50, 100])) $limit = 10;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$search = $_GET['search'] ?? '';
$category_id = $_GET['category_id'] ?? '';
$academic_year_id = $_GET['academic_year_id'] ?? (get_global_active_academic_year($pdo)['id'] ?? '');
$status_filter = $_GET['status_filter'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$sort = $_GET['sort'] ?? 'newest';

$where = [];
$params = [];

if (!empty($search)) {
    $where[] = "(a.title LIKE :search OR a.content LIKE :search)";
    $params['search'] = "%$search%";
}
if (!empty($category_id)) {
    $where[] = "a.category_id = :category_id";
    $params['category_id'] = $category_id;
}
if (!empty($academic_year_id)) {
    $where[] = "a.academic_year_id = :academic_year_id";
    $params['academic_year_id'] = $academic_year_id;
}
if (!empty($status_filter)) {
    if ($status_filter === 'draft') {
        $where[] = "(ay.status != 'archived' OR ay.status IS NULL) AND a.publish_date > NOW()";
    } elseif ($status_filter === 'published') {
        $where[] = "(ay.status != 'archived' OR ay.status IS NULL) AND (a.publish_date <= NOW() OR a.publish_date IS NULL)";
    } elseif ($status_filter === 'archived') {
        $where[] = "ay.status = 'archived'";
    }
}
if (!empty($date_from)) {
    $where[] = "(a.publish_date >= :date_from OR (a.publish_date IS NULL AND DATE(a.created_at) >= :date_from))";
    $params['date_from'] = $date_from;
}
if (!empty($date_to)) {
    $where[] = "(a.publish_date <= :date_to OR (a.publish_date IS NULL AND DATE(a.created_at) <= :date_to))";
    $params['date_to'] = $date_to;
}

$whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Sorting (purely chronological, urgent does not affect order)
$orderBy = "ORDER BY a.publish_date DESC, a.created_at DESC";
if ($sort === 'oldest') {
    $orderBy = "ORDER BY a.publish_date ASC, a.created_at ASC";
} elseif ($sort === 'most_viewed') {
    $orderBy = "ORDER BY a.view_count DESC, a.publish_date DESC";
} elseif ($sort === 'recently_updated') {
    $orderBy = "ORDER BY a.updated_at DESC, a.publish_date DESC";
} elseif ($sort === 'newest') {
    $orderBy = "ORDER BY a.publish_date DESC, a.created_at DESC";
}

try {
    // Count total for pagination
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM announcements a $whereClause");
    $countStmt->execute($params);
    $total_items = $countStmt->fetchColumn();
    $total_pages = ceil($total_items / $limit);

    // Fetch paginated results with attachment count
    $sql = "SELECT a.*, 
                   c.category_name as category_name, 
                   ay.year_name as academic_year_name,
                   ay.status as academic_year_status,
                   u.username as author_name,
                   (SELECT COUNT(*) FROM attachments att WHERE att.announcement_id = a.id) as attachment_count,
                   (SELECT file_name FROM attachments att WHERE att.announcement_id = a.id LIMIT 1) as first_attachment_name
            FROM announcements a 
            LEFT JOIN categories c ON a.category_id = c.id 
            LEFT JOIN academic_years ay ON a.academic_year_id = ay.id 
            LEFT JOIN users u ON a.user_id = u.id
            $whereClause 
            $orderBy
            LIMIT $limit OFFSET $offset";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll();

    // Process data for JSON
    $announcements = [];
    foreach ($results as $a) {
        // Calculate status dynamically
        $calculatedStatus = get_calculated_status($pdo, $a['publish_date'], $a['academic_year_id']);
        
        $statusText = 'Published';
        $statusClass = 'bg-green-100 text-green-800';
        
        if ($calculatedStatus === 'draft') {
            $statusText = 'Draft';
            $statusClass = 'bg-yellow-100 text-yellow-800';
        } elseif ($calculatedStatus === 'archived') {
            $statusText = 'Archived';
            $statusClass = 'bg-gray-100 text-gray-800';
        }
        
        $a['status_text'] = $statusText;
        $a['status_class'] = $statusClass;
        $a['publish_date_formatted'] = $a['publish_date'] ? date('M d, Y H:i:s', strtotime($a['publish_date'])) : 'Immediate';
        $a['created_date_formatted'] = date('M d, Y H:i:s', strtotime($a['created_at']));
        $a['category_name'] = $a['category_name'] ?? 'Uncategorized';
        $a['author_name'] = $a['author_name'] ?? 'Admin';
        
        $announcements[] = $a;
    }

    echo json_encode([
        'success' => true,
        'announcements' => $announcements,
        'pagination' => [
            'total_items' => $total_items,
            'total_pages' => $total_pages,
            'current_page' => $page,
            'limit' => $limit,
            'offset' => $offset
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
