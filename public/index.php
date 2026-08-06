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
<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<!-- Sleek Hero Section -->
<div class="relative bg-white pt-24 pb-12 lg:pt-32 lg:pb-24 overflow-hidden">
    <div class="absolute inset-0 z-0">
        <div class="absolute inset-y-0 left-0 w-1/2 bg-gray-50 rounded-r-full opacity-50 transform -translate-x-1/3"></div>
        <div class="absolute top-0 right-0 w-1/3 h-1/3 bg-blue-50 rounded-full blur-3xl opacity-60 transform translate-x-1/2 -translate-y-1/2"></div>
    </div>
    
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-gray-900 tracking-tight mb-6">
            Welcome to <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600">UCSMTLA Academic Hub</span>
        </h1>
        <p class="text-lg md:text-xl text-gray-600 max-w-2xl mx-auto mb-10 leading-relaxed">
            Your centralized portal for university announcements, academic updates, and campus news. Stay informed and connected.
        </p>
    </div>
</div>

<!-- Announcements Section -->
<main class="bg-gray-50 py-12 relative z-20" id="announcements-section">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Filter Section -->
        <div class="bg-white/80 backdrop-blur-xl p-6 md:p-8 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100/50 mb-12">
            <form id="filter-form" class="space-y-6">
                <!-- Top Row: Search and Dropdowns -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <!-- Search -->
                    <div class="md:col-span-6 lg:col-span-6 relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i data-lucide="search" class="w-5 h-5 text-gray-400 group-focus-within:text-blue-600 transition-colors"></i>
                        </div>
                        <input type="text" id="filter-q" name="q" placeholder="Search announcements..." class="w-full pl-12 pr-4 py-3.5 bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 rounded-2xl focus:ring-2 focus:ring-inset focus:ring-blue-600 text-gray-900 transition-all shadow-sm hover:bg-gray-100/50 focus:bg-white outline-none">
                    </div>
                    
                    <!-- Category -->
                    <div class="md:col-span-3 lg:col-span-3 relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i data-lucide="folder" class="w-4 h-4 text-gray-400 group-focus-within:text-blue-600 transition-colors"></i>
                        </div>
                        <select id="filter-category" name="category" class="w-full pl-10 pr-10 py-3.5 bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 rounded-2xl focus:ring-2 focus:ring-inset focus:ring-blue-600 text-gray-900 transition-all shadow-sm hover:bg-gray-100/50 focus:bg-white cursor-pointer appearance-none outline-none">
                            <option value="">All Categories</option>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                            <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400"></i>
                        </div>
                    </div>

                    <!-- Type -->
                    <div class="md:col-span-3 lg:col-span-3 relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i data-lucide="tag" class="w-4 h-4 text-gray-400 group-focus-within:text-blue-600 transition-colors"></i>
                        </div>
                        <select id="filter-type" name="type" class="w-full pl-10 pr-10 py-3.5 bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 rounded-2xl focus:ring-2 focus:ring-inset focus:ring-blue-600 text-gray-900 transition-all shadow-sm hover:bg-gray-100/50 focus:bg-white cursor-pointer appearance-none outline-none">
                            <option value="">All Types</option>
                            <option value="normal">Normal</option>
                            <option value="urgent">Urgent</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                            <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Bottom Row: Dates and Actions -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                    <!-- Dates -->
                    <div class="md:col-span-8 lg:col-span-8 flex flex-col sm:flex-row gap-4 items-center">
                        <div class="w-full sm:w-1/2 relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i data-lucide="calendar" class="w-4 h-4 text-gray-400 group-focus-within:text-blue-600 transition-colors"></i>
                            </div>
                            <input type="date" id="filter-date-from" name="date_from" title="Date From" class="w-full pl-11 pr-4 py-3 bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 rounded-xl focus:ring-2 focus:ring-inset focus:ring-blue-600 text-gray-600 transition-all text-sm shadow-sm hover:bg-gray-100/50 focus:bg-white outline-none">
                        </div>
                        <span class="text-gray-400 font-medium hidden sm:block text-sm">to</span>
                        <div class="w-full sm:w-1/2 relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i data-lucide="calendar" class="w-4 h-4 text-gray-400 group-focus-within:text-blue-600 transition-colors"></i>
                            </div>
                            <input type="date" id="filter-date-to" name="date_to" title="Date To" class="w-full pl-11 pr-4 py-3 bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 rounded-xl focus:ring-2 focus:ring-inset focus:ring-blue-600 text-gray-600 transition-all text-sm shadow-sm hover:bg-gray-100/50 focus:bg-white outline-none">
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="md:col-span-4 lg:col-span-4 flex gap-3">
                        <button type="button" id="reset-filters" class="flex-1 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 font-bold py-3 px-4 rounded-xl transition-all shadow-sm hover:shadow flex items-center justify-center gap-2 group outline-none">
                            <i data-lucide="rotate-ccw" class="w-4 h-4 text-gray-400 group-hover:text-gray-600 transition-colors"></i> Reset
                        </button>
                        <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-xl transition-all shadow-md hover:shadow-lg hover:shadow-blue-600/20 flex items-center justify-center gap-2 transform hover:-translate-y-0.5 outline-none">
                            <i data-lucide="search" class="w-4 h-4"></i> Search
                        </button>
                    </div>
                </div>
            </form>
            
            <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between">
                <div class="text-sm font-semibold text-gray-600 bg-gray-50 px-4 py-2 rounded-lg border border-gray-200 flex items-center gap-2">
                    <i data-lucide="layers" class="w-4 h-4 text-gray-400"></i>
                    <span id="filter-results-count">Showing <?php echo count($grid_announcements) + ($latest ? 1 : 0); ?> announcements</span>
                </div>
                <div id="filter-loading" class="hidden items-center gap-2 text-blue-600 text-sm font-semibold bg-blue-50 px-4 py-2 rounded-lg">
                    <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> Filtering...
                </div>
            </div>
        </div>

        <!-- Announcement Grid -->
        <div id="announcements-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 pb-12">
            <!-- Latest Announcement (Large Featured Card) -->
            <?php if ($latest): ?>
                <a href="announcement.php?id=<?php echo $latest['id']; ?>" class="md:col-span-2 lg:col-span-3 group bg-white rounded-3xl shadow-[0_2px_20px_rgb(0,0,0,0.04)] hover:shadow-[0_8px_30px_rgb(0,0,0,0.12)] border overflow-hidden flex flex-col md:flex-row transform transition-all duration-300 hover:-translate-y-1 <?php echo $latest['is_urgent'] ? 'border-red-200 ring-2 ring-red-500/20' : 'border-gray-100'; ?>">
                    <!-- Thumbnail image -->
                    <div class="md:w-2/5 h-64 md:h-auto bg-gray-50 relative overflow-hidden flex items-center justify-center border-b md:border-b-0 md:border-r border-gray-100">
                        <?php if($latest['is_urgent']): ?>
                            <div class="absolute top-4 left-4 bg-red-500 text-white text-[10px] font-bold px-3 py-1.5 rounded-full shadow-lg uppercase tracking-wider z-10 animate-pulse flex items-center gap-1.5">
                                <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i> Urgent
                            </div>
                        <?php endif; ?>
                        
                        <?php if(!empty($latest_image)): ?>
                            <img src="<?php echo htmlspecialchars(base_url($latest_image)); ?>" alt="Featured Thumbnail" class="w-full h-full object-cover transform transition-transform duration-700 group-hover:scale-105">
                        <?php else: ?>
                            <div class="absolute inset-0 bg-gradient-to-br from-indigo-50 to-blue-50/30"></div>
                            <i data-lucide="star" class="w-16 h-16 text-indigo-200 z-0 transform transition-transform duration-700 group-hover:scale-110"></i>
                        <?php endif; ?>
                    </div>

                    <!-- Card content -->
                    <div class="p-8 md:p-10 flex-1 flex flex-col justify-center">
                        <div class="flex items-center gap-4 mb-4">
                            <span class="text-[10px] font-bold tracking-wider uppercase text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-md border border-indigo-100/50">
                                <?php echo htmlspecialchars($latest['category_name'] ?? 'General'); ?>
                            </span>
                            <span class="text-xs text-gray-500 font-medium flex items-center gap-1.5">
                                <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                                <?php echo date('M d, Y', strtotime($latest['publish_date'] ?? $latest['created_at'])); ?>
                            </span>
                        </div>
                        <h3 class="text-2xl md:text-3xl font-bold text-gray-900 mb-4 group-hover:text-blue-600 transition-colors line-clamp-2 leading-tight">
                            <?php echo htmlspecialchars($latest['title']); ?>
                        </h3>
                        <p class="text-gray-500 text-base mb-8 line-clamp-3 leading-relaxed">
                            <?php echo htmlspecialchars(strip_tags($latest['content'])); ?>
                        </p>
                        
                        <div class="mt-auto">
                            <span class="inline-flex items-center text-sm font-bold text-blue-600 group-hover:text-blue-800 transition-colors bg-blue-50/50 px-4 py-2 rounded-xl border border-blue-100/50">
                                Read Full Announcement
                                <i data-lucide="arrow-right" class="w-4 h-4 ml-1.5 transform group-hover:translate-x-1 transition-transform"></i>
                            </span>
                        </div>
                    </div>
                </a>
            <?php endif; ?>

            <!-- Grid Content -->
            <?php if (!empty($grid_announcements)): ?>
                <?php foreach($grid_announcements as $a): ?>
                    <a href="announcement.php?id=<?php echo $a['id']; ?>" class="group bg-white rounded-3xl shadow-[0_2px_20px_rgb(0,0,0,0.04)] hover:shadow-[0_8px_30px_rgb(0,0,0,0.12)] border overflow-hidden flex flex-col transform transition-all duration-300 hover:-translate-y-2 <?php echo $a['is_urgent'] ? 'border-red-200 ring-2 ring-red-500/20' : 'border-gray-100'; ?>">
                        <!-- Thumbnail image -->
                        <div class="h-48 bg-gray-50 relative overflow-hidden flex items-center justify-center border-b border-gray-100">
                            <?php if($a['is_urgent']): ?>
                                <div class="absolute top-4 left-4 bg-red-500 text-white text-[10px] font-bold px-3 py-1.5 rounded-full shadow-lg uppercase tracking-wider z-10 animate-pulse flex items-center gap-1.5">
                                    <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i> Urgent
                                </div>
                            <?php endif; ?>
                            
                            <?php if(!empty($a['image_path'])): ?>
                                <img src="<?php echo htmlspecialchars(base_url($a['image_path'])); ?>" alt="Thumbnail" class="w-full h-full object-cover transform transition-transform duration-700 group-hover:scale-105">
                            <?php else: ?>
                                <div class="absolute inset-0 bg-gradient-to-br from-gray-50 to-gray-100"></div>
                                <i data-lucide="image" class="w-12 h-12 text-gray-300 z-0 transform transition-transform duration-700 group-hover:scale-110"></i>
                            <?php endif; ?>
                        </div>

                        <!-- Card content -->
                        <div class="p-6 flex-1 flex flex-col">
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-[10px] font-bold tracking-wider uppercase text-blue-700 bg-blue-50 px-2.5 py-1 rounded-md border border-blue-100/50">
                                    <?php echo htmlspecialchars($a['category_name'] ?? 'General'); ?>
                                </span>
                                <span class="text-[11px] text-gray-500 font-medium flex items-center gap-1.5">
                                    <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                                    <?php echo date('M d, Y', strtotime($a['publish_date'] ?? $a['created_at'])); ?>
                                </span>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 mb-3 group-hover:text-blue-600 transition-colors line-clamp-2 leading-snug">
                                <?php echo htmlspecialchars($a['title']); ?>
                            </h3>
                            <p class="text-gray-500 text-sm mb-6 line-clamp-2 flex-1 leading-relaxed">
                                <?php echo htmlspecialchars(strip_tags($a['content'])); ?>
                            </p>
                            
                            <div class="mt-auto pt-4 border-t border-gray-100">
                                <span class="inline-flex items-center text-sm font-bold text-blue-600 group-hover:text-blue-800 transition-colors">
                                    Read More
                                    <i data-lucide="arrow-right" class="w-4 h-4 ml-1.5 transform group-hover:translate-x-1 transition-transform"></i>
                                </span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php elseif (!$latest): ?>
                <div class="col-span-1 md:col-span-2 lg:col-span-3 bg-white p-12 rounded-3xl shadow-sm border border-gray-100 text-center">
                    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i data-lucide="file-question" class="w-10 h-10 text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">No announcements found</h3>
                    <p class="text-gray-500">There are currently no announcements to display.</p>
                </div>
            <?php endif; ?>
        </div>
        
    </div>
</main>

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
        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData);
        
        loading.classList.remove('hidden');
        loading.classList.add('flex');
        
        fetch('ajax_fetch_announcements.php?' + params.toString())
            .then(response => response.json())
            .then(data => {
                grid.innerHTML = data.html;
                countDisplay.innerHTML = `Showing ${data.count} announcements`;
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            })
            .catch(error => console.error('Error fetching announcements:', error))
            .finally(() => {
                loading.classList.add('hidden');
                loading.classList.remove('flex');
            });
    }

    filterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        fetchAnnouncements();
    });

    const autoFetchInputs = filterForm.querySelectorAll('select, input[type="date"]');
    autoFetchInputs.forEach(input => {
        input.addEventListener('change', fetchAnnouncements);
    });

    resetBtn.addEventListener('click', function() {
        filterForm.reset();
        fetchAnnouncements();
    });
});
</script>

<?php include '../includes/footer.php'; ?>
