<?php
require_once '../config/session.php';
require_once '../config/db.php';
require_once '../config/functions.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    die("Invalid Announcement ID.");
}

// Fetch announcement details
$stmt = $pdo->prepare("SELECT a.*, c.category_name, u.username as author_name 
                       FROM announcements a 
                       LEFT JOIN categories c ON a.category_id = c.id 
                       LEFT JOIN users u ON a.user_id = u.id 
                       WHERE a.id = :id LIMIT 1");
$stmt->execute(['id' => $id]);
$announcement = $stmt->fetch();

if (!$announcement) {
    die("Announcement not found.");
}

// Update view count if it's a GET request and not a page reload? Simple increment for now.
$pdo->prepare("UPDATE announcements SET view_count = view_count + 1 WHERE id = :id")->execute(['id' => $id]);

// Calculate estimated reading time
$word_count = str_word_count(strip_tags($announcement['content']));
$reading_time = ceil($word_count / 200); // Average reading speed: 200 words per minute

// Fetch attachments
$stmt_attach = $pdo->prepare("SELECT * FROM attachments WHERE announcement_id = :id");
$stmt_attach->execute(['id' => $id]);
$all_attachments = $stmt_attach->fetchAll();

$images = [];
$documents = [];
foreach ($all_attachments as $att) {
    if (strpos($att['file_type'], 'image/') === 0) {
        $images[] = $att;
    } else {
        $documents[] = $att;
    }
}

$cover_image = null;
if (count($images) > 0) {
    $cover_image = array_shift($images); // Use the first image as the cover image
}

// Fetch related announcements
$stmt_related = $pdo->prepare("
    SELECT a.*, c.category_name,
           (SELECT file_path FROM attachments WHERE announcement_id = a.id AND file_type LIKE 'image/%' LIMIT 1) as image_path
    FROM announcements a
    LEFT JOIN categories c ON a.category_id = c.id
    WHERE a.category_id = :category_id AND a.id != :id
    ORDER BY a.publish_date DESC, a.created_at DESC
    LIMIT 3
");
$stmt_related->execute(['category_id' => $announcement['category_id'], 'id' => $id]);
$related_announcements = $stmt_related->fetchAll();

$is_student = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'student';
$user_id = $_SESSION['user_id'] ?? 0;
$is_bookmarked = false;

if ($is_student && $id) {
    $stmt_bookmark = $pdo->prepare("SELECT id FROM bookmarks WHERE user_id = :uid AND announcement_id = :aid");
    $stmt_bookmark->execute(['uid' => $user_id, 'aid' => $id]);
    $is_bookmarked = (bool)$stmt_bookmark->fetch();
}
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<div class="min-h-screen bg-[#F5F7FB] py-10">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Back Button -->
        <div class="mb-6">
            <a href="javascript:history.back()" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-blue-600 transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                Back to previous page
            </a>
        </div>

        <div class="bg-white shadow-sm border border-gray-100 rounded-3xl overflow-hidden relative mb-12">
            
            <?php if ($announcement['is_urgent']): ?>
                <!-- Urgent Banner -->
                <div class="bg-red-600 text-white px-6 py-2 flex items-center justify-center font-bold uppercase tracking-wider text-sm">
                    <i data-lucide="alert-triangle" class="w-5 h-5 mr-2"></i>
                    Urgent Announcement
                </div>
            <?php endif; ?>

            <?php if ($cover_image): ?>
                <!-- Large cover image -->
                <div class="w-full h-[300px] md:h-[450px] overflow-hidden bg-gray-100">
                    <img src="../<?php echo htmlspecialchars($cover_image['file_path']); ?>" alt="Cover Image" class="w-full h-full object-cover">
                </div>
            <?php endif; ?>

            <div class="p-8 md:p-12">
                <!-- Header Section -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6 pb-6 border-b border-gray-100">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-200">
                                <?php echo htmlspecialchars($announcement['category_name'] ?? 'Uncategorized'); ?>
                            </span>
                            <?php if ($announcement['is_urgent']): ?>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-100 animate-pulse">
                                    <i data-lucide="alert-triangle" class="w-3 h-3 mr-1.5 fill-red-600"></i> URGENT
                                </span>
                            <?php endif; ?>
                        </div>
                        <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 leading-tight">
                            <?php echo htmlspecialchars($announcement['title']); ?>
                        </h1>
                    </div>
                    
                    <?php if ($is_student): ?>
                        <button id="bookmark-btn" class="flex-shrink-0 group bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-xl p-3 transition-all duration-200 shadow-sm focus:outline-none" data-id="<?php echo $id; ?>" title="<?php echo $is_bookmarked ? 'Remove Bookmark' : 'Bookmark Announcement'; ?>">
                            <i id="bookmark-icon" data-lucide="bookmark" class="w-6 h-6 transition-colors duration-200 <?php echo $is_bookmarked ? 'text-red-500 fill-red-500' : 'text-gray-400 group-hover:text-red-400'; ?>"></i>
                        </button>
                    <?php endif; ?>
                </div>

                <!-- Meta Info -->
                <div class="flex flex-wrap items-center gap-y-3 gap-x-6 text-sm font-medium text-gray-500 mb-10">
                    <div class="flex items-center bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-100">
                        <i data-lucide="user" class="w-4 h-4 mr-2 text-gray-400"></i>
                        <span class="text-gray-700">Posted by <strong><?php echo htmlspecialchars($announcement['author_name'] ?? 'Admin'); ?></strong></span>
                    </div>
                    <div class="flex items-center">
                        <i data-lucide="calendar" class="w-4 h-4 mr-2 text-gray-400"></i>
                        <?php echo date('F j, Y, g:i a', strtotime($announcement['created_at'])); ?>
                    </div>
                    <div class="flex items-center">
                        <i data-lucide="clock" class="w-4 h-4 mr-2 text-gray-400"></i>
                        <?php echo $reading_time; ?> min read
                    </div>
                    <div class="flex items-center">
                        <i data-lucide="eye" class="w-4 h-4 mr-2 text-gray-400"></i>
                        <?php echo number_format($announcement['view_count'] + 1); ?> Views
                    </div>
                </div>
                
                <!-- Main Content -->
                <div class="prose prose-lg prose-blue max-w-none text-gray-800 leading-relaxed mb-12">
                    <?php 
                    // Output raw HTML content if it was created with a rich text editor.
                    echo $announcement['content']; 
                    ?>
                </div>

                <!-- Share Buttons -->
                <div class="border-t border-gray-100 pt-8 mb-12">
                    <h4 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4">Share this announcement</h4>
                    <div class="flex flex-wrap gap-3">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" target="_blank" class="inline-flex items-center px-4 py-2 bg-[#1877F2] text-white rounded-lg hover:bg-[#166FE5] transition-colors shadow-sm">
                            <i data-lucide="facebook" class="w-4 h-4 mr-2"></i> Facebook
                        </a>
                        <a href="https://t.me/share/url?url=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>&text=<?php echo urlencode($announcement['title']); ?>" target="_blank" class="inline-flex items-center px-4 py-2 bg-[#229ED9] text-white rounded-lg hover:bg-[#1C88BA] transition-colors shadow-sm">
                            <i data-lucide="send" class="w-4 h-4 mr-2"></i> Telegram
                        </a>
                        <button onclick="navigator.clipboard.writeText(window.location.href); alert('Link copied!');" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors shadow-sm font-medium">
                            <i data-lucide="link" class="w-4 h-4 mr-2"></i> Copy Link
                        </button>
                    </div>
                </div>

                <!-- Image Gallery -->
                <?php if (!empty($images)): ?>
                    <div class="mb-12">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                            <i data-lucide="image" class="w-5 h-5 mr-2 text-blue-600"></i>
                            Image Gallery
                        </h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <?php foreach ($images as $img): ?>
                                <a href="../<?php echo htmlspecialchars($img['file_path']); ?>" target="_blank" class="block h-48 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow group relative bg-gray-100">
                                    <img src="../<?php echo htmlspecialchars($img['file_path']); ?>" alt="Gallery Image" class="w-full h-full object-cover transform transition-transform duration-500 group-hover:scale-105">
                                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors flex items-center justify-center">
                                        <i data-lucide="maximize" class="w-8 h-8 text-white opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Attachments Section (Documents) -->
                <?php if (!empty($documents)): ?>
                    <div class="bg-gray-50 rounded-xl p-6 border border-gray-100 mb-12">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                            <i data-lucide="paperclip" class="w-5 h-5 mr-2 text-blue-600"></i>
                            Attachments (<?php echo count($documents); ?>)
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <?php foreach ($documents as $att): ?>
                                <div class="flex items-center justify-between p-4 bg-white border border-gray-200 rounded-xl hover:border-blue-400 hover:shadow-md transition-all group">
                                    <div class="flex items-center flex-1 min-w-0 mr-4">
                                        <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center mr-3 flex-shrink-0 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                            <i data-lucide="file-text" class="w-5 h-5"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-gray-900 truncate group-hover:text-blue-600 transition-colors">
                                                <?php echo htmlspecialchars($att['file_name']); ?>
                                            </p>
                                            <p class="text-xs text-gray-500 uppercase tracking-wider mt-0.5">
                                                <?php echo round($att['file_size'] / 1024, 2); ?> KB
                                            </p>
                                        </div>
                                    </div>
                                    <a href="../<?php echo htmlspecialchars($att['file_path']); ?>" download class="flex-shrink-0 inline-flex items-center justify-center w-10 h-10 rounded-lg bg-gray-100 text-gray-600 hover:bg-blue-600 hover:text-white transition-colors">
                                        <i data-lucide="download" class="w-5 h-5"></i>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Comments Section Placeholder -->
                <div class="border-t border-gray-100 pt-10">
                    <?php if ($is_student): ?>
                        <div class="bg-blue-50/50 border border-blue-100 rounded-xl p-6 text-center">
                            <i data-lucide="message-square" class="w-8 h-8 text-blue-400 mx-auto mb-3"></i>
                            <h4 class="text-gray-900 font-semibold mb-1">Comments</h4>
                            <p class="text-sm text-gray-500">Comments section functionality can be integrated here.</p>
                        </div>
                    <?php else: ?>
                        <div class="bg-gray-50 border border-gray-200 rounded-xl p-8 text-center">
                            <i data-lucide="lock" class="w-10 h-10 text-gray-400 mx-auto mb-4"></i>
                            <h4 class="text-lg font-bold text-gray-900 mb-2">Guest view only</h4>
                            <p class="text-gray-600 mb-6">Login as a student to participate in discussions.</p>
                            <a href="../auth/login.php" class="inline-flex items-center px-6 py-2.5 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                                Login to Comment
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>

        <!-- Related Announcements -->
        <?php if (!empty($related_announcements)): ?>
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                Related Announcements
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php foreach($related_announcements as $related): ?>
                    <a href="announcement.php?id=<?php echo $related['id']; ?>" class="group bg-white rounded-2xl shadow-sm hover:shadow-xl border border-gray-100 overflow-hidden flex flex-col transform transition-all duration-300 hover:-translate-y-1">
                        <!-- Thumbnail -->
                        <div class="h-40 bg-gray-100 relative overflow-hidden flex items-center justify-center border-b border-gray-100">
                            <?php if(!empty($related['image_path'])): ?>
                                <img src="../<?php echo htmlspecialchars($related['image_path']); ?>" alt="Thumbnail" class="w-full h-full object-cover transform transition-transform duration-700 group-hover:scale-105">
                            <?php else: ?>
                                <div class="absolute inset-0 bg-gradient-to-br from-gray-50 to-gray-100"></div>
                                <svg class="w-12 h-12 text-gray-300 z-0" fill="currentColor" viewBox="0 0 20 20"><path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z"></path></svg>
                            <?php endif; ?>
                        </div>
                        <!-- Content -->
                        <div class="p-5 flex-1 flex flex-col">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-[10px] font-bold tracking-wider uppercase text-blue-700 bg-blue-50 px-2 py-1 rounded">
                                    <?php echo htmlspecialchars($related['category_name'] ?? 'General'); ?>
                                </span>
                                <span class="text-xs text-gray-500 font-medium">
                                    <?php echo date('M d, Y', strtotime($related['publish_date'] ?? $related['created_at'])); ?>
                                </span>
                            </div>
                            <h3 class="text-base font-bold text-gray-900 mb-2 group-hover:text-blue-600 transition-colors line-clamp-2">
                                <?php echo htmlspecialchars($related['title']); ?>
                            </h3>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($is_student): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const bookmarkBtn = document.getElementById('bookmark-btn');
    if (bookmarkBtn) {
        bookmarkBtn.addEventListener('click', function() {
            const announcementId = this.dataset.id;
            const icon = document.getElementById('bookmark-icon');
            
            const formData = new FormData();
            formData.append('announcement_id', announcementId);
            formData.append('csrf_token', '<?php echo generate_csrf_token(); ?>');
            
            // Pop animation
            bookmarkBtn.classList.add('scale-95');
            setTimeout(() => bookmarkBtn.classList.remove('scale-95'), 150);

            fetch('../ajax/toggle_bookmark.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (data.action === 'added') {
                        icon.classList.remove('text-gray-400', 'group-hover:text-red-400');
                        icon.classList.add('text-red-500', 'fill-red-500');
                        bookmarkBtn.title = 'Remove Bookmark';
                    } else {
                        icon.classList.remove('text-red-500', 'fill-red-500');
                        icon.classList.add('text-gray-400', 'group-hover:text-red-400');
                        bookmarkBtn.title = 'Bookmark Announcement';
                    }
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => console.error(err));
        });
    }
});
</script>
<?php endif; ?>

<script>
// Initialize Lucide icons if the library is present
if (typeof lucide !== 'undefined') {
    lucide.createIcons();
}
</script>

<?php include '../includes/footer.php'; ?>
