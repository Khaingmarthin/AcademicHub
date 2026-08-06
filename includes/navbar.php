<!-- includes/navbar.php -->
<?php
$is_public_area = strpos($_SERVER['PHP_SELF'], '/public/') !== false || (strpos($_SERVER['PHP_SELF'], '/index.php') !== false && !isset($_SESSION['user_id']));
?>
<div id="main-content-wrapper" class="flex-1 flex flex-col overflow-hidden relative">
    <?php if ($is_public_area): ?>
    
    <!-- Public Navbar -->
    <nav id="public-navbar" class="w-full z-[100] transition-all duration-300 bg-white/70 backdrop-blur-xl border-b border-gray-100 shadow-sm sticky top-0">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20 items-center">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center">
                    <a href="<?php echo htmlspecialchars(base_url('/public/index.php')); ?>" class="flex items-center gap-3 group">
                        <img class="h-10 w-auto transform transition-transform duration-300 group-hover:scale-105" src="<?php echo htmlspecialchars(base_url('/assets/images/ucsmtla logo.png')); ?>" alt="UCSMTLA Logo">
                        <span class="font-bold text-xl text-gray-900 tracking-tight group-hover:text-[#2563EB] transition-colors">UCSMTLA<br><span class="text-[10px] font-bold text-[#2563EB] leading-none uppercase tracking-wider block mt-0.5">Academic Hub</span></span>
                    </a>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-1">
                    <?php
                    $current_page = basename($_SERVER['PHP_SELF']);
                    $menu_items = [
                        'index.php' => 'Home',
                        'search.php' => 'Announcements',
                        'campus.php' => 'Campus',
                        'about.php' => 'About',
                        'contact.php' => 'Contact'
                    ];

                    foreach ($menu_items as $url => $label) {
                        $isActive = ($current_page === $url);
                        $activeClass = $isActive 
                            ? 'text-[#2563EB] font-bold bg-blue-50' 
                            : 'text-gray-600 font-medium hover:text-[#2563EB] hover:bg-blue-50/50';
                        
                        echo '<a href="' . htmlspecialchars(base_url('/public/' . $url)) . '" class="relative px-4 py-2.5 rounded-xl transition-all duration-300 ' . $activeClass . ' group text-sm">';
                        echo $label;
                        
                        // Active indicator line
                        if ($isActive) {
                            echo '<span class="absolute bottom-0 left-1/2 -translate-x-1/2 w-8 h-1 bg-[#2563EB] rounded-t-md"></span>';
                        }
                        echo '</a>';
                    }
                    ?>
                    
                    <div class="ml-4 pl-4 border-l border-gray-200">
                        <a href="<?php echo htmlspecialchars(base_url('/auth/login.php')); ?>" class="inline-flex items-center justify-center px-6 py-2.5 border border-transparent rounded-xl text-sm font-bold text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-300">
                            Student Login
                        </a>
                    </div>
                </div>

                <!-- Mobile menu button -->
                <div class="flex items-center md:hidden">
                    <button id="mobile-menu-btn" type="button" class="inline-flex items-center justify-center p-2 rounded-xl text-gray-500 hover:text-[#2563EB] hover:bg-blue-50 focus:outline-none transition-colors" aria-expanded="false">
                        <span class="sr-only">Open main menu</span>
                        <i data-lucide="menu" class="h-6 w-6"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="md:hidden hidden bg-white/95 backdrop-blur-xl border-t border-gray-100 shadow-lg absolute w-full transition-all duration-300">
            <div class="px-4 pt-2 pb-6 space-y-1">
                <?php
                foreach ($menu_items as $url => $label) {
                    $isActive = ($current_page === $url);
                    $activeClass = $isActive 
                        ? 'bg-blue-50 text-[#2563EB] font-bold border-l-4 border-[#2563EB]' 
                        : 'text-gray-600 font-medium hover:bg-blue-50/50 hover:text-[#2563EB] border-l-4 border-transparent';
                    
                    echo '<a href="' . htmlspecialchars(base_url('/public/' . $url)) . '" class="block pl-3 pr-4 py-3 rounded-r-lg transition-colors text-sm ' . $activeClass . '">';
                    echo $label;
                    echo '</a>';
                }
                ?>
                <div class="mt-6 px-3">
                    <a href="<?php echo htmlspecialchars(base_url('/auth/login.php')); ?>" class="w-full flex items-center justify-center px-4 py-3 border border-transparent rounded-xl text-sm font-bold text-white bg-[#2563EB] hover:bg-blue-700 shadow-md transition-colors">
                        Student Login
                    </a>
                </div>
            </div>
        </div>
    </nav>
    
    <main class="flex-1 overflow-x-hidden overflow-y-auto bg-white transition-colors duration-300 relative scroll-smooth" id="public-main-content">
    
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const navbar = document.getElementById('public-navbar');
            const mobileBtn = document.getElementById('mobile-menu-btn');
            const mobileMenu = document.getElementById('mobile-menu');
            const menuIcon = mobileBtn.querySelector('i');
            const mainContent = document.getElementById('public-main-content');
            
            if (mainContent && navbar) {
                // Scroll effect for glassmorphism
                mainContent.addEventListener('scroll', () => {
                    if (mainContent.scrollTop > 20) {
                        navbar.classList.add('shadow-md', 'bg-white/95');
                        navbar.classList.remove('shadow-sm', 'bg-white/70');
                    } else {
                        navbar.classList.remove('shadow-md', 'bg-white/95');
                        navbar.classList.add('shadow-sm', 'bg-white/70');
                    }
                });
            }

            if (mobileBtn && mobileMenu && menuIcon) {
                // Mobile menu toggle
                mobileBtn.addEventListener('click', () => {
                    mobileMenu.classList.toggle('hidden');
                    if (mobileMenu.classList.contains('hidden')) {
                        menuIcon.setAttribute('data-lucide', 'menu');
                    } else {
                        menuIcon.setAttribute('data-lucide', 'x');
                    }
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                });
            }
        });
    </script>
    
    <?php else: ?>
    <?php
    // Format Current Page Title
    $page_name = basename($_SERVER['PHP_SELF'], '.php');
    $page_title = ucwords(str_replace('_', ' ', $page_name));
    if ($page_title == 'Index' || $page_title == 'Dashboard') $page_title = 'Dashboard';
    
    // Try to fetch selected academic year if not already fetched
    if (!isset($current_academic_year) || !isset($current_academic_year_status)) {
        if (isset($pdo)) {
            if (isset($_SESSION['current_academic_year_id'])) {
                $stmt_ay = $pdo->prepare("SELECT id, year_name as name, status FROM academic_years WHERE id = :id LIMIT 1");
                $stmt_ay->execute(['id' => $_SESSION['current_academic_year_id']]);
            } else {
                $stmt_ay = $pdo->query("SELECT id, year_name as name, status FROM academic_years WHERE status = 'Active' LIMIT 1");
            }
            $active_ay_row = $stmt_ay->fetch();
            $current_academic_year = $active_ay_row ? $active_ay_row['name'] : 'Not Set';
            $current_academic_year_status = $active_ay_row ? $active_ay_row['status'] : '';
        } else {
            $current_academic_year = 'Not Set';
            $current_academic_year_status = '';
        }
    }
    
    // Fetch all academic years for dropdown if admin
    $all_academic_years = [];
    if (isset($pdo) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
        $stmt_all_ay = $pdo->query("SELECT id, year_name, status FROM academic_years ORDER BY id DESC");
        $all_academic_years = $stmt_all_ay->fetchAll();
    }
    
    // Get user details
    $user_name = $_SESSION['user_name'] ?? 'Daw Khin Moe Aye';
    $user_role = $_SESSION['user_role'] ?? 'Admin';
    $first_letter = strtoupper(substr($user_name, 0, 1));
    $user_email = $_SESSION['user_email'] ?? '';
    
    // Fetch read-only details for Account Info
    $account_created = 'Unknown';
    $last_login_time = 'Never logged';
    $account_username = 'Unknown';
    if (isset($pdo) && isset($_SESSION['user_id'])) {
        $stmt_u = $pdo->prepare("SELECT username, role, created_at FROM users WHERE id = :id LIMIT 1");
        $stmt_u->execute(['id' => $_SESSION['user_id']]);
        $user_info = $stmt_u->fetch();
        if ($user_info) {
            $account_created = date('M d, Y', strtotime($user_info['created_at']));
            $account_username = $user_info['username'];
        }
        
        $stmt_l = $pdo->prepare("SELECT created_at FROM activity_logs WHERE user_id = :id AND (activity = 'Login' OR activity = 'login') ORDER BY created_at DESC LIMIT 1");
        $stmt_l->execute(['id' => $_SESSION['user_id']]);
        $last_login_row = $stmt_l->fetch();
        if ($last_login_row) {
            $last_login_time = date('M d, Y g:i A', strtotime($last_login_row['created_at']));
        }
    }
    
    // Browser / Device Info
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $browser = "Unknown Browser";
    if (preg_match('/firefox/i', $user_agent)) $browser = "Firefox";
    elseif (preg_match('/chrome|crios/i', $user_agent)) $browser = "Chrome";
    elseif (preg_match('/safari/i', $user_agent)) $browser = "Safari";
    elseif (preg_match('/edge/i', $user_agent)) $browser = "Edge";

    $platform = "Unknown OS";
    if (preg_match('/windows|win32/i', $user_agent)) $platform = "Windows";
    elseif (preg_match('/macintosh|mac os x/i', $user_agent)) $platform = "Mac OS";
    elseif (preg_match('/linux/i', $user_agent)) $platform = "Linux";
    elseif (preg_match('/android/i', $user_agent)) $platform = "Android";
    elseif (preg_match('/iphone/i', $user_agent)) $platform = "iOS";
    ?>
    
    <header class="admin-header bg-white text-gray-800 border-b border-gray-100 shadow-sm z-30 fixed top-0 right-0 flex items-center justify-between px-8 transition-all-300">
        <!-- Left: Collapse Icon & System Title -->
        <div class="flex items-center">
            <!-- Mobile Menu Hamburger Button -->
            <button id="admin-mobile-menu-btn" class="lg:hidden mr-4 text-gray-600 hover:bg-gray-100 rounded-lg p-2 focus:outline-none transition-colors duration-200" aria-label="Toggle Menu">
                <i data-lucide="menu" class="h-6 w-6"></i>
            </button>
            <div class="flex items-center space-x-2">
                <span class="text-lg md:text-xl font-bold tracking-wide text-gray-800">UCSMTLA Announcement System</span>
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
                <div id="user-profile-dropdown" class="absolute right-0 mt-2 w-[300px] bg-white border border-gray-100 rounded-xl shadow-lg py-3 hidden z-50 transform origin-top-right transition-all duration-200">
                    <!-- Header -->
                    <div class="px-4 pb-3 mb-2 border-b border-gray-50">
                        <p class="text-sm font-bold text-gray-800 truncate"><?php echo htmlspecialchars($user_name); ?></p>
                        <p class="text-xs text-gray-400 truncate mt-0.5"><?php echo htmlspecialchars($user_email); ?></p>
                        <span class="inline-block mt-2 text-[10px] font-bold text-[#2563EB] bg-blue-50 border border-blue-100 px-2 py-0.5 rounded-md capitalize"><?php echo htmlspecialchars($user_role); ?></span>
                    </div>
                    <!-- Menu Items -->
                    <a href="#" id="header-my-profile-btn" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                        <i data-lucide="user" class="w-4 h-4 mr-3 text-gray-400"></i> My Profile
                    </a>
                    <a href="#" id="header-account-settings-btn" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                        <i data-lucide="settings" class="w-4 h-4 mr-3 text-gray-400"></i> Account Settings
                    </a>
                    <hr class="border-gray-100 my-2">
                    <a href="#" id="header-logout-btn" class="flex items-center px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors font-medium">
                        <i data-lucide="log-out" class="w-4 h-4 mr-3 text-red-400"></i> Logout
                    </a>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const trigger = document.getElementById('user-profile-trigger');
                const dropdown = document.getElementById('user-profile-dropdown');
                
                if (trigger && dropdown) {
                    trigger.addEventListener('click', (e) => {
                        e.stopPropagation();
                        dropdown.classList.toggle('hidden');
                    });
                    
                    document.addEventListener('click', (e) => {
                        if (!dropdown.contains(e.target) && !trigger.contains(e.target)) {
                            dropdown.classList.add('hidden');
                        }
                    });
                }

                // Profile Drawer triggers
                const drawer = document.getElementById('profile-drawer');
                const drawerBackdrop = document.getElementById('profile-drawer-backdrop');
                const myProfileBtn = document.getElementById('header-my-profile-btn');
                const closeDrawerBtn = document.getElementById('close-profile-drawer');
                const cancelDrawerBtn = document.getElementById('profile-drawer-cancel');
                
                function openDrawer() {
                    drawer.classList.remove('translate-x-full');
                    drawer.classList.add('open');
                    drawerBackdrop.classList.remove('hidden');
                    setTimeout(() => drawerBackdrop.classList.remove('opacity-0'), 10);
                    if (dropdown) dropdown.classList.add('hidden');
                }
                
                function closeDrawer() {
                    drawer.classList.add('translate-x-full');
                    drawer.classList.remove('open');
                    drawerBackdrop.classList.add('opacity-0');
                    setTimeout(() => drawerBackdrop.classList.add('hidden'), 300);
                }
                
                if (myProfileBtn) myProfileBtn.addEventListener('click', openDrawer);
                if (closeDrawerBtn) closeDrawerBtn.addEventListener('click', closeDrawer);
                if (cancelDrawerBtn) cancelDrawerBtn.addEventListener('click', closeDrawer);
                if (drawerBackdrop) drawerBackdrop.addEventListener('click', closeDrawer);

                // Profile updates AJAX
                const profileForm = document.getElementById('edit-profile-form');
                if (profileForm) {
                    profileForm.addEventListener('submit', (e) => {
                        e.preventDefault();
                        const submitBtn = document.getElementById('profile-drawer-save');
                        const origText = submitBtn.innerText;
                        submitBtn.disabled = true;
                        submitBtn.innerText = 'Saving...';
                        
                        fetch('<?php echo htmlspecialchars(base_url("/ajax/update_profile.php")); ?>', {
                            method: 'POST',
                            body: new FormData(profileForm)
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                document.querySelector('#user-profile-trigger p.text-gray-800').textContent = data.username;
                                document.querySelector('#user-profile-trigger span').textContent = data.username.charAt(0).toUpperCase();
                                document.querySelector('#user-profile-dropdown p.text-sm').textContent = data.username;
                                document.querySelector('#user-profile-dropdown p.text-xs').textContent = data.email;
                                if (window.Toast) {
                                    window.Toast.show('Success', 'Profile updated successfully.', 'success');
                                }
                                closeDrawer();
                            } else {
                                alert(data.message);
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            alert('An error occurred.');
                        })
                        .finally(() => {
                            submitBtn.disabled = false;
                            submitBtn.innerText = origText;
                        });
                    });
                }

                // Password show/hide toggle
                window.togglePasswordVisibility = function(inputId, iconId) {
                    const input = document.getElementById(inputId);
                    const icon = document.getElementById(iconId);
                    if (input && icon) {
                        if (input.type === 'password') {
                            input.type = 'text';
                            icon.setAttribute('data-lucide', 'eye-off');
                        } else {
                            input.type = 'password';
                            icon.setAttribute('data-lucide', 'eye');
                        }
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    }
                };

                // Password strength checker
                const newPass = document.getElementById('new-password');
                const strengthBar = document.getElementById('strength-bar');
                const reqLen = document.getElementById('req-length');
                const reqUpper = document.getElementById('req-uppercase');
                const reqLower = document.getElementById('req-lowercase');
                const reqNum = document.getElementById('req-number');
                const reqSpecial = document.getElementById('req-special');

                if (newPass) {
                    newPass.addEventListener('input', () => {
                        const val = newPass.value;
                        let score = 0;
                        
                        const hasLen = val.length >= 8;
                        const hasUpper = /[A-Z]/.test(val);
                        const hasLower = /[a-z]/.test(val);
                        const hasNum = /[0-9]/.test(val);
                        const hasSpecial = /[^A-Za-z0-9]/.test(val);
                        
                        if (hasLen) { score++; reqLen.className = 'text-green-600 flex items-center gap-1.5'; } else { reqLen.className = 'text-gray-400 flex items-center gap-1.5'; }
                        if (hasUpper) { score++; reqUpper.className = 'text-green-600 flex items-center gap-1.5'; } else { reqUpper.className = 'text-gray-400 flex items-center gap-1.5'; }
                        if (hasLower) { score++; reqLower.className = 'text-green-600 flex items-center gap-1.5'; } else { reqLower.className = 'text-gray-400 flex items-center gap-1.5'; }
                        if (hasNum) { score++; reqNum.className = 'text-green-600 flex items-center gap-1.5'; } else { reqNum.className = 'text-gray-400 flex items-center gap-1.5'; }
                        if (hasSpecial) { score++; reqSpecial.className = 'text-green-600 flex items-center gap-1.5'; } else { reqSpecial.className = 'text-gray-400 flex items-center gap-1.5'; }

                        strengthBar.className = 'h-1.5 rounded-full transition-all duration-300';
                        if (val.length === 0) {
                            strengthBar.style.width = '0%';
                        } else if (score <= 2) {
                            strengthBar.style.width = '33%';
                            strengthBar.classList.add('bg-red-500');
                        } else if (score <= 4) {
                            strengthBar.style.width = '66%';
                            strengthBar.classList.add('bg-yellow-500');
                        } else {
                            strengthBar.style.width = '100%';
                            strengthBar.classList.add('bg-green-500');
                        }
                    });
                }

                // Password AJAX updates
                const passwordForm = document.getElementById('change-password-form');
                if (passwordForm) {
                    passwordForm.addEventListener('submit', (e) => {
                        e.preventDefault();
                        const submitBtn = document.getElementById('password-update-btn');
                        const origText = submitBtn.innerText;
                        submitBtn.disabled = true;
                        submitBtn.innerText = 'Updating...';
                        
                        fetch('<?php echo htmlspecialchars(base_url("/ajax/update_password.php")); ?>', {
                            method: 'POST',
                            body: new FormData(passwordForm)
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                if (window.Toast) window.Toast.show('Success', 'Password updated successfully.', 'success');
                                passwordForm.reset();
                                strengthBar.style.width = '0%';
                                closeDrawer();
                            } else {
                                alert(data.message);
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            alert('An error occurred.');
                        })
                        .finally(() => {
                            submitBtn.disabled = false;
                            submitBtn.innerText = origText;
                        });
                    });
                }

                // Account settings modal controls
                const settingsModal = document.getElementById('account-settings-modal');
                const settingsBtn = document.getElementById('header-account-settings-btn');
                const closeSettingsBtn = document.getElementById('close-settings-modal');
                const settingsBackdrop = document.getElementById('settings-modal-backdrop');
                
                if (settingsBtn) {
                    settingsBtn.addEventListener('click', (e) => {
                        e.preventDefault();
                        settingsModal.classList.remove('hidden');
                        if (dropdown) dropdown.classList.add('hidden');
                    });
                }
                if (closeSettingsBtn) closeSettingsBtn.addEventListener('click', () => settingsModal.classList.add('hidden'));
                if (settingsBackdrop) settingsBackdrop.addEventListener('click', () => settingsModal.classList.add('hidden'));

                // Logout confirmation modal controls
                const logoutModal = document.getElementById('logout-confirm-modal');
                const logoutBtn = document.getElementById('header-logout-btn');
                const closeLogoutBtn = document.getElementById('close-logout-modal');
                const cancelLogoutBtn = document.getElementById('logout-modal-cancel');
                
                if (logoutBtn) {
                    logoutBtn.addEventListener('click', (e) => {
                        e.preventDefault();
                        logoutModal.classList.remove('hidden');
                        if (dropdown) dropdown.classList.add('hidden');
                    });
                }
                if (closeLogoutBtn) closeLogoutBtn.addEventListener('click', () => logoutModal.classList.add('hidden'));
                if (cancelLogoutBtn) cancelLogoutBtn.addEventListener('click', () => logoutModal.classList.add('hidden'));
            });
        </script>
    </header>

    <!-- Slide-over Drawer Panel (My Profile) -->
    <div id="profile-drawer-backdrop" class="fixed inset-0 bg-gray-900/30 backdrop-blur-sm z-40 hidden opacity-0 transition-opacity duration-300"></div>
    <div id="profile-drawer" class="profile-drawer fixed right-0 top-0 h-full w-full max-w-[450px] bg-[#F8FAFC] shadow-2xl z-50 flex flex-col border-l border-gray-100">
        <!-- Drawer Header -->
        <div class="h-[72px] px-6 bg-white border-b border-gray-100 flex items-center justify-between flex-shrink-0">
            <h3 class="text-base font-bold text-gray-800">My Profile</h3>
            <button id="close-profile-drawer" class="text-gray-400 hover:text-gray-600 focus:outline-none p-1 hover:bg-gray-50 rounded-lg transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        
        <!-- Drawer Content (Scrollable) -->
        <div class="flex-1 overflow-y-auto p-6 space-y-6 [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none]">
            <!-- Card 1: Personal Information -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h4 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i data-lucide="user" class="w-4 h-4 text-[#2563EB]"></i> Personal Information
                </h4>
                <form id="edit-profile-form" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Full Name</label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($user_name); ?>" required class="w-full border-gray-200 rounded-xl shadow-sm focus:ring-[#2563EB] focus:border-[#2563EB] px-4 py-2 border text-sm text-gray-800">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Email Address</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user_email); ?>" required class="w-full border-gray-200 rounded-xl shadow-sm focus:ring-[#2563EB] focus:border-[#2563EB] px-4 py-2 border text-sm text-gray-800">
                    </div>
                    <div class="flex space-x-3 pt-2">
                        <button type="button" id="profile-drawer-cancel" class="flex-1 justify-center rounded-xl border border-gray-200 shadow-sm px-4 py-2 bg-white text-xs font-bold text-gray-600 hover:bg-gray-50 transition-colors">Cancel</button>
                        <button type="submit" id="profile-drawer-save" class="flex-1 justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-[#2563EB] text-xs font-bold text-white hover:bg-blue-700 transition-colors">Save Changes</button>
                    </div>
                </form>
            </div>
            
            <!-- Card 2: Change Password -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h4 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i data-lucide="lock" class="w-4 h-4 text-orange-500"></i> Change Password
                </h4>
                <form id="change-password-form" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Current Password</label>
                        <div class="relative">
                            <input type="password" name="current_password" id="curr-password" required class="w-full border-gray-200 rounded-xl shadow-sm focus:ring-[#2563EB] focus:border-[#2563EB] px-4 py-2 border text-sm text-gray-800 pr-10">
                            <button type="button" onclick="togglePasswordVisibility('curr-password', 'icon-curr-pass')" class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600">
                                <i data-lucide="eye" id="icon-curr-pass" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">New Password</label>
                        <div class="relative">
                            <input type="password" name="new_password" id="new-password" required class="w-full border-gray-200 rounded-xl shadow-sm focus:ring-[#2563EB] focus:border-[#2563EB] px-4 py-2 border text-sm text-gray-800 pr-10">
                            <button type="button" onclick="togglePasswordVisibility('new-password', 'icon-new-pass')" class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600">
                                <i data-lucide="eye" id="icon-new-pass" class="w-4 h-4"></i>
                            </button>
                        </div>
                        <div class="mt-2 w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                            <div id="strength-bar" class="h-1.5 w-0 transition-all duration-300"></div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Confirm Password</label>
                        <div class="relative">
                            <input type="password" name="confirm_password" id="conf-password" required class="w-full border-gray-200 rounded-xl shadow-sm focus:ring-[#2563EB] focus:border-[#2563EB] px-4 py-2 border text-sm text-gray-800 pr-10">
                            <button type="button" onclick="togglePasswordVisibility('conf-password', 'icon-conf-pass')" class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600">
                                <i data-lucide="eye" id="icon-conf-pass" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Requirements Checklist -->
                    <div class="space-y-1.5 py-1 border-t border-gray-50 pt-3">
                        <p class="text-[11px] font-bold text-gray-400 mb-1">Password Requirements:</p>
                        <div id="req-length" class="text-gray-400 text-[11px] flex items-center gap-1.5"><i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i> Min 8 characters</div>
                        <div id="req-uppercase" class="text-gray-400 text-[11px] flex items-center gap-1.5"><i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i> One uppercase letter</div>
                        <div id="req-lowercase" class="text-gray-400 text-[11px] flex items-center gap-1.5"><i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i> One lowercase letter</div>
                        <div id="req-number" class="text-gray-400 text-[11px] flex items-center gap-1.5"><i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i> One number</div>
                        <div id="req-special" class="text-gray-400 text-[11px] flex items-center gap-1.5"><i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i> One special char</div>
                    </div>
                    
                    <button type="submit" id="password-update-btn" class="w-full justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-orange-500 hover:bg-orange-600 text-xs font-bold text-white transition-colors">Update Password</button>
                </form>
            </div>
            
            <!-- Card 3: Account Information -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 space-y-4">
                <h4 class="text-sm font-bold text-gray-800 border-b border-gray-50 pb-2 flex items-center gap-2">
                    <i data-lucide="info" class="w-4 h-4 text-purple-500"></i> Account Information
                </h4>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Username</span>
                        <span class="text-xs font-bold text-gray-700"><?php echo htmlspecialchars($account_username); ?></span>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Role</span>
                        <span class="text-xs font-bold text-gray-700 capitalize"><?php echo htmlspecialchars($user_role); ?></span>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Last Login</span>
                        <span class="text-xs font-bold text-gray-700"><?php echo htmlspecialchars($last_login_time); ?></span>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Account Created</span>
                        <span class="text-xs font-bold text-gray-700"><?php echo htmlspecialchars($account_created); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Account Settings Modal -->
    <div id="account-settings-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden">
        <div class="fixed inset-0 bg-gray-900/30 backdrop-blur-sm transition-opacity" id="settings-modal-backdrop"></div>
        <div class="bg-white rounded-xl overflow-hidden shadow-2xl transform transition-all sm:max-w-md sm:w-full p-6 z-10 border border-gray-100 space-y-4">
            <div class="flex justify-between items-center border-b border-gray-50 pb-2">
                <h3 class="text-base font-bold text-gray-800 flex items-center gap-2"><i data-lucide="settings" class="w-5 h-5 text-[#2563EB]"></i> Account Settings</h3>
                <button id="close-settings-modal" class="text-gray-400 hover:text-gray-600 p-1 hover:bg-gray-50 rounded-lg"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>
            <div class="space-y-3">
                <p class="text-xs text-gray-500 font-medium">Session Information:</p>
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 space-y-2">
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-400">Current Login</span>
                        <span class="font-bold text-gray-700"><?php echo date('M d, Y g:i A'); ?></span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-400">Platform OS</span>
                        <span class="font-bold text-gray-700"><?php echo htmlspecialchars($platform); ?></span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-400">Browser</span>
                        <span class="font-bold text-gray-700"><?php echo htmlspecialchars($browser); ?></span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-400">IP Address</span>
                        <span class="font-bold text-gray-700"><?php echo htmlspecialchars($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'); ?></span>
                    </div>
                </div>
            </div>
            <div class="flex space-x-3 pt-2">
                <a href="../auth/logout.php" class="flex-1 text-center justify-center rounded-xl border border-transparent shadow-sm px-4 py-2.5 bg-red-600 text-xs font-bold text-white hover:bg-red-700 transition-colors">Sign out from the current session</a>
            </div>
        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div id="logout-confirm-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden">
        <div class="fixed inset-0 bg-gray-900/30 backdrop-blur-sm transition-opacity" id="logout-modal-backdrop"></div>
        <div class="bg-white rounded-xl overflow-hidden shadow-2xl transform transition-all sm:max-w-md sm:w-full p-6 z-10 border border-gray-100 space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-red-50 text-red-500 flex items-center justify-center">
                    <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                </div>
                <h3 class="text-base font-bold text-gray-800">Logout</h3>
            </div>
            <p class="text-xs text-gray-500 font-medium">Are you sure you want to log out?</p>
            <div class="flex space-x-3 pt-2">
                <button id="logout-modal-cancel" class="flex-1 justify-center rounded-xl border border-gray-200 px-4 py-2 bg-white text-xs font-bold text-gray-600 hover:bg-gray-50 transition-colors">Cancel</button>
                <a href="../auth/logout.php" class="flex-1 text-center justify-center rounded-xl border border-transparent px-4 py-2 bg-red-600 text-xs font-bold text-white hover:bg-red-700 transition-colors">Logout</a>
            </div>
        </div>
    </div>
    
    <!-- Spacer matching the header height -->
    <div class="h-[72px] flex-shrink-0"></div>

    <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#F8FAFC] dark:bg-gray-900 p-8 transition-colors duration-300 relative">
    <?php endif; ?>
