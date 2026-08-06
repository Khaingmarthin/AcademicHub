<?php
require_once '../config/session.php';
require_admin();
require_once '../config/db.php';
require_once '../config/functions.php';

$id = $_GET['id'] ?? 0;

// Fetch categories for the dropdown
$stmt = $pdo->query("SELECT id, category_name as name FROM categories ORDER BY category_name ASC");
$categories = $stmt->fetchAll();

// Fetch academic years
$stmt = $pdo->query("SELECT id, year_name as name FROM academic_years ORDER BY year_name DESC");
$academic_years = $stmt->fetchAll();

// Fetch announcement
$stmt = $pdo->prepare("SELECT * FROM announcements WHERE id = :id");
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
    <h1 class="text-2xl font-semibold text-gray-900">Edit Announcement</h1>
    <a href="announcements.php" class="text-gray-600 hover:text-gray-900">&larr; Back to list</a>
</div>

<div class="bg-white shadow overflow-hidden sm:rounded-lg p-6">
    <div id="alert-container" class="hidden mb-4 p-4 rounded border-l-4"></div>
    
    <form id="announcementForm" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        
        <?php include '_announcement_form.php'; ?>
        
        <div class="mt-6 flex justify-end">
            <button type="submit" id="submitBtn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded focus:outline-none focus:shadow-outline transition-colors">
                Update Announcement
            </button>
        </div>
    </form>
</div>

<script>
document.getElementById('announcementForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('submitBtn');
    const alertContainer = document.getElementById('alert-container');
    const formData = new FormData(this);
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = 'Updating...';
    
    fetch('../ajax/update_announcement.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        alertContainer.classList.remove('hidden', 'bg-red-100', 'border-red-500', 'text-red-700', 'bg-green-100', 'border-green-500', 'text-green-700');
        if (data.success) {
            alertContainer.classList.add('bg-green-100', 'border-green-500', 'text-green-700');
            alertContainer.innerHTML = 'Announcement updated successfully! Redirecting...';
            setTimeout(() => {
                window.location.href = 'announcements.php';
            }, 1500);
        } else {
            alertContainer.classList.add('bg-red-100', 'border-red-500', 'text-red-700');
            alertContainer.innerHTML = 'Error: ' + data.message;
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Update Announcement';
        }
    })
    .catch(error => {
        alertContainer.classList.remove('hidden');
        alertContainer.classList.add('bg-red-100', 'border-red-500', 'text-red-700');
        alertContainer.innerHTML = 'An unexpected error occurred.';
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Update Announcement';
    });
});
</script>

<?php include '../includes/footer.php'; ?>
