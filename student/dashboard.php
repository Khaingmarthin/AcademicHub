<?php
require_once '../config/session.php';
// We allow access if it's a student (or an admin testing the student view)
if (!is_logged_in() || ($_SESSION['user_role'] !== 'student' && $_SESSION['user_role'] !== 'admin')) {
    header("Location: /auth/login.php");
    exit();
}
require_once '../config/db.php';
require_once '../config/functions.php';

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Fetch Active Academic Year
$stmt = $pdo->query("SELECT id FROM academic_years WHERE status = 'active' LIMIT 1");
$active_ay_id = $stmt->fetchColumn() ?: 0;
$ay_condition = $active_ay_id > 0 ? "AND academic_year_id = $active_ay_id" : "";
$ay_condition_t = $active_ay_id > 0 ? "AND t.academic_year_id = $active_ay_id" : "";

// Fetch Quick Stats
$stats = [
    'announcements' => 0,
    'bookmarks' => 0,
    'comments' => 0
];
$stmt = $pdo->query("SELECT COUNT(*) FROM announcements WHERE status = 'published' $ay_condition");
$stats['announcements'] = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM bookmarks WHERE user_id = :uid");
$stmt->execute(['uid' => $user_id]);
$stats['bookmarks'] = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM comments WHERE user_id = :uid");
$stmt->execute(['uid' => $user_id]);
$stats['comments'] = $stmt->fetchColumn();

// Fetch Latest Announcements
$stmt = $pdo->query("SELECT id, title, created_at FROM announcements WHERE status = 'published' $ay_condition ORDER BY created_at DESC LIMIT 5");
$latest_announcements = $stmt->fetchAll();

// Fetch Bookmarks
$stmt = $pdo->prepare("SELECT a.id, a.title FROM bookmarks b JOIN announcements a ON b.announcement_id = a.id WHERE b.user_id = :uid ORDER BY b.created_at DESC LIMIT 5");
$stmt->execute(['uid' => $user_id]);
$bookmarks = $stmt->fetchAll();

// Fetch Recent Comments
$stmt = $pdo->prepare("SELECT c.comment as content, c.created_at, a.title, a.id as announcement_id FROM comments c JOIN announcements a ON c.announcement_id = a.id WHERE c.user_id = :uid ORDER BY c.created_at DESC LIMIT 3");
$stmt->execute(['uid' => $user_id]);
$recent_comments = $stmt->fetchAll();

// Fetch Notifications
$stmt = $pdo->prepare("SELECT id, title, message, created_at, is_read FROM notifications WHERE user_id = :uid ORDER BY created_at DESC LIMIT 5");
$stmt->execute(['uid' => $user_id]);
$notifications = $stmt->fetchAll();

// Removed granular timetable query as we are now using PDFs

?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>
<?php include '../includes/navbar.php'; ?>

<div class="max-w-7xl mx-auto">
    <!-- Greeting & Date -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Welcome back, <?php echo htmlspecialchars(explode(' ', $user_name)[0]); ?>! 👋</h1>
            <p class="mt-1 text-sm text-gray-500">Here is what's happening at UCSMTLA today.</p>
        </div>
        <div class="mt-4 md:mt-0 bg-white px-4 py-2 rounded-lg shadow-sm border border-gray-100 flex items-center">
            <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            <span class="font-medium text-gray-700"><?php echo date('l, F j, Y'); ?></span>
        </div>
    </div>

    <!-- Quick Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white transform transition duration-300 hover:scale-105">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium uppercase tracking-wider">Total Announcements</p>
                    <p class="text-3xl font-bold mt-1"><?php echo $stats['announcements']; ?></p>
                </div>
                <div class="bg-blue-400 bg-opacity-30 rounded-full p-3">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white transform transition duration-300 hover:scale-105">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm font-medium uppercase tracking-wider">My Bookmarks</p>
                    <p class="text-3xl font-bold mt-1"><?php echo $stats['bookmarks']; ?></p>
                </div>
                <div class="bg-purple-400 bg-opacity-30 rounded-full p-3">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path></svg>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl shadow-lg p-6 text-white transform transition duration-300 hover:scale-105">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-emerald-100 text-sm font-medium uppercase tracking-wider">My Comments</p>
                    <p class="text-3xl font-bold mt-1"><?php echo $stats['comments']; ?></p>
                </div>
                <div class="bg-emerald-400 bg-opacity-30 rounded-full p-3">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Grid Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left Column -->
        <div class="lg:col-span-2 space-y-8">
            
            <!-- Latest Announcements -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h2 class="text-lg font-bold text-gray-800 flex items-center">
                        <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
                        Latest Announcements
                    </h2>
                    <a href="announcements.php" class="text-sm text-blue-600 hover:text-blue-800 font-medium">View all &rarr;</a>
                </div>
                <div class="divide-y divide-gray-100">
                    <?php if (empty($latest_announcements)): ?>
                        <div class="p-6 text-center text-gray-500">No recent announcements.</div>
                    <?php else: ?>
                        <?php foreach ($latest_announcements as $a): ?>
                            <a href="../public/announcement.php?id=<?php echo $a['id']; ?>" class="block p-5 hover:bg-gray-50 transition duration-150">
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                                        <p class="text-sm font-semibold text-gray-800 truncate"><?php echo htmlspecialchars($a['title']); ?></p>
                                    </div>
                                    <span class="text-xs text-gray-400 flex-shrink-0"><?php echo date('M d', strtotime($a['created_at'])); ?></span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Comments & Bookmarks Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Bookmarks -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                        <h2 class="text-md font-bold text-gray-800 flex items-center">
                            <svg class="w-4 h-4 text-purple-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path></svg>
                            Recent Bookmarks
                        </h2>
                    </div>
                    <ul class="divide-y divide-gray-100">
                        <?php if (empty($bookmarks)): ?>
                            <li class="p-4 text-center text-sm text-gray-500">No bookmarks yet.</li>
                        <?php else: ?>
                            <?php foreach ($bookmarks as $b): ?>
                                <li>
                                    <a href="../public/announcement.php?id=<?php echo $b['id']; ?>" class="block p-4 hover:bg-gray-50 text-sm font-medium text-gray-700 hover:text-purple-600 truncate transition-colors">
                                        <?php echo htmlspecialchars($b['title']); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Recent Comments -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                        <h2 class="text-md font-bold text-gray-800 flex items-center">
                            <svg class="w-4 h-4 text-emerald-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                            My Comments
                        </h2>
                    </div>
                    <ul class="divide-y divide-gray-100">
                        <?php if (empty($recent_comments)): ?>
                            <li class="p-4 text-center text-sm text-gray-500">No comments yet.</li>
                        <?php else: ?>
                            <?php foreach ($recent_comments as $c): ?>
                                <li class="p-4 hover:bg-gray-50 transition-colors">
                                    <p class="text-xs text-gray-400 mb-1">On: <a href="../public/announcement.php?id=<?php echo $c['announcement_id']; ?>" class="hover:underline text-gray-600 truncate"><?php echo htmlspecialchars($c['title']); ?></a></p>
                                    <p class="text-sm text-gray-700 italic truncate">"<?php echo htmlspecialchars($c['content']); ?>"</p>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            
        </div>

        <!-- Right Column -->
        <div class="space-y-8">
                      <!-- Timetable Shortcut -->
            <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl shadow-lg p-6 text-white transform transition duration-300 hover:scale-105 relative overflow-hidden group">
                <div class="absolute top-0 right-0 p-4 opacity-10 pointer-events-none transform group-hover:scale-110 transition-transform duration-500">
                    <svg class="w-32 h-32 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path></svg>
                </div>
                <div class="relative z-10 flex flex-col h-full">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold flex items-center">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                            Class Timetable
                        </h2>
                    </div>
                    <p class="text-indigo-100 mb-6 text-sm">View and download your official schedule for the current semester.</p>
                    <div class="mt-auto">
                        <a href="timetable.php" class="inline-flex items-center bg-white text-indigo-600 font-bold py-2 px-4 rounded-lg shadow hover:bg-indigo-50 transition-colors">
                            Open Timetable
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Notification Panel -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-5 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                    <h2 class="text-lg font-bold text-gray-800 flex items-center">
                        <svg class="w-5 h-5 text-yellow-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        Recent Notifications
                    </h2>
                </div>
                <ul class="divide-y divide-gray-100">
                    <?php if (empty($notifications)): ?>
                        <li class="p-6 text-center text-gray-500 text-sm">You have no new notifications.</li>
                    <?php else: ?>
                        <?php foreach ($notifications as $n): ?>
                            <li class="p-4 hover:bg-gray-50 transition-colors <?php echo !$n['is_read'] ? 'bg-blue-50/30' : ''; ?>">
                                <div class="flex items-start">
                                    <?php if (!$n['is_read']): ?>
                                        <div class="w-2 h-2 mt-1.5 rounded-full bg-blue-500 flex-shrink-0 mr-3"></div>
                                    <?php else: ?>
                                        <div class="w-2 h-2 mt-1.5 rounded-full bg-gray-300 flex-shrink-0 mr-3"></div>
                                    <?php endif; ?>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-800"><?php echo htmlspecialchars($n['title']); ?></p>
                                        <p class="text-xs text-gray-600 mt-1"><?php echo htmlspecialchars($n['message']); ?></p>
                                        <p class="text-[10px] text-gray-400 mt-2 uppercase tracking-wide"><?php echo date('M d, Y h:i A', strtotime($n['created_at'])); ?></p>
                                    </div>
                                    <?php if (!$n['is_read']): ?>
                                    <div class="ml-auto">
                                        <button class="mark-read-btn text-[10px] text-blue-600 hover:text-blue-800 font-medium bg-white px-2 py-1 rounded shadow-sm border border-blue-100" data-id="<?php echo $n['id']; ?>">
                                            Mark Read
                                        </button>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.mark-read-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const notifId = this.dataset.id;
            const formData = new FormData();
            formData.append('id', notifId);
            formData.append('csrf_token', '<?php echo generate_csrf_token(); ?>');
            
            fetch('../ajax/mark_notification_read.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const li = this.closest('li');
                    li.classList.remove('bg-blue-50/30');
                    const indicator = li.querySelector('.bg-blue-500');
                    if (indicator) {
                        indicator.classList.remove('bg-blue-500');
                        indicator.classList.add('bg-gray-300');
                    }
                    this.remove();
                }
            })
            .catch(err => console.error(err));
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>
