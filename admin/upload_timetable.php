<?php
require_once '../config/session.php';
require_admin();
require_once '../config/db.php';
require_once '../config/functions.php';

// Fetch Academic Year based on session
$active_ay_id = $_SESSION['current_academic_year_id'] ?? 0;
if ($active_ay_id) {
    $stmt = $pdo->prepare("SELECT id, year_name as name FROM academic_years WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $active_ay_id]);
} else {
    $stmt = $pdo->query("SELECT id, year_name as name FROM academic_years WHERE status = 'Active' LIMIT 1");
}
$active_ay = $stmt->fetch();
$active_ay_name = $active_ay ? $active_ay['name'] : 'Not Set';
$active_ay_id = $active_ay ? (int)$active_ay['id'] : 0;

if ($active_ay_id === 0) {
    die("No active academic year found. Please set an active academic year first.");
}

// Fetch Classrooms for the selected Academic Year
$stmt = $pdo->prepare("SELECT c.id, c.classroom_name, ayl.level_name 
                       FROM classrooms c 
                       LEFT JOIN academic_year_levels ayl ON c.academic_year_level_id = ayl.id
                       WHERE c.academic_year_id = :ay_id 
                       ORDER BY ayl.id ASC, c.classroom_name ASC");
$stmt->execute(['ay_id' => $active_ay_id]);
$classrooms = $stmt->fetchAll();
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>
<?php include '../includes/navbar.php'; ?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Upload Timetable PDF</h1>
    <a href="timetables.php" class="text-blue-600 hover:text-blue-900 font-medium">&larr; Back to Timetables</a>
</div>

<div class="bg-white shadow sm:rounded-lg p-6 max-w-2xl border border-gray-100">
    <div class="bg-blue-50 text-blue-800 p-4 rounded mb-6 text-sm flex items-center border border-blue-100">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span>This timetable will automatically be assigned to the selected Academic Year: <strong><?php echo htmlspecialchars($active_ay_name); ?></strong>.</span>
    </div>

    <div id="alert-container" class="hidden mb-4 p-4 rounded border-l-4 text-sm"></div>

    <form id="uploadForm" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
        
        <div class="space-y-6">
            <div>
                <label for="classroom_id" class="block text-sm font-medium text-gray-700">Select Classroom</label>
                <select id="classroom_id" name="classroom_id" required class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md border shadow-sm">
                    <option value="">-- Choose Classroom --</option>
                    <?php foreach ($classrooms as $c): ?>
                        <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['classroom_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="semester" class="block text-sm font-medium text-gray-700">Select Semester</label>
                <select id="semester" name="semester" required class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md border shadow-sm">
                    <option value="">-- Choose Semester --</option>
                    <option value="first">First Semester</option>
                    <option value="second">Second Semester</option>
                </select>
                <p class="text-xs text-gray-500 mt-1">First Semester (Dec 1 - Mar 30) / Second Semester (Jun 1 - Sep 30)</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Timetable PDF File</label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:bg-gray-50 transition-colors">
                    <div class="space-y-1 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="flex text-sm text-gray-600 justify-center">
                            <label for="file_upload" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                <span>Upload a file</span>
                                <input id="file_upload" name="timetable_file" type="file" accept="application/pdf,image/jpeg,image/png" required class="sr-only">
                            </label>
                        </div>
                        <p class="text-xs text-gray-500">PDF, JPG, PNG up to 10MB</p>
                    </div>
                </div>
                <div id="file-name-display" class="mt-2 text-sm text-gray-700 text-center font-medium"></div>
            </div>
        </div>

        <div class="mt-8 flex justify-end">
            <button type="submit" id="submitBtn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Upload & Save
            </button>
        </div>
    </form>
</div>

<script>
// Display chosen file name
document.getElementById('file_upload').addEventListener('change', function(e) {
    if (this.files && this.files[0]) {
        document.getElementById('file-name-display').textContent = "Selected: " + this.files[0].name;
    }
});

// Handle form submission via AJAX
document.getElementById('uploadForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = 'Uploading...';
    
    const formData = new FormData(this);
    
    fetch('../ajax/upload_timetable.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        const alertContainer = document.getElementById('alert-container');
        alertContainer.classList.remove('hidden', 'bg-red-50', 'border-red-500', 'text-red-700', 'bg-green-50', 'border-green-500', 'text-green-700');
        
        if (data.success) {
            alertContainer.classList.add('bg-green-50', 'border-green-500', 'text-green-700');
            alertContainer.innerHTML = 'Timetable uploaded successfully! Redirecting...';
            setTimeout(() => {
                window.location.href = 'timetables.php';
            }, 1000);
        } else {
            alertContainer.classList.add('bg-red-50', 'border-red-500', 'text-red-700');
            alertContainer.innerHTML = data.message;
            btn.disabled = false;
            btn.innerHTML = 'Upload & Save';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        btn.disabled = false;
        btn.innerHTML = 'Upload & Save';
    });
});
</script>

<?php include '../includes/footer.php'; ?>
