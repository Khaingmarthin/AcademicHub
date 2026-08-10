<?php
require_once '../config/session.php';
require_admin();
require_once '../config/db.php';
require_once '../config/functions.php';

// Fetch Academic Year based on session
$active_ay_id = get_global_active_academic_year($pdo)['id'] ?? 0;
if ($active_ay_id) {
    $stmt = $pdo->prepare("SELECT id, year_name as name FROM academic_years WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $active_ay_id]);
} else {
    $stmt = $pdo->query("SELECT id, year_name as name FROM academic_years WHERE status = 'Active' LIMIT 1");
}
$active_ay = $stmt->fetch();
$active_ay_name = $active_ay ? $active_ay['name'] : 'Not Set';
$active_ay_id = $active_ay ? (int)$active_ay['id'] : 0;

// Fetch timetables
$timetables = [];
if ($active_ay_id > 0) {
    $stmt = $pdo->prepare("
        SELECT t.*, 
               m.major_name as major_name, 
               ayl.level_name as level_name,
               c.classroom_name as classroom_name
        FROM timetables t 
        LEFT JOIN majors m ON t.major_id = m.id 
        LEFT JOIN academic_year_levels ayl ON t.academic_year_level_id = ayl.id
        LEFT JOIN classrooms c ON t.classroom_id = c.id
        WHERE t.academic_year_id = :ay_id 
        ORDER BY ayl.id ASC, m.major_name ASC, t.semester ASC
    ");
    $stmt->execute(['ay_id' => $active_ay_id]);
    $timetables = $stmt->fetchAll();
}
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>
<?php include '../includes/navbar.php'; ?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Timetable Management</h1>
    <a href="upload_timetable.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition-colors inline-flex items-center shadow-sm">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
        Upload Timetable PDF
    </a>
</div>

<?php if ($active_ay_id === 0): ?>
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-yellow-700">No active academic year found. Please activate an academic year first.</p>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 flex justify-between items-center">
        <div>
            <span class="text-sm text-blue-600 font-semibold uppercase tracking-wider">Current Academic Year</span>
            <p class="text-xl font-bold text-blue-900 mt-1"><?php echo htmlspecialchars($active_ay_name); ?></p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5" id="timetable_grid">
        <?php foreach ($timetables as $t): ?>
        <div class="timetable-card group relative flex flex-col bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md hover:-translate-y-1 transition-all duration-300">
            <div class="p-5 flex-1 flex flex-col">
                <!-- Header / Semester Badge -->
                <div class="flex justify-between items-start mb-4">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-black tracking-widest <?php echo $t['semester'] === 'first' ? 'bg-indigo-50 text-indigo-700 border border-indigo-100' : 'bg-emerald-50 text-emerald-700 border border-emerald-100'; ?> uppercase">
                        <?php echo htmlspecialchars($t['semester']); ?> Semester
                    </span>
                    <span class="text-[10px] font-semibold text-gray-400">
                        <?php echo date('M d, Y', strtotime($t['created_at'])); ?>
                    </span>
                </div>
                
                <!-- Title -->
                <h3 class="text-lg font-bold text-gray-900 mb-1 leading-tight">
                    <?php echo htmlspecialchars($t['classroom_name'] ?? 'Unknown Classroom'); ?>
                </h3>
                <p class="text-xs text-gray-500 mb-6 font-medium">
                    <?php echo htmlspecialchars($t['major_name'] ?? 'Unknown Major'); ?>
                </p>
                
                <!-- Actions -->
                <div class="mt-auto grid grid-cols-3 gap-2">
                    <a href="../../<?php echo htmlspecialchars($t['file_path']); ?>" target="_blank" class="flex items-center justify-center gap-1.5 px-2 py-2 text-[11px] font-bold text-[#2563EB] bg-blue-50 border border-blue-100 rounded-lg hover:bg-[#2563EB] hover:text-white transition-colors duration-200">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        View
                    </a>
                    <a href="edit_timetable.php?id=<?php echo $t['id']; ?>" class="flex items-center justify-center gap-1.5 px-2 py-2 text-[11px] font-bold text-gray-700 bg-gray-50 border border-gray-200 rounded-lg hover:bg-gray-700 hover:text-white transition-colors duration-200">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        Edit
                    </a>
                    <button onclick="deleteTimetable(<?php echo $t['id']; ?>)" class="flex items-center justify-center gap-1.5 px-2 py-2 text-[11px] font-bold text-red-600 bg-red-50 border border-red-100 rounded-lg hover:bg-red-600 hover:text-white transition-colors duration-200">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        Delete
                    </button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        
        <?php if (empty($timetables)): ?>
        <div class="col-span-full py-12 flex flex-col items-center justify-center text-gray-500 bg-white rounded-xl border border-gray-100 border-dashed">
            <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            <p class="text-sm font-medium">No timetables uploaded for the current academic year.</p>
        </div>
        <?php endif; ?>
    </div>
<?php endif; ?>
<script>
async function deleteTimetable(id) {
    if (!confirm('Are you sure you want to delete this timetable? This action cannot be undone.')) return;
    
    try {
        const res = await fetch('../ajax/delete_timetable.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${id}&csrf_token=<?php echo generate_csrf_token(); ?>`
        });
        const data = await res.json();
        
        if (data.success) {
            window.location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    } catch(err) {
        alert('An unexpected error occurred.');
    }
}
</script>

<?php include '../includes/footer.php'; ?>
