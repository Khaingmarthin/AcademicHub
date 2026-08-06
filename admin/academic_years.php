<?php
require_once '../config/session.php';
require_admin();
require_once '../config/db.php';
require_once '../config/functions.php';

// Fetch all academic years
$stmt = $pdo->query("SELECT id, year_name as name, status, start_date, end_date, created_at, updated_at FROM academic_years ORDER BY year_name DESC");
$academic_years = $stmt->fetchAll();

// Check if any is active
$has_active = false;
foreach ($academic_years as $ay) {
    if ($ay['status'] === 'active') {
        $has_active = true;
        break;
    }
}
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>
<?php include '../includes/navbar.php'; ?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Academic Years</h1>
    <a href="create_academic_year.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition-colors inline-flex items-center shadow-sm">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        Add Academic Year
    </a>
</div>

<div class="bg-white shadow overflow-hidden sm:rounded-lg">
    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
        <h3 class="text-lg leading-6 font-medium text-gray-900">Manage Years</h3>
        <p class="mt-1 max-w-2xl text-sm text-gray-500">Only one academic year can be active at a time.</p>
    </div>
    <div id="alert-container" class="hidden m-4 p-4 rounded border-l-4"></div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                    <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($academic_years as $ay): ?>
                <tr id="row-<?php echo $ay['id']; ?>">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-bold text-gray-900"><?php echo htmlspecialchars($ay['name']); ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <?php if ($ay['status'] === 'active'): ?>
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 border border-green-200">
                                Active
                            </span>
                        <?php elseif ($ay['status'] === 'preparation'): ?>
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 border border-blue-200">
                                Preparation
                            </span>
                        <?php else: ?>
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 border border-gray-200">
                                Archived
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?php echo date('M d, Y', strtotime($ay['created_at'])); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <?php if ($ay['status'] !== 'active'): ?>
                            <button onclick="setStatus(<?php echo $ay['id']; ?>, 'active')" class="text-green-600 hover:text-green-900 mr-3">Set Active</button>
                        <?php endif; ?>
                        
                        <?php if ($ay['status'] === 'preparation'): ?>
                            <button onclick="setStatus(<?php echo $ay['id']; ?>, 'archived')" class="text-gray-600 hover:text-gray-900 mr-3">Archive</button>
                        <?php endif; ?>
                        
                        <a href="edit_academic_year.php?id=<?php echo $ay['id']; ?>" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($academic_years)): ?>
                <tr>
                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">No academic years found.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function setStatus(id, newStatus) {
    if (confirm('Are you sure you want to change the status to ' + newStatus + '? ' + (newStatus === 'active' ? 'This will archive the currently active year and update all dashboards.' : ''))) {
        fetch('../ajax/set_academic_year_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id=' + id + '&status=' + newStatus + '&csrf_token=<?php echo generate_csrf_token(); ?>'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
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
