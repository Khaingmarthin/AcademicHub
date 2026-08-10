<?php
// includes/sidebar.php

$current_page = basename($_SERVER['PHP_SELF']);

function nav_link($url, $icon, $label, $current_page, $badge = null, $iconColorClass = '') {
    $isActive = ($current_page == $url);
    $activeClass = $isActive 
        ? "bg-[#2563EB] text-white shadow-md shadow-blue-500/30 transform scale-[1.02]" 
        : "text-gray-600 hover:bg-gray-100 hover:text-[#2563EB]";
    
    $iconColor = $isActive ? "text-white" : $iconColorClass;
    
    $badgeHtml = '';
    if ($badge) {
        $badgeClass = $isActive ? "bg-white text-blue-600" : "bg-red-500 text-white";
        $badgeHtml = "<span class=\"ml-auto px-2 py-0.5 text-[10px] font-bold rounded-full $badgeClass shadow-sm\">$badge</span>";
    }

    return "
    <a href=\"$url\" class=\"flex items-center px-4 py-2.5 mb-1 rounded-xl transition-all duration-300 font-semibold text-sm $activeClass group relative overflow-hidden\">
        <i data-lucide=\"$icon\" class=\"w-5 h-5 mr-3 $iconColor transition-transform duration-300 group-hover:scale-110\"></i>
        <span class=\"z-10 tracking-wide\">$label</span>
        $badgeHtml
        " . ($isActive ? "<div class='absolute inset-0 bg-white opacity-10 pointer-events-none'></div>" : "") . "
    </a>
    ";
}
?>

<!-- Mobile Sidebar Backdrop -->
<div id="admin-sidebar-backdrop" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-40 lg:hidden opacity-0 invisible transition-all duration-300 cursor-pointer"></div>

<!-- Sidebar -->
<aside class="admin-sidebar bg-slate-100 text-gray-700 shadow-sm flex-shrink-0 flex flex-col transition-all-300 w-[260px] lg:w-[280px] h-screen overflow-hidden hidden lg:flex fixed left-0 top-0 z-40 border-r border-gray-200">
    <!-- Brand -->
    <div class="h-[72px] flex items-center justify-center border-b border-gray-200/50 px-6 relative flex-shrink-0">
        <a href="dashboard.php" class="flex items-center gap-3 group">
            <img src="<?php echo htmlspecialchars(base_url('/assets/images/ucsmtla logo.png')); ?>" alt="UCSMTLA Logo" class="h-9 w-auto object-contain drop-shadow-sm group-hover:scale-105 transition-transform duration-300">
            <div class="flex flex-col leading-tight">
                <span class="text-lg font-black bg-clip-text text-transparent bg-gradient-to-r from-gray-800 to-gray-600 tracking-tight">UCSMTLA</span>
                <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Admin Panel</span>
            </div>
        </a>
        <button id="admin-mobile-close-btn" class="lg:hidden absolute right-4 text-gray-400 hover:text-red-500 hover:bg-red-50 p-2 rounded-lg transition-colors">
            <i data-lucide="x" class="w-5 h-5"></i>
        </button>
    </div>
    
    <!-- Navigation -->
    <nav class="flex-1 py-6 px-4">
        <?php echo nav_link('dashboard.php', 'layout-dashboard', 'Dashboard', $current_page, null, 'text-blue-500'); ?>
        <?php echo nav_link('announcements.php', 'megaphone', 'Announcements', $current_page, null, 'text-orange-500'); ?>
        <?php echo nav_link('categories.php', 'tags', 'Categories', $current_page, null, 'text-purple-500'); ?>
        <?php echo nav_link('faculties.php', 'building-2', 'Faculties', $current_page, null, 'text-indigo-500'); ?>
        <?php echo nav_link('courses.php', 'graduation-cap', 'Courses', $current_page, null, 'text-cyan-500'); ?>
        <?php echo nav_link('classrooms.php', 'monitor-play', 'Classrooms', $current_page, null, 'text-teal-500'); ?>
        <?php echo nav_link('timetables.php', 'calendar-days', 'Timetables', $current_page, null, 'text-rose-500'); ?>
        <?php echo nav_link('academic_years.php', 'calendar-clock', 'Academic Years', $current_page, null, 'text-amber-500'); ?>
    </nav>
    
    <!-- Bottom Profile Area -->
    <div class="p-4 border-t border-gray-200/50 mt-auto">
        <a href="../auth/logout.php" class="flex items-center justify-center w-full px-4 py-3 bg-red-50 text-red-600 hover:bg-red-600 hover:text-white rounded-xl transition-all duration-300 font-bold text-sm group shadow-sm">
            <i data-lucide="log-out" class="w-4 h-4 mr-2 transition-transform group-hover:-translate-x-1"></i>
            Logout
        </a>
    </div>
</aside>

<!-- Main content area wrapper -->
<div id="main-content-wrapper" class="flex flex-col flex-1 min-w-0 bg-[#F8FAFC]">
