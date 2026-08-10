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

<!-- Custom Styles for Landing Page -->
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap');
    
    #public-main-content, #public-navbar, #mobile-menu, section {
        font-family: 'Poppins', sans-serif !important;
    }

    @keyframes fadeDown {
        0% { opacity: 0; transform: translateY(-20px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    @keyframes slideInFromLeft {
        0% { opacity: 0; transform: translateX(-30px); }
        100% { opacity: 1; transform: translateX(0); }
    }
    @keyframes slideInFromRight {
        0% { opacity: 0; transform: translateX(30px); }
        100% { opacity: 1; transform: translateX(0); }
    }
    
    .animate-fade-down { animation: fadeDown 0.3s ease-in-out both; }
    .animate-slide-in-left { animation: slideInFromLeft 0.3s ease-in-out both; }
    .animate-slide-in-right { animation: slideInFromRight 0.3s ease-in-out both; }
</style>

<!-- Main Document Container -->
<div class="min-h-screen w-full flex flex-col">

    <!-- HEADER -->
    <?php include '../includes/navbar.php'; ?>

    <!-- MAIN CONTENT -->
    <main class="w-full flex-1" id="public-main-content">

        <!-- 1. HERO & LATEST ANNOUNCEMENT SECTION -->
        <section id="hero" class="w-full relative bg-cover bg-center" style="background-image: url('<?php echo htmlspecialchars(base_url('/assets/images/ucsmtla7.jpg')); ?>');">
            <!-- Light overlay to maintain text visibility -->
            <div class="absolute inset-0 bg-white/85 backdrop-blur-[1px]"></div>
            
            <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8 py-12 sm:py-16 lg:py-20 grid grid-cols-1 lg:grid-cols-[1.1fr_0.9fr] gap-12 lg:gap-16 items-center relative z-10">
                
                <!-- Hero Content -->
                <div class="w-full">
                    <!-- Academic Session Badge -->
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-[#EFF6FF] border border-[#BFDBFE] mb-8">
                        <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                        <span class="text-sm font-semibold text-[#2563EB] tracking-wide">● <?php echo htmlspecialchars($active_ay_name); ?> Academic Session</span>
                    </div>

                    <!-- Main Heading -->
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold leading-tight tracking-tight mb-6 max-w-4xl">
                        <span class="text-slate-900 block">Your University,</span>
                        <span class="text-blue-600 block">Reimagined.</span>
                    </h1>

                    <!-- Description -->
                    <p class="text-lg text-slate-600 leading-relaxed max-w-3xl mb-8">
                        Real-time announcements, smart notifications, campus guides, and everything you need — all in one beautifully designed platform.
                    </p>

                    <!-- CTA Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 items-center w-full sm:w-auto mt-6 mb-12 md:mb-16">
                        <a href="#announcements" class="inline-flex items-center justify-center w-full sm:w-auto px-8 py-4 rounded-xl text-base font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition-all">
                            Explore Announcements
                            <i data-lucide="arrow-right" class="w-5 h-5 ml-2"></i>
                        </a>
                        <a href="../auth/login.php" class="inline-flex items-center justify-center w-full sm:w-auto px-8 py-4 rounded-xl text-base font-bold text-blue-600 bg-white border border-blue-200 hover:bg-blue-50 transition-all">
                            Student Login
                            <i data-lucide="arrow-right" class="w-5 h-5 ml-2"></i>
                        </a>
                    </div>
                </div>

                <!-- Latest Announcement -->
                <div class="w-full relative">
                    <?php if ($latest): ?>
                        <a href="announcement.php?id=<?php echo $latest['id']; ?>" class="block w-full bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-md transition-shadow duration-300">
                            <!-- Image Container -->
                            <div class="relative w-full aspect-[16/9] bg-slate-100 overflow-hidden flex items-center justify-center">
                                <?php if($latest['is_urgent']): ?>
                                    <div class="absolute top-5 left-5 bg-red-500 text-white text-sm font-semibold px-4 py-2 rounded-full shadow-sm uppercase tracking-wider z-10 flex items-center gap-1.5">
                                        <i data-lucide="alert-circle" class="w-4 h-4"></i> Urgent
                                    </div>
                                <?php else: ?>
                                    <div class="absolute top-5 left-5 bg-blue-600 text-white text-sm font-semibold px-4 py-2 rounded-full shadow-sm uppercase tracking-wider z-10">
                                        LATEST
                                    </div>
                                <?php endif; ?>
                                
                                <?php 
                                $image_exists = false;
                                if (!empty($latest_image)) {
                                    $local_path = dirname(__DIR__) . '/' . $latest_image;
                                    if (file_exists($local_path)) {
                                        $image_exists = true;
                                    }
                                }
                                ?>
                                
                                <?php if($image_exists): ?>
                                    <img src="<?php echo htmlspecialchars(base_url($latest_image)); ?>" alt="Announcement Image" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full bg-slate-50 flex items-center justify-center">
                                        <i data-lucide="image" class="w-16 h-16 text-slate-300"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <!-- Card content -->
                            <div class="p-6 md:p-8">
                                <div class="flex items-center gap-3 mb-4">
                                    <span class="text-xs font-bold tracking-wider uppercase text-blue-600">
                                        <?php echo htmlspecialchars($latest['category_name'] ?? 'General'); ?>
                                    </span>
                                    <span class="text-sm text-slate-500 font-medium">
                                        <?php echo date('M d, Y', strtotime($latest['publish_date'] ?? $latest['created_at'])); ?>
                                    </span>
                                </div>
                                <h3 class="text-2xl font-bold text-slate-900 mb-3 leading-snug">
                                    <?php echo htmlspecialchars($latest['title']); ?>
                                </h3>
                                <p class="text-base text-slate-600 mb-6 line-clamp-3 leading-relaxed">
                                    <?php echo htmlspecialchars(strip_tags($latest['content'])); ?>
                                </p>
                                <span class="inline-flex items-center font-bold text-blue-600 hover:text-blue-700 transition-colors">
                                    Read More
                                    <i data-lucide="arrow-right" class="w-4 h-4 ml-1.5"></i>
                                </span>
                            </div>
                        </a>
                    <?php else: ?>
                        <div class="w-full bg-white rounded-2xl border border-slate-200 p-12 text-center shadow-sm">
                            <i data-lucide="bell-off" class="w-12 h-12 text-slate-400 mx-auto mb-4"></i>
                            <h3 class="text-lg font-bold mb-2 text-slate-900">No latest announcement available</h3>
                            <p class="text-slate-500">Check back soon for new updates.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
            </div>
        </section>

        <!-- 2. ANNOUNCEMENTS SECTION -->
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

        <!-- 3. CAMPUS SECTION -->
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

        <!-- 4. ABOUT SECTION -->
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

        <!-- 5. CONTACT SECTION -->
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

    <!-- FOOTER -->
    <?php include '../includes/footer.php'; ?>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filter-form');
    const resetBtn = document.getElementById('reset-filters');
    const grid = document.getElementById('announcements-grid');
    const countDisplay = document.getElementById('filter-results-count');
    const loading = document.getElementById('filter-loading');

    // Make sure Lucide icons are processed if not already
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    function fetchAnnouncements() {
        if (!filterForm) return;
        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData);
        
        if (loading) {
            loading.classList.remove('hidden');
            loading.classList.add('flex');
        }
        
        fetch('ajax_fetch_announcements.php?' + params.toString())
            .then(response => response.json())
            .then(data => {
                if (grid) grid.innerHTML = data.html;
                if (countDisplay) countDisplay.innerHTML = `Showing ${data.count} announcements`;
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            })
            .catch(error => console.error('Error fetching announcements:', error))
            .finally(() => {
                if (loading) {
                    loading.classList.add('hidden');
                    loading.classList.remove('flex');
                }
            });
    }

    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            fetchAnnouncements();
        });

        const autoFetchInputs = filterForm.querySelectorAll('select, input[type="date"]');
        autoFetchInputs.forEach(input => {
            input.addEventListener('change', fetchAnnouncements);
        });

        if (resetBtn) {
            resetBtn.addEventListener('click', function() {
                filterForm.reset();
                fetchAnnouncements();
            });
        }
    }
});
</script>
