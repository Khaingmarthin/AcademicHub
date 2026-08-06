<?php
require_once '../config/session.php';
require_admin();
require_once '../config/db.php';
require_once '../config/functions.php';

$ay = null;
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>
<?php include '../includes/navbar.php'; ?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Add Academic Year</h1>
    <a href="academic_years.php" class="text-blue-600 hover:text-blue-900 font-medium">&larr; Back to List</a>
</div>

<div class="bg-white shadow overflow-hidden sm:rounded-lg p-6 max-w-2xl">
    <div id="alert-container" class="hidden mb-4 p-4 rounded border-l-4"></div>
    
    <form id="ayForm">
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
        
        <?php include '_academic_year_form.php'; ?>
        
        <div class="mt-6 flex justify-end">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition-colors shadow-sm">
                Save Academic Year
            </button>
        </div>
    </form>
</div>

<script>
document.getElementById('ayForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('../ajax/create_academic_year.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        const alertContainer = document.getElementById('alert-container');
        alertContainer.classList.remove('hidden', 'bg-red-50', 'border-red-500', 'text-red-700', 'bg-green-50', 'border-green-500', 'text-green-700');
        
        if (data.success) {
            alertContainer.classList.add('bg-green-50', 'border-green-500', 'text-green-700');
            alertContainer.innerHTML = 'Academic year added successfully! Redirecting...';
            setTimeout(() => {
                window.location.href = 'academic_years.php';
            }, 1000);
        } else {
            alertContainer.classList.add('bg-red-50', 'border-red-500', 'text-red-700');
            alertContainer.innerHTML = data.message;
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
});
</script>

<?php include '../includes/footer.php'; ?>
