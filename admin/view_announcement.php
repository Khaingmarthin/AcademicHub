<?php
require_once '../config/session.php';
require_admin();
require_once '../config/db.php';
require_once '../config/functions.php';

$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT a.*, c.category_name as category_name, ay.year_name as academic_year_name, ay.status as academic_year_status, u.username as author_name 
                       FROM announcements a 
                       LEFT JOIN categories c ON a.category_id = c.id 
                       LEFT JOIN academic_years ay ON a.academic_year_id = ay.id
                       LEFT JOIN users u ON a.user_id = u.id 
                       WHERE a.id = :id");
$stmt->execute(['id' => $id]);
$announcement = $stmt->fetch();

if (!$announcement) {
    redirect('announcements.php');
}
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>
<?php include '../includes/navbar.php'; ?>

<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-semibold text-gray-900">View Announcement</h1>
    <div>
        <a href="edit_announcement.php?id=<?php echo $id; ?>" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mr-2 transition-colors">Edit</a>
        <a href="announcements.php" class="text-gray-600 hover:text-gray-900">Back to list</a>
    </div>
</div>

<div class="bg-white shadow overflow-hidden sm:rounded-lg">
    <div class="px-4 py-5 sm:px-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900">
            <?php echo htmlspecialchars($announcement['title']); ?>
        </h3>
        <p class="mt-1 max-w-2xl text-sm text-gray-500">
            By <?php echo htmlspecialchars($announcement['author_name']); ?> on <?php echo date('F j, Y, g:i a', strtotime($announcement['created_at'])); ?>
        </p>
    </div>
    <div class="border-t border-gray-200">
        <dl>
            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">Category</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"><?php echo htmlspecialchars($announcement['category_name'] ?? 'Uncategorized'); ?></dd>
            </div>
            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">Academic Year</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"><?php echo htmlspecialchars($announcement['academic_year_name'] ?? 'Not Specified'); ?></dd>
            </div>
            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">Flags</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    <?php if ($announcement['is_urgent']): ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 mr-2">Urgent</span>
                    <?php else: ?>
                        <span class="text-gray-400 italic">None</span>
                    <?php endif; ?>
                </dd>
            </div>

            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">Status</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2 capitalize">
                    <?php 
                        $calculatedStatus = get_calculated_status($pdo, $announcement['publish_date'], $announcement['academic_year_id']);
                        echo htmlspecialchars(ucfirst($calculatedStatus));
                    ?>
                </dd>
            </div>
            <div class="bg-white px-4 py-5 sm:px-6">
                <dt class="text-sm font-medium text-gray-500 mb-4">Content</dt>
                <dd class="mt-1 text-sm text-gray-900 whitespace-pre-wrap prose max-w-none"><?php echo htmlspecialchars($announcement['content']); ?></dd>
            </div>
            <?php if (!empty($announcement['attachment_path'])): ?>
            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 border-t border-gray-200">
                <dt class="text-sm font-medium text-gray-500">Attachment</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    <a href="/<?php echo htmlspecialchars($announcement['attachment_path']); ?>" target="_blank" class="inline-flex items-center text-blue-600 hover:text-blue-500 font-medium">
                        <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                        Download / View Attachment
                    </a>
                </dd>
            </div>
            <?php endif; ?>
        </dl>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
