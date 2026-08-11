<?php
// includes/sidebar.php

$current_page = basename($_SERVER['PHP_SELF']);

function nav_link($url, $icon, $label, $current_page, $badge = null, $iconColorClass = '', $active_pages = []) {
    $isActive = ($current_page == $url || in_array($current_page, $active_pages));
    $activeClass = $isActive 
        ? "bg-white text-blue-700 shadow-lg shadow-blue-900/25 scale-[1.02]"
        : "text-sky-50 hover:bg-white/15 hover:text-white";
    
    $iconColor = $isActive ? "text-blue-600" : $iconColorClass;
    
    $badgeHtml = '';
    if ($badge) {
        $badgeClass = $isActive ? "bg-blue-600 text-white" : "bg-red-500 text-white";
        $badgeHtml = "<span class=\"ml-auto px-2 py-0.5 text-[10px] font-bold rounded-full $badgeClass shadow-sm\">$badge</span>";
    }

    return "
    <a href=\"$url\" class=\"flex items-center px-4 py-2.5 mb-1 rounded-xl transition-all duration-300 font-semibold text-sm $activeClass group relative overflow-hidden\">
        <i data-lucide=\"$icon\" class=\"w-5 h-5 mr-3 $iconColor transition-transform duration-300 group-hover:scale-110\"></i>
        <span class=\"z-10 tracking-wide\">$label</span>
        $badgeHtml
        " . ($isActive ? "<div class='absolute inset-0 bg-blue-500/10 pointer-events-none'></div>" : "") . "
    </a>
    ";
}
?>

<!-- Mobile Sidebar Backdrop -->
<div id="admin-sidebar-backdrop" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-40 lg:hidden opacity-0 invisible transition-all duration-300 cursor-pointer"></div>

<!-- Sidebar -->
<aside class="admin-sidebar text-sky-50 flex-shrink-0 flex flex-col transition-all-300 w-[260px] lg:w-[280px] h-screen overflow-hidden hidden lg:flex fixed left-0 top-0 z-40 border-r border-white/20 bg-gradient-to-b from-sky-400 via-blue-500 to-blue-800 shadow-2xl shadow-sky-900/40">
    <!-- Brand -->
    <div class="h-[72px] flex items-center justify-center border-b border-white/20 px-6 relative flex-shrink-0">
        <a href="dashboard.php" class="flex items-center gap-3 group">
            <img src="<?php echo htmlspecialchars(base_url('/assets/images/ucsmtla logo.png')); ?>" alt="UCSMTLA Logo" class="h-9 w-auto object-contain drop-shadow-[0_2px_6px_rgba(0,0,0,0.4)] group-hover:scale-105 transition-transform duration-300">
            <div class="flex flex-col leading-tight">
                <span class="text-lg font-black text-white tracking-tight">UCSMTLA</span>
                <span class="text-[10px] font-bold text-sky-200 uppercase tracking-wider">Admin Panel</span>
            </div>
        </a>
        <button id="admin-mobile-close-btn" class="lg:hidden absolute right-4 text-sky-200 hover:text-white hover:bg-white/15 p-2 rounded-lg transition-colors">
            <i data-lucide="x" class="w-5 h-5"></i>
        </button>
    </div>
    
    <!-- Navigation -->
    <nav class="flex-1 py-6 px-4 overflow-y-auto scrollbar-hide">
        <?php echo nav_link('dashboard.php', 'layout-dashboard', 'Dashboard', $current_page, null, 'text-sky-100'); ?>
        <?php echo nav_link('announcements.php', 'megaphone', 'Announcements', $current_page, null, 'text-orange-200'); ?>
        <?php echo nav_link('categories.php', 'tags', 'Categories', $current_page, null, 'text-purple-200'); ?>
        <?php echo nav_link('faculties.php', 'building-2', 'Faculties', $current_page, null, 'text-indigo-200'); ?>
        <?php echo nav_link('courses.php', 'graduation-cap', 'Courses', $current_page, null, 'text-cyan-200'); ?>
        <?php echo nav_link('classrooms.php', 'monitor-play', 'Classrooms', $current_page, null, 'text-teal-200'); ?>
        <?php echo nav_link('timetables.php', 'calendar-days', 'Timetables', $current_page, null, 'text-rose-200'); ?>
        <?php echo nav_link('academic_years.php', 'calendar-clock', 'Academic Years', $current_page, null, 'text-amber-200'); ?>
        <?php echo nav_link('students.php', 'users', 'Student Management', $current_page, null, 'text-emerald-200', ['promote_students.php']); ?>
    </nav>
    
    <!-- Bottom Profile Area -->
    <div class="p-4 border-t border-white/20 mt-auto">
        <a href="../auth/logout.php" class="flex items-center justify-center w-full px-4 py-3 bg-white/10 text-sky-50 hover:bg-white hover:text-blue-700 rounded-xl transition-all duration-300 font-bold text-sm group shadow-sm border border-white/20">
            <i data-lucide="log-out" class="w-4 h-4 mr-2 transition-transform group-hover:-translate-x-1"></i>
            Logout
        </a>
    </div>
</aside>

<!-- Prefetch sidebar targets on hover/focus to reduce page load time -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    var links = document.querySelectorAll('aside.admin-sidebar nav a[href]');
    var warmed = {};
    function warm(url) {
        if (warmed[url] || !url) return;
        warmed[url] = true;
        var link = document.createElement('link');
        link.rel = 'prefetch';
        link.href = url;
        document.head.appendChild(link);
    }
    for (var i = 0; i < links.length; i++) {
        links[i].addEventListener('mouseenter', function () { warm(this.href); }, { passive: true });
        links[i].addEventListener('focus', function () { warm(this.href); });
    }
});
</script>

<!-- Main content area wrapper -->
<div id="main-content-wrapper" class="flex flex-col flex-1 min-w-0 bg-[#F8FAFC]">
