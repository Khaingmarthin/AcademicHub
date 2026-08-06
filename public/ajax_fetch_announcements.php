<?php
session_start();
require_once '../config/db.php';
require_once '../config/functions.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$type = isset($_GET['type']) ? $_GET['type'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Status logic constraint
$published_sql = "(ay.status != 'archived' OR ay.status IS NULL) AND (a.publish_date <= NOW() OR a.publish_date IS NULL)";

// Active academic year constraint
$stmt = $pdo->query("SELECT id, year_name FROM academic_years WHERE status = 'active' LIMIT 1");
$active_ay = $stmt->fetch();
$active_ay_id = $active_ay ? $active_ay['id'] : 0;
$ay_sql = $active_ay_id > 0 ? "AND a.academic_year_id = $active_ay_id" : "";

$where = ["$published_sql", "$ay_sql"];
$params = [];

if (!empty($q)) {
    $where[] = "(a.title LIKE :q OR a.content LIKE :q)";
    $params['q'] = "%$q%";
}

if ($category > 0) {
    $where[] = "a.category_id = :category";
    $params['category'] = $category;
}

if ($type === 'normal') {
    $where[] = "a.is_urgent = 0";
} elseif ($type === 'urgent') {
    $where[] = "a.is_urgent = 1";
}

if (!empty($date_from)) {
    $where[] = "DATE(COALESCE(a.publish_date, a.created_at)) >= :date_from";
    $params['date_from'] = $date_from;
}

if (!empty($date_to)) {
    $where[] = "DATE(COALESCE(a.publish_date, a.created_at)) <= :date_to";
    $params['date_to'] = $date_to;
}

$where_clause = implode(" AND ", array_filter($where));
if (!empty($where_clause)) {
    $where_clause = "WHERE " . $where_clause;
}

// Check if no filters applied, then we exclude the latest one like the home page does
$no_filters = empty($q) && empty($category) && empty($type) && empty($date_from) && empty($date_to);

if ($no_filters) {
    // Find latest ID to exclude
    $stmt_latest = $pdo->query("SELECT a.id FROM announcements a LEFT JOIN academic_years ay ON a.academic_year_id = ay.id WHERE $published_sql $ay_sql ORDER BY a.publish_date DESC, a.created_at DESC LIMIT 1");
    $latest = $stmt_latest->fetch();
    if ($latest) {
        $where_clause .= ($where_clause ? " AND " : "WHERE ") . "a.id != :latest_id";
        $params['latest_id'] = $latest['id'];
    }
}

// We might want to limit results if no filters
$limit_clause = $no_filters ? "LIMIT 6" : "LIMIT 50"; // Limit to 50 when filtering to avoid huge lists

$query = "SELECT a.*, c.category_name as category_name,
                 (SELECT CONCAT('assets/uploads/attachments/', file_name) FROM attachments WHERE announcement_id = a.id AND file_type LIKE 'image/%' LIMIT 1) as image_path
          FROM announcements a 
          LEFT JOIN categories c ON a.category_id = c.id 
          LEFT JOIN academic_years ay ON a.academic_year_id = ay.id
          $where_clause 
          ORDER BY a.publish_date DESC, a.created_at DESC 
          $limit_clause";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$announcements = $stmt->fetchAll();

$count = count($announcements);
$html = '';

if ($count > 0) {
    foreach ($announcements as $a) {
        $urgent_class = $a['is_urgent'] ? 'border-red-200 ring-2 ring-red-500/20' : 'border-gray-100';
        $html .= '<a href="announcement.php?id=' . $a['id'] . '" class="group bg-white rounded-3xl shadow-sm hover:shadow-xl border overflow-hidden flex flex-col transform transition-all duration-300 hover:-translate-y-2 ' . $urgent_class . '">';
        
        $html .= '<div class="h-48 bg-gray-100 relative overflow-hidden flex items-center justify-center border-b border-gray-100">';
        
        if ($a['is_urgent']) {
            $html .= '<div class="absolute top-4 left-4 bg-red-500 text-white text-[10px] font-bold px-2 py-1 rounded shadow-md uppercase tracking-wide z-10 animate-pulse flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        Urgent
                      </div>';
        }
        
        if (!empty($a['image_path'])) {
            $html .= '<img src="' . htmlspecialchars(base_url($a['image_path'])) . '" alt="Announcement Thumbnail" class="w-full h-full object-cover transform transition-transform duration-700 group-hover:scale-105">';
        } else {
            $html .= '<div class="absolute inset-0 bg-gradient-to-br from-gray-50 to-gray-100"></div>
                      <svg class="w-16 h-16 text-gray-300 z-0 transform transition-transform duration-700 group-hover:scale-110" fill="currentColor" viewBox="0 0 20 20"><path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z"></path></svg>';
        }
        
        $html .= '</div>';
        
        $html .= '<div class="p-5 sm:p-6 flex-1 flex flex-col">';
        $html .= '<div class="flex items-center justify-between mb-4">';
        $html .= '<span class="text-xs font-bold tracking-wider uppercase text-blue-700 bg-blue-50 px-2.5 py-1 rounded-md">' . htmlspecialchars($a['category_name'] ?? 'General') . '</span>';
        $html .= '<span class="text-[11px] sm:text-xs text-gray-500 font-medium flex items-center">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    ' . date('M d, Y', strtotime($a['publish_date'] ?? $a['created_at'])) . '
                  </span>';
        $html .= '</div>';
        
        $html .= '<h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-3 group-hover:text-blue-600 transition-colors line-clamp-2 leading-snug">' . htmlspecialchars($a['title']) . '</h3>';
        $html .= '<p class="text-gray-600 text-sm mb-6 line-clamp-3 flex-1">' . htmlspecialchars(strip_tags($a['content'])) . '</p>';
        
        $html .= '<div class="mt-auto pt-4 border-t border-gray-100">
                    <span class="inline-flex items-center text-sm font-bold text-blue-600 group-hover:text-blue-800 transition-colors">
                        Read More
                        <svg class="w-4 h-4 ml-1.5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </span>
                  </div>';
        $html .= '</div>';
        $html .= '</a>';
    }
} else {
    $html = '<div class="col-span-1 md:col-span-2 lg:col-span-3 bg-white p-10 rounded-2xl shadow-sm border border-gray-100 text-center">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">No announcements found</h3>
                <p class="text-gray-500">Try adjusting your filters or search term to find what you are looking for.</p>
             </div>';
}

header('Content-Type: application/json');
echo json_encode([
    'count' => $count,
    'html' => $html
]);
