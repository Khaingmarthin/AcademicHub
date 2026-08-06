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

<!-- Main Container -->
<div class="min-h-screen w-full flex items-center justify-center font-sans relative overflow-hidden" style="background: linear-gradient(135deg, #EFF6FF 0%, #DBEAFE 25%, #BFDBFE 55%, #93C5FD 100%);">
    
    <!-- Background Blurred Circles -->
    <div class="absolute top-[-10%] right-[-10%] w-[500px] h-[500px] bg-[#60A5FA] opacity-20 rounded-full blur-[100px] pointer-events-none"></div>
    <div class="absolute bottom-[-10%] left-[-10%] w-[500px] h-[500px] bg-[#818CF8] opacity-15 rounded-full blur-[100px] pointer-events-none"></div>

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10 w-full max-w-6xl animate-fade-in-up">
        <div class="flex flex-col lg:flex-row items-center justify-center lg:justify-between w-full h-full min-h-[calc(100vh-4rem)] py-8 gap-8 lg:gap-12">
            
            <!-- Left Side (Illustration) -->
            <div class="hidden lg:flex flex-col w-[45%] text-left">
                <div class="relative w-full aspect-[4/3] rounded-3xl overflow-hidden shadow-2xl transform transition-transform hover:scale-[1.02] duration-500 border border-white/30">
                    <img src="<?php echo htmlspecialchars(base_url('/assets/images/ucsmtla2.jpg')); ?>" alt="Academic Campus" class="w-full h-full object-cover">
                    <!-- Subtle dark gradient at the bottom for text readability -->
                    <div class="absolute inset-0 bg-gradient-to-t from-blue-900/80 via-blue-900/20 to-transparent"></div>
                    <div class="absolute bottom-8 left-8 right-8 text-white">
                        <h3 class="text-3xl font-bold mb-3 leading-tight text-white drop-shadow-md">Stay Connected with<br>Campus Announcements.</h3>
                        <p class="text-blue-100 text-lg font-medium drop-shadow-sm">Your central hub for academic excellence at UCSMTLA.</p>
                    </div>
                </div>
            </div>

            <!-- Right Side (Login Card) -->
            <div class="w-full lg:w-[55%] flex justify-center lg:justify-end">
                <div class="w-full max-w-[460px] bg-white/90 backdrop-blur-xl rounded-3xl p-6 sm:p-8 shadow-[0_20px_40px_-15px_rgba(0,0,0,0.1)] border border-white/50 animate-slide-up">
                    
                    <!-- Header -->
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 mx-auto bg-white rounded-2xl shadow-sm border border-gray-100 flex items-center justify-center p-2.5 mb-4 transition-transform duration-300 hover:scale-105">
                            <img src="<?php echo htmlspecialchars(base_url('/assets/images/ucsmtla%20logo.png')); ?>" alt="UCSMTLA Logo" class="w-full h-full object-contain">
                        </div>
                        <h1 class="text-[22px] font-bold text-[#1E293B] mb-1">UCSMTLA Academic Hub</h1>
                       
                        <h2 class="text-2xl font-light text-[#1E293B] mb-2 tracking-wide">Welcome Back</h2>
                    </div>

                    <!-- Flash Messages -->
                    <div class="mb-6">
                        <?php display_flash_messages(); ?>
                    </div>

                    <!-- Form -->
                    <form action="login.php" method="POST" class="space-y-4">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        
                        <!-- Email -->
                        <div class="space-y-1.5 text-left">
                            <label for="email" class="block text-sm font-semibold text-[#1E293B]">Email Address</label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#64748B] group-focus-within:text-[#2563EB] transition-colors">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path></svg>
                                </div>
                                <input id="email" name="email" type="email" required
                                    class="block w-full h-[48px] pl-11 pr-4 bg-[#F8FAFC] border border-[#E5E7EB] rounded-xl text-[#1E293B] placeholder-[#94A3B8] focus:outline-none focus:ring-2 focus:ring-[#2563EB] focus:border-[#2563EB] focus:bg-white transition-all duration-300 p-3"
                                    placeholder="Enter your email"
                                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="space-y-1.5 text-left">
                            <label for="password" class="block text-sm font-semibold text-[#1E293B]">Password</label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#64748B] group-focus-within:text-[#2563EB] transition-colors">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                </div>
                                <input id="password" name="password" type="password" required
                                    class="block w-full h-[48px] pl-11 pr-12 bg-[#F8FAFC] border border-[#E5E7EB] rounded-xl text-[#1E293B] placeholder-[#94A3B8] focus:outline-none focus:ring-2 focus:ring-[#2563EB] focus:border-[#2563EB] focus:bg-white transition-all duration-300 p-3"
                                    placeholder="Enter your password">
                                <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-4 flex items-center text-[#64748B] hover:text-[#1E293B] transition-colors focus:outline-none">
                                    <svg id="eye-icon" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </button>
                            </div>
                        </div>

                        <!-- Options -->
                        <div class="flex items-center justify-between pt-2">
                            <!-- Checkbox -->
                            <label class="flex items-center space-x-2.5 cursor-pointer group gap-3">
                                <div class="relative flex items-center justify-center">
                                    <input type="checkbox" name="remember" class="peer appearance-none w-4 h-4 border border-[#E5E7EB] rounded bg-[#F8FAFC] checked:bg-[#2563EB] checked:border-[#2563EB] transition-all cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#2563EB]/50 focus:ring-offset-1">
                                    <svg class="absolute w-3 h-3 text-white opacity-0 peer-checked:opacity-100 pointer-events-none" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <span class="text-sm font-medium text-[#64748B] group-hover:text-[#1E293B] transition-colors">Remember me</span>
                            </label>
                            
                            <a href="forgot-password.php" class="text-sm font-semibold text-[#2563EB] hover:text-[#4F46E5] hover:underline transition-all">Forgot Password?</a>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="w-full h-[48px] mt-4 bg-gradient-to-r from-[#2563EB] to-[#4F46E5] text-white rounded-xl font-bold text-[15px] shadow-[0_8px_20px_-6px_rgba(37,99,235,0.5)] hover:shadow-[0_12px_24px_-8px_rgba(79,70,229,0.7)] hover:-translate-y-0.5 transition-all duration-300 group focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#2563EB] flex items-center justify-center space-x-2 p-3">
                            <span>Sign In</span>
                            <svg class="w-5 h-5 transition-transform duration-300 group-hover:translate-x-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </button>
                    </form>
                    
                    <!-- Secondary Button -->
                    <div class="mt-6 pt-4 border-t border-[#E5E7EB]">
                        <a href="<?php echo htmlspecialchars(base_url('/public/index.php')); ?>" class="w-full h-[48px] flex items-center justify-center space-x-2 border border-[#E5E7EB] bg-white hover:bg-[#F8FAFC] text-[#64748B] hover:text-[#1E293B] rounded-xl font-semibold text-[15px] transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#E5E7EB] p-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                            <span>Continue as Guest</span>
                        </a>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>

<style>
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .animate-fade-in-up {
        animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    .animate-slide-up {
        animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.2s forwards;
        opacity: 0;
    }
    
    /* Ensure autofill matches style */
    input:-webkit-autofill,
    input:-webkit-autofill:hover, 
    input:-webkit-autofill:focus, 
    input:-webkit-autofill:active {
        -webkit-box-shadow: 0 0 0 50px #F8FAFC inset !important;
        -webkit-text-fill-color: #1E293B !important;
        transition: background-color 5000s ease-in-out 0s;
    }
</style>

<script>
function togglePassword() {
    const pwdInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eye-icon');
    if (pwdInput.type === 'password') {
        pwdInput.type = 'text';
        eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />';
    } else {
        pwdInput.type = 'password';
        eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />';
    }
}
</script>

<?php include '../includes/footer.php'; ?>
