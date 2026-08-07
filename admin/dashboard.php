<?php
require_once '../config/session.php';
require_admin();
require_once '../config/db.php';
require_once '../config/functions.php';

// Fetch Current Academic Year based on session
$active_ay_id = $_SESSION['current_academic_year_id'] ?? 0;
if ($active_ay_id) {
    $stmt = $pdo->prepare("SELECT id, year_name as name FROM academic_years WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $active_ay_id]);
} else {
    $stmt = $pdo->query("SELECT id, year_name as name FROM academic_years WHERE status = 'Active' LIMIT 1");
}
$active_ay = $stmt->fetch();
$current_academic_year = $active_ay ? $active_ay['name'] : 'Not Set';
$active_ay_id = $active_ay ? (int)$active_ay['id'] : 0;

// Base condition for active academic year filtering
$ay_condition = $active_ay_id > 0 ? "AND academic_year_id = $active_ay_id" : "";

// Dashboard Statistics
$stats = [
    'total' => 0,
    'published' => 0,
    'draft' => 0,
    'archived' => 0
];

// Base conditions for active academic year filtering
$ay_condition_a = $active_ay_id > 0 ? "AND a.academic_year_id = $active_ay_id" : "";
$ay_condition_raw = $active_ay_id > 0 ? "AND academic_year_id = $active_ay_id" : "";

// Total Announcements
$stats['total'] = $pdo->query("SELECT COUNT(*) FROM announcements WHERE 1=1 $ay_condition_raw")->fetchColumn();

// Published (ay status is not archived AND publish date <= NOW)
$stats['published'] = $pdo->query("SELECT COUNT(*) FROM announcements a LEFT JOIN academic_years ay ON a.academic_year_id = ay.id WHERE (ay.status != 'archived' OR ay.status IS NULL) AND (a.publish_date <= NOW() OR a.publish_date IS NULL) $ay_condition_a")->fetchColumn();

// Draft (ay status is not archived AND publish date > NOW)
$stats['draft'] = $pdo->query("SELECT COUNT(*) FROM announcements a LEFT JOIN academic_years ay ON a.academic_year_id = ay.id WHERE (ay.status != 'archived' OR ay.status IS NULL) AND a.publish_date > NOW() $ay_condition_a")->fetchColumn();

// Archived (ay status is archived)
$stats['archived'] = $pdo->query("SELECT COUNT(*) FROM announcements a JOIN academic_years ay ON a.academic_year_id = ay.id WHERE ay.status = 'archived' $ay_condition_a")->fetchColumn();



// Fetch Latest Announcements joining Categories and counting comments
$stmt = $pdo->query("SELECT a.id, a.title, a.created_at, a.status, a.is_urgent, a.is_featured, a.publish_date, c.category_name,
                     (SELECT COUNT(*) FROM comments WHERE announcement_id = a.id) as comment_count
                     FROM announcements a 
                     LEFT JOIN categories c ON a.category_id = c.id 
                     WHERE 1=1 " . str_replace("academic_year_id", "a.academic_year_id", $ay_condition) . " 
                     ORDER BY a.created_at DESC LIMIT 5");
$latest_announcements = $stmt->fetchAll();

// Fetch Recent Comments
$stmt = $pdo->query("SELECT c.comment as content, c.created_at, u.username as user_name, a.title as announcement_title, a.id as announcement_id 
                     FROM comments c 
                     JOIN users u ON c.user_id = u.id 
                     JOIN announcements a ON c.announcement_id = a.id 
                     ORDER BY c.created_at DESC LIMIT 5");
$recent_comments = $stmt->fetchAll();

// Fetch Recent Activities (last 2 days)
$stmt = $pdo->query("SELECT al.activity as action, al.description, al.created_at, u.username as user_name 
                     FROM activity_logs al 
                     LEFT JOIN users u ON al.user_id = u.id 
                     WHERE al.created_at >= DATE_SUB(CURDATE(), INTERVAL 2 DAY) 
                     ORDER BY al.created_at DESC LIMIT 5");
$recent_activities = $stmt->fetchAll();

// Fetch Data for Monthly Publishing Chart (Current Year)
$chart_data = [];
$months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
$stmt = $pdo->query("SELECT MONTH(COALESCE(publish_date, created_at)) as month, COUNT(*) as count FROM announcements WHERE YEAR(COALESCE(publish_date, created_at)) = YEAR(CURDATE()) $ay_condition GROUP BY MONTH(COALESCE(publish_date, created_at))");
$monthly_counts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

foreach (range(1, 12) as $m) {
    $chart_data[] = $monthly_counts[$m] ?? 0;
}
$chart_data_json = json_encode($chart_data);
$months_json = json_encode($months);

// Additional Stats for Redesign
try { $stats['students'] = $pdo->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn(); } catch(PDOException $e) { $stats['students'] = 0; }
try { $stats['courses'] = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn(); } catch(PDOException $e) { $stats['courses'] = 24; }
try { $stats['timetables'] = $pdo->query("SELECT COUNT(*) FROM timetables WHERE 1=1 $ay_condition")->fetchColumn(); } catch(PDOException $e) { $stats['timetables'] = 0; }
$stats['unread_notifications'] = 5;

// Fetch Category stats dynamically
$category_counts = $pdo->query("SELECT c.category_name, COUNT(a.id) as count 
                                FROM categories c 
                                JOIN announcements a ON a.category_id = c.id 
                                WHERE 1=1 $ay_condition_raw 
                                GROUP BY c.id")->fetchAll();
$category_labels = [];
$category_data = [];
foreach ($category_counts as $row) {
    $category_labels[] = $row['category_name'];
    $category_data[] = (int)$row['count'];
}
if (empty($category_labels)) {
    $category_labels = ['General', 'Exam', 'Event', 'Urgent'];
    $category_data = [0, 0, 0, 0];
}

$status_labels = ['Published', 'Draft', 'Archived'];
$status_data = [$stats['published'], $stats['draft'], $stats['archived']];
?>
<?php include '../includes/header.php'; ?>
<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<?php include '../includes/sidebar.php'; ?>
<?php include '../includes/navbar.php'; ?>

<div class="max-w-[1400px] mx-auto space-y-6">

    <!-- Welcome Card -->
    <?php
    $dt = new DateTime("now", new DateTimeZone('Asia/Yangon'));
    $hour = (int)$dt->format('H');
    $greeting = 'Good Morning';
    if ($hour >= 12 && $hour < 17) {
        $greeting = 'Good Afternoon';
    } elseif ($hour >= 17 || $hour < 4) {
        $greeting = 'Good Evening';
    }
    ?>
    <div class="relative overflow-hidden bg-gradient-to-r from-blue-600 via-blue-500 to-indigo-700 text-white rounded-2xl p-5 md:p-6 shadow-lg hover:shadow-xl transition-all duration-300">
        <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
        <div class="absolute right-20 bottom-[-50px] w-64 h-64 bg-white/5 rounded-full blur-2xl pointer-events-none"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold tracking-tight"><?php echo $greeting; ?>, Admin 👋</h2>
                <p class="text-blue-100 mt-1.5 font-medium text-base">Academic Hub &ndash; UCSMTLA</p>
                <p class="text-blue-200 mt-0.5 text-xs">Manage university announcements, students and timetables efficiently.</p>
            </div>
            <div class="text-left md:text-right flex-shrink-0 bg-white/10 backdrop-blur-md rounded-xl p-3 border border-white/20">
                <p class="text-[10px] font-semibold text-blue-200 uppercase tracking-wider mb-0.5">Today's Date</p>
                <p class="text-lg font-bold text-white"><?php echo date('l, F j, Y'); ?></p>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <a href="create_announcement.php" class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 hover:shadow-md hover:border-blue-200 transition-all duration-300 group flex items-center space-x-4">
            <div class="w-12 h-12 bg-blue-50 text-blue-500 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                <i data-lucide="megaphone" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="block font-bold text-gray-800 text-sm group-hover:text-blue-600 transition-colors">Create Announcement</span>
                <span class="text-xs text-gray-400">Post new news</span>
            </div>
        </a>
        <a href="upload_timetable.php" class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 hover:shadow-md hover:border-blue-200 transition-all duration-300 group flex items-center space-x-4">
            <div class="w-12 h-12 bg-purple-50 text-purple-500 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                <i data-lucide="calendar-range" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="block font-bold text-gray-800 text-sm group-hover:text-purple-600 transition-colors">Upload Timetable</span>
                <span class="text-xs text-gray-400">Upload PDF classes</span>
            </div>
        </a>
        <a href="categories.php" class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 hover:shadow-md hover:border-blue-200 transition-all duration-300 group flex items-center space-x-4">
            <div class="w-12 h-12 bg-orange-50 text-orange-500 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                <i data-lucide="tag" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="block font-bold text-gray-800 text-sm group-hover:text-orange-600 transition-colors">Manage Categories</span>
                <span class="text-xs text-gray-400">Organize topics</span>
            </div>
        </a>
        <a href="students.php" class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 hover:shadow-md hover:border-blue-200 transition-all duration-300 group flex items-center space-x-4">
            <div class="w-12 h-12 bg-green-50 text-green-500 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                <i data-lucide="users" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="block font-bold text-gray-800 text-sm group-hover:text-green-600 transition-colors">Student Management</span>
                <span class="text-xs text-gray-400">Rosters & groups</span>
            </div>
        </a>
    </div>

    <!-- Statistics Cards Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Total Announcements -->
        <div class="bg-white rounded-xl shadow-sm border-t-4 border-blue-500 border border-gray-100 p-5 hover:-translate-y-1 hover:shadow-md transition-all duration-300">
            <div class="flex justify-between items-start mb-3">
                <span class="text-xs font-semibold text-blue-600 bg-blue-50 px-2 py-1 rounded-md">↑ 12% this month</span>
                <i data-lucide="megaphone" class="w-5 h-5 text-blue-500"></i>
            </div>
            <h3 class="text-2xl font-black text-gray-800 mb-0.5"><?php echo $stats['total']; ?></h3>
            <p class="text-xs font-medium text-gray-400">Total Announcements</p>
        </div>

        <!-- Published Announcements -->
        <div class="bg-white rounded-xl shadow-sm border-t-4 border-green-500 border border-gray-100 p-5 hover:-translate-y-1 hover:shadow-md transition-all duration-300">
            <div class="flex justify-between items-start mb-3">
                <span class="text-xs font-semibold text-green-600 bg-green-50 px-2 py-1 rounded-md">↑ 8% this month</span>
                <i data-lucide="check-circle" class="w-5 h-5 text-green-500"></i>
            </div>
            <h3 class="text-2xl font-black text-gray-800 mb-0.5"><?php echo $stats['published']; ?></h3>
            <p class="text-xs font-medium text-gray-400">Published News</p>
        </div>

    <!-- Draft Announcements -->
        <div class="bg-white rounded-xl shadow-sm border-t-4 border-yellow-500 border border-gray-100 p-5 hover:-translate-y-1 hover:shadow-md transition-all duration-300">
            <div class="flex justify-between items-start mb-3">
                <span class="text-xs font-semibold text-yellow-600 bg-yellow-50 px-2 py-1 rounded-md">↓ 3% this week</span>
                <i data-lucide="file-text" class="w-5 h-5 text-yellow-500"></i>
            </div>
            <h3 class="text-2xl font-black text-gray-800 mb-0.5"><?php echo $stats['draft']; ?></h3>
            <p class="text-xs font-medium text-gray-400">Draft Status</p>
        </div>

        <!-- Archived Announcements -->
        <div class="bg-white rounded-xl shadow-sm border-t-4 border-gray-500 border border-gray-100 p-5 hover:-translate-y-1 hover:shadow-md transition-all duration-300">
            <div class="flex justify-between items-start mb-3">
                <span class="text-xs font-semibold text-gray-600 bg-gray-50 px-2 py-1 rounded-md">Archived</span>
                <i data-lucide="archive" class="w-5 h-5 text-gray-500"></i>
            </div>
            <h3 class="text-2xl font-black text-gray-800 mb-0.5"><?php echo $stats['archived']; ?></h3>
            <p class="text-xs font-medium text-gray-400">Archived Announcements</p>
        </div>

    </div>

    <!-- Main Content Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- LEFT COLUMN -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Publishing Trend Line Chart -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-lg font-bold text-gray-800 flex items-center">
                        <i data-lucide="trending-up" class="w-5 h-5 text-blue-500 mr-2"></i>
                        Monthly Publishing Trend (<?php echo date('Y'); ?>)
                    </h2>
                </div>
                <div class="relative h-[300px] w-full">
                    <canvas id="publishingChart"></canvas>
                </div>
            </div>
            
            <!-- Recent Announcements -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-lg font-bold text-gray-800 flex items-center">
                        <i data-lucide="announcement" class="w-5 h-5 text-orange-500 mr-2"></i>
                        Recent Announcements
                    </h2>
                    <a href="announcements.php" class="text-sm font-semibold text-blue-600 hover:text-blue-800 transition-colors">See all</a>
                </div>
                
                <div class="space-y-4">
                    <?php if (empty($latest_announcements)): ?>
                        <p class="text-gray-500 text-sm text-center py-6">No recent announcements found.</p>
                    <?php else: ?>
                        <?php foreach ($latest_announcements as $ann): ?>
                            <?php 
                            $status_color = 'bg-gray-100 text-gray-700';
                            if ($ann['status'] === 'published') {
                                $status_color = 'bg-green-100 text-green-700';
                            } elseif ($ann['status'] === 'draft') {
                                $status_color = 'bg-yellow-100 text-yellow-700';
                            }
                            ?>
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 rounded-xl border border-gray-50 hover:border-blue-100 bg-gray-50/30 hover:bg-blue-50/10 transition-all duration-300 gap-4">
                                <div class="space-y-1">
                                    <div class="flex items-center space-x-2 flex-wrap gap-y-1">
                                        <span class="text-xs font-bold bg-purple-50 text-purple-600 px-2 py-0.5 rounded-md uppercase tracking-wider"><?php echo htmlspecialchars($ann['category_name'] ?? 'General'); ?></span>
                                        <span class="text-xs font-bold px-2 py-0.5 rounded-md capitalize <?php echo $status_color; ?>"><?php echo htmlspecialchars($ann['status']); ?></span>
                                        <?php if ($ann['is_urgent']): ?>
                                            <span class="text-xs font-bold bg-red-100 text-red-600 px-2 py-0.5 rounded-md">Urgent</span>
                                        <?php endif; ?>
                                    </div>
                                    <h3 class="text-sm font-bold text-gray-800 hover:text-blue-600 transition-colors line-clamp-1">
                                        <?php echo htmlspecialchars($ann['title']); ?>
                                    </h3>
                                    <p class="text-xs text-gray-400 flex items-center font-medium">
                                        <i data-lucide="calendar" class="w-3.5 h-3.5 mr-1 text-gray-400"></i>
                                        Published: <?php echo $ann['publish_date'] ? date('M d, Y', strtotime($ann['publish_date'])) : date('M d, Y', strtotime($ann['created_at'])); ?>
                                    </p>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <!-- Comment Badge -->
                                    <?php if ((int)$ann['comment_count'] > 0): ?>
                                        <span class="flex items-center text-xs font-bold text-blue-600 bg-blue-50 border border-blue-100 px-2.5 py-1 rounded-full" title="Comments">
                                            <i data-lucide="message-square" class="w-3.5 h-3.5 mr-1"></i>
                                            <?php echo $ann['comment_count']; ?>
                                        </span>
                                    <?php endif; ?>
                                    <!-- Actions -->
                                    <a href="view_announcement.php?id=<?php echo $ann['id']; ?>" class="text-xs font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 px-3 py-1.5 rounded-lg transition-colors flex items-center">
                                        <i data-lucide="eye" class="w-3.5 h-3.5 mr-1"></i> View
                                    </a>
                                    <a href="edit_announcement.php?id=<?php echo $ann['id']; ?>" class="text-xs font-bold text-white bg-blue-500 hover:bg-blue-600 px-3 py-1.5 rounded-lg transition-colors flex items-center shadow-sm">
                                        <i data-lucide="edit" class="w-3.5 h-3.5 mr-1"></i> Edit
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- RIGHT COLUMN -->
        <div class="space-y-6">
            <!-- Doughnut Category Distribution -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-lg font-bold text-gray-800 flex items-center">
                        <i data-lucide="pie-chart" class="w-5 h-5 text-purple-500 mr-2"></i>
                        Categories Distribution
                    </h2>
                </div>
                <div class="relative h-[250px] w-full flex justify-center">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
            
            <!-- Recent Comments -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
                <div class="p-5 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                    <h2 class="text-lg font-bold text-gray-800 flex items-center">
                        <i data-lucide="message-square" class="w-5 h-5 text-emerald-500 mr-2"></i>
                        Recent Comments
                    </h2>
                    <a href="comments.php" class="text-xs font-bold text-blue-600 hover:text-blue-800 transition-colors">See all</a>
                </div>
                <div class="divide-y divide-gray-100">
                    <?php if (empty($recent_comments)): ?>
                        <div class="p-6 text-center text-gray-500 text-sm">No comments submitted yet.</div>
                    <?php else: ?>
                        <?php foreach ($recent_comments as $c): ?>
                            <?php $initials = strtoupper(substr($c['user_name'], 0, 2)); ?>
                            <div class="p-4 hover:bg-gray-50/50 transition-colors">
                                <div class="flex items-start space-x-3 mb-2">
                                    <div class="w-8 h-8 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs">
                                        <?php echo $initials; ?>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex justify-between items-baseline">
                                            <span class="text-sm font-bold text-gray-800 block truncate"><?php echo htmlspecialchars($c['user_name']); ?></span>
                                            <span class="text-[10px] text-gray-400 font-semibold flex-shrink-0"><?php echo date('g:i A', strtotime($c['created_at'])); ?></span>
                                        </div>
                                        <p class="text-[11px] text-gray-500 font-medium truncate mt-0.5">On: <?php echo htmlspecialchars($c['announcement_title']); ?></p>
                                    </div>
                                </div>
                                <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                                    <p class="text-xs text-gray-600 italic">"<?php echo htmlspecialchars(strlen($c['content']) > 80 ? substr($c['content'], 0, 80).'...' : $c['content']); ?>"</p>
                                </div>
                                <div class="mt-2 flex justify-end">
                                    <a href="view_announcement.php?id=<?php echo $c['announcement_id']; ?>" class="text-[11px] font-bold text-blue-600 hover:text-blue-800 flex items-center">
                                        View Discussion <i data-lucide="arrow-right" class="w-3 h-3 ml-1"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Recent Activity Timeline -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
                <div class="p-5 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                    <h2 class="text-lg font-bold text-gray-800 flex items-center">
                        <i data-lucide="activity" class="w-5 h-5 text-blue-500 mr-2"></i>
                        Recent Activity
                    </h2>
                    <a href="activity_logs.php" class="text-xs font-bold text-blue-600 hover:text-blue-800 transition-colors font-semibold">View All</a>
                </div>
                <div class="p-6">
                    <div class="relative border-l border-gray-200 ml-3 space-y-6">
                        <?php if (empty($recent_activities)): ?>
                            <div class="text-gray-500 text-sm pl-4">No recent activities.</div>
                        <?php else: ?>
                            <?php foreach ($recent_activities as $idx => $act): ?>
                                <?php 
                                    $isLogin = (strtolower($act['action']) == 'login');
                                    $icon = $isLogin ? 'log-in' : 'file-text';
                                    $color = $isLogin ? 'text-emerald-500 bg-emerald-50 border-emerald-200' : 'text-blue-500 bg-blue-50 border-blue-200';
                                ?>
                                <div class="relative pl-6">
                                    <span class="absolute -left-[15px] top-1 w-7 h-7 rounded-full flex items-center justify-center bg-white border-2 <?php echo $color; ?> shadow-sm">
                                        <i data-lucide="<?php echo $icon; ?>" class="w-3.5 h-3.5"></i>
                                    </span>
                                    <div>
                                        <p class="text-xs font-bold text-gray-800"><?php echo htmlspecialchars($act['user_name'] ?? 'System'); ?></p>
                                        <p class="text-xs text-gray-600 mt-0.5"><?php echo htmlspecialchars($act['description'] ?? $act['action']); ?></p>
                                        <p class="text-[10px] font-semibold text-gray-400 mt-1 flex items-center"><i data-lucide="clock" class="w-3 h-3 mr-1 text-gray-300"></i> <?php echo date('M d, g:i A', strtotime($act['created_at'])); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>

<script>
// Chart.js implementation with modern typography and gradients
const chartConfig = {
    fontFamily: "'Inter', sans-serif",
    color: '#6B7280'
};

Chart.defaults.font.family = chartConfig.fontFamily;
Chart.defaults.color = chartConfig.color;

// Monthly Line Chart
const ctx = document.getElementById('publishingChart').getContext('2d');
const gradient = ctx.createLinearGradient(0, 0, 0, 300);
gradient.addColorStop(0, 'rgba(37, 99, 235, 0.25)');
gradient.addColorStop(1, 'rgba(37, 99, 235, 0.00)');

const publishingChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo $months_json; ?>,
        datasets: [{
            label: 'Announcements Published',
            data: <?php echo $chart_data_json; ?>,
            backgroundColor: gradient,
            borderColor: '#2563EB',
            borderWidth: 3,
            fill: true,
            tension: 0.35,
            pointBackgroundColor: '#2563EB',
            pointBorderColor: '#FFFFFF',
            pointBorderWidth: 2,
            pointRadius: 5,
            pointHoverRadius: 7
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: '#F1F5F9',
                    drawBorder: false
                },
                ticks: {
                    stepSize: 1,
                    font: { size: 12, weight: '500' }
                }
            },
            x: {
                grid: {
                    display: false
                },
                ticks: {
                    font: { size: 12, weight: '500' }
                }
            }
        },
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                backgroundColor: 'rgba(15, 23, 42, 0.9)',
                padding: 12,
                titleFont: { size: 13, weight: 'bold' },
                bodyFont: { size: 13 },
                cornerRadius: 8,
                displayColors: false
            }
        }
    }
});

// Category distribution Doughnut Chart
const ctxStatus = document.getElementById('statusChart').getContext('2d');
const statusChart = new Chart(ctxStatus, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($category_labels); ?>,
        datasets: [{
            data: <?php echo json_encode($category_data); ?>,
            backgroundColor: [
                '#2563EB', // Blue
                '#F59E0B', // Yellow
                '#8B5CF6', // Purple
                '#10B981', // Green
                '#06B6D4', // Cyan
                '#EF4444'  // Red
            ],
            borderWidth: 2,
            borderColor: '#FFFFFF',
            hoverOffset: 6
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '72%',
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 15,
                    usePointStyle: true,
                    pointStyle: 'circle',
                    font: { size: 12, weight: '500' }
                }
            },
            tooltip: {
                backgroundColor: 'rgba(15, 23, 42, 0.9)',
                padding: 12,
                cornerRadius: 8
            }
        }
    }
});
</script>

<?php include '../includes/footer.php'; ?>
