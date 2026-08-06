<?php
require_once '../config/session.php';
require_student();
require_once '../config/db.php';
require_once '../config/functions.php';

$user_id = $_SESSION['user_id'];

// Pagination
$limit = 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Fetch total count
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM bookmarks WHERE user_id = :uid");
$countStmt->execute(['uid' => $user_id]);
$total_items = $countStmt->fetchColumn();
$total_pages = ceil($total_items / $limit);

// Fetch bookmarks with announcement details
$stmt = $pdo->prepare("
    SELECT b.id as bookmark_id, b.created_at as bookmarked_at, 
           a.id as announcement_id, a.title, a.content, a.created_at as posted_at,
           c.name as category_name
    FROM bookmarks b
    JOIN announcements a ON b.announcement_id = a.id
    LEFT JOIN categories c ON a.category_id = c.id
    WHERE b.user_id = :uid
    ORDER BY b.created_at DESC
    LIMIT $limit OFFSET $offset
");
$stmt->execute(['uid' => $user_id]);
$bookmarks = $stmt->fetchAll();
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>
<?php include '../includes/navbar.php'; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                <svg class="w-8 h-8 mr-3 text-red-500 fill-current" viewBox="0 0 24 24" stroke="currentColor" fill="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                </svg>
                My Bookmarks
            </h1>
            <p class="mt-2 text-sm text-gray-600">Saved announcements for quick reference.</p>
        </div>
    </div>

    <?php if (empty($bookmarks)): ?>
        <div class="flex flex-col items-center justify-center py-20 px-4 bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 transition-colors">
            <div class="bg-red-50 dark:bg-red-900/30 p-6 rounded-full mb-6">
                <svg class="h-16 w-16 text-red-500 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white text-center mb-2">No bookmarks yet</h3>
            <p class="text-gray-500 dark:text-gray-400 text-center max-w-md mb-8">When you see an interesting announcement, click the heart icon to save it here for quick access later.</p>
            <a href="../public/index.php" class="btn-primary flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                Browse Announcements
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($bookmarks as $b): ?>
                <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300 border border-gray-100 overflow-hidden flex flex-col relative" id="bookmark-card-<?php echo $b['announcement_id']; ?>">
                    <div class="p-5 flex-1">
                        <div class="flex justify-between items-start mb-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <?php echo htmlspecialchars($b['category_name'] ?? 'General'); ?>
                            </span>
                            <button class="remove-bookmark text-red-500 hover:text-red-700 focus:outline-none transition-colors" data-id="<?php echo $b['announcement_id']; ?>" title="Remove Bookmark">
                                <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24">
                                    <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                </svg>
                            </button>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2 line-clamp-2">
                            <a href="../public/announcement.php?id=<?php echo $b['announcement_id']; ?>" class="hover:text-blue-600 transition-colors">
                                <?php echo htmlspecialchars($b['title']); ?>
                            </a>
                        </h3>
                        <p class="text-gray-600 text-sm mb-4 line-clamp-3">
                            <?php echo strip_tags($b['content']); ?>
                        </p>
                    </div>
                    <div class="px-5 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500 mt-auto">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            Posted <?php echo date('M d, Y', strtotime($b['posted_at'])); ?>
                        </div>
                        <a href="../public/announcement.php?id=<?php echo $b['announcement_id']; ?>" class="text-blue-600 font-medium hover:underline">Read More &rarr;</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="mt-10 flex items-center justify-between bg-white px-4 py-3 rounded-lg shadow-sm border border-gray-100 sm:px-6">
            <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Showing <span class="font-medium"><?php echo $offset + 1; ?></span> to <span class="font-medium"><?php echo min($offset + $limit, $total_items); ?></span> of <span class="font-medium"><?php echo $total_items; ?></span> saved
                    </p>
                </div>
                <div>
                    <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>" class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">
                                <span class="sr-only">Previous</span>
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" /></svg>
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold <?php echo $i === $page ? 'z-10 bg-blue-600 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600' : 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>" class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">
                                <span class="sr-only">Next</span>
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" /></svg>
                            </a>
                        <?php endif; ?>
                    </nav>
                </div>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const removeBtns = document.querySelectorAll('.remove-bookmark');
    removeBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const announcementId = this.dataset.id;
            
            ConfirmModal.show(
                'Remove Bookmark', 
                'Are you sure you want to remove this announcement from your bookmarks?', 
                'Remove', 
                'bg-red-600 hover:bg-red-700', 
                function() {
                    const formData = new FormData();
                    formData.append('announcement_id', announcementId);
                    formData.append('csrf_token', '<?php echo generate_csrf_token(); ?>');
                    
                    fetch('../ajax/toggle_bookmark.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success && data.action === 'removed') {
                            Toast.show('Success', 'Bookmark removed successfully', 'success');
                            const card = document.getElementById('bookmark-card-' + announcementId);
                            card.style.transition = 'all 0.3s ease';
                            card.style.opacity = '0';
                            card.style.transform = 'scale(0.9)';
                            setTimeout(() => {
                                card.remove();
                                if (document.querySelectorAll('.remove-bookmark').length === 0) {
                                    window.location.reload();
                                }
                            }, 300);
                        } else {
                            Toast.show('Error', data.message, 'error');
                        }
                    })
                    .catch(err => Toast.show('Error', 'Network request failed', 'error'));
                }
            );
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>
