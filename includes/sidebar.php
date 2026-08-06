<!-- includes/sidebar.php -->
<?php 
$role = $_SESSION['user_role'] ?? ''; 
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

function is_active($page, $dir = 'admin') {
    global $current_page, $current_dir;
    if (is_array($page)) {
        return in_array($current_page, $page) && $current_dir == $dir;
    }
    return $current_page == $page && $current_dir == $dir;
}

function nav_link($href, $icon, $label, $active, $iconColor = 'text-gray-400') {
    if ($active) {
        $activeClass = 'bg-[#2563EB] text-white shadow-sm font-semibold';
        $iconClass = 'text-white';
    } else {
        $activeClass = 'text-gray-700 hover:bg-gray-100 hover:text-gray-900';
        $iconClass = $iconColor;
    }
    
    return '
    <a href="' . $href . '" class="sidebar-item-link relative flex items-center px-5 h-[52px] rounded-xl transition-all duration-300 gap-4 ' . $activeClass . ' group" title="' . $label . '">
        <div class="flex-shrink-0 w-6 h-6 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
            <i data-lucide="' . $icon . '" class="w-5 h-5 ' . $iconClass . '"></i>
        </div>
        <span class="sidebar-full-content text-sm font-semibold transition-opacity duration-300">' . $label . '</span>
        <span class="sidebar-tooltip absolute left-full ml-4 px-3 py-1.5 bg-gray-900/90 text-white text-xs font-semibold rounded-lg shadow-lg backdrop-blur-sm whitespace-nowrap z-50 pointer-events-none transition-opacity duration-300 opacity-0 group-hover:opacity-100">' . $label . '</span>
    </a>';
}
?>

<!-- Sidebar Backdrop (for Mobile overlay) -->
<div id="sidebar-backdrop" class="fixed inset-0 bg-black/50 z-30 hidden opacity-0 transition-opacity duration-300 lg:hidden"></div>

<!-- Sidebar container -->
<aside class="admin-sidebar text-gray-700 shadow-sm flex-shrink-0 flex flex-col transition-all-300">
    <!-- Top Section -->
    <div class="h-[72px] px-6 flex items-center justify-between border-b border-gray-100 flex-shrink-0 relative">
        <div class="flex items-center gap-4 sidebar-full-content">
            <!-- University Logo in rounded square -->
            <div class="w-10 h-10 bg-white border border-gray-100 rounded-xl flex items-center justify-center flex-shrink-0 transition-transform duration-300 hover:scale-105 p-1 overflow-hidden">
                <img src="<?php echo htmlspecialchars(base_url('/assets/images/ucsmtla%20logo.png')); ?>" alt="UCSMTLA Logo" class="w-full h-full object-contain">
            </div>
            <!-- Title -->
            <div class="leading-tight">
                <h1 class="text-gray-800 font-bold text-sm tracking-wide">UCSMTLA</h1>
                <span class="text-gray-400 text-[10px] font-bold uppercase tracking-wider">Admin Panel</span>
            </div>
        </div>
        
        <!-- Collapse Button in normal view (only visible when sidebar is expanded) -->
        <button class="sidebar-collapse-btn sidebar-full-content text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg p-2 focus:outline-none transition-colors duration-200" aria-label="Collapse Sidebar">
            <i data-lucide="chevron-left" class="h-5 w-5"></i>
        </button>
        
        <!-- Expand Button in collapsed view (only visible when sidebar is collapsed) -->
        <button class="sidebar-collapse-btn sidebar-collapsed-content-flex hidden absolute inset-0 m-auto w-10 h-10 bg-gray-100 hover:bg-gray-200 rounded-xl flex items-center justify-center text-gray-600 focus:outline-none transition-colors duration-200" aria-label="Expand Sidebar">
            <i data-lucide="menu" class="h-5 w-5"></i>
        </button>
    </div>
    
    <!-- Navigation Container -->
    <nav class="flex-1 px-4 py-4 overflow-y-auto [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none] space-y-1">
        <?php if ($role === 'admin'): ?>
            <?php echo nav_link('../admin/dashboard.php', 'layout-grid', 'Dashboard', is_active('dashboard.php'), 'text-blue-500'); ?>
            <?php echo nav_link('../admin/announcements.php', 'megaphone', 'Announcements', is_active(['announcements.php', 'create_announcement.php', 'edit_announcement.php', 'view_announcement.php']), 'text-orange-500'); ?>
            <?php echo nav_link('../admin/categories.php', 'tag', 'Categories', is_active('categories.php'), 'text-purple-500'); ?>
            
            <?php echo nav_link('../admin/faculties.php', 'building-2', 'Faculties', is_active('faculties.php'), 'text-yellow-500'); ?>
            <?php echo nav_link('../admin/courses.php', 'book-open', 'Courses', is_active('courses.php'), 'text-indigo-500'); ?>
            <?php echo nav_link('../admin/classrooms.php', 'presentation', 'Classrooms', is_active('classrooms.php'), 'text-emerald-500'); ?>
            
            <?php echo nav_link('../admin/timetables.php', 'calendar-days', 'Timetables', is_active(['timetables.php', 'upload_timetable.php']), 'text-cyan-500'); ?>
            <?php echo nav_link('../admin/academic_years.php', 'graduation-cap', 'Academic Years', is_active(['academic_years.php']), 'text-teal-500'); ?>
            <?php echo nav_link('../admin/students.php', 'users', 'Student Management', is_active(['students.php', 'promote_students.php']), 'text-green-500'); ?>
        <?php else: ?>
            <?php echo nav_link('../student/dashboard.php', 'layout-grid', 'Dashboard', is_active('dashboard.php', 'student'), 'text-blue-500'); ?>
            <?php echo nav_link('../student/announcements.php', 'megaphone', 'Announcements', is_active('announcements.php', 'student'), 'text-orange-500'); ?>
            <?php echo nav_link('../student/timetable.php', 'calendar-days', 'Timetables', is_active('timetable.php', 'student'), 'text-cyan-500'); ?>
        <?php endif; ?>
    </nav>
    
    <!-- Logout Container -->
    <div class="p-4 border-t border-gray-100 mt-auto flex-shrink-0">
        <?php echo nav_link('../auth/logout.php', 'log-out', 'Logout', false, 'text-red-500'); ?>
    </div>
</aside>
