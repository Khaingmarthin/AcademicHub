<?php
require_once '../config/session.php';
require_once '../config/db.php';
require_once '../config/functions.php';

// If already logged in, redirect based on role
if (is_logged_in()) {
    if ($_SESSION['user_role'] === 'admin') {
        redirect('/admin/dashboard.php');
    } else {
        redirect('/student/dashboard.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!verify_csrf_token($csrf_token)) {
        set_flash_message('error', 'Invalid security token.');
    } else {
        // Find user by email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Login successful
            session_regenerate_id(true); // Prevent session fixation
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['username'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];

            // Remember me logic
            if ($remember) {
                // The new schema doesn't have remember_token, so we just set session cookie lifetime
                $params = session_get_cookie_params();
                setcookie(session_name(), session_id(), time() + (86400 * 30), $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
            }

            // Log activity
            log_activity($user['id'], 'login', 'User logged in successfully');

            if ($user['role'] === 'admin') {
                redirect('/admin/dashboard.php');
            } else {
                redirect('/student/dashboard.php');
            }
        } else {
            set_flash_message('error', 'Invalid email or password.');
        }
    }
}
?>
<?php 
$is_auth_page = true;
include '../includes/header.php'; 
?>

<!-- ========== LOGIN PAGE ========== -->
<div class="flex h-screen w-screen bg-slate-50 font-sans overflow-hidden">
    <!-- ===== LEFT HERO SECTION ===== -->
    <div class="relative hidden lg:flex lg:w-[55%] items-center justify-center overflow-hidden bg-gradient-to-br from-blue-700 via-blue-600 to-blue-400">
        <!-- Floating Abstract Shapes -->
        <div class="absolute -top-10 -right-10 w-96 h-96 bg-white/10 rounded-full blur-3xl mix-blend-overlay"></div>
        <div class="absolute -bottom-10 -left-10 w-72 h-72 bg-blue-300/20 rounded-full blur-3xl mix-blend-overlay"></div>
        <div class="absolute top-1/3 left-1/4 w-48 h-48 bg-white/10 rounded-full blur-2xl"></div>

        <!-- Hero Content -->
        <div class="relative z-10 text-white text-center px-10 max-w-lg">
            <!-- University Logo -->
            <div class="w-20 h-20 mx-auto mb-6 bg-white/10 backdrop-blur-md rounded-2xl flex items-center justify-center border border-white/20 shadow-xl">
                <img src="<?php echo htmlspecialchars(base_url('/assets/images/ucsmtla%20logo.png')); ?>" alt="UCSMTLA Logo" class="w-16 h-16 object-contain drop-shadow-md">
            </div>

            <!-- Title -->
            <h1 class="text-4xl font-extrabold tracking-tight mb-2 drop-shadow-lg">UCSMTLA Academic Hub</h1>
            <p class="text-lg font-medium text-white/90 mb-1">Smart Digital Announcement Platform</p>
            <p class="text-sm font-light text-white/70 mb-10">University of Computer Studies (Meiktila)</p>

            <!-- Feature Cards -->
            <div class="flex flex-col gap-4 mb-10 text-left">
                <div class="flex items-center gap-4 bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-4 transition hover:bg-white/20">
                    <div class="p-3 bg-white/20 rounded-lg">
                        <i data-lucide="bell" class="w-6 h-6 text-white"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-white">Real-time Announcements</h3>
                        <p class="text-xs text-white/80">Stay updated with the latest campus news</p>
                    </div>
                </div>
                <div class="flex items-center gap-4 bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-4 transition hover:bg-white/20">
                    <div class="p-3 bg-white/20 rounded-lg">
                        <i data-lucide="calendar" class="w-6 h-6 text-white"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-white">Academic Timetables</h3>
                        <p class="text-xs text-white/80">Access your class schedules anytime</p>
                    </div>
                </div>
                <div class="flex items-center gap-4 bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-4 transition hover:bg-white/20">
                    <div class="p-3 bg-white/20 rounded-lg">
                        <i data-lucide="info" class="w-6 h-6 text-white"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-white">Campus Information</h3>
                        <p class="text-xs text-white/80">Everything you need in one place</p>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <p class="text-xs text-white/60">&copy; 2026 University of Computer Studies (Meiktila)</p>
        </div>
    </div>

    <!-- ===== RIGHT LOGIN SECTION ===== -->
    <div class="flex-1 flex items-center justify-center p-6 lg:p-12 relative animate-fade-in">
        <!-- Mobile Logo (shows only on small screens) -->
        <div class="absolute top-8 left-8 lg:hidden flex items-center gap-3">
            <img src="<?php echo htmlspecialchars(base_url('/assets/images/ucsmtla%20logo.png')); ?>" alt="Logo" class="w-10 h-10">
            <span class="font-bold text-gray-800">UCSMTLA</span>
        </div>

        <div class="w-full max-w-md bg-white rounded-2xl shadow-xl border border-gray-100 p-8 sm:p-10 relative z-10">
            
            <!-- Logo in Card for Mobile -->
            <div class="flex flex-col items-center mb-8 lg:hidden">
                <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center mb-3 border border-blue-100">
                    <img src="<?php echo htmlspecialchars(base_url('/assets/images/ucsmtla%20logo.png')); ?>" alt="UCSMTLA Logo" class="w-10 h-10 object-contain">
                </div>
                <h2 class="text-xl font-bold text-gray-900">UCSMTLA Academic Hub</h2>
                <span class="mt-1 px-3 py-1 bg-blue-50 text-blue-700 text-[10px] font-bold rounded-full tracking-wider">ADMINISTRATOR LOGIN</span>
            </div>

            <!-- Heading -->
            <div class="mb-8 hidden lg:block">
                <span class="inline-block px-3 py-1 bg-blue-50 text-blue-700 text-[10px] font-bold rounded-full tracking-wider mb-4">ADMINISTRATOR LOGIN</span>
                <h1 class="text-3xl font-extrabold text-gray-900 mb-2 tracking-tight">Welcome Back</h1>
                <p class="text-sm text-gray-500 leading-relaxed">Sign in with your administrator account to access the Academic Hub Management System.</p>
            </div>

            <!-- Flash Messages -->
            <div class="mb-6">
                <?php display_flash_messages(); ?>
            </div>

            <!-- Form -->
            <form action="login.php" method="POST" class="space-y-5" id="loginForm">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                
                <!-- Email Field -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email Address</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="mail" class="w-5 h-5 text-gray-400"></i>
                        </div>
                        <input id="email" name="email" type="email" required
                            class="block w-full pl-10 pr-3 py-2.5 border border-gray-200 rounded-xl bg-gray-50/50 text-gray-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors sm:text-sm"
                            placeholder="admin@ucsmtla.edu.mm"
                            value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                </div>

                <!-- Password Field -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="lock" class="w-5 h-5 text-gray-400"></i>
                        </div>
                        <input id="password" name="password" type="password" required
                            class="block w-full pl-10 pr-10 py-2.5 border border-gray-200 rounded-xl bg-gray-50/50 text-gray-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors sm:text-sm"
                            placeholder="••••••••">
                        <button type="button" id="togglePasswordBtn" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                            <i data-lucide="eye" id="eye-icon" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember / Forgot -->
                <div class="flex items-center justify-between mt-2">
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input type="checkbox" name="remember" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 cursor-pointer">
                        <span class="text-sm text-gray-600 group-hover:text-gray-900 transition-colors">Remember me</span>
                    </label>
                    <a href="forgot-password.php" class="text-sm font-medium text-blue-600 hover:text-blue-800 transition-colors">Forgot Password?</a>
                </div>

                <!-- Submit Button -->
                <button type="submit" id="loginSubmitBtn" class="w-full mt-6 py-3 px-4 flex justify-center items-center gap-2 border border-transparent rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all shadow-md shadow-blue-500/20 group">
                    <span id="loginBtnText">Sign In</span>
                    <i data-lucide="arrow-right" id="loginBtnArrow" class="w-4 h-4 transition-transform group-hover:translate-x-1"></i>
                    <i data-lucide="loader-2" id="loginBtnSpinner" class="w-4 h-4 animate-spin hidden"></i>
                </button>
            </form>

            <!-- Divider -->
            <div class="mt-8 relative flex items-center">
                <div class="flex-grow border-t border-gray-200"></div>
                <span class="flex-shrink-0 mx-4 text-gray-400 text-xs font-semibold uppercase tracking-wider">OR</span>
                <div class="flex-grow border-t border-gray-200"></div>
            </div>

            <!-- Guest Button -->
            <a href="<?php echo htmlspecialchars(base_url('/public/index.php')); ?>" class="mt-6 w-full flex items-center justify-center gap-2 py-2.5 px-4 border border-gray-200 rounded-xl text-sm font-semibold text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200 transition-colors shadow-sm">
                <i data-lucide="user" class="w-4 h-4"></i>
                <span>Continue as Guest</span>
            </a>

            <!-- Footer Help -->
            <div class="mt-8 text-center">
                <span class="text-sm text-gray-500">Need help?</span>
                <a href="<?php echo htmlspecialchars(base_url('/public/contact.php')); ?>" class="text-sm font-medium text-blue-600 hover:text-blue-800 ml-1 transition-colors">Contact the Academic Office</a>
            </div>
        </div>
    </div>
</div>

<!-- ========== SCRIPTS ========== -->
<script>
(function() {
    'use strict';

    // ─── Password Toggle ───
    const toggleBtn = document.getElementById('togglePasswordBtn');
    const pwdInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eye-icon');

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            if (pwdInput.type === 'password') {
                pwdInput.type = 'text';
                eyeIcon.setAttribute('data-lucide', 'eye-off');
            } else {
                pwdInput.type = 'password';
                eyeIcon.setAttribute('data-lucide', 'eye');
            }
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
    }

    // ─── Form Submit Loading State ───
    const form = document.getElementById('loginForm');
    const submitBtn = document.getElementById('loginSubmitBtn');
    const btnText = document.getElementById('loginBtnText');
    const btnArrow = document.getElementById('loginBtnArrow');
    const btnSpinner = document.getElementById('loginBtnSpinner');

    if (form && submitBtn) {
        form.addEventListener('submit', function() {
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
            if (btnText) btnText.textContent = 'Signing In...';
            if (btnArrow) btnArrow.classList.add('hidden');
            if (btnSpinner) btnSpinner.classList.remove('hidden');
        });
    }

    // Render icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
})();
</script>

<?php include '../includes/footer.php'; ?>
