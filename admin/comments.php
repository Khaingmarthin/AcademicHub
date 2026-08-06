<?php
require_once '../config/session.php';
require_admin();
require_once '../config/db.php';
require_once '../config/functions.php';

// Pagination
$limit = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Fetch total
$countStmt = $pdo->query("SELECT COUNT(*) FROM comments");
$total_items = $countStmt->fetchColumn();
$total_pages = ceil($total_items / $limit);

// Fetch comments (Newest first)
$sql = "SELECT c.*, u.username as author_name, a.title as announcement_title 
        FROM comments c
        JOIN users u ON c.user_id = u.id
        JOIN announcements a ON c.announcement_id = a.id
        ORDER BY c.created_at DESC
        LIMIT $limit OFFSET $offset";
$stmt = $pdo->query($sql);
$comments = $stmt->fetchAll();
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>
<?php include '../includes/navbar.php'; ?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Manage Comments</h1>
</div>

<div class="bg-white shadow overflow-hidden sm:rounded-lg">
    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
        <h3 class="text-lg leading-6 font-medium text-gray-900">All Comments</h3>
        <p class="mt-1 max-w-2xl text-sm text-gray-500">View and moderate student discussions across all announcements.</p>
    </div>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Author</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comment</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Announcement</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($comments as $c): ?>
                <tr id="row-<?php echo $c['id']; ?>">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-xs uppercase">
                                <?php echo htmlspecialchars(substr($c['author_name'], 0, 1)); ?>
                            </div>
                            <div class="ml-3">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($c['author_name']); ?></div>
                                <?php if ($c['parent_id']): ?>
                                    <div class="text-xs text-gray-500 flex items-center">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                                        Reply
                                    </div>
                                <?php else: ?>
                                    <div class="text-xs text-gray-500">Root Comment</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-900 line-clamp-2" title="<?php echo htmlspecialchars($c['content']); ?>">
                            <?php echo htmlspecialchars($c['content']); ?>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <a href="../../public/announcement.php?id=<?php echo $c['announcement_id']; ?>" target="_blank" class="text-sm text-blue-600 hover:underline line-clamp-1">
                            <?php echo htmlspecialchars($c['announcement_title']); ?>
                        </a>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?php echo date('M d, Y h:i A', strtotime($c['created_at'])); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <button onclick="deleteComment(<?php echo $c['id']; ?>)" class="text-red-600 hover:text-red-900 bg-red-50 px-3 py-1 rounded border border-red-100">Delete</button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($comments)): ?>
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">No comments found.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-700">
                    Showing <span class="font-medium"><?php echo $offset + 1; ?></span> to <span class="font-medium"><?php echo min($offset + $limit, $total_items); ?></span> of <span class="font-medium"><?php echo $total_items; ?></span> results
                </p>
            </div>
            <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">Previous</a>
                    <?php endif; ?>
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">Next</a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function deleteComment(id) {
    if (confirm('Are you sure you want to delete this comment? If this is a root comment, all replies will also be deleted.')) {
        fetch('../ajax/delete_comment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id=' + id + '&csrf_token=<?php echo generate_csrf_token(); ?>'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('row-' + id).remove();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An unexpected error occurred.');
        });
    }
}
</script>

<?php include '../includes/footer.php'; ?>
