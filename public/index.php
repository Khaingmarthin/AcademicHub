<?php
session_start();
require_once '../config/db.php';
require_once '../config/functions.php';

// Fetch Categories for Filter
$stmt = $pdo->query("SELECT id, category_name as name FROM categories ORDER BY category_name ASC");
$categories = $stmt->fetchAll();

$category_count = count($categories);

// Status logic constraint
$published_sql = "(ay.status != 'archived' OR ay.status IS NULL) AND (a.publish_date <= NOW() OR a.publish_date IS NULL)";

// Active academic year constraint
$stmt = $pdo->query("SELECT id, year_name FROM academic_years WHERE status = 'active' LIMIT 1");
$active_ay = $stmt->fetch();
$active_ay_id = $active_ay ? $active_ay['id'] : 0;
$active_ay_name = $active_ay ? $active_ay['year_name'] : 'Current';
$ay_sql = $active_ay_id > 0 ? "AND a.academic_year_id = $active_ay_id" : "";

// Keep footer academic year pill dynamic (previously set by navbar.php)
$current_academic_year = $active_ay ? $active_ay['year_name'] : 'Not Set';

// Stats: Today's Announcements
$stmt = $pdo->query("SELECT COUNT(*) FROM announcements a LEFT JOIN academic_years ay ON a.academic_year_id = ay.id WHERE DATE(a.publish_date) = CURDATE() AND $published_sql $ay_sql");
$today_count = (int)$stmt->fetchColumn();

// Stats: Urgent Announcements
$stmt = $pdo->query("SELECT COUNT(*) FROM announcements a LEFT JOIN academic_years ay ON a.academic_year_id = ay.id WHERE a.is_urgent = 1 AND $published_sql $ay_sql");
$urgent_count = (int)$stmt->fetchColumn();

// Fetch Latest Published Announcement (Newest)
$stmt = $pdo->query("SELECT a.*, c.category_name as category_name 
                     FROM announcements a 
                     LEFT JOIN categories c ON a.category_id = c.id 
                     LEFT JOIN academic_years ay ON a.academic_year_id = ay.id
                     WHERE $published_sql $ay_sql 
                     ORDER BY a.publish_date DESC, a.created_at DESC 
                     LIMIT 1");
$latest = $stmt->fetch();
$latest_id = $latest ? $latest['id'] : 0;

// Fetch image for latest announcement
$latest_image = null;
if ($latest) {
    $stmt = $pdo->prepare("SELECT CONCAT('assets/uploads/attachments/', file_name) FROM attachments WHERE announcement_id = ? AND file_type LIKE 'image/%' LIMIT 1");
    $stmt->execute([$latest_id]);
    $latest_image = $stmt->fetchColumn();
}

// Fetch Next 6 Announcements
$stmt = $pdo->prepare("SELECT a.*, c.category_name as category_name,
                              (SELECT CONCAT('assets/uploads/attachments/', file_name) FROM attachments WHERE announcement_id = a.id AND file_type LIKE 'image/%' LIMIT 1) as image_path
                       FROM announcements a 
                       LEFT JOIN categories c ON a.category_id = c.id 
                       LEFT JOIN academic_years ay ON a.academic_year_id = ay.id
                       WHERE $published_sql $ay_sql AND a.id != :latest_id 
                       ORDER BY a.publish_date DESC, a.created_at DESC 
                       LIMIT 6");
$stmt->execute(['latest_id' => $latest_id]);
$grid_announcements = $stmt->fetchAll();

// About tab statistics
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'");
$about_student_count = (int)$stmt->fetchColumn();
$stmt = $pdo->query("SELECT COUNT(*) FROM academic_years");
$about_ay_count = (int)$stmt->fetchColumn();
$about_department_count = 5;

/**
 * Resolve a stored announcement attachment to an existing image file.
 * Attachments may live under either uploads/attachments or uploads/announcements.
 * Returns null when no real file exists so a placeholder is shown instead of a broken image.
 */
function ucsmtla_public_image_url($path) {
    if (empty($path)) return null;
    $root = dirname(__DIR__) . DIRECTORY_SEPARATOR;
    if (file_exists($root . $path)) return $path;
    $alt = preg_replace('#^assets/uploads/attachments/#', 'assets/uploads/announcements/', $path, 1);
    if ($alt !== $path && file_exists($root . $alt)) return $alt;
    return null;
}

$latest_image_url = ucsmtla_public_image_url($latest_image);

// Campus information — uses existing system data when available, otherwise the
// values already used across the project's public pages.
$uni_name = 'University of Computer Studies (Meiktila)';
$uni_address = 'Meiktila-Tharzi Road, Pan Taw Sat Village, TawMa Village Group, Meiktila, Mandalay Division.';
$uni_phone = '(+95) 64 53 2005';
$uni_email = 'studentaffair@ucsmtla.edu.mm, ucsmtla_admin@ucsmtla.edu.mm';
$uni_website = 'https://www.ucsmtla.edu.mm';
try {
    $stmt = $pdo->query("SELECT university_name, address, phone, email, website FROM system_settings ORDER BY id ASC LIMIT 1");
    $sys = $stmt->fetch();
    if ($sys) {
        if (!empty($sys['university_name'])) $uni_name = $sys['university_name'];
        if (!empty($sys['address'])) $uni_address = $sys['address'];
        if (!empty($sys['phone'])) $uni_phone = $sys['phone'];
        if (!empty($sys['email'])) $uni_email = $sys['email'];
        if (!empty($sys['website'])) $uni_website = $sys['website'];
    }
} catch (PDOException $e) {
    // Settings table unavailable — fall back to the default campus information above.
}
?>
<?php
$is_public_area = true;
include '../includes/header.php'; 
?>

<main id="public-main-content" class="w-full font-display">

    <!-- ==================== STICKY NAVBAR ==================== -->
    <header class="sticky top-0 z-50 bg-[linear-gradient(180deg,#0288D1_0%,#0269A8_45%,#02528E_100%)] backdrop-blur-[12px] border-b border-white/[0.14] shadow-[0_16px_32px_-18px_rgba(1,64,116,0.6)]">
        <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8 py-3">
            <div class="flex items-center justify-between gap-4">
                <!-- LEFT: Brand -->
                <a href="index.php" class="flex items-center gap-3 group flex-shrink-0">
                    <img src="<?php echo htmlspecialchars(base_url('/assets/images/ucsmtla logo.png')); ?>" alt="UCSMTLA Logo" class="h-11 sm:h-12 w-auto object-contain drop-shadow-[0_6px_16px_rgba(15,23,42,0.3)] transition-transform duration-300 group-hover:scale-105">
                    <span class="flex flex-col leading-none">
                        <span class="text-[1.35rem] sm:text-2xl leading-none font-extrabold tracking-[-0.02em] text-white">UCSMTLA</span>
                        <span class="mt-1 text-[11px] font-semibold tracking-[0.06em] uppercase text-sky-100">Academic Hub</span>
                    </span>
                </a>

                <!-- CENTER: Navigation pill -->
                <nav class="nav-pill hidden lg:flex lg:items-center lg:gap-1 lg:absolute lg:left-1/2 lg:-translate-x-1/2 lg:bg-white/10 lg:border lg:border-white/20 lg:backdrop-blur-[10px] lg:rounded-full lg:p-1.5 lg:shadow-[0_12px_32px_-14px_rgba(2,136,209,0.55)]" aria-label="Primary navigation">
                    <a href="#section-home" data-section="section-home" class="nav-item active">Home</a>
                    <a href="#section-announcements" data-section="section-announcements" class="nav-item">Announcements</a>
                    <a href="#section-campus" data-section="section-campus" class="nav-item">Campus</a>
                    <a href="#section-about" data-section="section-about" class="nav-item">About</a>
                    <a href="#contact" data-section="contact" class="nav-item">Contact</a>
                </nav>

                <!-- RIGHT: Student Login + Mobile Toggle -->
                <div class="flex items-center gap-2 flex-shrink-0">
                    <a href="../auth/login.php" class="hidden sm:inline-flex items-center gap-2 bg-slate-900/80 text-cyan-400 border border-cyan-500 font-bold text-sm px-[22px] py-[11px] rounded-[14px] shadow-[0_0_15px_rgba(6,182,212,0.5)] transition-all duration-300 hover:bg-cyan-500 hover:text-white hover:-translate-y-0.5 hover:shadow-[0_0_25px_rgba(6,182,212,0.8)]">
                        <i data-lucide="log-in" class="w-4 h-4"></i>
                        Student Login
                    </a>
                    <a href="../auth/login.php" class="sm:hidden inline-flex items-center gap-1.5 bg-slate-900/80 text-cyan-400 border border-cyan-500 font-bold text-[13px] px-4 py-2.5 rounded-xl shadow-[0_0_15px_rgba(6,182,212,0.5)] transition-all duration-300 hover:bg-cyan-500 hover:text-white">
                        <i data-lucide="log-in" class="w-4 h-4"></i>
                        Login
                    </a>
                    <button id="public-mobile-menu-btn" class="inline-flex items-center justify-center w-11 h-11 rounded-[14px] bg-white/10 border border-white/20 text-white transition-colors duration-300 hover:bg-white/20 lg:hidden" aria-label="Toggle menu">
                        <i data-lucide="menu" class="w-6 h-6"></i>
                    </button>
                </div>
            </div>

            <!-- Mobile Menu -->
            <nav id="public-mobile-menu" class="mobile-menu" aria-label="Mobile navigation">
                <a href="#section-home" data-section="section-home" class="active">Home</a>
                <a href="#section-announcements" data-section="section-announcements">Announcements</a>
                <a href="#section-campus" data-section="section-campus">Campus</a>
                <a href="#section-about" data-section="section-about">About</a>
                <a href="#contact" data-section="contact">Contact</a>
                <a href="../auth/login.php" class="mt-1 flex items-center gap-2">
                    <i data-lucide="log-in" class="w-4 h-4"></i>
                    Student Login
                </a>
            </nav>
        </div>
    </header>

    <!-- ==================== HOME TAB ==================== -->
    <section id="section-home" class="w-full scroll-mt-[88px]">

    <!-- ==================== HERO ==================== -->
    <section id="hero" class="relative overflow-hidden" style="background-image: radial-gradient(ellipse 60% 40% at 50% 2%, rgba(255,255,255,0.14) 0%, rgba(255,255,255,0) 66%), radial-gradient(ellipse 52% 48% at 76% 44%, rgba(103,232,249,0.36) 0%, rgba(103,232,249,0) 62%), radial-gradient(ellipse 55% 52% at 22% 30%, rgba(0,188,212,0.20) 0%, rgba(0,188,212,0) 60%), linear-gradient(180deg, rgba(0,159,227,0.66) 0%, rgba(2,136,209,0.56) 40%, rgba(34,211,238,0.44) 70%, rgba(103,232,249,0.34) 100%), url('<?php echo htmlspecialchars(base_url('/assets/images/ucsmtla7.jpg')); ?>'); background-size: cover, cover, cover, cover, cover; background-position: center;">

        <div class="relative z-10 flex flex-col mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8 pt-2 pb-14 lg:pt-4 lg:pb-20">

            <!-- ==================== ACADEMIC YEAR BADGE ==================== -->
            <div class="mt-3 lg:mt-4">
                <span class="inline-flex items-center gap-2.5 bg-white/10 border border-white/30 backdrop-blur-[10px] rounded-full px-[18px] py-2 shadow-[0_12px_30px_-14px_rgba(2,136,209,0.6)]">
                    <span class="relative w-2.5 h-2.5 rounded-full bg-green-400 flex-shrink-0 after:content-[''] after:absolute after:-inset-1 after:rounded-full after:bg-green-400/50 after:animate-ping-dot"></span>
                    <span class="text-sm font-semibold text-white tracking-[0.02em]"><?php echo htmlspecialchars($active_ay_name); ?> Academic Session</span>
                </span>
            </div>

            <!-- ==================== HERO CONTENT ==================== -->
            <div class="grid grid-cols-1 gap-[2.75rem] items-center mt-6 lg:grid-cols-[1.1fr_0.9fr] lg:gap-[3.5rem] lg:mt-8">
                <!-- LEFT: Copy -->
                <div class="text-center lg:text-left">
                    <h1 class="text-[1.8rem] leading-[1.2] font-extrabold tracking-[-0.02em] sm:text-[2.5rem] lg:text-[3rem] animate-slide-in-left">
                        <span class="block text-white [text-shadow:0_2px_10px_rgba(1,64,116,0.7),0_10px_32px_rgba(1,64,116,0.4)]">University of</span>
                        <span class="block text-white [text-shadow:0_2px_10px_rgba(1,64,116,0.7),0_10px_32px_rgba(1,64,116,0.4)]">Computer Studies Meiktila</span>
                    </h1>

                    <p class="mt-5 max-w-[32rem] text-[1.0625rem] leading-[1.7] text-white/95 [text-shadow:0_1px_6px_rgba(1,64,116,0.6)] sm:text-lg animate-slide-in-left">
                        Real-time announcements, smart notifications, campus guides, and everything you need — all in one beautifully designed platform.
                    </p>

                    <div class="flex flex-col gap-4 mt-8 items-center sm:flex-row lg:justify-start animate-slide-in-left">
                        <a href="#section-announcements" class="inline-flex items-center justify-center gap-2.5 bg-slate-900/80 text-cyan-400 border border-cyan-500 font-bold text-[15px] px-7 py-[15px] rounded-2xl shadow-[0_0_15px_rgba(6,182,212,0.5)] transition-all duration-300 hover:bg-cyan-500 hover:text-white hover:-translate-y-[3px] hover:shadow-[0_0_25px_rgba(6,182,212,0.8)]">
                            Explore Announcements
                            <i data-lucide="arrow-down" class="w-5 h-5"></i>
                        </a>
                        <a href="../auth/login.php" class="inline-flex items-center justify-center gap-2.5 bg-slate-900/80 text-cyan-400 border border-cyan-500 font-bold text-[15px] px-7 py-[15px] rounded-2xl shadow-[0_0_15px_rgba(6,182,212,0.5)] transition-all duration-300 hover:bg-cyan-500 hover:text-white hover:-translate-y-[3px] hover:shadow-[0_0_25px_rgba(6,182,212,0.8)]">
                            <i data-lucide="log-in" class="w-5 h-5"></i>
                            Student Login
                        </a>
                    </div>
                </div>

                <!-- RIGHT: Latest Announcement Card -->
                <div class="w-full animate-slide-in-right-hero">
                    <?php if ($latest): ?>
                        <a href="announcement.php?id=<?php echo $latest['id']; ?>" class="group block w-full bg-white rounded-[1.75rem] overflow-hidden ring-1 ring-white/40 shadow-[0_30px_60px_-22px_rgba(2,136,209,0.55),0_34px_80px_-26px_rgba(103,232,249,0.45)] transition-all duration-[350ms] hover:-translate-y-[6px] hover:shadow-[0_40px_70px_-22px_rgba(2,136,209,0.6),0_42px_90px_-28px_rgba(103,232,249,0.5)]">
                            <div class="relative h-52 sm:h-60 overflow-hidden bg-[linear-gradient(135deg,#dbeafe,#e0f2fe)]">
                                <span class="absolute top-4 left-4 z-10 inline-flex items-center gap-1.5 bg-blue-600 text-white text-[11px] font-bold tracking-[0.1em] uppercase px-4 py-[7px] rounded-full shadow-[0_10px_24px_-8px_rgba(37,99,235,0.7)]">
                                    <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                                    Latest
                                </span>
                                <?php if ($latest_image_url): ?>
                                    <img src="<?php echo htmlspecialchars(base_url($latest_image_url)); ?>" alt="Announcement Image" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-[1.06]">
                                <?php else: ?>
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <i data-lucide="image" class="w-16 h-16 text-blue-300"></i>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="px-6 pt-6 pb-7 sm:p-7">
                                <div class="flex items-center gap-3 flex-wrap">
                                    <span class="inline-flex items-center text-[11px] font-bold tracking-[0.1em] uppercase text-blue-600 bg-blue-50 px-3 py-1.5 rounded-[10px]"><?php echo htmlspecialchars($latest['category_name'] ?? 'General'); ?></span>
                                    <span class="text-sm text-slate-500 font-medium">
                                        <?php echo date('M d, Y', strtotime($latest['publish_date'] ?? $latest['created_at'])); ?>
                                    </span>
                                </div>
                                <h3 class="mt-3 text-[1.3rem] font-bold text-slate-900 leading-[1.3] transition-colors duration-200 group-hover:text-blue-700"><?php echo htmlspecialchars($latest['title']); ?></h3>
                                <p class="mt-2.5 text-[15px] leading-[1.65] text-slate-600 line-clamp-3"><?php echo htmlspecialchars(strip_tags($latest['content'])); ?></p>
                                <span class="inline-flex items-center gap-2 mt-[18px] bg-blue-600 text-white text-sm font-bold px-5 py-[11px] rounded-xl transition-all duration-200 group-hover:bg-blue-700">
                                    Read More
                                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                </span>
                            </div>
                        </a>
                    <?php else: ?>
                        <div class="block w-full bg-white rounded-[1.75rem] overflow-hidden ring-1 ring-white/40 shadow-[0_30px_60px_-22px_rgba(2,136,209,0.55),0_34px_80px_-26px_rgba(103,232,249,0.45)] p-10 text-center">
                            <i data-lucide="bell-off" class="w-12 h-12 text-gray-400 mx-auto mb-4"></i>
                            <h3 class="text-lg font-bold mb-2 text-gray-900">No latest announcement available</h3>
                            <p class="text-gray-500">Check back soon for new updates.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
        
        <!-- Bottom Wave Transition to #EEF5FF -->
        <div class="absolute bottom-0 left-0 w-full overflow-hidden leading-[0]">
            <svg class="relative block w-full h-[20px] sm:h-[30px] lg:h-[45px]" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
                <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V120H0V95.8C59.71,118.08,130.83,119.74,196.46,108.77Z" fill="#EEF5FF"></path>
            </svg>
        </div>
    </section>

    <!-- ==================== LATEST ANNOUNCEMENTS SECTION ==================== -->
    <section id="home-announcements" class="w-full bg-[#EEF5FF]">
        <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8 py-16 lg:py-20">

            <!-- Section Heading -->
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-8 mb-10">
                <div class="max-w-2xl">
                    <span class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-blue-700 mb-3">
                        <span class="w-8 h-8 rounded-lg bg-blue-600 text-white inline-flex items-center justify-center">
                            <i data-lucide="megaphone" class="w-4 h-4"></i>
                        </span>
                        Stay Updated
                    </span>
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-3">Latest Announcements</h2>
                    <p class="text-base sm:text-lg text-gray-600">Stay informed about the latest university activities, updates, and academic news.</p>
                </div>
                <div class="flex flex-wrap items-center gap-3 flex-shrink-0">
                    <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white border border-blue-100 text-sm font-semibold text-gray-700 shadow-sm">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                        <?php echo $today_count; ?> Today
                    </span>
                    <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white border border-red-100 text-sm font-semibold text-gray-700 shadow-sm">
                        <span class="w-2 h-2 rounded-full bg-red-500"></span>
                        <?php echo $urgent_count; ?> Urgent
                    </span>
                    <a href="#section-announcements" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-full text-sm font-semibold text-blue-700 bg-blue-100/70 hover:bg-blue-200 transition-colors">
                        View All Announcements
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>

            <!-- Search & Filter Toolbar -->
            <div class="bg-white rounded-3xl border border-blue-100 shadow-sm mb-12 overflow-hidden">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-6 md:px-8 pt-6 pb-5 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-600 text-white rounded-xl flex items-center justify-center flex-shrink-0">
                            <i data-lucide="filter" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Search &amp; Filter</h3>
                            <p class="text-sm text-gray-500">Narrow down announcements by keyword, category, or type.</p>
                        </div>
                    </div>
                    <button type="button" id="filter-reset" class="inline-flex items-center gap-2 text-sm font-semibold text-gray-500 hover:text-blue-700 transition-colors self-start sm:self-auto">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                        Reset Filters
                    </button>
                </div>

                <form id="filter-form" action="search.php" method="get" class="px-6 md:px-8 py-6 md:py-7">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-5">
                        <!-- Search -->
                        <div class="md:col-span-6">
                            <label for="filter-q" class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1.5">Search</label>
                            <div class="relative">
                                <i data-lucide="search" class="absolute left-4 top-3.5 w-5 h-5 text-gray-400 pointer-events-none"></i>
                                <input type="text" id="filter-q" name="q" placeholder="Search announcements by title or content..." class="w-full pl-11 pr-4 py-3 bg-[#F8FAFC] border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-600 focus:border-blue-600 outline-none transition-all">
                            </div>
                        </div>

                        <!-- Category -->
                        <div class="md:col-span-3">
                            <label for="filter-category" class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1.5">Category</label>
                            <div class="relative">
                                <i data-lucide="folder" class="absolute left-4 top-3.5 w-5 h-5 text-gray-400 pointer-events-none"></i>
                                <select id="filter-category" name="category" class="w-full pl-11 pr-10 py-3 bg-[#F8FAFC] border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-600 focus:border-blue-600 outline-none transition-all appearance-none cursor-pointer">
                                    <option value="">All Categories</option>
                                    <?php foreach($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <i data-lucide="chevron-down" class="absolute right-4 top-3.5 w-5 h-5 text-gray-400 pointer-events-none"></i>
                            </div>
                        </div>

                        <!-- Type -->
                        <div class="md:col-span-3">
                            <label for="filter-type" class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1.5">Type</label>
                            <div class="relative">
                                <i data-lucide="tag" class="absolute left-4 top-3.5 w-5 h-5 text-gray-400 pointer-events-none"></i>
                                <select id="filter-type" name="type" class="w-full pl-11 pr-10 py-3 bg-[#F8FAFC] border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-600 focus:border-blue-600 outline-none transition-all appearance-none cursor-pointer">
                                    <option value="">All Types</option>
                                    <option value="normal">Normal</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                                <i data-lucide="chevron-down" class="absolute right-4 top-3.5 w-5 h-5 text-gray-400 pointer-events-none"></i>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <p class="text-xs text-gray-400">
                            Showing announcements for the
                            <span class="font-semibold text-gray-600"><?php echo htmlspecialchars($active_ay_name); ?></span>
                            academic session.
                        </p>
                        <button type="submit" class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 transition-colors shadow-sm">
                            <i data-lucide="search" class="w-4 h-4"></i>
                            Apply Filters
                        </button>
                    </div>
                </form>
            </div>

            <!-- Announcement Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php if (!empty($grid_announcements)): ?>
                    <?php foreach($grid_announcements as $a): ?>
                        <?php $a_image = ucsmtla_public_image_url($a['image_path']); ?>
                        <a href="announcement.php?id=<?php echo $a['id']; ?>" class="group bg-white rounded-3xl shadow-sm hover:shadow-xl border border-blue-100 overflow-hidden flex flex-col transform transition-all duration-300 hover:-translate-y-1 <?php echo $a['is_urgent'] ? 'border-red-200 ring-1 ring-red-500/40' : ''; ?>">
                            <!-- Image / Placeholder -->
                            <div class="relative h-52 bg-gradient-to-br from-blue-50 to-blue-100 overflow-hidden">
                                <?php if($a['is_urgent']): ?>
                                    <div class="absolute top-4 left-4 bg-red-500 text-white text-[10px] font-bold px-3 py-1.5 rounded-full shadow-md uppercase tracking-wider z-10 flex items-center gap-1.5">
                                        <i data-lucide="alert-circle" class="w-3 h-3"></i> Urgent
                                    </div>
                                <?php endif; ?>

                                <?php if($a_image): ?>
                                    <img src="<?php echo htmlspecialchars(base_url($a_image)); ?>" alt="<?php echo htmlspecialchars($a['title']); ?>" class="w-full h-full object-cover transform transition-transform duration-700 group-hover:scale-105" loading="lazy">
                                <?php else: ?>
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <i data-lucide="image" class="w-12 h-12 text-blue-300 transform transition-transform duration-700 group-hover:scale-110"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-black/30 to-transparent pointer-events-none"></div>
                            </div>

                            <!-- Content -->
                            <div class="p-6 flex-1 flex flex-col">
                                <div class="flex items-center justify-between gap-3 mb-3">
                                    <span class="text-[10px] font-bold tracking-wider uppercase text-blue-700 bg-blue-50 px-2.5 py-1 rounded-md">
                                        <?php echo htmlspecialchars($a['category_name'] ?? 'General'); ?>
                                    </span>
                                    <span class="text-xs text-gray-500 font-medium flex items-center gap-1">
                                        <i data-lucide="clock" class="w-3 h-3"></i>
                                        <?php echo date('M d, Y', strtotime($a['publish_date'] ?? $a['created_at'])); ?>
                                    </span>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 mb-3 group-hover:text-blue-600 transition-colors line-clamp-2">
                                    <?php echo htmlspecialchars($a['title']); ?>
                                </h3>
                                <p class="text-gray-500 text-sm mb-6 line-clamp-3 leading-relaxed">
                                    <?php echo htmlspecialchars(strip_tags($a['content'])); ?>
                                </p>
                                <div class="mt-auto pt-4 border-t border-gray-100">
                                    <span class="inline-flex items-center text-sm font-semibold text-blue-600 group-hover:text-blue-700 transition-colors">
                                        Read Full Announcement
                                        <i data-lucide="arrow-right" class="w-4 h-4 ml-1.5 transform group-hover:translate-x-1 transition-transform"></i>
                                    </span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-span-full bg-white p-12 rounded-3xl border border-blue-100 text-center">
                        <i data-lucide="file-question" class="w-12 h-12 text-gray-400 mx-auto mb-4"></i>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">No announcements found</h3>
                        <p class="text-gray-500">There are currently no announcements to display.</p>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </section>

    <!-- ==================== CAMPUS INFORMATION SECTION ==================== -->
    <section id="home-campus" class="w-full bg-white">
        <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8 py-16 lg:py-20">

            <!-- Section Heading -->
            <div class="max-w-2xl mb-12">
                <span class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-blue-700 mb-3">
                    <span class="w-8 h-8 rounded-lg bg-blue-600 text-white inline-flex items-center justify-center">
                        <i data-lucide="map-pin" class="w-4 h-4"></i>
                    </span>
                    Visit Us
                </span>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Campus Information</h2>
                <p class="text-base sm:text-lg text-gray-600">Everything you need to know about our university campus, location, and how to reach us.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-stretch">

                <!-- LEFT: University / Campus Information -->
                <div class="bg-[#EEF5FF] border border-blue-100 rounded-3xl p-8 md:p-10">
                    <h3 class="text-xl font-bold text-gray-900 mb-8 flex items-center">
                        <i data-lucide="building-2" class="w-6 h-6 text-blue-600 mr-3"></i>
                        University Information
                    </h3>
                    <ul class="space-y-6">
                        <li class="flex items-start">
                            <div class="w-12 h-12 bg-white text-blue-600 rounded-xl flex items-center justify-center flex-shrink-0 border border-blue-100 shadow-sm">
                                <i data-lucide="graduation-cap" class="w-6 h-6"></i>
                            </div>
                            <div class="ml-5">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">University Name</p>
                                <p class="text-lg font-bold text-gray-900"><?php echo htmlspecialchars($uni_name); ?></p>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <div class="w-12 h-12 bg-white text-blue-600 rounded-xl flex items-center justify-center flex-shrink-0 border border-blue-100 shadow-sm">
                                <i data-lucide="map-pin" class="w-6 h-6"></i>
                            </div>
                            <div class="ml-5">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Address</p>
                                <p class="text-lg font-bold text-gray-900"><?php echo htmlspecialchars($uni_address); ?></p>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <div class="w-12 h-12 bg-white text-blue-600 rounded-xl flex items-center justify-center flex-shrink-0 border border-blue-100 shadow-sm">
                                <i data-lucide="phone" class="w-6 h-6"></i>
                            </div>
                            <div class="ml-5">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Phone</p>
                                <p class="text-lg font-bold text-gray-900"><?php echo htmlspecialchars($uni_phone); ?></p>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <div class="w-12 h-12 bg-white text-blue-600 rounded-xl flex items-center justify-center flex-shrink-0 border border-blue-100 shadow-sm">
                                <i data-lucide="mail" class="w-6 h-6"></i>
                            </div>
                            <div class="ml-5">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Email</p>
                                <a href="mailto:<?php echo htmlspecialchars($uni_email); ?>" class="text-lg font-bold text-blue-700 hover:text-blue-900 transition-colors"><?php echo htmlspecialchars($uni_email); ?></a>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <div class="w-12 h-12 bg-white text-blue-600 rounded-xl flex items-center justify-center flex-shrink-0 border border-blue-100 shadow-sm">
                                <i data-lucide="globe" class="w-6 h-6"></i>
                            </div>
                            <div class="ml-5">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Website</p>
                                <a href="<?php echo htmlspecialchars($uni_website); ?>" target="_blank" rel="noopener noreferrer" class="text-lg font-bold text-blue-700 hover:text-blue-900 transition-colors inline-flex items-center">
                                    <?php echo htmlspecialchars(preg_replace('#^https?://#', '', $uni_website)); ?>
                                    <i data-lucide="external-link" class="w-4 h-4 ml-1.5"></i>
                                </a>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- RIGHT: Embedded Google Map -->
                <div class="bg-white border border-blue-100 rounded-3xl shadow-sm p-2 md:p-3 overflow-hidden flex flex-col">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3744.1723555543163!2d95.88210341538356!3d20.893874986071855!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x30cb673ce2030e49%3A0xc3f58a36faea9127!2sUniversity%20of%20Computer%20Studies%20(Meiktila)!5e0!3m2!1sen!2smm!4v1684305374483!5m2!1sen!2smm"
                        class="w-full flex-1 min-h-[320px] lg:min-h-[460px] rounded-2xl border-0"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        title="UCSMTLA Campus Map"></iframe>
                </div>

            </div>
        </div>
    </section>

    </section><!-- /#section-home -->

    <!-- ==================== ANNOUNCEMENTS TAB ==================== -->
    <section id="section-announcements" class="w-full bg-[#EEF5FF] scroll-mt-[88px]">
        <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8 py-16 lg:py-20">

            <!-- Section Heading -->
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6 mb-10">
                <div class="max-w-2xl">
                    <span class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-blue-700 mb-3">
                        <span class="w-8 h-8 rounded-lg bg-blue-600 text-white inline-flex items-center justify-center">
                            <i data-lucide="list" class="w-4 h-4"></i>
                        </span>
                        Browse All
                    </span>
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-3">All Announcements</h2>
                    <p class="text-base sm:text-lg text-gray-600">Search and filter every announcement from the current academic session.</p>
                </div>
                <span id="tab-results-count" class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white border border-blue-100 text-sm font-semibold text-gray-700 shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                    0 found
                </span>
            </div>

            <!-- Search & Filter Toolbar -->
            <div class="bg-white rounded-3xl border border-blue-100 shadow-sm mb-10 overflow-hidden">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-6 md:px-8 pt-6 pb-5 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-600 text-white rounded-xl flex items-center justify-center flex-shrink-0">
                            <i data-lucide="filter" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Search &amp; Filter</h3>
                            <p class="text-sm text-gray-500">Narrow down announcements by keyword, category, or type.</p>
                        </div>
                    </div>
                    <button type="button" id="tab-filter-reset" class="inline-flex items-center gap-2 text-sm font-semibold text-gray-500 hover:text-blue-700 transition-colors self-start sm:self-auto">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                        Reset Filters
                    </button>
                </div>

                <form id="tab-filter-form" class="px-6 md:px-8 py-6 md:py-7">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-5">
                        <div class="md:col-span-6">
                            <label for="tab-filter-q" class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1.5">Search</label>
                            <div class="relative">
                                <i data-lucide="search" class="absolute left-4 top-3.5 w-5 h-5 text-gray-400 pointer-events-none"></i>
                                <input type="text" id="tab-filter-q" name="q" placeholder="Search announcements by title or content..." class="w-full pl-11 pr-4 py-3 bg-[#F8FAFC] border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-600 focus:border-blue-600 outline-none transition-all">
                            </div>
                        </div>
                        <div class="md:col-span-3">
                            <label for="tab-filter-category" class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1.5">Category</label>
                            <div class="relative">
                                <i data-lucide="folder" class="absolute left-4 top-3.5 w-5 h-5 text-gray-400 pointer-events-none"></i>
                                <select id="tab-filter-category" name="category" class="w-full pl-11 pr-10 py-3 bg-[#F8FAFC] border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-600 focus:border-blue-600 outline-none transition-all appearance-none cursor-pointer">
                                    <option value="">All Categories</option>
                                    <?php foreach($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <i data-lucide="chevron-down" class="absolute right-4 top-3.5 w-5 h-5 text-gray-400 pointer-events-none"></i>
                            </div>
                        </div>
                        <div class="md:col-span-3">
                            <label for="tab-filter-type" class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1.5">Type</label>
                            <div class="relative">
                                <i data-lucide="tag" class="absolute left-4 top-3.5 w-5 h-5 text-gray-400 pointer-events-none"></i>
                                <select id="tab-filter-type" name="type" class="w-full pl-11 pr-10 py-3 bg-[#F8FAFC] border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-600 focus:border-blue-600 outline-none transition-all appearance-none cursor-pointer">
                                    <option value="">All Types</option>
                                    <option value="normal">Normal</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                                <i data-lucide="chevron-down" class="absolute right-4 top-3.5 w-5 h-5 text-gray-400 pointer-events-none"></i>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <p class="text-xs text-gray-400">
                            Showing announcements for the
                            <span class="font-semibold text-gray-600"><?php echo htmlspecialchars($active_ay_name); ?></span>
                            academic session.
                        </p>
                        <button type="submit" class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 transition-colors shadow-sm">
                            <i data-lucide="search" class="w-4 h-4"></i>
                            Apply Filters
                        </button>
                    </div>
                </form>
            </div>

            <!-- Results Grid -->
            <div class="relative">
                <div id="tab-loading" class="hidden absolute inset-0 bg-[#EEF5FF]/80 flex items-center justify-center z-10 rounded-3xl">
                    <div class="flex items-center gap-3 bg-white px-6 py-4 rounded-2xl shadow-lg">
                        <i data-lucide="loader-circle" class="w-5 h-5 text-blue-600 animate-spin"></i>
                        <span class="text-sm font-semibold text-gray-700">Loading announcements...</span>
                    </div>
                </div>

                <div id="tab-results-container" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div class="col-span-full bg-white p-12 rounded-3xl border border-blue-100 text-center">
                        <i data-lucide="loader-circle" class="w-10 h-10 text-blue-500 mx-auto mb-4 animate-spin"></i>
                        <h3 class="text-lg font-bold text-gray-900 mb-1">Loading announcements</h3>
                        <p class="text-gray-500">Please wait while we fetch the latest announcements.</p>
                    </div>
                </div>

                <div id="tab-pagination-container" class="mt-10 hidden"></div>
            </div>

        </div>
    </section>

    <!-- ==================== CAMPUS TAB ==================== -->
    <section id="section-campus" class="w-full bg-[#F5F7FB] scroll-mt-[88px]">
        <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8 py-16 lg:py-20">

            <!-- Section Heading -->
            <div class="max-w-2xl mb-12">
                <span class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-blue-700 mb-3">
                    <span class="w-8 h-8 rounded-lg bg-blue-600 text-white inline-flex items-center justify-center">
                        <i data-lucide="map-pin" class="w-4 h-4"></i>
                    </span>
                    Visit Us
                </span>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Campus Information</h2>
                <p class="text-base sm:text-lg text-gray-600">Everything you need to know about the University of Computer Studies (Meiktila).</p>
            </div>

            <!-- Two-Column Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12 items-stretch">

                <!-- LEFT: University Information -->
                <div class="bg-white border border-blue-100 rounded-3xl p-8 md:p-10">
                    <h3 class="text-xl font-bold text-gray-900 mb-8 flex items-center">
                        <i data-lucide="building-2" class="w-6 h-6 text-blue-600 mr-3"></i>
                        University Information
                    </h3>
                    <ul class="space-y-6">
                        <li class="flex items-start">
                            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center flex-shrink-0 border border-blue-100 shadow-sm">
                                <i data-lucide="graduation-cap" class="w-6 h-6"></i>
                            </div>
                            <div class="ml-5">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">University Name</p>
                                <p class="text-lg font-bold text-gray-900"><?php echo htmlspecialchars($uni_name); ?></p>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center flex-shrink-0 border border-blue-100 shadow-sm">
                                <i data-lucide="map-pin" class="w-6 h-6"></i>
                            </div>
                            <div class="ml-5">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Address</p>
                                <p class="text-lg font-bold text-gray-900"><?php echo htmlspecialchars($uni_address); ?></p>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center flex-shrink-0 border border-blue-100 shadow-sm">
                                <i data-lucide="phone" class="w-6 h-6"></i>
                            </div>
                            <div class="ml-5">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Phone</p>
                                <p class="text-lg font-bold text-gray-900"><?php echo htmlspecialchars($uni_phone); ?></p>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center flex-shrink-0 border border-blue-100 shadow-sm">
                                <i data-lucide="mail" class="w-6 h-6"></i>
                            </div>
                            <div class="ml-5">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Email</p>
                                <a href="mailto:<?php echo htmlspecialchars($uni_email); ?>" class="text-lg font-bold text-blue-700 hover:text-blue-900 transition-colors"><?php echo htmlspecialchars($uni_email); ?></a>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center flex-shrink-0 border border-blue-100 shadow-sm">
                                <i data-lucide="globe" class="w-6 h-6"></i>
                            </div>
                            <div class="ml-5">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Website</p>
                                <a href="<?php echo htmlspecialchars($uni_website); ?>" target="_blank" rel="noopener noreferrer" class="text-lg font-bold text-blue-700 hover:text-blue-900 transition-colors inline-flex items-center">
                                    <?php echo htmlspecialchars(preg_replace('#^https?://#', '', $uni_website)); ?>
                                    <i data-lucide="external-link" class="w-4 h-4 ml-1.5"></i>
                                </a>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- RIGHT: Google Map -->
                <div class="bg-white border border-blue-100 rounded-3xl shadow-sm p-2 md:p-3 overflow-hidden flex flex-col">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3744.1723555543163!2d95.88210341538356!3d20.893874986071855!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x30cb673ce2030e49%3A0xc3f58a36faea9127!2sUniversity%20of%20Computer%20Studies%20(Meiktila)!5e0!3m2!1sen!2smm!4v1684305374483!5m2!1sen!2smm"
                        class="w-full flex-1 min-h-[320px] lg:min-h-[460px] rounded-2xl border-0"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        title="UCSMTLA Campus Map"></iframe>
                </div>
            </div>

            <!-- Feature Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-lg border border-gray-100 transform transition-all duration-300 hover:-translate-y-1 group">
                    <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform duration-300">
                        <i data-lucide="building-2" class="w-7 h-7"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-blue-600 transition-colors">Campus Facilities</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">Experience our state-of-the-art computer labs, comprehensive digital library, and engaging student activity centers.</p>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-lg border border-gray-100 transform transition-all duration-300 hover:-translate-y-1 group">
                    <div class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform duration-300">
                        <i data-lucide="monitor-play" class="w-7 h-7"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-indigo-600 transition-colors">Modern Learning Environment</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">Our campus features fully air-conditioned, smart classrooms equipped with high-speed Wi-Fi and interactive technologies.</p>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-lg border border-gray-100 transform transition-all duration-300 hover:-translate-y-1 group">
                    <div class="w-14 h-14 bg-teal-50 text-teal-600 rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform duration-300">
                        <i data-lucide="users" class="w-7 h-7"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-teal-600 transition-colors">Student Services</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">Dedicated administrative staff providing support with registration, financial aid, and a central IT helpdesk for your needs.</p>
                </div>
            </div>

        </div>
    </section>

    <!-- ==================== ABOUT TAB ==================== -->
    <section id="section-about" class="w-full bg-[#F5F7FB] scroll-mt-[88px]">
        <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8 py-16 lg:py-20 space-y-20">

            <!-- Section Heading -->
            <div class="max-w-3xl mx-auto text-center">
                <span class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-blue-700 mb-3">
                    <span class="w-8 h-8 rounded-lg bg-blue-600 text-white inline-flex items-center justify-center">
                        <i data-lucide="building" class="w-4 h-4"></i>
                    </span>
                    About Us
                </span>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">About UCSMTLA</h2>
                <p class="text-base sm:text-lg text-gray-600">Dedicated to producing highly qualified IT professionals to meet the nation's growing technological needs. We blend rigorous academic study with practical innovation.</p>
            </div>

            <!-- Introduction & Vision / Mission -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div class="space-y-8">
                    <h3 class="text-2xl font-extrabold text-gray-900 flex items-center">
                        <i data-lucide="info" class="w-7 h-7 text-blue-600 mr-3"></i>
                        University Introduction
                    </h3>
                    <p class="text-lg text-gray-600 leading-relaxed">
                        The University of Computer Studies (Meiktila) is a premier institution dedicated to providing high-quality education in computer science and technology. Established to cater to the increasing demand for IT professionals, UCSMTLA fosters a culture of innovation, rigorous research, and technical excellence to prepare our students for the rapidly evolving digital era.
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4">
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center mb-4">
                                <i data-lucide="eye" class="w-6 h-6"></i>
                            </div>
                            <h4 class="text-xl font-bold text-gray-900 mb-2">Vision</h4>
                            <p class="text-sm text-gray-600">To become a world-class IT university producing excellent human resources and innovative leaders for the global society.</p>
                        </div>
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                            <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center mb-4">
                                <i data-lucide="target" class="w-6 h-6"></i>
                            </div>
                            <h4 class="text-xl font-bold text-gray-900 mb-2">Mission</h4>
                            <p class="text-sm text-gray-600">Produce competent computer scientists and engineers. Promote R&amp;D in IT, and provide quality IT services to the community.</p>
                        </div>
                    </div>
                </div>
                <div class="relative h-[400px] lg:h-[480px] rounded-3xl overflow-hidden shadow-2xl">
                    <img src="<?php echo htmlspecialchars(base_url('/assets/images/about_hero.png')); ?>" alt="University Campus" class="absolute inset-0 w-full h-full object-cover">
                    <div class="absolute inset-0 bg-blue-900 opacity-20"></div>
                </div>
            </div>

            <!-- Academic Programs -->
            <div>
                <div class="text-center mb-12">
                    <h3 class="text-3xl font-extrabold text-gray-900 flex items-center justify-center">
                        <i data-lucide="book-open" class="w-8 h-8 text-blue-600 mr-3"></i>
                        Academic Programs
                    </h3>
                    <p class="mt-4 text-lg text-gray-500">Comprehensive degree programs tailored for the modern tech landscape.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-5xl mx-auto">
                    <div class="bg-white rounded-3xl shadow-lg border border-gray-100 overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
                        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-8 text-white relative overflow-hidden">
                            <i data-lucide="code-2" class="w-24 h-24 absolute -bottom-4 -right-4 opacity-20 transform group-hover:scale-110 transition-transform duration-500"></i>
                            <h4 class="text-2xl font-bold relative z-10">Computer Science</h4>
                            <p class="text-blue-100 mt-2 relative z-10 font-medium">B.C.Sc. (Bachelor of Computer Science)</p>
                        </div>
                        <div class="p-8">
                            <p class="text-gray-600 mb-6">Focuses on software engineering, artificial intelligence, database systems, and core computational theories. Graduates are equipped to become top-tier software developers and data analysts.</p>
                            <ul class="space-y-3">
                                <li class="flex items-center text-sm text-gray-700 font-medium"><i data-lucide="check-circle" class="w-5 h-5 text-blue-500 mr-3"></i> Software Engineering</li>
                                <li class="flex items-center text-sm text-gray-700 font-medium"><i data-lucide="check-circle" class="w-5 h-5 text-blue-500 mr-3"></i> Artificial Intelligence</li>
                                <li class="flex items-center text-sm text-gray-700 font-medium"><i data-lucide="check-circle" class="w-5 h-5 text-blue-500 mr-3"></i> Database Management</li>
                            </ul>
                        </div>
                    </div>
                    <div class="bg-white rounded-3xl shadow-lg border border-gray-100 overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
                        <div class="bg-gradient-to-r from-teal-500 to-emerald-500 p-8 text-white relative overflow-hidden">
                            <i data-lucide="cpu" class="w-24 h-24 absolute -bottom-4 -right-4 opacity-20 transform group-hover:scale-110 transition-transform duration-500"></i>
                            <h4 class="text-2xl font-bold relative z-10">Computer Technology</h4>
                            <p class="text-teal-100 mt-2 relative z-10 font-medium">B.C.Tech. (Bachelor of Computer Technology)</p>
                        </div>
                        <div class="p-8">
                            <p class="text-gray-600 mb-6">Emphasizes hardware architecture, embedded systems, networking, and network security. Designed for students passionate about the physical infrastructure that powers modern IT.</p>
                            <ul class="space-y-3">
                                <li class="flex items-center text-sm text-gray-700 font-medium"><i data-lucide="check-circle" class="w-5 h-5 text-teal-500 mr-3"></i> Network Engineering</li>
                                <li class="flex items-center text-sm text-gray-700 font-medium"><i data-lucide="check-circle" class="w-5 h-5 text-teal-500 mr-3"></i> Embedded Systems</li>
                                <li class="flex items-center text-sm text-gray-700 font-medium"><i data-lucide="check-circle" class="w-5 h-5 text-teal-500 mr-3"></i> Cyber Security</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Campus Life & Administration -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="bg-white rounded-3xl shadow-lg border border-gray-100 p-10">
                    <div class="w-14 h-14 bg-orange-50 text-orange-600 rounded-2xl flex items-center justify-center mb-6">
                        <i data-lucide="coffee" class="w-7 h-7"></i>
                    </div>
                    <h3 class="text-2xl font-extrabold text-gray-900 mb-4">Campus Life</h3>
                    <p class="text-gray-600 leading-relaxed mb-6">
                        UCSMTLA offers a vibrant campus life that extends beyond the classroom. We host numerous extracurricular activities, coding bootcamps, sports tournaments, and cultural events. Students have access to a massive central library, modern cafeterias, and collaborative student lounges that foster teamwork and creativity.
                    </p>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 rounded-xl p-4 flex items-center border border-gray-100">
                            <i data-lucide="users" class="w-5 h-5 text-gray-400 mr-3"></i>
                            <span class="text-sm font-semibold text-gray-700">Student Clubs</span>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4 flex items-center border border-gray-100">
                            <i data-lucide="monitor-play" class="w-5 h-5 text-gray-400 mr-3"></i>
                            <span class="text-sm font-semibold text-gray-700">Hackathons</span>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-3xl shadow-lg border border-gray-100 p-10">
                    <div class="w-14 h-14 bg-rose-50 text-rose-600 rounded-2xl flex items-center justify-center mb-6">
                        <i data-lucide="briefcase" class="w-7 h-7"></i>
                    </div>
                    <h3 class="text-2xl font-extrabold text-gray-900 mb-4">Administration</h3>
                    <p class="text-gray-600 leading-relaxed mb-6">
                        Our administrative team is dedicated to providing robust support systems for our students and faculty. The administration comprises the Rector's Office, Academic Affairs, Student Services, and Financial Aid departments, ensuring a smooth, fully supported academic journey for every student.
                    </p>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 rounded-xl p-4 flex items-center border border-gray-100">
                            <i data-lucide="file-text" class="w-5 h-5 text-gray-400 mr-3"></i>
                            <span class="text-sm font-semibold text-gray-700">Registrar</span>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4 flex items-center border border-gray-100">
                            <i data-lucide="life-buoy" class="w-5 h-5 text-gray-400 mr-3"></i>
                            <span class="text-sm font-semibold text-gray-700">Student Affairs</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- University Statistics -->
            <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-12 relative overflow-hidden">
                <div class="absolute inset-0 bg-blue-600 opacity-[0.03]"></div>
                <div class="text-center mb-10 relative z-10">
                    <h3 class="text-3xl font-extrabold text-gray-900">University Statistics</h3>
                    <p class="mt-4 text-lg text-gray-500">A glance at our academic footprint.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative z-10">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i data-lucide="users" class="w-8 h-8"></i>
                        </div>
                        <div class="text-4xl font-black text-gray-900 mb-2"><?php echo number_format($about_student_count); ?>+</div>
                        <div class="text-sm font-bold text-gray-500 uppercase tracking-widest">Active Students</div>
                    </div>
                    <div class="text-center">
                        <div class="w-16 h-16 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i data-lucide="layers" class="w-8 h-8"></i>
                        </div>
                        <div class="text-4xl font-black text-gray-900 mb-2"><?php echo $about_department_count; ?></div>
                        <div class="text-sm font-bold text-gray-500 uppercase tracking-widest">Departments</div>
                    </div>
                    <div class="text-center">
                        <div class="w-16 h-16 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i data-lucide="calendar" class="w-8 h-8"></i>
                        </div>
                        <div class="text-4xl font-black text-gray-900 mb-2"><?php echo $about_ay_count; ?></div>
                        <div class="text-sm font-bold text-gray-500 uppercase tracking-widest">Academic Years</div>
                    </div>
                </div>
            </div>

            <!-- CTA -->
            <div class="text-center pb-4">
                <a href="#section-announcements" class="inline-flex items-center justify-center px-8 py-4 border border-transparent text-lg font-bold rounded-xl text-white bg-blue-600 hover:bg-blue-700 shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                    Explore Announcements
                    <i data-lucide="arrow-right" class="w-5 h-5 ml-2"></i>
                </a>
            </div>

        </div>
    </section>

    <!-- ==================== CONTACT TAB ==================== -->
    <section id="contact" class="w-full bg-[#F5F7FB] scroll-mt-[88px]">
        <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8 py-16 lg:py-20">

            <!-- Section Heading -->
            <div class="max-w-2xl mb-12">
                <span class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-blue-700 mb-3">
                    <span class="w-8 h-8 rounded-lg bg-blue-600 text-white inline-flex items-center justify-center">
                        <i data-lucide="send" class="w-4 h-4"></i>
                    </span>
                    Get In Touch
                </span>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Contact Us</h2>
                <p class="text-base sm:text-lg text-gray-600">Have a question or need assistance? Get in touch with UCSMTLA and our team will be happy to help.</p>
            </div>

            <?php
            // Office hours — kept as the values already used across the project's public pages.
            // No office-hours configuration exists in system_settings, so these stay static.
            $contact_today = (int)date('N'); // 1 = Monday ... 7 = Sunday
            $oh_rows = [
                ['days' => 'Monday &ndash; Friday', 'time' => '9:00 AM &ndash; 4:00 PM', 'open' => true,  'is_today' => $contact_today >= 1 && $contact_today <= 5],
                ['days' => 'Saturday',              'time' => 'Closed',                  'open' => false, 'is_today' => $contact_today === 6],
                ['days' => 'Sunday',                'time' => 'Closed',                  'open' => false, 'is_today' => $contact_today === 7],
            ];
            ?>

            <!-- Two-Column Layout: Contact Form | Office Hours & Info -->
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-8 lg:gap-10 items-start">

                <!-- LEFT: Contact Form (60%) -->
                <div class="lg:col-span-3">
                    <div class="bg-white rounded-3xl shadow-lg border border-blue-100 p-8 sm:p-10">
                        <h3 class="text-2xl font-extrabold text-gray-900 mb-2 flex items-center">
                            <i data-lucide="send" class="w-7 h-7 text-blue-600 mr-3"></i>
                            Send a Message
                        </h3>
                        <p class="text-gray-500 mb-8">Fill out the form below and our team will get back to you shortly.</p>

                        <div id="contact-notification" class="hidden mb-6 p-4 rounded-xl flex items-start">
                            <i id="notif-icon" data-lucide="check-circle" class="w-5 h-5 mr-3 mt-0.5"></i>
                            <p id="notif-message" class="text-sm font-medium"></p>
                        </div>

                        <form id="contact-form" class="space-y-6" novalidate>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div>
                                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Full Name <span class="text-red-500">*</span></label>
                                    <input type="text" name="name" id="name" autocomplete="name" class="w-full px-4 py-3 bg-[#F8FAFC] border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-gray-900 placeholder-gray-400" placeholder="John Doe">
                                    <p id="name-error" class="hidden mt-1.5 text-sm text-red-500 font-medium"></p>
                                </div>
                                <div>
                                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email Address <span class="text-red-500">*</span></label>
                                    <input type="email" name="email" id="email" autocomplete="email" class="w-full px-4 py-3 bg-[#F8FAFC] border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-gray-900 placeholder-gray-400" placeholder="john@example.com">
                                    <p id="email-error" class="hidden mt-1.5 text-sm text-red-500 font-medium"></p>
                                </div>
                            </div>
                            <div>
                                <label for="subject" class="block text-sm font-semibold text-gray-700 mb-2">Subject <span class="text-red-500">*</span></label>
                                <input type="text" name="subject" id="subject" class="w-full px-4 py-3 bg-[#F8FAFC] border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-gray-900 placeholder-gray-400" placeholder="How can we help you?">
                                <p id="subject-error" class="hidden mt-1.5 text-sm text-red-500 font-medium"></p>
                            </div>
                            <div>
                                <label for="message" class="block text-sm font-semibold text-gray-700 mb-2">Message <span class="text-red-500">*</span></label>
                                <textarea id="message" name="message" rows="6" class="w-full px-4 py-3 bg-[#F8FAFC] border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-gray-900 placeholder-gray-400 resize-none" placeholder="Write your message here..."></textarea>
                                <p id="message-error" class="hidden mt-1.5 text-sm text-red-500 font-medium"></p>
                            </div>
                            <div>
                                <button type="submit" id="submit-btn" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-4 border border-transparent rounded-xl shadow-md text-base font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-300 transform hover:-translate-y-0.5">
                                    <i data-lucide="send" class="w-5 h-5"></i>
                                    Send Message
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- RIGHT: Office Hours & Contact Information (40%) -->
                <div class="lg:col-span-2 space-y-8">

                    <!-- Office Hours Card -->
                    <div class="bg-white rounded-3xl shadow-lg border border-blue-100 p-8">
                        <div class="flex items-center mb-6">
                            <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mr-4 flex-shrink-0">
                                <i data-lucide="clock" class="w-7 h-7"></i>
                            </div>
                            <div>
                                <h3 class="text-2xl font-extrabold text-gray-900 leading-tight">Office Hours</h3>
                                <p class="text-sm text-gray-500">University Hours</p>
                            </div>
                        </div>
                        <ul class="divide-y divide-gray-100">
                            <?php foreach ($oh_rows as $row): ?>
                                <li class="flex items-center justify-between gap-3 py-4 <?php echo $row['is_today'] ? 'bg-blue-50 rounded-xl px-4 -mx-4' : ''; ?>">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <span class="w-2.5 h-2.5 rounded-full <?php echo $row['open'] ? 'bg-green-500' : 'bg-gray-300'; ?> flex-shrink-0"></span>
                                        <span class="text-sm font-semibold text-gray-800">
                                            <?php echo $row['days']; ?>
                                        </span>
                                        <?php if ($row['is_today']): ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-blue-600 text-white text-[10px] font-bold uppercase tracking-wider">
                                                <i data-lucide="star" class="w-3 h-3"></i>
                                                Today
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <span class="text-sm font-bold whitespace-nowrap <?php echo $row['open'] ? 'text-blue-700 bg-blue-50 px-3 py-1 rounded-lg' : 'text-gray-500 bg-gray-100 px-3 py-1 rounded-lg'; ?>">
                                        <?php echo $row['time']; ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- Contact Information Card -->
                    <div class="bg-white rounded-3xl shadow-lg border border-blue-100 p-8">
                        <div class="flex items-center mb-6">
                            <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mr-4 flex-shrink-0">
                                <i data-lucide="info" class="w-7 h-7"></i>
                            </div>
                            <div>
                                <h3 class="text-2xl font-extrabold text-gray-900 leading-tight">Contact Information</h3>
                                <p class="text-sm text-gray-500">Reach us directly</p>
                            </div>
                        </div>
                        <ul class="space-y-5">
                            <li class="flex items-start">
                                <i data-lucide="phone" class="w-5 h-5 text-blue-600 mr-3 mt-0.5 flex-shrink-0"></i>
                                <div class="min-w-0">
                                    <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-0.5">Phone</p>
                                    <a href="tel:<?php echo htmlspecialchars(preg_replace('/[^0-9+]/', '', $uni_phone)); ?>" class="text-blue-600 font-bold hover:text-blue-800 transition-colors break-words"><?php echo htmlspecialchars($uni_phone); ?></a>
                                </div>
                            </li>
                            <li class="flex items-start">
                                <i data-lucide="mail" class="w-5 h-5 text-blue-600 mr-3 mt-0.5 flex-shrink-0"></i>
                                <div class="min-w-0">
                                    <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-0.5">Email</p>
                                    <a href="mailto:<?php echo htmlspecialchars($uni_email); ?>" class="text-blue-600 font-bold hover:text-blue-800 transition-colors break-words"><?php echo htmlspecialchars($uni_email); ?></a>
                                </div>
                            </li>
                            <li class="flex items-start">
                                <i data-lucide="map-pin" class="w-5 h-5 text-blue-600 mr-3 mt-0.5 flex-shrink-0"></i>
                                <div class="min-w-0">
                                    <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-0.5">Address</p>
                                    <p class="text-gray-700 font-medium leading-relaxed"><?php echo htmlspecialchars($uni_address); ?></p>
                                </div>
                            </li>
                        </ul>
                    </div>

                </div>

            </div>
        </div>
    </section>

</main>

<!-- SPA tab switching, in-page search, and contact form -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    /* ==================== SINGLE-PAGE SCROLL NAVIGATION ==================== */
    var navTargets = ['section-home', 'section-announcements', 'section-campus', 'section-about', 'contact'];

    function updateActiveNav() {
        var pos = window.scrollY + 120;
        var current = 'section-home';
        for (var i = 0; i < navTargets.length; i++) {
            var el = document.getElementById(navTargets[i]);
            if (el && el.offsetTop <= pos) current = navTargets[i];
        }
        document.querySelectorAll('.nav-pill .nav-item').forEach(function (a) {
            a.classList.toggle('active', a.getAttribute('data-section') === current);
        });
        var mobileMenu = document.getElementById('public-mobile-menu');
        if (mobileMenu) {
            mobileMenu.querySelectorAll('a[data-section]').forEach(function (a) {
                a.classList.toggle('active', a.getAttribute('data-section') === current);
            });
        }
    }
    window.addEventListener('scroll', updateActiveNav, { passive: true });
    window.addEventListener('resize', updateActiveNav);

    /* ==================== MOBILE MENU ==================== */
    var btn = document.getElementById('public-mobile-menu-btn');
    var menu = document.getElementById('public-mobile-menu');

    function closeMobileMenu() {
        if (menu) menu.classList.remove('open');
        if (btn) {
            var icon = btn.querySelector('[data-lucide]');
            if (icon) icon.setAttribute('data-lucide', 'menu');
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
    }

    if (btn && menu) {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            menu.classList.toggle('open');
            var isOpen = menu.classList.contains('open');
            var icon = btn.querySelector('[data-lucide]');
            if (icon) icon.setAttribute('data-lucide', isOpen ? 'x' : 'menu');
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
        document.addEventListener('click', function (e) {
            if (menu.classList.contains('open') && !menu.contains(e.target) && e.target !== btn && !btn.contains(e.target)) {
                closeMobileMenu();
            }
        });
        menu.querySelectorAll('a[href^="#"]').forEach(function (link) {
            link.addEventListener('click', closeMobileMenu);
        });
    }

    /* ==================== HOME FILTER RESET ==================== */
    var resetBtn = document.getElementById('filter-reset');
    var form = document.getElementById('filter-form');
    if (resetBtn && form) {
        resetBtn.addEventListener('click', function () { form.reset(); });
    }

    /* ==================== ANNOUNCEMENTS SECTION: IN-PAGE SEARCH ==================== */
    var tabResults = document.getElementById('tab-results-container');
    var tabLoading = document.getElementById('tab-loading');
    var tabPagination = document.getElementById('tab-pagination-container');
    var tabCount = document.getElementById('tab-results-count');
    var tabFilterForm = document.getElementById('tab-filter-form');
    var tabCurrentPage = 1;

    function tabCard(r) {
        var urgentClass = r.is_urgent == 1 ? 'border-red-200 ring-1 ring-red-500/40' : 'border-blue-100';
        var img = r.image_path
            ? '<img src="../' + r.image_path + '" alt="' + r.title + '" class="w-full h-full object-cover transform transition-transform duration-700 group-hover:scale-105" loading="lazy">'
            : '<div class="absolute inset-0 flex items-center justify-center"><i data-lucide="image" class="w-12 h-12 text-blue-300 transform transition-transform duration-700 group-hover:scale-110"></i></div>';
        var urgentBadge = r.is_urgent == 1
            ? '<div class="absolute top-4 left-4 bg-red-500 text-white text-[10px] font-bold px-3 py-1.5 rounded-full shadow-md uppercase tracking-wider z-10 flex items-center gap-1.5"><i data-lucide="alert-circle" class="w-3 h-3"></i> Urgent</div>'
            : '';
        return '<a href="announcement.php?id=' + r.id + '" class="group bg-white rounded-3xl shadow-sm hover:shadow-xl border overflow-hidden flex flex-col transform transition-all duration-300 hover:-translate-y-1 ' + urgentClass + '">' +
            '<div class="relative h-52 bg-gradient-to-br from-blue-50 to-blue-100 overflow-hidden">' + urgentBadge + img +
            '<div class="absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-black/30 to-transparent pointer-events-none"></div></div>' +
            '<div class="p-6 flex-1 flex flex-col">' +
            '<div class="flex items-center justify-between gap-3 mb-3">' +
            '<span class="text-[10px] font-bold tracking-wider uppercase text-blue-700 bg-blue-50 px-2.5 py-1 rounded-md">' + (r.category_name || 'General') + '</span>' +
            '<span class="text-xs text-gray-500 font-medium flex items-center gap-1"><i data-lucide="clock" class="w-3 h-3"></i>' + r.formatted_date + '</span></div>' +
            '<h3 class="text-lg font-bold text-gray-900 mb-3 group-hover:text-blue-600 transition-colors line-clamp-2">' + r.title + '</h3>' +
            '<p class="text-gray-500 text-sm mb-6 line-clamp-3 leading-relaxed">' + r.snippet + '</p>' +
            '<div class="mt-auto pt-4 border-t border-gray-100"><span class="inline-flex items-center text-sm font-semibold text-blue-600 group-hover:text-blue-700 transition-colors">Read Full Announcement<i data-lucide="arrow-right" class="w-4 h-4 ml-1.5 transform group-hover:translate-x-1 transition-transform"></i></span></div>' +
            '</div></a>';
    }

    function renderTabPagination(pg) {
        if (pg.total_pages <= 1) { tabPagination.classList.add('hidden'); return; }
        tabPagination.classList.remove('hidden');
        tabPagination.innerHTML =
            '<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white rounded-2xl border border-blue-100 px-6 py-4">' +
            '<p class="text-sm text-gray-600">Page <span class="font-medium text-gray-900">' + pg.current_page + '</span> of <span class="font-medium text-gray-900">' + pg.total_pages + '</span></p>' +
            '<div class="flex items-center gap-2">' +
            '<button type="button" onclick="window.ucsmtlaTabPage(' + (pg.current_page - 1) + ')" ' + (pg.current_page === 1 ? 'disabled' : '') + ' class="inline-flex items-center gap-1 px-4 py-2 rounded-xl text-sm font-semibold border border-gray-200 bg-white text-gray-700 hover:bg-blue-50 disabled:opacity-50 transition-colors"><i data-lucide="chevron-left" class="w-4 h-4"></i> Previous</button>' +
            '<button type="button" onclick="window.ucsmtlaTabPage(' + (pg.current_page + 1) + ')" ' + (pg.current_page === pg.total_pages ? 'disabled' : '') + ' class="inline-flex items-center gap-1 px-4 py-2 rounded-xl text-sm font-semibold border border-gray-200 bg-white text-gray-700 hover:bg-blue-50 disabled:opacity-50 transition-colors">Next <i data-lucide="chevron-right" class="w-4 h-4"></i></button>' +
            '</div></div>';
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function loadTabResults() {
        if (!tabResults) return;
        if (tabLoading) tabLoading.classList.remove('hidden');
        tabPagination.classList.add('hidden');

        var params = new URLSearchParams(new FormData(tabFilterForm));
        params.append('page', tabCurrentPage);

        fetch('../ajax/search_results.php?' + params.toString())
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    if (data.results.length === 0) {
                        tabResults.innerHTML = '<div class="col-span-full bg-white p-12 rounded-3xl border border-blue-100 text-center">' +
                            '<i data-lucide="file-question" class="w-12 h-12 text-gray-400 mx-auto mb-4"></i>' +
                            '<h3 class="text-xl font-bold text-gray-900 mb-2">No announcements found</h3>' +
                            '<p class="text-gray-500">Try adjusting your filters or search term to find what you are looking for.</p></div>';
                    } else {
                        tabResults.innerHTML = data.results.map(tabCard).join('');
                    }
                    renderTabPagination(data.pagination);
                    if (tabCount) tabCount.innerHTML = '<span class="w-2 h-2 rounded-full bg-blue-500"></span> ' + data.pagination.total_items + ' found';
                } else {
                    tabResults.innerHTML = '<div class="col-span-full bg-white p-12 rounded-3xl border border-red-100 text-center text-red-500">Error: ' + data.message + '</div>';
                }
                if (typeof lucide !== 'undefined') lucide.createIcons();
            })
            .catch(function () {
                tabResults.innerHTML = '<div class="col-span-full bg-white p-12 rounded-3xl border border-red-100 text-center text-red-500">Failed to load announcements.</div>';
            })
            .finally(function () {
                if (tabLoading) tabLoading.classList.add('hidden');
            });
    }

    window.ucsmtlaTabPage = function (p) {
        tabCurrentPage = p;
        loadTabResults();
        var announceEl = document.getElementById('section-announcements');
        if (announceEl) announceEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
        else window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    if (tabFilterForm) {
        tabFilterForm.addEventListener('submit', function (e) {
            e.preventDefault();
            tabCurrentPage = 1;
            loadTabResults();
        });
        var tabReset = document.getElementById('tab-filter-reset');
        if (tabReset) {
            tabReset.addEventListener('click', function () {
                tabFilterForm.reset();
                tabCurrentPage = 1;
                loadTabResults();
            });
        }
    }

    /* ==================== CONTACT FORM ==================== */
    var contactForm = document.getElementById('contact-form');
    if (contactForm) {
        var submitBtn = document.getElementById('submit-btn');
        var notification = document.getElementById('contact-notification');
        var notifMessage = document.getElementById('notif-message');
        var notifIcon = document.getElementById('notif-icon');

        var contactFields = ['name', 'email', 'subject', 'message'];

        function setFieldError(fieldId, message) {
            var input = document.getElementById(fieldId);
            var error = document.getElementById(fieldId + '-error');
            if (!input || !error) return;
            if (message) {
                input.classList.add('border-red-400', 'ring-2', 'ring-red-100');
                input.setAttribute('aria-invalid', 'true');
                error.textContent = message;
                error.classList.remove('hidden');
            } else {
                input.classList.remove('border-red-400', 'ring-2', 'ring-red-100');
                input.removeAttribute('aria-invalid');
                error.textContent = '';
                error.classList.add('hidden');
            }
        }

        function fieldMessage(fieldId, value) {
            if (value === '') return 'This field is required.';
            if (fieldId === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) return 'Please enter a valid email address.';
            return '';
        }

        function validateContactForm() {
            var valid = true;
            contactFields.forEach(function (id) {
                var input = document.getElementById(id);
                var msg = fieldMessage(id, input ? input.value.trim() : '');
                setFieldError(id, msg);
                if (msg) valid = false;
            });
            return valid;
        }

        contactFields.forEach(function (id) {
            var input = document.getElementById(id);
            if (!input) return;
            input.addEventListener('input', function () { setFieldError(id, ''); });
            input.addEventListener('blur', function () {
                setFieldError(id, fieldMessage(id, input.value.trim()));
            });
        });

        contactForm.addEventListener('submit', function (e) {
            e.preventDefault();
            if (!validateContactForm()) return;

            var originalBtnHtml = submitBtn.innerHTML;
            submitBtn.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Sending...';
            submitBtn.disabled = true;

            fetch('ajax_submit_contact.php', { method: 'POST', body: new FormData(contactForm) })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    notification.classList.remove('hidden', 'bg-red-50', 'text-red-800', 'bg-green-50', 'text-green-800');
                    if (data.success) {
                        notification.classList.add('bg-green-50', 'text-green-800');
                        notifIcon.setAttribute('data-lucide', 'check-circle');
                        notifIcon.classList.remove('text-red-500');
                        notifIcon.classList.add('text-green-500');
                        notifMessage.innerText = data.message;
                        contactForm.reset();
                        validateContactForm();
                    } else {
                        notification.classList.add('bg-red-50', 'text-red-800');
                        notifIcon.setAttribute('data-lucide', 'alert-circle');
                        notifIcon.classList.remove('text-green-500');
                        notifIcon.classList.add('text-red-500');
                        notifMessage.innerText = data.message || 'An error occurred. Please try again.';
                    }
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                    submitBtn.innerHTML = originalBtnHtml;
                    submitBtn.disabled = false;
                })
                .catch(function () {
                    notification.classList.remove('hidden', 'bg-green-50', 'text-green-800');
                    notification.classList.add('bg-red-50', 'text-red-800');
                    notifIcon.setAttribute('data-lucide', 'alert-circle');
                    notifIcon.classList.remove('text-green-500');
                    notifIcon.classList.add('text-red-500');
                    notifMessage.innerText = 'Network error. Please try again later.';
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                    submitBtn.innerHTML = originalBtnHtml;
                    submitBtn.disabled = false;
                });
        });
    }

    /* Initialize: load the announcements browser and set the active nav state */
    loadTabResults();
    updateActiveNav();
});
</script>

<?php include '../includes/footer.php'; ?>
