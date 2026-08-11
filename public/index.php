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
$today_count = $stmt->fetchColumn();

// Stats: Urgent Announcements
$stmt = $pdo->query("SELECT COUNT(*) FROM announcements a LEFT JOIN academic_years ay ON a.academic_year_id = ay.id WHERE a.is_urgent = 1 AND $published_sql $ay_sql");
$urgent_count = $stmt->fetchColumn();

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
?>
<?php
$is_public_area = true;
include '../includes/header.php'; 
?>

<!-- ===== Page-Specific Styles (Guest Home only) ===== -->
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap');

    #public-main-content, #public-mobile-menu, section {
        font-family: 'Poppins', 'Inter', sans-serif;
    }

    /* ---------- Hero background & decoration ---------- */
    .hero-bg {
        background: linear-gradient(180deg, #1E40AF 0%, #2563EB 52%, #38BDF8 100%);
    }
    .hero-dots {
        background-image: radial-gradient(rgba(255,255,255,0.13) 1.5px, transparent 1.5px);
        background-size: 26px 26px;
    }
    .hero-blob {
        position: absolute;
        border-radius: 9999px;
        filter: blur(90px);
        pointer-events: none;
    }
    .hero-blob-1 { width: 540px; height: 540px; top: -180px; right: -180px; background: rgba(125,211,252,0.22); }
    .hero-blob-2 { width: 560px; height: 560px; bottom: -240px; left: -240px; background: rgba(96,165,250,0.20); }
    .hero-blob-3 { width: 440px; height: 440px; top: 42%; left: 42%; background: rgba(255,255,255,0.07); }
    .hero-curve {
        position: absolute;
        left: 0; right: 0; bottom: 0;
        height: 3.5rem;
        background: #ffffff;
        border-top-left-radius: 3rem;
        border-top-right-radius: 3rem;
    }
    .hero-inner {
        position: relative;
        z-index: 10;
        display: flex;
        flex-direction: column;
    }
    @media (min-width: 1024px) {
        .hero-inner { min-height: 100vh; justify-content: center; }
    }

    /* ---------- Brand ---------- */
    .hero-logo-img { height: 2.75rem; width: auto; object-fit: contain; filter: drop-shadow(0 6px 16px rgba(15,23,42,0.4)); }
    @media (min-width: 640px) { .hero-logo-img { height: 3rem; } }
    .brand-name { font-size: 1.35rem; line-height: 1; font-weight: 800; letter-spacing: -0.02em; color: #fff; }
    @media (min-width: 640px) { .brand-name { font-size: 1.5rem; } }
    .brand-sub { margin-top: 4px; font-size: 11px; font-weight: 600; letter-spacing: 0.06em; text-transform: uppercase; color: #e0f2fe; }

    /* ---------- Centered navigation pill ---------- */
    .nav-pill { display: none; }
    @media (min-width: 1024px) {
        .nav-pill {
            display: flex;
            align-items: center;
            gap: 4px;
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(255,255,255,0.10);
            border: 1px solid rgba(255,255,255,0.22);
            backdrop-filter: blur(10px);
            border-radius: 9999px;
            padding: 6px;
            box-shadow: 0 12px 32px -14px rgba(30,58,138,0.6);
        }
    }
    .nav-item {
        padding: 8px 20px;
        border-radius: 9999px;
        font-size: 14px;
        font-weight: 600;
        color: rgba(224,242,254,0.85);
        transition: all .3s ease;
        white-space: nowrap;
    }
    .nav-item:hover { background: rgba(255,255,255,0.12); color: #fff; }
    .nav-item.active { background: rgba(255,255,255,0.22); color: #fff; box-shadow: inset 0 1px 3px rgba(0,0,0,0.15); }

    /* ---------- Header buttons ---------- */
    .btn-login {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #fff;
        color: #1d4ed8;
        font-weight: 700;
        font-size: 14px;
        padding: 11px 22px;
        border-radius: 14px;
        box-shadow: 0 12px 30px -12px rgba(30,58,138,0.6);
        transition: all .3s ease;
    }
    .btn-login:hover { background: #eff6ff; color: #1e40af; transform: translateY(-2px); box-shadow: 0 16px 34px -12px rgba(30,58,138,0.65); }
    .btn-login-mobile {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #fff;
        color: #1d4ed8;
        font-weight: 700;
        font-size: 13px;
        padding: 10px 16px;
        border-radius: 12px;
        box-shadow: 0 10px 26px -12px rgba(30,58,138,0.6);
        transition: all .3s ease;
    }
    .btn-hamburger {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        border-radius: 14px;
        background: rgba(255,255,255,0.10);
        border: 1px solid rgba(255,255,255,0.22);
        color: #fff;
        transition: background .3s ease;
    }
    .btn-hamburger:hover { background: rgba(255,255,255,0.20); }
    @media (min-width: 1024px) { .btn-hamburger { display: none; } }

    /* ---------- Mobile menu ---------- */
    .mobile-menu {
        display: none;
        margin-top: 16px;
        background: rgba(255,255,255,0.10);
        border: 1px solid rgba(255,255,255,0.22);
        backdrop-filter: blur(12px);
        border-radius: 18px;
        padding: 12px;
        box-shadow: 0 20px 40px -16px rgba(30,58,138,0.6);
    }
    .mobile-menu.open { display: block; }
    @media (min-width: 1024px) { .mobile-menu { display: none !important; } }
    .mobile-menu a {
        display: block;
        padding: 11px 14px;
        border-radius: 12px;
        font-weight: 600;
        color: rgba(224,242,254,0.9);
        transition: all .2s ease;
    }
    .mobile-menu a:hover, .mobile-menu a.active { background: rgba(255,255,255,0.12); color: #fff; }

    /* ---------- Academic year badge ---------- */
    .ay-badge-wrap { margin-top: 2.25rem; }
    @media (min-width: 1024px) { .ay-badge-wrap { margin-top: 3rem; } }
    .ay-badge {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        background: rgba(15,23,42,0.5);
        border: 1px solid rgba(255,255,255,0.22);
        backdrop-filter: blur(10px);
        border-radius: 9999px;
        padding: 8px 18px;
        box-shadow: 0 12px 30px -14px rgba(30,58,138,0.6);
    }
    .ay-dot {
        position: relative;
        width: 10px;
        height: 10px;
        border-radius: 9999px;
        background: #4ade80;
        flex-shrink: 0;
    }
    .ay-dot::after {
        content: '';
        position: absolute;
        inset: -4px;
        border-radius: 9999px;
        background: rgba(74,222,128,0.5);
        animation: ping 1.5s cubic-bezier(0,0,.2,1) infinite;
    }
    @keyframes ping { 0% { transform: scale(.6); opacity: .7; } 80%, 100% { transform: scale(1.7); opacity: 0; } }
    .ay-text { font-size: 14px; font-weight: 600; color: #fff; letter-spacing: 0.02em; }

    /* ---------- Hero content ---------- */
    .hero-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 3.5rem;
        align-items: center;
        margin-top: 2.5rem;
    }
    @media (min-width: 1024px) { .hero-grid { grid-template-columns: 1.15fr 0.85fr; gap: 4rem; margin-top: 3.5rem; } }

    .hero-copy { text-align: center; }
    @media (min-width: 1024px) { .hero-copy { text-align: left; } }

    .hero-title {
        font-size: 2.5rem;
        line-height: 1.06;
        font-weight: 800;
        letter-spacing: -0.02em;
    }
    @media (min-width: 640px) { .hero-title { font-size: 3.25rem; } }
    @media (min-width: 1024px) { .hero-title { font-size: 4rem; } }
    .hero-title .line-1 { display: block; color: #fff; text-shadow: 0 6px 24px rgba(15,23,42,0.25); }
    .hero-title .line-2 { display: block; color: #bae6fd; text-shadow: 0 6px 24px rgba(15,23,42,0.25); }

    .hero-desc {
        margin-top: 1.5rem;
        max-width: 34rem;
        font-size: 1.125rem;
        line-height: 1.7;
        color: rgba(224,242,254,0.92);
    }
    @media (min-width: 640px) { .hero-desc { font-size: 1.25rem; } }

    .hero-cta {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        margin-top: 2.25rem;
        align-items: center;
    }
    @media (min-width: 640px) { .hero-cta { flex-direction: row; } }
    @media (min-width: 1024px) { .hero-cta { justify-content: flex-start; } }

    .btn-primary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        background: #fff;
        color: #1d4ed8;
        font-weight: 700;
        font-size: 15px;
        padding: 15px 28px;
        border-radius: 16px;
        box-shadow: 0 16px 36px -14px rgba(30,58,138,0.7);
        transition: all .3s ease;
    }
    .btn-primary:hover { background: #eff6ff; transform: translateY(-3px); box-shadow: 0 22px 40px -14px rgba(30,58,138,0.75); }

    .btn-secondary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        background: rgba(15,23,42,0.45);
        color: #fff;
        font-weight: 700;
        font-size: 15px;
        padding: 15px 28px;
        border-radius: 16px;
        border: 1px solid rgba(255,255,255,0.25);
        backdrop-filter: blur(8px);
        box-shadow: 0 12px 30px -14px rgba(30,58,138,0.5);
        transition: all .3s ease;
    }
    .btn-secondary:hover { background: rgba(15,23,42,0.6); transform: translateY(-3px); }

    /* ---------- Latest announcement card ---------- */
    .ann-card {
        display: block;
        width: 100%;
        background: #fff;
        border-radius: 1.75rem;
        overflow: hidden;
        box-shadow: 0 30px 60px -20px rgba(30,58,138,0.45);
        transition: all .35s ease;
    }
    .ann-card:hover { transform: translateY(-6px); box-shadow: 0 40px 70px -20px rgba(30,58,138,0.55); }
    .ann-img {
        position: relative;
        height: 14rem;
        overflow: hidden;
        background: linear-gradient(135deg, #dbeafe, #f0f9ff);
    }
    @media (min-width: 640px) { .ann-img { height: 16rem; } }
    .ann-img img { width: 100%; height: 100%; object-fit: cover; transition: transform .8s ease; }
    .ann-card:hover .ann-img img { transform: scale(1.06); }
    .ann-badge {
        position: absolute;
        top: 1rem;
        left: 1rem;
        z-index: 10;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #2563eb;
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        padding: 7px 16px;
        border-radius: 9999px;
        box-shadow: 0 10px 24px -8px rgba(37,99,235,0.7);
    }
    .ann-placeholder-icon { width: 4rem; height: 4rem; color: #93c5fd; }
    .ann-body { padding: 1.5rem 1.5rem 1.75rem; }
    @media (min-width: 640px) { .ann-body { padding: 1.75rem; } }
    .ann-cat {
        display: inline-flex;
        align-items: center;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: #2563eb;
        background: #eff6ff;
        padding: 6px 12px;
        border-radius: 10px;
    }
    .ann-date { font-size: 14px; color: #64748b; font-weight: 500; }
    .ann-title {
        margin-top: 12px;
        font-size: 1.35rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.3;
        transition: color .25s ease;
    }
    .ann-card:hover .ann-title { color: #1d4ed8; }
    .ann-excerpt {
        margin-top: 10px;
        font-size: 15px;
        line-height: 1.65;
        color: #475569;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .ann-readmore {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 18px;
        background: #2563eb;
        color: #fff;
        font-size: 14px;
        font-weight: 700;
        padding: 11px 20px;
        border-radius: 12px;
        transition: all .25s ease;
    }
    .ann-card:hover .ann-readmore { background: #1d4ed8; }

    /* ---------- Entry animations ---------- */
    @keyframes fadeDown { from { opacity: 0; transform: translateY(-18px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes slideLeft { from { opacity: 0; transform: translateX(-26px); } to { opacity: 1; transform: translateX(0); } }
    @keyframes slideRight { from { opacity: 0; transform: translateX(26px); } to { opacity: 1; transform: translateX(0); } }
    .animate-fade-down { animation: fadeDown .45s ease both; }
    .animate-slide-in-left { animation: slideLeft .5s ease both; }
    .animate-slide-in-right { animation: slideRight .5s ease both; }
</style>

<main id="public-main-content" class="w-full">

    <!-- ==================== HERO & LATEST ANNOUNCEMENT ==================== -->
    <section id="hero" class="hero-bg relative overflow-hidden">
        <!-- Decorative background -->
        <div class="absolute inset-0 hero-dots" aria-hidden="true"></div>
        <div class="hero-blob hero-blob-1" aria-hidden="true"></div>
        <div class="hero-blob hero-blob-2" aria-hidden="true"></div>
        <div class="hero-blob hero-blob-3" aria-hidden="true"></div>
        <div class="hero-curve" aria-hidden="true"></div>

        <div class="hero-inner mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8 pt-5 pb-24">

            <!-- ==================== HEADER ==================== -->
            <header class="relative">
                <div class="flex items-center justify-between gap-4">
                    <!-- LEFT: Brand -->
                    <a href="index.php" class="flex items-center gap-3 group flex-shrink-0">
                        <img src="<?php echo htmlspecialchars(base_url('/assets/images/ucsmtla logo.png')); ?>" alt="UCSMTLA Logo" class="hero-logo-img transition-transform duration-300 group-hover:scale-105">
                        <span class="flex flex-col leading-none">
                            <span class="brand-name">UCSMTLA</span>
                            <span class="brand-sub">Academic Hub</span>
                        </span>
                    </a>

                    <!-- CENTER: Navigation pill -->
                    <nav class="nav-pill" aria-label="Primary navigation">
                        <?php
                        $public_nav = [
                            'index.php'    => 'Home',
                            'search.php'   => 'Announcements',
                            'campus.php'   => 'Campus',
                            'about.php'    => 'About',
                            'contact.php'  => 'Contact',
                        ];
                        foreach ($public_nav as $nav_url => $nav_label):
                            $nav_active = ($nav_url === 'index.php');
                        ?>
                        <a href="<?php echo $nav_url; ?>" class="nav-item <?php echo $nav_active ? 'active' : ''; ?>"><?php echo $nav_label; ?></a>
                        <?php endforeach; ?>
                    </nav>

                    <!-- RIGHT: Student Login + Mobile Toggle -->
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <a href="../auth/login.php" class="btn-login hidden sm:inline-flex">
                            <i data-lucide="log-in" class="w-4 h-4"></i>
                            Student Login
                        </a>
                        <a href="../auth/login.php" class="btn-login-mobile sm:hidden">
                            <i data-lucide="log-in" class="w-4 h-4"></i>
                            Login
                        </a>
                        <button id="public-mobile-menu-btn" class="btn-hamburger" aria-label="Toggle menu">
                            <i data-lucide="menu" class="w-6 h-6"></i>
                        </button>
                    </div>
                </div>

                <!-- Mobile Menu -->
                <nav id="public-mobile-menu" class="mobile-menu" aria-label="Mobile navigation">
                    <?php foreach ($public_nav as $nav_url => $nav_label): ?>
                    <a href="<?php echo $nav_url; ?>" class="<?php echo $nav_url === 'index.php' ? 'active' : ''; ?>">
                        <?php echo $nav_label; ?>
                    </a>
                    <?php endforeach; ?>
                    <a href="../auth/login.php" class="mt-1 flex items-center gap-2">
                        <i data-lucide="log-in" class="w-4 h-4"></i>
                        Student Login
                    </a>
                </nav>
            </header>

            <!-- ==================== ACADEMIC YEAR BADGE ==================== -->
            <div class="ay-badge-wrap">
                <span class="ay-badge">
                    <span class="ay-dot"></span>
                    <span class="ay-text"><?php echo htmlspecialchars($active_ay_name); ?> Academic Session</span>
                </span>
            </div>

            <!-- ==================== HERO CONTENT ==================== -->
            <div class="hero-grid">
                <!-- LEFT: Copy -->
                <div class="hero-copy">
                    <h1 class="hero-title animate-slide-in-left">
                        <span class="line-1">Your University,</span>
                        <span class="line-2">Reimagined.</span>
                    </h1>

                    <p class="hero-desc animate-slide-in-left">
                        Real-time announcements, smart notifications, campus guides, and everything you need — all in one beautifully designed platform.
                    </p>

                    <div class="hero-cta animate-slide-in-left">
                        <a href="search.php" class="btn-primary">
                            Explore Announcements
                            <i data-lucide="arrow-right" class="w-5 h-5"></i>
                        </a>
                        <a href="../auth/login.php" class="btn-secondary">
                            <i data-lucide="log-in" class="w-5 h-5"></i>
                            Student Login
                        </a>
                    </div>
                </div>

                <!-- RIGHT: Latest Announcement Card -->
                <div class="w-full animate-slide-in-right">
                    <?php if ($latest): ?>
                        <a href="announcement.php?id=<?php echo $latest['id']; ?>" class="ann-card">
                            <!-- Image -->
                            <div class="ann-img">
                                <span class="ann-badge">
                                    <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                                    Latest
                                </span>

                                <?php
                                $image_exists = false;
                                if (!empty($latest_image)) {
                                    $local_path = dirname(__DIR__) . '/' . $latest_image;
                                    if (file_exists($local_path)) {
                                        $image_exists = true;
                                    }
                                }
                                ?>

                                <?php if ($image_exists): ?>
                                    <img src="<?php echo htmlspecialchars(base_url($latest_image)); ?>" alt="Announcement Image">
                                <?php else: ?>
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <i data-lucide="image" class="ann-placeholder-icon"></i>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Content -->
                            <div class="ann-body">
                                <div class="flex items-center gap-3 flex-wrap">
                                    <span class="ann-cat"><?php echo htmlspecialchars($latest['category_name'] ?? 'General'); ?></span>
                                    <span class="ann-date">
                                        <?php echo date('M d, Y', strtotime($latest['publish_date'] ?? $latest['created_at'])); ?>
                                    </span>
                                </div>
                                <h3 class="ann-title"><?php echo htmlspecialchars($latest['title']); ?></h3>
                                <p class="ann-excerpt"><?php echo htmlspecialchars(strip_tags($latest['content'])); ?></p>
                                <span class="ann-readmore">
                                    Read More
                                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                </span>
                            </div>
                        </a>
                    <?php else: ?>
                        <div class="ann-card p-10 text-center">
                            <i data-lucide="bell-off" class="w-12 h-12 text-gray-400 mx-auto mb-4"></i>
                            <h3 class="text-lg font-bold mb-2 text-gray-900">No latest announcement available</h3>
                            <p class="text-gray-500">Check back soon for new updates.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </section>

    <!-- ==================== ANNOUNCEMENTS SECTION ==================== -->
    <section id="announcements" class="w-full bg-white">
        <div class="mx-auto w-full max-w-[1400px] px-6 sm:px-8 lg:px-10 xl:px-12 py-20 lg:py-24">
            
            <!-- Section Heading -->
            <div class="mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Latest Announcements</h2>
                <p class="text-lg text-gray-600 max-w-2xl">Stay informed about the latest university activities, updates, and academic news.</p>
            </div>

            <!-- Filter Section -->
            <div class="bg-[#F8FAFC] p-6 md:p-8 rounded-3xl border border-gray-100 mb-10">
                <form id="filter-form" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        <!-- Search -->
                        <div class="md:col-span-6 lg:col-span-6 relative">
                            <i data-lucide="search" class="absolute left-4 top-3.5 w-5 h-5 text-gray-400"></i>
                            <input type="text" id="filter-q" name="q" placeholder="Search announcements..." class="w-full pl-11 pr-4 py-3 bg-white border border-gray-200 rounded-2xl focus:ring-2 focus:ring-blue-600 focus:border-blue-600 outline-none transition-all shadow-sm">
                        </div>
                        
                        <!-- Category -->
                        <div class="md:col-span-3 lg:col-span-3 relative">
                            <i data-lucide="folder" class="absolute left-4 top-3.5 w-5 h-5 text-gray-400"></i>
                            <select id="filter-category" name="category" class="w-full pl-11 pr-10 py-3 bg-white border border-gray-200 rounded-2xl focus:ring-2 focus:ring-blue-600 focus:border-blue-600 outline-none transition-all shadow-sm appearance-none cursor-pointer">
                                <option value="">All Categories</option>
                                <?php foreach($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <i data-lucide="chevron-down" class="absolute right-4 top-3.5 w-5 h-5 text-gray-400 pointer-events-none"></i>
                        </div>

                        <!-- Type -->
                        <div class="md:col-span-3 lg:col-span-3 relative">
                            <i data-lucide="tag" class="absolute left-4 top-3.5 w-5 h-5 text-gray-400"></i>
                            <select id="filter-type" name="type" class="w-full pl-11 pr-10 py-3 bg-white border border-gray-200 rounded-2xl focus:ring-2 focus:ring-blue-600 focus:border-blue-600 outline-none transition-all shadow-sm appearance-none cursor-pointer">
                                <option value="">All Types</option>
                                <option value="normal">Normal</option>
                                <option value="urgent">Urgent</option>
                            </select>
                            <i data-lucide="chevron-down" class="absolute right-4 top-3.5 w-5 h-5 text-gray-400 pointer-events-none"></i>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Announcement Grid -->
            <div id="announcements-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php if (!empty($grid_announcements)): ?>
                    <?php foreach($grid_announcements as $a): ?>
                        <a href="announcement.php?id=<?php echo $a['id']; ?>" class="group bg-white rounded-3xl shadow-sm hover:shadow-xl border border-gray-100 overflow-hidden flex flex-col transform transition-all duration-300 hover:-translate-y-1 <?php echo $a['is_urgent'] ? 'border-red-200 ring-1 ring-red-500' : ''; ?>">
                            <!-- Thumbnail -->
                            <div class="h-48 bg-gray-50 relative overflow-hidden flex items-center justify-center border-b border-gray-100">
                                <?php if($a['is_urgent']): ?>
                                    <div class="absolute top-4 left-4 bg-red-500 text-white text-[10px] font-bold px-3 py-1.5 rounded-full shadow-sm uppercase tracking-wider z-10 flex items-center gap-1.5">
                                        <i data-lucide="alert-circle" class="w-3 h-3"></i> Urgent
                                    </div>
                                <?php endif; ?>
                                
                                <?php if(!empty($a['image_path'])): ?>
                                    <img src="<?php echo htmlspecialchars(base_url($a['image_path'])); ?>" alt="Thumbnail" class="w-full h-full object-cover transform transition-transform duration-700 group-hover:scale-105">
                                <?php else: ?>
                                    <div class="absolute inset-0 bg-[#F8FAFC]"></div>
                                    <i data-lucide="image" class="w-12 h-12 text-gray-300 z-0 transform transition-transform duration-700 group-hover:scale-110"></i>
                                <?php endif; ?>
                            </div>

                            <!-- Content -->
                            <div class="p-6 flex-1 flex flex-col">
                                <div class="flex items-center gap-3 mb-3">
                                    <span class="text-[10px] font-bold tracking-wider uppercase text-blue-600 bg-blue-50 px-2.5 py-1 rounded-md">
                                        <?php echo htmlspecialchars($a['category_name'] ?? 'General'); ?>
                                    </span>
                                    <span class="text-xs text-gray-500 font-medium flex items-center gap-1">
                                        <i data-lucide="clock" class="w-3 h-3"></i>
                                        <?php echo date('M d, Y', strtotime($a['publish_date'] ?? $a['created_at'])); ?>
                                    </span>
                                </div>
                                <h3 class="text-xl font-bold text-gray-900 mb-3 group-hover:text-blue-600 transition-colors line-clamp-2">
                                    <?php echo htmlspecialchars($a['title']); ?>
                                </h3>
                                <p class="text-gray-500 text-sm mb-6 line-clamp-3 leading-relaxed">
                                    <?php echo htmlspecialchars(strip_tags($a['content'])); ?>
                                </p>
                                <div class="mt-auto">
                                    <span class="inline-flex items-center text-sm font-semibold text-blue-600 group-hover:text-blue-700 transition-colors">
                                        Read Full Announcement
                                        <i data-lucide="arrow-right" class="w-4 h-4 ml-1.5 transform group-hover:translate-x-1 transition-transform"></i>
                                    </span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-span-full bg-[#F8FAFC] p-12 rounded-3xl border border-gray-100 text-center">
                        <i data-lucide="file-question" class="w-12 h-12 text-gray-400 mx-auto mb-4"></i>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">No announcements found</h3>
                        <p class="text-gray-500">There are currently no announcements to display.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="mt-12 text-center">
                <a href="search.php" class="inline-flex items-center justify-center px-8 py-4 rounded-2xl text-lg font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 transition-colors">
                    View All Announcements
                </a>
            </div>
        </div>
    </section>

    <!-- ==================== CAMPUS SECTION ==================== -->
    <section id="campus" class="w-full bg-[#F8FAFC]">
        <div class="mx-auto w-full max-w-[1400px] px-6 sm:px-8 lg:px-10 xl:px-12 py-20 lg:py-24">
            <div class="mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Campus Information</h2>
                <p class="text-lg text-gray-600 max-w-2xl">Discover everything you need to know about our university campus facilities, academic calendars, and resources.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 flex flex-col">
                    <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mb-6">
                        <i data-lucide="map" class="w-7 h-7"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Campus Map</h3>
                    <p class="text-gray-500 mb-6 flex-1">Navigate your way around the campus with our interactive university map.</p>
                    <a href="campus.php" class="text-blue-600 font-semibold inline-flex items-center hover:text-blue-700">Explore Map <i data-lucide="arrow-right" class="w-4 h-4 ml-1"></i></a>
                </div>
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 flex flex-col">
                    <div class="w-14 h-14 bg-green-50 text-green-600 rounded-2xl flex items-center justify-center mb-6">
                        <i data-lucide="calendar" class="w-7 h-7"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Academic Calendar</h3>
                    <p class="text-gray-500 mb-6 flex-1">Keep track of important dates, exam schedules, and university holidays.</p>
                    <a href="campus.php" class="text-blue-600 font-semibold inline-flex items-center hover:text-blue-700">View Calendar <i data-lucide="arrow-right" class="w-4 h-4 ml-1"></i></a>
                </div>
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 flex flex-col">
                    <div class="w-14 h-14 bg-purple-50 text-purple-600 rounded-2xl flex items-center justify-center mb-6">
                        <i data-lucide="book-open" class="w-7 h-7"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Library & Resources</h3>
                    <p class="text-gray-500 mb-6 flex-1">Access our digital library and learn about physical library facilities.</p>
                    <a href="campus.php" class="text-blue-600 font-semibold inline-flex items-center hover:text-blue-700">Learn More <i data-lucide="arrow-right" class="w-4 h-4 ml-1"></i></a>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== ABOUT SECTION ==================== -->
    <section id="about" class="w-full bg-white">
        <div class="mx-auto w-full max-w-[1400px] px-6 sm:px-8 lg:px-10 xl:px-12 py-20 lg:py-24">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                <div>
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">About UCSMTLA</h2>
                    <p class="text-lg text-gray-600 leading-relaxed mb-6">
                        The University of Computer Studies, Meiktila is dedicated to producing highly qualified computing professionals and researchers. We strive to provide a world-class education and foster innovation in technology.
                    </p>
                    <p class="text-lg text-gray-600 leading-relaxed mb-8">
                        Our academic hub serves as the central point for students and faculty to engage, communicate, and stay updated with the latest university developments.
                    </p>
                    <a href="about.php" class="inline-flex items-center justify-center px-8 py-4 rounded-2xl text-lg font-semibold text-gray-900 bg-white border border-gray-200 hover:bg-gray-50 hover:border-gray-300 shadow-sm transition-colors">
                        Read More About Us
                    </a>
                </div>
                <div class="bg-[#F8FAFC] rounded-3xl p-10 border border-gray-100 text-center relative overflow-hidden">
                    <i data-lucide="graduation-cap" class="w-24 h-24 text-blue-200 mx-auto mb-6 relative z-10"></i>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2 relative z-10">Excellence in Computing</h3>
                    <p class="text-gray-500 relative z-10">Empowering the next generation of IT leaders.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== CONTACT SECTION ==================== -->
    <section id="contact" class="w-full bg-[#0F172A] text-white">
        <div class="mx-auto w-full max-w-[1400px] px-6 sm:px-8 lg:px-10 xl:px-12 py-20 lg:py-24">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div>
                    <h2 class="text-3xl md:text-4xl font-bold mb-6">Get in Touch</h2>
                    <p class="text-gray-400 text-lg mb-8 max-w-md">Have questions? Feel free to reach out to our administration for any inquiries regarding academics or campus life.</p>
                    
                    <div class="space-y-6">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-white/10 rounded-full flex items-center justify-center flex-shrink-0">
                                <i data-lucide="map-pin" class="w-6 h-6 text-blue-400"></i>
                            </div>
                            <div>
                                <h4 class="text-lg font-bold mb-1">Location</h4>
                                <p class="text-gray-400">Meiktila, Mandalay Region, Myanmar</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-white/10 rounded-full flex items-center justify-center flex-shrink-0">
                                <i data-lucide="mail" class="w-6 h-6 text-blue-400"></i>
                            </div>
                            <div>
                                <h4 class="text-lg font-bold mb-1">Email</h4>
                                <p class="text-gray-400">info@ucsmtla.edu.mm</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-white/5 p-8 rounded-3xl border border-white/10 backdrop-blur">
                    <h3 class="text-xl font-bold mb-6">Contact Us</h3>
                    <a href="contact.php" class="w-full inline-flex items-center justify-center px-6 py-4 rounded-xl text-lg font-semibold text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                        Go to Contact Page
                        <i data-lucide="arrow-right" class="w-5 h-5 ml-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

</main>

<!-- Mobile menu toggle -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('public-mobile-menu-btn');
    var menu = document.getElementById('public-mobile-menu');

    function closeMenu() {
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
        menu.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', closeMenu);
        });
        document.addEventListener('click', function (e) {
            if (menu.classList.contains('open') && !menu.contains(e.target) && e.target !== btn && !btn.contains(e.target)) {
                closeMenu();
            }
        });
    }
});
</script>

<?php include '../includes/footer.php'; ?>
