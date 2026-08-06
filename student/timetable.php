<?php
require_once '../config/session.php';
require_student();
require_once '../config/db.php';
require_once '../config/functions.php';

$user_id = $_SESSION['user_id'];

// 1. Get student's classroom
$stmt = $pdo->prepare("SELECT u.classroom_id, c.classroom_name, ayl.level_name, m.major_name
                       FROM users u 
                       LEFT JOIN classrooms c ON u.classroom_id = c.id 
                       LEFT JOIN academic_year_levels ayl ON c.academic_year_level_id = ayl.id
                       LEFT JOIN majors m ON c.major_id = m.id
                       WHERE u.id = :uid LIMIT 1");
$stmt->execute(['uid' => $user_id]);
$student_info = $stmt->fetch();

if (!$student_info || !$student_info['classroom_id']) {
    die("Student is not assigned to any classroom. Please contact administration.");
}

$classroom_id = $student_info['classroom_id'];
$classroom_name = $student_info['classroom_name'];
$level_name = $student_info['level_name'];
$major_name = $student_info['major_name'];

// 2. Get Academic Year based on session
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

// 3. Determine current semester based on date
// Dec 1 to Mar 31 = first
// Jun 1 to Sep 30 = second
$current_month = (int)date('m');
$current_semester = 'none';

if ($current_month == 12 || ($current_month >= 1 && $current_month <= 3)) {
    $current_semester = 'first';
} elseif ($current_month >= 6 && $current_month <= 9) {
    $current_semester = 'second';
}

// 4. Fetch the timetable PDF
$timetable = null;
if ($active_ay_id > 0 && $current_semester !== 'none') {
    $stmt = $pdo->prepare("SELECT file_path, created_at 
                           FROM timetables 
                           WHERE classroom_id = :cid 
                           AND academic_year_id = :ayid 
                           AND semester = :sem 
                           LIMIT 1");
    $stmt->execute([
        'cid' => $classroom_id,
        'ayid' => $active_ay_id,
        'sem' => $current_semester
    ]);
    $timetable = $stmt->fetch();
}
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>
<?php include '../includes/navbar.php'; ?>

<div class="max-w-7xl mx-auto">
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                <svg class="w-8 h-8 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                My Class Timetable
            </h1>
            <p class="mt-1 text-sm text-gray-500">View and download your official schedule.</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
        <div class="p-6 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-100 flex flex-col md:flex-row md:justify-between md:items-center">
            <div class="space-y-1">
                <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Current Academic Profile</p>
                <div class="flex items-center space-x-4 mt-2">
                    <div class="flex items-center bg-white px-3 py-1 rounded-full shadow-sm border border-gray-200">
                        <span class="w-2 h-2 rounded-full bg-blue-500 mr-2"></span>
                        <span class="text-sm font-bold text-gray-800"><?php echo htmlspecialchars($major_name); ?></span>
                    </div>
                    <div class="flex items-center bg-white px-3 py-1 rounded-full shadow-sm border border-gray-200">
                        <span class="w-2 h-2 rounded-full bg-indigo-500 mr-2"></span>
                        <span class="text-sm font-bold text-gray-800"><?php echo htmlspecialchars($active_ay_name); ?></span>
                    </div>
                    <div class="flex items-center bg-white px-3 py-1 rounded-full shadow-sm border border-gray-200">
                        <span class="w-2 h-2 rounded-full <?php echo $current_semester === 'none' ? 'bg-gray-400' : 'bg-emerald-500'; ?> mr-2"></span>
                        <span class="text-sm font-bold text-gray-800 capitalize">
                            <?php echo $current_semester !== 'none' ? htmlspecialchars($current_semester) . ' Semester' : 'Outside Active Semester'; ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <?php if ($timetable): ?>
            <div class="mt-4 md:mt-0">
                <a href="../<?php echo htmlspecialchars($timetable['file_path']); ?>" download class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Download PDF
                </a>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="p-6">
            <?php if ($active_ay_id === 0): ?>
                <div class="text-center py-12">
                    <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <h3 class="text-lg font-medium text-gray-900">Academic Year Not Set</h3>
                    <p class="mt-1 text-gray-500">The administration has not set an active academic year yet.</p>
                </div>
            <?php elseif ($current_semester === 'none'): ?>
                <div class="text-center py-12">
                    <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <h3 class="text-lg font-medium text-gray-900">Break Period</h3>
                    <p class="mt-1 text-gray-500">You are currently outside the official semester dates. Timetables are only available during active semesters.</p>
                </div>
            <?php elseif (!$timetable): ?>
                <div class="text-center py-12">
                    <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <h3 class="text-lg font-medium text-gray-900">Timetable Not Available</h3>
                    <p class="mt-1 text-gray-500">Your timetable for this semester has not been uploaded yet.</p>
                </div>
            <?php else: ?>
                <div class="bg-gray-800 rounded-lg overflow-hidden shadow-inner flex flex-col" style="height: 700px;">
                    <div class="bg-gray-900 px-4 py-2 flex justify-between items-center text-gray-400 text-sm">
                        <span>Timetable Preview</span>
                        <span>Uploaded: <?php echo date('M d, Y h:i A', strtotime($timetable['created_at'])); ?></span>
                    </div>
                    <iframe src="../<?php echo htmlspecialchars($timetable['file_path']); ?>" width="100%" height="100%" class="border-0 bg-white" title="Timetable PDF">
                        This browser does not support PDFs. Please download the PDF to view it.
                    </iframe>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
