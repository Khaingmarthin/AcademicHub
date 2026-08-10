<?php
// includes/navbar.php

// Ensure we have $pdo available (usually included in header or via session)
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';

// Safe check for current academic year if not already defined
if (!isset($current_academic_year)) {
    $active_ay_id = get_global_active_academic_year($pdo)['id'] ?? 0;
    if ($active_ay_id) {
        $stmt = $pdo->prepare("SELECT year_name as name FROM academic_years WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $active_ay_id]);
    } else {
        $stmt = $pdo->query("SELECT year_name as name FROM academic_years WHERE status = 'Active' LIMIT 1");
    }
    $active_ay = $stmt->fetch();
    $current_academic_year = $active_ay ? $active_ay['name'] : 'Not Set';
}

// User details
$user_name = $_SESSION['user_name'] ?? 'Admin';
$user_role = $_SESSION['user_role'] ?? 'Administrator';
$first_letter = strtoupper(substr($user_name, 0, 1));
$user_email = $_SESSION['user_email'] ?? 'admin@ucsmtla.edu.mm';

// Page Title logic
if (!isset($page_title)) {
    $page_name = basename($_SERVER['PHP_SELF'], '.php');
    $page_title = ucwords(str_replace('_', ' ', $page_name));
    if ($page_title == 'Index' || $page_title == 'Dashboard') $page_title = 'Dashboard';
}

// Mobile Detection
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$is_mobile = false;
$platform = "Desktop";
if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $user_agent)) {
    $is_mobile = true;
}
if (preg_match('/linux/i', $user_agent)) $platform = "Linux";
elseif (preg_match('/macintosh|mac os x/i', $user_agent)) $platform = "Mac";
elseif (preg_match('/windows|win32/i', $user_agent)) $platform = "Windows";
elseif (preg_match('/android/i', $user_agent)) $platform = "Android";
elseif (preg_match('/iphone/i', $user_agent)) $platform = "iOS";
?>

<header class="admin-header bg-white text-gray-800 border-b border-gray-200/60 shadow-sm z-30 sticky top-0 flex-shrink-0 flex items-center justify-between px-6 lg:px-8 h-[72px] w-full">
    <!-- Left: Collapse Icon & System Title -->
    <div class="flex items-center">
        <!-- Mobile Menu Hamburger Button -->
        <button id="admin-mobile-menu-btn" class="lg:hidden mr-4 text-gray-600 hover:bg-gray-100 rounded-lg p-2 focus:outline-none transition-colors duration-200" aria-label="Toggle Menu">
            <i data-lucide="menu" class="h-6 w-6"></i>
        </button>
        <div class="flex items-center space-x-4">
            <span class="text-lg md:text-xl font-bold tracking-wide text-gray-800">UCSMTLA Announcement System</span>
            <span class="hidden md:inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800 border border-green-200 shadow-sm">
                Active Academic Year: <?php echo htmlspecialchars($current_academic_year); ?>
            </span>
        </div>
    </div>
    
    <!-- Center: Empty (For future breadcrumb support) -->
    <div class="hidden md:block flex-1"></div>
    
    <!-- Right: Clock, Avatar, Profile Info -->
    <div class="flex items-center space-x-6">
        <!-- Live Clock Section -->
        <div class="hidden sm:flex flex-col items-end text-sm leading-none mr-2">
            <span id="live-time" class="font-bold text-lg text-gray-800 leading-tight tracking-wide">--:--:-- --</span>
            <span id="current-date" class="text-gray-400 text-xs font-semibold mt-1">Thursday, 23 Jul 2026</span>
        </div>
        
        <!-- User Profile Dropdown Container -->
        <div class="relative" id="user-profile-menu-container">
            <!-- User Profile Block -->
            <div id="user-profile-trigger" class="flex items-center space-x-3 cursor-pointer hover:bg-gray-50 p-2 rounded-xl transition-colors">
                <!-- Rounded Avatar -->
                <div class="h-10 w-10 rounded-full bg-blue-50 flex items-center justify-center border border-blue-100 shadow-sm transition-transform duration-300 hover:scale-105">
                    <span class="text-[#2563EB] font-bold text-lg"><?php echo htmlspecialchars($first_letter); ?></span>
                </div>
                <!-- Admin Name & Role -->
                <div class="hidden md:block text-left leading-tight">
                    <p class="text-sm font-bold text-gray-800"><?php echo htmlspecialchars($user_name); ?></p>
                    <p class="text-xs text-gray-400 font-medium capitalize"><?php echo htmlspecialchars($user_role); ?></p>
                </div>
                <!-- Chevron Down Icon -->
                <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400 transition-transform duration-200 hover-scale-icon"></i>
            </div>

            <!-- Dropdown Menu (300px Floating Menu) -->
            <div id="user-profile-dropdown" class="absolute right-0 mt-2 w-72 bg-white rounded-2xl shadow-xl border border-gray-100 opacity-0 invisible transform translate-y-2 transition-all duration-300 z-50 overflow-hidden">
                <!-- Dropdown Header -->
                <div class="px-5 py-4 bg-gray-50 border-b border-gray-100 flex items-center space-x-4">
                    <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center border-2 border-white shadow-sm">
                        <span class="text-[#2563EB] font-bold text-xl"><?php echo htmlspecialchars($first_letter); ?></span>
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-sm font-bold text-gray-800 truncate"><?php echo htmlspecialchars($user_name); ?></p>
                        <p class="text-xs text-gray-500 truncate" title="<?php echo htmlspecialchars($user_email); ?>"><?php echo htmlspecialchars($user_email); ?></p>
                        <span class="inline-block mt-1 px-2 py-0.5 bg-blue-100 text-blue-700 text-[10px] font-bold rounded capitalize"><?php echo htmlspecialchars($user_role); ?></span>
                    </div>
                </div>

                <!-- Dropdown Links -->
                <div class="py-2">
                    <div class="px-4 py-2">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Account</p>
                        <a href="profile.php" class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-lg transition-colors duration-200">
                            <i data-lucide="user" class="w-4 h-4 mr-3 text-gray-400"></i> My Profile
                        </a>
                        <a href="settings.php" class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-lg transition-colors duration-200">
                            <i data-lucide="settings" class="w-4 h-4 mr-3 text-gray-400"></i> Settings
                        </a>
                    </div>
                    <div class="border-t border-gray-100 my-1"></div>
                    <div class="px-4 py-2">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">System</p>
                        <div class="flex items-center justify-between px-3 py-2 text-sm text-gray-700">
                            <span class="flex items-center"><i data-lucide="monitor" class="w-4 h-4 mr-3 text-gray-400"></i> Device</span>
                            <span class="text-xs font-semibold text-gray-500 bg-gray-100 px-2 py-1 rounded"><?php echo $platform; ?></span>
                        </div>
                    </div>
                </div>

                <!-- Logout Button -->
                <div class="p-4 bg-gray-50 border-t border-gray-100">
                    <a href="../auth/logout.php" class="flex items-center justify-center w-full px-4 py-2 text-sm font-bold text-red-600 bg-red-50 hover:bg-red-100 rounded-xl transition-colors duration-200">
                        <i data-lucide="log-out" class="w-4 h-4 mr-2"></i> Log Out
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<main class="flex-1 overflow-y-auto overflow-x-hidden p-6 md:p-8 w-full box-border">
