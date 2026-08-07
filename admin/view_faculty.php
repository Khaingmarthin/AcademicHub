<?php
require_once '../config/session.php';
require_admin();
require_once '../config/db.php';
require_once '../config/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch faculty with course count
$stmt = $pdo->prepare("
    SELECT f.*, COUNT(c.id) as course_count 
    FROM faculties f 
    LEFT JOIN courses c ON f.id = c.faculty_id 
    WHERE f.id = :id 
    GROUP BY f.id
");
$stmt->execute(['id' => $id]);
$faculty = $stmt->fetch();

if (!$faculty) {
    set_flash_message('error', 'Faculty not found.');
    header('Location: faculties.php');
    exit;
}

// Determine type classification
$academic_codes = ['FCS', 'FIS', 'FCST', 'ITSM', 'PHYSICS', 'LANGUAGE', 'MATH'];
$code = strtoupper($faculty['faculty_code']);
$is_academic = in_array($code, $academic_codes);
$icon = $is_academic ? 'graduation-cap' : 'building-2';

// Color mapping for badge
$colors = [
    'FCS' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-600', 'border' => 'border-blue-100', 'ring' => 'ring-blue-100'],
    'FIS' => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-600', 'border' => 'border-indigo-100', 'ring' => 'ring-indigo-100'],
    'FCST' => ['bg' => 'bg-purple-50', 'text' => 'text-purple-600', 'border' => 'border-purple-100', 'ring' => 'ring-purple-100'],
    'ITSM' => ['bg' => 'bg-cyan-50', 'text' => 'text-cyan-600', 'border' => 'border-cyan-100', 'ring' => 'ring-cyan-100'],
    'PHYSICS' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'border' => 'border-emerald-100', 'ring' => 'ring-emerald-100'],
    'LANGUAGE' => ['bg' => 'bg-orange-50', 'text' => 'text-orange-600', 'border' => 'border-orange-100', 'ring' => 'ring-orange-100'],
    'MATH' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'border' => 'border-amber-100', 'ring' => 'ring-amber-100'],
    'ADMIN' => ['bg' => 'bg-slate-50', 'text' => 'text-slate-600', 'border' => 'border-slate-100', 'ring' => 'ring-slate-100'],
    'FINANCE' => ['bg' => 'bg-green-50', 'text' => 'text-green-600', 'border' => 'border-green-100', 'ring' => 'ring-green-100'],
    'STUDENT_AFFAIRS' => ['bg' => 'bg-teal-50', 'text' => 'text-teal-600', 'border' => 'border-teal-100', 'ring' => 'ring-teal-100'],
    'LIBRARY' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-600', 'border' => 'border-rose-100', 'ring' => 'ring-rose-100'],
];
$badgeColors = $colors[$code] ?? ['bg' => 'bg-gray-50', 'text' => 'text-gray-600', 'border' => 'border-gray-100', 'ring' => 'ring-gray-100'];
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>
<?php include '../includes/navbar.php'; ?>

<div class="min-h-screen bg-[#F8FAFC] pb-12">
    <div class="max-w-[1000px] mx-auto px-4 sm:px-6 lg:px-8 pt-8">

        <!-- Breadcrumb & Back Navigation -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3 text-sm">
                <a href="faculties.php" class="inline-flex items-center gap-1.5 text-gray-500 hover:text-[#2563EB] font-medium transition-colors duration-200 group">
                    <i data-lucide="arrow-left" class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform duration-200"></i>
                    Back to Faculties
                </a>
                <span class="text-gray-300">/</span>
                <span class="text-gray-700 font-semibold"><?php echo htmlspecialchars($faculty['faculty_name']); ?></span>
            </div>
            <div class="flex items-center gap-2">
                <a href="edit_faculty.php?id=<?php echo $id; ?>"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-bold rounded-xl shadow-sm hover:shadow-md transition-all duration-200">
                    <i data-lucide="pencil" class="w-4 h-4"></i>
                    Edit Faculty
                </a>
            </div>
        </div>

        <!-- Faculty Header Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
            <div class="p-6 sm:p-8">
                <div class="flex flex-col sm:flex-row sm:items-center gap-5">
                    <!-- Faculty Icon -->
                    <div class="w-16 h-16 rounded-2xl <?php echo $badgeColors['bg']; ?> <?php echo $badgeColors['text']; ?> flex items-center justify-center flex-shrink-0 ring-4 <?php echo $badgeColors['ring']; ?>">
                        <i data-lucide="<?php echo $icon; ?>" class="w-8 h-8"></i>
                    </div>
                    <!-- Faculty Title -->
                    <div class="flex-1 min-w-0">
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight leading-tight">
                            <?php echo htmlspecialchars($faculty['faculty_name']); ?>
                        </h1>
                    </div>
                </div>
            </div>
        </div>

        <!-- Details Container -->
        <div class="space-y-6 mb-6">
            <?php if (!empty(trim($faculty['vision']))): ?>
            <!-- Vision Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                    <div class="w-8 h-8 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center">
                        <i data-lucide="eye" class="w-4 h-4"></i>
                    </div>
                    <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Vision</h2>
                </div>
                <div class="p-6">
                    <p class="text-base text-gray-600 leading-relaxed whitespace-pre-line"><?php echo htmlspecialchars(trim($faculty['vision'])); ?></p>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty(trim($faculty['mission']))): ?>
            <!-- Mission Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                    <div class="w-8 h-8 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center">
                        <i data-lucide="target" class="w-4 h-4"></i>
                    </div>
                    <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Mission</h2>
                </div>
                <div class="p-6">
                    <p class="text-base text-gray-600 leading-relaxed whitespace-pre-line"><?php echo htmlspecialchars(trim($faculty['mission'])); ?></p>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty(trim($faculty['description']))): ?>
            <!-- Description Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                    <div class="w-8 h-8 bg-emerald-50 text-emerald-600 rounded-lg flex items-center justify-center">
                        <i data-lucide="file-text" class="w-4 h-4"></i>
                    </div>
                    <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Description</h2>
                </div>
                <div class="p-6">
                    <p class="text-base text-gray-600 leading-relaxed whitespace-pre-line"><?php echo htmlspecialchars(trim($faculty['description'])); ?></p>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (empty(trim($faculty['vision'])) && empty(trim($faculty['mission'])) && empty(trim($faculty['description']))): ?>
            <!-- Empty State -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                    <div class="w-8 h-8 bg-gray-50 text-gray-600 rounded-lg flex items-center justify-center">
                        <i data-lucide="file-text" class="w-4 h-4"></i>
                    </div>
                    <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Details</h2>
                </div>
                <div class="p-6">
                    <p class="text-base text-gray-400 italic">No details provided.</p>
                </div>
            </div>
            <?php endif; ?>
        </div>        <!-- Actions Footer -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex flex-col sm:flex-row items-center justify-between gap-4">
            <a href="faculties.php" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-bold rounded-xl transition-all duration-200 w-full sm:w-auto justify-center">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Back to Faculties
            </a>
            <a href="edit_faculty.php?id=<?php echo $id; ?>"
               id="btn_edit_faculty"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#2563EB] hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-sm hover:shadow-md transition-all duration-200 w-full sm:w-auto justify-center">
                <i data-lucide="pencil" class="w-4 h-4"></i>
                Edit Faculty
            </a>
        </div>

    </div>
</div>



<?php include '../includes/footer.php'; ?>
