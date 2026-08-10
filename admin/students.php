<?php
require_once '../config/session.php';
require_admin();
require_once '../config/db.php';
require_once '../config/functions.php';

// Fetch Active Academic Year
$active_ay_id = get_global_active_academic_year($pdo)['id'] ?? 0;
if (!$active_ay_id) {
    $stmt = $pdo->query("SELECT id FROM academic_years WHERE status = 'Active' LIMIT 1");
    $active_ay_id = $stmt->fetchColumn() ?: 0;
}

// Fetch all Academic Years for filter
$academic_years = $pdo->query("SELECT id, year_name FROM academic_years ORDER BY id DESC")->fetchAll();

// Fetch all Majors
$majors = $pdo->query("SELECT id, major_name FROM majors ORDER BY major_name ASC")->fetchAll();

// Fetch all Year Levels
$year_levels = $pdo->query("SELECT id, level_name FROM academic_year_levels ORDER BY id ASC")->fetchAll();

// Fetch all Classrooms for the Add Modal and filters
$classrooms_all = $pdo->query("SELECT id, major_id, academic_year_id, academic_year_level_id, classroom_name FROM classrooms ORDER BY classroom_name ASC")->fetchAll();

// Fetch Students with relations
$stmt = $pdo->query("
    SELECT 
        u.*, 
        c.classroom_name, 
        c.academic_year_id,
        c.major_id,
        c.academic_year_level_id,
        ay.year_name as academic_year,
        m.major_name as major,
        ayl.level_name as year_level
    FROM users u
    LEFT JOIN classrooms c ON u.classroom_id = c.id
    LEFT JOIN academic_years ay ON c.academic_year_id = ay.id
    LEFT JOIN majors m ON c.major_id = m.id
    LEFT JOIN academic_year_levels ayl ON c.academic_year_level_id = ayl.id
    WHERE u.role = 'student'
    ORDER BY u.created_at DESC
");
$students = $stmt->fetchAll();

$csrf_token = generate_csrf_token();
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>
<?php include '../includes/navbar.php'; ?>

<!-- Table styles use Tailwind auto-layout -->

<div class="min-h-screen bg-[#F8FAFC] pb-12 relative z-0">
    <div class="w-full px-4 sm:px-6 lg:px-8 pt-8">
        
        <!-- Page Header -->
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Student Management</h1>
                <p class="mt-2 text-sm text-gray-600 font-medium">Manage registered students for the current academic year.</p>
            </div>
            
            <div class="flex gap-2">
                <a href="promote_students.php" class="inline-flex items-center gap-2 px-4 py-2 border border-gray-200 bg-white text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-50 transition-colors shadow-sm">
                    <i data-lucide="arrow-up-right" class="w-4 h-4 text-purple-600"></i> Promote Students
                </a>
                <button onclick="openImportModal()" class="inline-flex items-center gap-2 px-4 py-2 border border-gray-200 bg-white text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-50 transition-colors shadow-sm">
                    <i data-lucide="upload" class="w-4 h-4 text-emerald-600"></i> Import
                </button>
                <button onclick="exportCSV()" class="inline-flex items-center gap-2 px-4 py-2 border border-gray-200 bg-white text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-50 transition-colors shadow-sm">
                    <i data-lucide="download" class="w-4 h-4 text-indigo-600"></i> Export
                </button>
            </div>
        </div>

        <!-- Success Alert -->
        <div id="top_success_alert" class="hidden items-center justify-between px-5 py-3 rounded-xl bg-green-50/90 border border-green-200 shadow-sm backdrop-blur-sm transition-all duration-300 mb-6">
                <div class="flex items-center gap-3">
                    <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
                    <p class="text-sm font-semibold text-green-700" id="top_success_message">Success!</p>
                </div>
            </div>

        <!-- Toolbar & Filters -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 mb-6 w-full relative z-20">
                <div class="flex flex-col md:flex-row flex-wrap items-start md:items-center gap-4 w-full">
                    
                    <!-- Search Input -->
                    <div class="relative w-full md:w-[calc(50%-0.5rem)] lg:w-auto lg:flex-1 min-w-[200px]">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="search" class="w-4 h-4 text-gray-400"></i>
                        </div>
                        <input type="text" id="filter_search" placeholder="Search by Roll Number, Name or Email..." 
                            class="block w-full pl-9 pr-3 py-2 border border-gray-200 rounded-xl bg-gray-50 placeholder-gray-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] text-sm font-medium transition-colors text-gray-900 h-[42px]">
                    </div>
                    
                    <!-- Dropdown Filters -->
                    <div class="flex flex-col sm:flex-row flex-wrap gap-3 w-full md:w-[calc(50%-0.5rem)] lg:w-auto lg:flex-[1.5] min-w-[250px]">
                        <div class="relative w-full sm:flex-1 min-w-[120px]">
                            <select id="filter_year" class="block w-full py-2 pl-3 pr-8 border border-gray-200 bg-gray-50 rounded-xl text-sm font-medium text-gray-700 focus:bg-white focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors cursor-pointer appearance-none h-[42px]">
                                <option value="">Year Level</option>
                                <?php foreach ($year_levels as $yl): ?>
                                <option value="<?php echo $yl['id']; ?>"><?php echo htmlspecialchars($yl['level_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400"><i data-lucide="chevron-down" class="w-4 h-4"></i></div>
                        </div>
                        
                        <div class="relative w-full sm:flex-1 min-w-[120px]">
                            <select id="filter_major" class="block w-full py-2 pl-3 pr-8 border border-gray-200 bg-gray-50 rounded-xl text-sm font-medium text-gray-700 focus:bg-white focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors cursor-pointer appearance-none h-[42px]">
                                <option value="">Major</option>
                                <?php foreach ($majors as $m): ?>
                                <option value="<?php echo $m['id']; ?>"><?php echo htmlspecialchars($m['major_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400"><i data-lucide="chevron-down" class="w-4 h-4"></i></div>
                        </div>

                        <div class="relative w-full sm:flex-1 min-w-[120px]">
                            <select id="filter_classroom" class="block w-full py-2 pl-3 pr-8 border border-gray-200 bg-gray-50 rounded-xl text-sm font-medium text-gray-700 focus:bg-white focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors cursor-pointer appearance-none h-[42px]">
                                <option value="">Classroom</option>
                                <?php foreach ($classrooms_all as $cl): ?>
                                <option value="<?php echo $cl['id']; ?>" data-major="<?php echo $cl['major_id']; ?>" data-ay="<?php echo $cl['academic_year_id']; ?>" data-year="<?php echo $cl['academic_year_level_id']; ?>"><?php echo htmlspecialchars($cl['classroom_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400"><i data-lucide="chevron-down" class="w-4 h-4"></i></div>
                        </div>
                        
                        <!-- Hidden filters -->
                        <input type="hidden" id="filter_ay" value="">
                        <input type="hidden" id="filter_status" value="">
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full md:w-full lg:w-auto lg:flex-shrink-0 lg:ml-auto mt-2 md:mt-0">
                        <button id="btn_search_filter" class="w-full sm:w-auto px-4 py-2 bg-[#2563EB] hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-sm transition-colors flex items-center justify-center gap-2 h-[42px]">
                            <i data-lucide="search" class="w-4 h-4"></i> Search
                        </button>
                        <button id="btn_reset_filter" class="w-full sm:w-auto px-4 py-2 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 text-sm font-bold rounded-xl shadow-sm transition-colors flex items-center justify-center gap-2 h-[42px]">
                            <i data-lucide="rotate-ccw" class="w-4 h-4"></i> Reset
                        </button>
                        <div class="hidden lg:block w-px h-8 bg-gray-200 mx-1 self-center"></div>
                        <button onclick="openAddModal()" class="w-full sm:w-auto px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl shadow-sm transition-colors flex items-center justify-center gap-2 h-[42px]">
                            <i data-lucide="plus" class="w-4 h-4"></i> Add Student
                        </button>
                    </div>
                </div>
            </div>

        <!-- Student List Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 w-full mb-6">
                <div class="w-full rounded-xl">
                    <table id="student-table" class="w-full text-left border-collapse table-auto md:table-fixed min-w-0">
                        <thead>
                            <tr class="bg-gray-50/50 border-b border-gray-100">
                                <th class="sticky top-0 bg-gray-50/90 backdrop-blur-sm px-2 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider z-10 cursor-pointer hover:bg-gray-200 transition-colors select-none group md:w-[10%]" onclick="sortTable('roll')">
                                    <div class="flex items-center gap-1">Roll No. <i data-lucide="chevrons-up-down" class="w-3 h-3 text-gray-400 group-hover:text-gray-600 transition-colors"></i></div>
                                </th>
                                <th class="sticky top-0 bg-gray-50/90 backdrop-blur-sm px-2 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider z-10 cursor-pointer hover:bg-gray-200 transition-colors select-none group md:w-[20%]" onclick="sortTable('name')">
                                    <div class="flex items-center gap-1">Name <i data-lucide="chevrons-up-down" class="w-3 h-3 text-gray-400 group-hover:text-gray-600 transition-colors"></i></div>
                                </th>
                                <th class="sticky top-0 bg-gray-50/90 backdrop-blur-sm px-2 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider z-10 md:w-[18%]">Email</th>
                                <th class="sticky top-0 bg-gray-50/90 backdrop-blur-sm px-2 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider z-10 md:w-[11%]">Academic Year</th>
                                <th class="sticky top-0 bg-gray-50/90 backdrop-blur-sm px-2 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider z-10 md:w-[10%]">Year Level</th>
                                <th class="sticky top-0 bg-gray-50/90 backdrop-blur-sm px-2 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider z-10 md:w-[10%]">Major</th>
                                <th class="sticky top-0 bg-gray-50/90 backdrop-blur-sm px-2 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider z-10 md:w-[11%]">Classroom</th>
                                <th class="sticky top-0 bg-gray-50/90 backdrop-blur-sm px-2 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider z-10 text-right w-[100px] md:w-[100px]">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="student_grid" class="divide-y divide-gray-100">
                            <?php foreach ($students as $s): 
                                $initials = strtoupper(substr($s['username'], 0, 1));
                            ?>
                            <tr class="student-card hover:bg-gray-50 transition-colors group"
                                data-search="<?php echo strtolower(htmlspecialchars($s['username'] . ' ' . $s['student_id'] . ' ' . $s['roll_number'] . ' ' . $s['email'] . ' ' . $s['classroom_name'])); ?>"
                                data-ay="<?php echo $s['academic_year_id']; ?>"
                                data-major="<?php echo $s['major_id']; ?>"
                                data-year="<?php echo $s['academic_year_level_id']; ?>"
                                data-classroom="<?php echo $s['classroom_id']; ?>"
                                data-status="<?php echo htmlspecialchars($s['status']); ?>"
                                data-sid="<?php echo htmlspecialchars($s['student_id']); ?>"
                                data-roll="<?php echo htmlspecialchars($s['roll_number']); ?>"
                                data-name="<?php echo htmlspecialchars($s['username']); ?>">
                                
                                <td class="px-2 py-3 text-sm font-medium text-gray-900 truncate" title="<?php echo htmlspecialchars($s['roll_number'] ?: 'N/A'); ?>">
                                    <?php echo htmlspecialchars($s['roll_number'] ?: 'N/A'); ?>
                                </td>
                                <td class="px-2 py-3">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <?php if (!empty($s['avatar'])): ?>
                                            <img src="../<?php echo htmlspecialchars($s['avatar']); ?>" class="w-7 h-7 rounded-full object-cover border border-gray-200 shadow-sm flex-shrink-0">
                                        <?php else: ?>
                                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-[10px] shadow-sm border border-gray-200 flex-shrink-0">
                                                <?php echo $initials; ?>
                                            </div>
                                        <?php endif; ?>
                                        <span class="font-bold text-gray-900 text-sm truncate flex-1" title="<?php echo htmlspecialchars($s['username']); ?>"><?php echo htmlspecialchars($s['username']); ?></span>
                                    </div>
                                </td>
                                <td class="px-2 py-3 text-sm text-gray-500 truncate" title="<?php echo htmlspecialchars($s['email']); ?>">
                                    <?php echo htmlspecialchars($s['email']); ?>
                                </td>
                                <td class="px-2 py-3 text-sm text-gray-600 truncate" title="<?php echo htmlspecialchars($s['academic_year'] ?: 'N/A'); ?>">
                                    <?php echo htmlspecialchars($s['academic_year'] ?: 'N/A'); ?>
                                </td>
                                <td class="px-2 py-3 text-sm text-gray-600 truncate" title="<?php echo htmlspecialchars($s['year_level'] ?: 'N/A'); ?>">
                                    <?php echo htmlspecialchars($s['year_level'] ?: 'N/A'); ?>
                                </td>
                                <td class="px-2 py-3 text-sm text-gray-600 truncate" title="<?php echo htmlspecialchars($s['major'] ?: 'N/A'); ?>">
                                    <?php echo htmlspecialchars($s['major'] ?: 'N/A'); ?>
                                </td>
                                <td class="px-2 py-3 text-sm text-gray-600 font-medium truncate" title="<?php echo htmlspecialchars($s['classroom_name'] ?: 'No Class'); ?>">
                                    <?php echo htmlspecialchars($s['classroom_name'] ?: 'No Class'); ?>
                                </td>
                                <td class="px-2 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1 transition-opacity">
                                        <button onclick='viewStudent(<?php echo json_encode($s); ?>)' class="p-2 rounded-lg text-gray-500 hover:bg-gray-200 transition-colors" title="View">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </button>
                                        <button onclick='openEditModal(<?php echo json_encode($s); ?>)' class="p-2 rounded-lg text-blue-600 hover:bg-blue-100 transition-colors" title="Edit">
                                            <i data-lucide="pencil" class="w-4 h-4"></i>
                                        </button>
                                        <button onclick="requestDeleteStudent(<?php echo $s['id']; ?>, '<?php echo addslashes($s['username']); ?>')" class="p-2 rounded-lg text-red-600 hover:bg-red-100 transition-colors" title="Delete">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Table Footer (Pagination) -->
                <div id="table_pagination" class="bg-white rounded-b-xl w-full px-6 py-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4 hidden">
                    <div class="flex items-center gap-4 text-sm text-gray-500">
                        <div id="pagination_info">Showing <span class="font-bold text-gray-900">0</span> to <span class="font-bold text-gray-900">0</span> of <span class="font-bold text-gray-900">0</span> students</div>
                        <div class="h-4 w-px bg-gray-300 hidden sm:block"></div>
                        <div class="flex items-center gap-2">
                            <label for="rows_per_page">Rows per page:</label>
                            <select id="rows_per_page" class="border border-gray-200 rounded-lg text-sm py-1 pl-2 pr-6 focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] cursor-pointer outline-none">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-1" id="pagination_controls">
                        <!-- Pagination buttons generated via JS -->
                    </div>
                </div>
            </div>

            <!-- Empty State -->
        <div id="search_empty_state" class="hidden flex-col items-center justify-center min-h-[400px] py-16 px-4 bg-white rounded-xl shadow-sm border border-gray-100 w-full text-center mb-6">
                <!-- Friendly Illustration -->
                <div class="mb-6 relative">
                    <div class="absolute inset-0 bg-blue-100 rounded-full blur-xl opacity-60"></div>
                    <div class="w-24 h-24 bg-blue-50 border border-blue-100 rounded-full flex items-center justify-center relative z-10 mx-auto">
                        <i data-lucide="search-x" class="w-10 h-10 text-[#2563EB]"></i>
                    </div>
                </div>
                
                <!-- Text Content -->
                <h3 class="text-xl font-bold text-gray-900 mb-2">No Students Found</h3>
                <p class="text-base text-gray-500 mb-8 max-w-sm">No students match your search criteria.</p>
                
                <!-- Action Button -->
                <button onclick="document.getElementById('btn_reset_filter').click()" class="inline-flex items-center justify-center px-6 py-2.5 rounded-xl border border-transparent bg-[#2563EB] text-sm font-bold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#2563EB] transition-colors gap-2 shadow-sm">
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                    Clear Filters
                </button>
            </div>
            
            <?php if (count($students) === 0): ?>
        <div class="flex flex-col items-center justify-center min-h-[400px] py-16 px-4 bg-white rounded-xl shadow-sm border border-gray-100 w-full text-center mb-6">
                <div class="mb-6 relative">
                    <div class="absolute inset-0 bg-blue-100 rounded-full blur-xl opacity-60"></div>
                    <div class="w-24 h-24 bg-blue-50 border border-blue-100 rounded-full flex items-center justify-center relative z-10 mx-auto">
                        <i data-lucide="users" class="w-10 h-10 text-[#2563EB]"></i>
                    </div>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">No Students Enrolled</h3>
                <p class="text-base text-gray-500 mb-8 max-w-sm">Start by adding a new student to your academic hub.</p>
                <button onclick="openAddModal()" class="inline-flex items-center justify-center px-6 py-2.5 rounded-xl border border-transparent bg-[#2563EB] text-sm font-bold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#2563EB] transition-colors gap-2 shadow-sm">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Add Student
                </button>
            </div>
            <?php endif; ?>

    </div>
</div>

<!-- Edit Drawer -->
<div id="modal_edit" class="fixed inset-0 z-50 hidden" aria-labelledby="drawer-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeModal('modal_edit')"></div>
    
    <div class="fixed inset-y-0 right-0 max-w-2xl w-full bg-[#F8FAFC] shadow-2xl flex flex-col overflow-y-auto border-l border-gray-100 animate-slide-in-right">
        
        <div class="bg-white px-6 py-5 border-b border-gray-100 flex items-center justify-between sticky top-0 z-10">
            <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                <i data-lucide="pencil" class="w-5 h-5 text-gray-500"></i>
                Edit Student Profile
            </h3>
            <button type="button" onclick="closeModal('modal_edit')" class="p-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-50 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="form_edit_student" onsubmit="submitEditStudent(event)" class="flex-1 flex flex-col">
            <input type="hidden" id="edit_id" name="id">
            
            <div class="p-6 space-y-8 flex-1">
                <!-- Section 1: Personal Information -->
                <div>
                    <h4 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-md bg-blue-50 text-blue-600 flex items-center justify-center text-xs">1</span>
                        Personal Information
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Student ID (Read-only)</label>
                            <input type="text" id="edit_student_id" name="student_id" disabled class="block w-full px-3 py-2 border border-gray-200 bg-gray-100 rounded-lg text-sm font-medium text-gray-500 cursor-not-allowed">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Roll Number <span class="text-red-500">*</span></label>
                            <input type="text" id="edit_roll_number" name="roll_number" class="block w-full px-3 py-2 border border-gray-200 bg-white rounded-lg text-sm font-medium text-gray-900 focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors">
                            <p class="text-red-500 text-[11px] mt-1 hidden" id="err_edit_roll_number">Roll Number is required.</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Student Name <span class="text-red-500">*</span></label>
                            <input type="text" id="edit_username" name="username" class="block w-full px-3 py-2 border border-gray-200 bg-white rounded-lg text-sm font-medium text-gray-900 focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors">
                            <p class="text-red-500 text-[11px] mt-1 hidden" id="err_edit_username">Student Name is required.</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Email Address <span class="text-red-500">*</span></label>
                            <input type="email" id="edit_email" name="email" class="block w-full px-3 py-2 border border-gray-200 bg-white rounded-lg text-sm font-medium text-gray-900 focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors">
                            <p class="text-red-500 text-[11px] mt-1 hidden" id="err_edit_email">Valid Email is required.</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Phone Number</label>
                            <input type="text" id="edit_phone" name="phone" placeholder="09xxxxxxxxx" class="block w-full px-3 py-2 border border-gray-200 bg-white rounded-lg text-sm font-medium text-gray-900 focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors">
                        </div>
                    </div>
                </div>

                <!-- Section 2: Academic Information -->
                <div>
                    <h4 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-md bg-blue-50 text-blue-600 flex items-center justify-center text-xs">2</span>
                        Academic Information
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Academic Year <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select id="edit_academic_year" class="block w-full py-2 pl-3 pr-10 border border-gray-200 bg-white rounded-lg text-sm font-medium text-gray-900 focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors appearance-none cursor-pointer" onchange="filterEditClassrooms()">
                                    <option value="">Select Academic Year...</option>
                                    <?php foreach ($academic_years as $ay): ?>
                                    <option value="<?php echo $ay['id']; ?>"><?php echo htmlspecialchars($ay['year_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400"><i data-lucide="chevron-down" class="w-4 h-4"></i></div>
                            </div>
                            <p class="text-red-500 text-[11px] mt-1 hidden" id="err_edit_academic_year">Academic Year is required.</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Year Level <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select id="edit_year" name="academic_year_level_id" class="block w-full py-2 pl-3 pr-10 border border-gray-200 bg-white rounded-lg text-sm font-medium text-gray-900 focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors appearance-none cursor-pointer" onchange="filterEditClassrooms()">
                                    <option value="">Select Year Level...</option>
                                    <?php foreach ($year_levels as $yl): ?>
                                    <option value="<?php echo $yl['id']; ?>"><?php echo htmlspecialchars($yl['level_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400"><i data-lucide="chevron-down" class="w-4 h-4"></i></div>
                            </div>
                            <p class="text-red-500 text-[11px] mt-1 hidden" id="err_edit_year">Year Level is required.</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Major <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select id="edit_major" name="major_id" class="block w-full py-2 pl-3 pr-10 border border-gray-200 bg-white rounded-lg text-sm font-medium text-gray-900 focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors appearance-none cursor-pointer" onchange="filterEditClassrooms()">
                                    <option value="">Select Major...</option>
                                    <?php foreach ($majors as $m): ?>
                                    <option value="<?php echo $m['id']; ?>"><?php echo htmlspecialchars($m['major_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400"><i data-lucide="chevron-down" class="w-4 h-4"></i></div>
                            </div>
                            <p class="text-red-500 text-[11px] mt-1 hidden" id="err_edit_major">Major is required.</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Classroom <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select id="edit_classroom_id" name="classroom_id" class="block w-full py-2 pl-3 pr-10 border border-gray-200 bg-white rounded-lg text-sm font-medium text-gray-900 focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors appearance-none cursor-pointer">
                                    <option value="">Select Classroom...</option>
                                    <?php foreach ($classrooms_all as $cl): ?>
                                    <option value="<?php echo $cl['id']; ?>" data-ay="<?php echo $cl['academic_year_id']; ?>" data-major="<?php echo $cl['major_id']; ?>" data-year="<?php echo $cl['academic_year_level_id']; ?>">
                                        <?php echo htmlspecialchars($cl['classroom_name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400"><i data-lucide="chevron-down" class="w-4 h-4"></i></div>
                            </div>
                            <p class="text-red-500 text-[11px] mt-1 hidden" id="err_edit_classroom_id">Classroom is required.</p>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Account -->
                <div>
                    <h4 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-md bg-blue-50 text-blue-600 flex items-center justify-center text-xs">3</span>
                        Account Information
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Username <span class="text-gray-400 font-normal ml-1">(Optional override)</span></label>
                            <input type="text" id="edit_account_username" name="account_username" class="block w-full px-3 py-2 border border-gray-200 bg-white rounded-lg text-sm font-medium text-gray-900 focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">New Password <span class="text-gray-400 font-normal ml-1">(Leave blank to keep current)</span></label>
                            <div class="relative">
                                <input type="password" id="edit_password" name="password" placeholder="******" class="block w-full px-3 py-2 border border-gray-200 bg-white rounded-lg text-sm font-medium text-gray-900 focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors pr-10">
                                <button type="button" onclick="togglePasswordVisibility('edit_password', 'icon_edit_pass')" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                                    <i id="icon_edit_pass" data-lucide="eye" class="w-4 h-4"></i>
                                </button>
                            </div>
                            <p class="text-red-500 text-[11px] mt-1 hidden" id="err_edit_password">Password must be at least 6 characters.</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Confirm New Password</label>
                            <div class="relative">
                                <input type="password" id="edit_password_confirm" name="password_confirm" placeholder="******" class="block w-full px-3 py-2 border border-gray-200 bg-white rounded-lg text-sm font-medium text-gray-900 focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors pr-10">
                                <button type="button" onclick="togglePasswordVisibility('edit_password_confirm', 'icon_edit_pass_conf')" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                                    <i id="icon_edit_pass_conf" data-lucide="eye" class="w-4 h-4"></i>
                                </button>
                            </div>
                            <p class="text-red-500 text-[11px] mt-1 hidden" id="err_edit_password_confirm">Passwords do not match.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex items-center justify-end gap-3 sticky bottom-0 mt-auto">
                <button type="button" onclick="closeModal('modal_edit')" class="px-6 py-2.5 rounded-xl border border-gray-200 bg-white text-sm font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200 transition-colors">
                    Cancel
                </button>
                <button type="submit" id="btn_submit_edit" class="px-6 py-2.5 rounded-xl border border-transparent bg-[#2563EB] text-sm font-bold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#2563EB] transition-colors flex items-center gap-2 shadow-sm">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Add Student Drawer -->
<div id="modal_form" class="fixed inset-0 z-50 hidden" aria-labelledby="drawer-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeModal('modal_form')"></div>
    
    <div class="fixed inset-y-0 right-0 max-w-2xl w-full bg-[#F8FAFC] shadow-2xl flex flex-col overflow-y-auto border-l border-gray-100 animate-slide-in-right">
        
        <div class="bg-white px-6 py-5 border-b border-gray-100 flex items-center justify-between sticky top-0 z-10">
            <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2" id="form_title">
                <i id="form_icon" data-lucide="user-plus" class="w-5 h-5 text-gray-500"></i>
                Add Student
            </h3>
            <button type="button" onclick="closeModal('modal_form')" class="p-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-50 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="form_student" onsubmit="validateAndSubmitStudent(event)" class="flex-1 flex flex-col">
            <div class="p-6 space-y-8 flex-1">
                <!-- Section 1: Student Information -->
                <div>
                    <h4 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-md bg-blue-50 text-blue-600 flex items-center justify-center text-xs">1</span>
                        Student Information
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Roll Number <span class="text-red-500">*</span></label>
                            <input type="text" id="add_roll_number" name="roll_number" placeholder="e.g. 1CS-023" class="block w-full px-3 py-2 border border-gray-200 bg-white rounded-lg text-sm font-medium text-gray-900 focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors">
                            <p class="text-red-500 text-[11px] mt-1 hidden" id="err_add_roll_number">Roll Number is required.</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Full Name <span class="text-red-500">*</span></label>
                            <input type="text" id="add_username" name="username" placeholder="Student Full Name" class="block w-full px-3 py-2 border border-gray-200 bg-white rounded-lg text-sm font-medium text-gray-900 focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors">
                            <p class="text-red-500 text-[11px] mt-1 hidden" id="err_add_username">Full Name is required.</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Email Address <span class="text-red-500">*</span></label>
                            <input type="email" id="add_email" name="email" placeholder="student@example.com" class="block w-full px-3 py-2 border border-gray-200 bg-white rounded-lg text-sm font-medium text-gray-900 focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors">
                            <p class="text-red-500 text-[11px] mt-1 hidden" id="err_add_email">Valid Email is required.</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Password <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="password" id="add_password" name="password" placeholder="******" class="block w-full px-3 py-2 border border-gray-200 bg-white rounded-lg text-sm font-medium text-gray-900 focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors pr-10">
                                <button type="button" onclick="togglePasswordVisibility('add_password', 'icon_add_pass')" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                                    <i id="icon_add_pass" data-lucide="eye" class="w-4 h-4"></i>
                                </button>
                            </div>
                            <p class="text-red-500 text-[11px] mt-1 hidden" id="err_add_password">Password must be at least 6 characters.</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Confirm Password <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="password" id="add_password_confirm" name="password_confirm" placeholder="******" class="block w-full px-3 py-2 border border-gray-200 bg-white rounded-lg text-sm font-medium text-gray-900 focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors pr-10">
                                <button type="button" onclick="togglePasswordVisibility('add_password_confirm', 'icon_add_pass_conf')" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                                    <i id="icon_add_pass_conf" data-lucide="eye" class="w-4 h-4"></i>
                                </button>
                            </div>
                            <p class="text-red-500 text-[11px] mt-1 hidden" id="err_add_password_confirm">Passwords must match.</p>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Academic Information -->
                <div>
                    <h4 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-md bg-blue-50 text-blue-600 flex items-center justify-center text-xs">2</span>
                        Academic Information
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Academic Year <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select id="add_academic_year" name="academic_year_id" class="block w-full py-2 pl-3 pr-10 border border-gray-200 bg-white rounded-lg text-sm font-medium text-gray-900 focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors appearance-none cursor-pointer" onchange="filterAddClassrooms()">
                                    <option value="">Select Academic Year...</option>
                                    <?php foreach ($academic_years as $ay): ?>
                                    <option value="<?php echo $ay['id']; ?>" <?php echo $ay['id'] == $active_ay_id ? 'selected' : ''; ?>><?php echo htmlspecialchars($ay['year_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400"><i data-lucide="chevron-down" class="w-4 h-4"></i></div>
                            </div>
                            <p class="text-red-500 text-[11px] mt-1 hidden" id="err_add_academic_year">Academic Year is required.</p>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Year Level <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select id="add_year_level" name="academic_year_level_id" class="block w-full py-2 pl-3 pr-10 border border-gray-200 bg-white rounded-lg text-sm font-medium text-gray-900 focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors appearance-none cursor-pointer" onchange="filterAddClassrooms()">
                                    <option value="">Select Year Level...</option>
                                    <?php foreach ($year_levels as $yl): ?>
                                    <option value="<?php echo $yl['id']; ?>"><?php echo htmlspecialchars($yl['level_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400"><i data-lucide="chevron-down" class="w-4 h-4"></i></div>
                            </div>
                            <p class="text-red-500 text-[11px] mt-1 hidden" id="err_add_year_level">Year Level is required.</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Major <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select id="add_major" name="major_id" class="block w-full py-2 pl-3 pr-10 border border-gray-200 bg-white rounded-lg text-sm font-medium text-gray-900 focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors appearance-none cursor-pointer" onchange="filterAddClassrooms()">
                                    <option value="">Select Major...</option>
                                    <?php foreach ($majors as $m): ?>
                                    <option value="<?php echo $m['id']; ?>"><?php echo htmlspecialchars($m['major_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400"><i data-lucide="chevron-down" class="w-4 h-4"></i></div>
                            </div>
                            <p class="text-red-500 text-[11px] mt-1 hidden" id="err_add_major">Major is required.</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Classroom / Section <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select id="add_classroom_id" name="classroom_id" class="block w-full py-2 pl-3 pr-10 border border-gray-200 bg-white rounded-lg text-sm font-medium text-gray-900 focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors appearance-none cursor-pointer">
                                    <option value="">Select Classroom...</option>
                                    <?php foreach ($classrooms_all as $cl): ?>
                                    <option value="<?php echo $cl['id']; ?>" data-ay="<?php echo $cl['academic_year_id']; ?>" data-major="<?php echo $cl['major_id']; ?>" data-year="<?php echo $cl['academic_year_level_id']; ?>">
                                        <?php echo htmlspecialchars($cl['classroom_name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400"><i data-lucide="chevron-down" class="w-4 h-4"></i></div>
                            </div>
                            <p class="text-red-500 text-[11px] mt-1 hidden" id="err_add_classroom_id">Classroom is required.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex items-center justify-end gap-3 sticky bottom-0 mt-auto">
                <button type="button" onclick="closeModal('modal_form')" class="px-6 py-2.5 rounded-xl border border-gray-200 bg-white text-sm font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200 transition-colors">
                    Cancel
                </button>
                <button type="submit" id="btn_submit_add" class="px-6 py-2.5 rounded-xl border border-transparent bg-[#2563EB] text-sm font-bold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#2563EB] transition-colors flex items-center gap-2 shadow-sm">
                    Create Student
                </button>
            </div>
        </form>
    </div>
</div>

<!-- View Drawer -->
<div id="modal_view" class="fixed inset-0 z-50 hidden" aria-labelledby="drawer-title" role="dialog" aria-modal="true">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeModal('modal_view')"></div>
    
    <!-- Drawer Panel -->
    <div class="fixed inset-y-0 right-0 max-w-md w-full bg-[#F8FAFC] shadow-2xl flex flex-col overflow-y-auto border-l border-gray-100 animate-slide-in-right">
        
        <!-- Header -->
        <div class="bg-white px-6 py-5 border-b border-gray-100 flex items-center justify-between sticky top-0 z-10">
            <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                <i data-lucide="user" class="w-5 h-5 text-gray-500"></i>
                Student Profile
            </h3>
            <button type="button" onclick="closeModal('modal_view')" class="p-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-50 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Content -->
        <div class="p-6 space-y-6 flex-1">
            <!-- Top Avatar/Header inside drawer -->
            <div class="flex items-center gap-4 mb-2">
                <div id="view_avatar" class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-2xl shadow-sm border-2 border-white flex-shrink-0"></div>
                <div class="overflow-hidden">
                    <h3 class="text-xl font-bold text-gray-900 truncate" id="view_name">Name</h3>
                    <div class="flex items-center gap-2 mt-1 flex-wrap">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold tracking-widest bg-blue-50 text-[#2563EB] border border-blue-100" id="view_id">ID</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border" id="view_status_badge">Active</span>
                    </div>
                </div>
            </div>
            
            <!-- Personal Information -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Personal Information</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-4">
                    <div>
                        <span class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Student ID</span>
                        <span class="text-sm font-semibold text-gray-900" id="view_student_id_val">-</span>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Roll Number</span>
                        <span class="text-sm font-semibold text-gray-900" id="view_roll_number_val">-</span>
                    </div>
                    <div class="sm:col-span-2">
                        <span class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Full Name</span>
                        <span class="text-sm font-semibold text-gray-900" id="view_fullname_val">-</span>
                    </div>
                    <div class="sm:col-span-2">
                        <span class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Email</span>
                        <span class="text-sm font-semibold text-gray-900" id="view_email">-</span>
                    </div>
                    <div class="sm:col-span-2">
                        <span class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Phone Number</span>
                        <span class="text-sm font-semibold text-gray-900" id="view_phone">-</span>
                    </div>
                </div>
            </div>

            <!-- Academic Information -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Academic Information</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-4">
                    <div>
                        <span class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Year Level</span>
                        <span class="text-sm font-semibold text-gray-900" id="view_year_level">-</span>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Major</span>
                        <span class="text-sm font-semibold text-gray-900" id="view_major">-</span>
                    </div>
                    <div class="sm:col-span-2">
                        <span class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Classroom / Section</span>
                        <span class="text-sm font-semibold text-gray-900" id="view_classroom">-</span>
                    </div>
                </div>
            </div>

            <!-- Account Information -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Account Information</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-4">
                    <div>
                        <span class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Username</span>
                        <span class="text-sm font-semibold text-gray-900" id="view_username">-</span>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Created Date</span>
                        <span class="text-sm font-semibold text-gray-900" id="view_created">-</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bg-white px-6 py-4 border-t border-gray-100 sm:flex sm:flex-row-reverse sticky bottom-0 mt-auto">
            <button type="button" onclick="closeModal('modal_view')" class="w-full inline-flex justify-center rounded-xl border border-gray-200 shadow-sm px-6 py-2 bg-white text-base font-bold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200 sm:w-auto sm:text-sm transition-colors">
                Close
            </button>
        </div>
    </div>
</div>
<style>
@keyframes slideInRight {
    from { transform: translateX(100%); }
    to { transform: translateX(0); }
}
.animate-slide-in-right {
    animation: slideInRight 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>

<!-- Import Modal -->
<div id="modal_import" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeModal('modal_import')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full">
            <form id="form_import" onsubmit="submitImport(event)" enctype="multipart/form-data">
                <div class="bg-white px-6 pt-6 pb-4 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-emerald-50 flex items-center justify-center">
                            <i data-lucide="upload" class="w-5 h-5 text-emerald-600"></i>
                        </div>
                        <h3 class="text-xl leading-6 font-bold text-gray-900">Import Students</h3>
                    </div>
                </div>
                
                <div class="bg-white px-6 py-6 space-y-4">
                    <p class="text-sm text-gray-500">Upload a CSV file. The file must have the following columns in order (no headers row needed, but if present it will be imported as well so it is better to delete headers or ensure row 1 is data):</p>
                    <div class="p-3 bg-gray-50 rounded-lg border border-gray-100 font-mono text-xs text-gray-600 overflow-x-auto">
                        Student ID, Full Name, Email, Classroom Name
                    </div>
                    <p class="text-xs text-gray-400">Example: 1CS-01, John Doe, john@test.com, First Year (A)</p>
                    
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">CSV File <span class="text-red-500">*</span></label>
                        <input type="file" name="import_file" accept=".csv" required class="block w-full px-3 py-2.5 border border-gray-200 bg-gray-50 rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors">
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-4 sm:flex sm:flex-row-reverse rounded-b-2xl border-t border-gray-100">
                    <button type="submit" id="btn_import" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-6 py-2.5 bg-emerald-600 text-base font-bold text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-600 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                        Upload
                    </button>
                    <button type="button" onclick="closeModal('modal_import')" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-200 shadow-sm px-6 py-2.5 bg-white text-base font-semibold text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Password Reset Modal -->
<div id="modal_reset" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeModal('modal_reset')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm w-full border-t-4 border-indigo-500">
            <div class="bg-white px-6 pt-6 pb-6 text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-indigo-50 mb-5">
                    <i data-lucide="key-round" class="h-8 w-8 text-indigo-600"></i>
                </div>
                <h3 class="text-xl leading-6 font-bold text-gray-900 mb-2">Reset Password</h3>
                <p class="text-sm text-gray-500 mb-4 px-2">
                    Are you sure you want to reset the password for <strong id="reset_student_name" class="text-gray-900"></strong>? A new temporary password will be generated.
                </p>
                <div id="temp_password_container" class="hidden mt-4 p-4 bg-indigo-50 border border-indigo-100 rounded-lg">
                    <span class="block text-xs font-semibold text-indigo-600 uppercase mb-1">New Temporary Password</span>
                    <span id="new_temp_password" class="text-lg font-mono font-bold text-gray-900 tracking-wider"></span>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 sm:flex sm:flex-row-reverse border-t border-gray-100 rounded-b-2xl">
                <button type="button" id="btn_confirm_reset" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-6 py-2.5 bg-indigo-600 text-base font-bold text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                    Reset Password
                </button>
                <button type="button" onclick="closeModal('modal_reset')" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-200 shadow-sm px-6 py-2.5 bg-white text-base font-semibold text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts for layout and filtering (Modal interactions and AJAX to be implemented) -->
<!-- Delete Confirmation Modal -->
<div id="modal_delete" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeModal('modal_delete')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i data-lucide="alert-triangle" class="h-6 w-6 text-red-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-bold text-gray-900" id="modal-title">Delete Student</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">Are you sure you want to permanently delete this student?</p>
                            <p class="text-sm text-gray-500 font-semibold mt-1">This action cannot be undone.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-100">
                <button type="button" id="btn_confirm_delete" onclick="confirmDeleteStudent()" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors flex items-center gap-2">
                    Delete Student
                </button>
                <button type="button" onclick="closeModal('modal_delete')" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-200 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const csrfToken = '<?php echo $csrf_token; ?>';

function openModal(id) {
    const el = document.getElementById(id);
    el.classList.remove('hidden');
    setTimeout(() => {
        const inner = el.querySelector('div.inline-block');
        if (inner) {
            inner.classList.remove('scale-95', 'opacity-0');
            inner.classList.add('scale-100', 'opacity-100');
        }
    }, 10);
}

function closeModal(id) {
    const el = document.getElementById(id);
    const inner = el.querySelector('div.inline-block');
    if (inner) {
        inner.classList.remove('scale-100', 'opacity-100');
        inner.classList.add('scale-95', 'opacity-0');
    }
    setTimeout(() => {
        el.classList.add('hidden');
    }, 200);
}

document.querySelectorAll('.inline-block.align-bottom').forEach(el => {
    el.classList.add('scale-95', 'opacity-0', 'transition-all', 'duration-200');
});

let currentPage = 1;
let rowsPerPage = 10;
let filteredStudents = [];

function applyFilters() {
    if (!document.getElementById('filter_search')) return;
    
    const term = document.getElementById('filter_search').value.toLowerCase();
    const ayFilter = document.getElementById('filter_ay').value;
    const majorFilter = document.getElementById('filter_major').value;
    const yearFilter = document.getElementById('filter_year').value;
    const classroomFilter = document.getElementById('filter_classroom').value;
    const statusFilter = document.getElementById('filter_status').value;
    
    const cards = Array.from(document.querySelectorAll('.student-card'));
    filteredStudents = [];
    
    cards.forEach(card => {
        const searchData = card.getAttribute('data-search');
        const ayData = card.getAttribute('data-ay');
        const majorData = card.getAttribute('data-major');
        const yearData = card.getAttribute('data-year');
        const classData = card.getAttribute('data-classroom');
        const statusData = card.getAttribute('data-status');
        
        let match = true;
        if (term && !searchData.includes(term)) match = false;
        if (ayFilter && ayData !== ayFilter) match = false;
        if (majorFilter && majorData !== majorFilter) match = false;
        if (yearFilter && yearData !== yearFilter) match = false;
        if (classroomFilter && classData !== classroomFilter) match = false;
        if (statusFilter && statusData !== statusFilter) match = false;
        
        if (match) {
            filteredStudents.push(card);
        } else {
            card.classList.add('hidden');
        }
    });
    
    currentPage = 1;
    renderPagination();
}

function renderPagination() {
    const total = filteredStudents.length;
    
    if (total === 0) {
        document.getElementById('search_empty_state').classList.remove('hidden');
        document.getElementById('search_empty_state').classList.add('flex');
        document.getElementById('table_pagination').classList.add('hidden');
        return;
    }
    
    document.getElementById('search_empty_state').classList.add('hidden');
    document.getElementById('search_empty_state').classList.remove('flex');
    document.getElementById('table_pagination').classList.remove('hidden');
    
    const totalPages = Math.ceil(total / rowsPerPage);
    if (currentPage > totalPages) currentPage = totalPages;
    
    const startIdx = (currentPage - 1) * rowsPerPage;
    const endIdx = Math.min(startIdx + rowsPerPage, total);
    
    filteredStudents.forEach((card, idx) => {
        if (idx >= startIdx && idx < endIdx) {
            card.classList.remove('hidden');
        } else {
            card.classList.add('hidden');
        }
    });
    
    document.getElementById('pagination_info').innerHTML = `Showing <span class="font-bold text-gray-900">${startIdx + 1}</span>–<span class="font-bold text-gray-900">${endIdx}</span> of <span class="font-bold text-gray-900">${total}</span> students`;
    
    let html = '';
    
    // Prev
    html += `<button onclick="changePage(${currentPage - 1})" class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm font-medium ${currentPage === 1 ? 'text-gray-300 cursor-not-allowed bg-gray-50' : 'text-gray-600 hover:bg-gray-50'} transition-colors" ${currentPage === 1 ? 'disabled' : ''}>Previous</button>`;
    
    // Page Numbers
    for (let i = 1; i <= totalPages; i++) {
        if (totalPages > 7) {
            if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                html += `<button onclick="changePage(${i})" class="w-8 h-8 rounded-lg text-sm font-medium flex items-center justify-center transition-colors ${currentPage === i ? 'bg-[#2563EB] text-white border border-[#2563EB]' : 'text-gray-600 border border-gray-200 hover:bg-gray-50'}">${i}</button>`;
            } else if (i === currentPage - 2 || i === currentPage + 2) {
                html += `<span class="px-1 text-gray-400">...</span>`;
            }
        } else {
            html += `<button onclick="changePage(${i})" class="w-8 h-8 rounded-lg text-sm font-medium flex items-center justify-center transition-colors ${currentPage === i ? 'bg-[#2563EB] text-white border border-[#2563EB]' : 'text-gray-600 border border-gray-200 hover:bg-gray-50'}">${i}</button>`;
        }
    }
    
    // Next
    html += `<button onclick="changePage(${currentPage + 1})" class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm font-medium ${currentPage === totalPages ? 'text-gray-300 cursor-not-allowed bg-gray-50' : 'text-gray-600 hover:bg-gray-50'} transition-colors" ${currentPage === totalPages ? 'disabled' : ''}>Next</button>`;
    
    document.getElementById('pagination_controls').innerHTML = html;
}

function changePage(page) {
    if (page < 1) return;
    currentPage = page;
    renderPagination();
}

document.getElementById('filter_search').addEventListener('input', applyFilters);
document.getElementById('filter_ay').addEventListener('change', applyFilters);
document.getElementById('filter_major').addEventListener('change', applyFilters);
document.getElementById('filter_year').addEventListener('change', applyFilters);
document.getElementById('filter_classroom').addEventListener('change', applyFilters);
document.getElementById('filter_status').addEventListener('change', applyFilters);

document.getElementById('btn_search_filter').addEventListener('click', applyFilters);
document.getElementById('btn_reset_filter').addEventListener('click', () => {
    document.getElementById('filter_search').value = '';
    document.getElementById('filter_ay').value = '';
    document.getElementById('filter_year').value = '';
    document.getElementById('filter_major').value = '';
    document.getElementById('filter_classroom').value = '';
    document.getElementById('filter_status').value = '';
    applyFilters();
});

document.addEventListener('DOMContentLoaded', () => {
    const rowsSelect = document.getElementById('rows_per_page');
    if (rowsSelect) {
        rowsSelect.addEventListener('change', (e) => {
            rowsPerPage = parseInt(e.target.value);
            currentPage = 1;
            renderPagination();
        });
    }
    applyFilters();
});

// Add logic for modals and CRUD operations below (to be implemented)
function openAddModal() {
    try {
        const form = document.getElementById('form_student');
        if(form) form.reset();
        
        // Clear validation errors
        document.querySelectorAll('[id^="err_add_"]').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('#form_student input, #form_student select').forEach(el => el.classList.remove('border-red-500'));
        
        filterAddClassrooms();
        openModal('modal_form');
    } catch(err) {
        console.error("Error opening Add Student modal:", err);
        alert("Could not open modal. Check console for details.");
    }
}

function filterAddClassrooms() {
    const ayId = document.getElementById('add_academic_year').value;
    const yearId = document.getElementById('add_year_level').value;
    const majorId = document.getElementById('add_major').value;
    const select = document.getElementById('add_classroom_id');
    const options = select.querySelectorAll('option[data-major]');
    
    let hasMatch = false;
    options.forEach(opt => {
        const optAy = opt.getAttribute('data-ay');
        const optYear = opt.getAttribute('data-year');
        const optMajor = opt.getAttribute('data-major');
        
        let show = true;
        if (ayId && optAy !== ayId) show = false;
        if (yearId && optYear !== yearId) show = false;
        if (majorId && optMajor !== majorId) show = false;
        
        if (show) {
            opt.style.display = '';
            hasMatch = true;
        } else {
            opt.style.display = 'none';
        }
    });
    
    if (select.value) {
        const selectedOpt = select.querySelector(`option[value="${select.value}"]`);
        if (selectedOpt && selectedOpt.style.display === 'none') {
            select.value = '';
        }
    }
}

async function validateAndSubmitStudent(e) {
    e.preventDefault();
    
    let isValid = true;
    
    document.querySelectorAll('[id^="err_add_"]').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('#form_student input, #form_student select').forEach(el => el.classList.remove('border-red-500'));
    
    const showError = (id) => {
        const errEl = document.getElementById('err_' + id);
        if (errEl) errEl.classList.remove('hidden');
        const inputEl = document.getElementById(id);
        if (inputEl) inputEl.classList.add('border-red-500');
        isValid = false;
    };
    
    const rollNumber = document.getElementById('add_roll_number').value.trim();
    if(!rollNumber) showError('add_roll_number');
    
    const username = document.getElementById('add_username').value.trim();
    if(!username) showError('add_username');
    
    const email = document.getElementById('add_email').value.trim();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if(!email || !emailRegex.test(email)) showError('add_email');
    
    if(!document.getElementById('add_academic_year').value) showError('add_academic_year');
    if(!document.getElementById('add_year_level').value) showError('add_year_level');
    if(!document.getElementById('add_major').value) showError('add_major');
    if(!document.getElementById('add_classroom_id').value) showError('add_classroom_id');
    
    const pass = document.getElementById('add_password').value;
    if(pass.length < 6) showError('add_password');
    
    const passConf = document.getElementById('add_password_confirm').value;
    if(pass !== passConf || !passConf) showError('add_password_confirm');
    
    if(!isValid) return;

    const btn = document.getElementById('btn_submit_add');
    const originalText = btn.innerHTML;
    btn.innerHTML = `<i data-lucide="loader-2" class="w-4 h-4 mr-2 animate-spin"></i> Creating...`;
    btn.disabled = true;
    lucide.createIcons();
    
    const formData = new FormData(e.target);
    const params = new URLSearchParams();
    for (const pair of formData) {
        params.append(pair[0], pair[1]);
    }
    params.append('csrf_token', csrfToken);
    
    try {
        const res = await fetch('../ajax/create_student.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: params.toString()
        });
        const data = await res.json();
        if(data.success) {
            showSuccess('Student created successfully!');
        } else {
            if (data.field) {
                showError(data.field);
                const errMsgEl = document.getElementById('err_' + data.field);
                if (errMsgEl) {
                    errMsgEl.textContent = data.message;
                    errMsgEl.classList.remove('hidden');
                }
            } else {
                alert('Error: ' + data.message);
            }
        }
    } catch(err) {
        console.error("Error creating student:", err);
        alert('An unexpected error occurred: ' + err.message);
    } finally {
        if(btn) {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
        lucide.createIcons();
    }
}

function togglePasswordVisibility(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    if (input.type === "password") {
        input.type = "text";
        icon.setAttribute('data-lucide', 'eye-off');
    } else {
        input.type = "password";
        icon.setAttribute('data-lucide', 'eye');
    }
    lucide.createIcons();
}

function openEditModal(student) {
    document.getElementById('edit_id').value = student.id;
    document.getElementById('edit_student_id').value = student.student_id || '';
    document.getElementById('edit_roll_number').value = student.roll_number || '';
    document.getElementById('edit_username').value = student.username || '';
    document.getElementById('edit_email').value = student.email || '';
    document.getElementById('edit_phone').value = student.phone || '';
    
    document.getElementById('edit_academic_year').value = student.academic_year_id || '';
    document.getElementById('edit_year').value = student.academic_year_level_id || '';
    document.getElementById('edit_major').value = student.major_id || '';
    
    document.getElementById('edit_account_username').value = student.username || '';
    document.getElementById('edit_password').value = '';
    document.getElementById('edit_password_confirm').value = '';
    
    document.querySelectorAll('[id^="err_edit_"]').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('#form_edit_student input, #form_edit_student select').forEach(el => el.classList.remove('border-red-500'));
    
    filterEditClassrooms();
    document.getElementById('edit_classroom_id').value = student.classroom_id || '';
    
    openModal('modal_edit');
}

function filterEditClassrooms() {
    const ayId = document.getElementById('edit_academic_year').value;
    const yearId = document.getElementById('edit_year').value;
    const majorId = document.getElementById('edit_major').value;
    const select = document.getElementById('edit_classroom_id');
    const options = select.querySelectorAll('option[data-major]');
    
    let hasMatch = false;
    options.forEach(opt => {
        const optAy = opt.getAttribute('data-ay');
        const optYear = opt.getAttribute('data-year');
        const optMajor = opt.getAttribute('data-major');
        let show = true;
        if (ayId && optAy !== ayId) show = false;
        if (yearId && optYear !== yearId) show = false;
        if (majorId && optMajor !== majorId) show = false;
        
        if (show) {
            opt.style.display = '';
            hasMatch = true;
        } else {
            opt.style.display = 'none';
        }
    });
    
    if (select.value) {
        const selectedOpt = select.querySelector(`option[value="${select.value}"]`);
        if (selectedOpt && selectedOpt.style.display === 'none') {
            select.value = '';
        }
    }
}

async function submitEditStudent(e) {
    e.preventDefault();
    
    let isValid = true;
    document.querySelectorAll('[id^="err_edit_"]').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('#form_edit_student input, #form_edit_student select').forEach(el => el.classList.remove('border-red-500'));
    
    const showError = (id) => {
        document.getElementById('err_' + id).classList.remove('hidden');
        document.getElementById(id).classList.add('border-red-500');
        isValid = false;
    };
    
    const rollNumber = document.getElementById('edit_roll_number').value.trim();
    if(!rollNumber) showError('edit_roll_number');

    const username = document.getElementById('edit_username').value.trim();
    if(!username) showError('edit_username');
    
    const email = document.getElementById('edit_email').value.trim();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if(!email || !emailRegex.test(email)) showError('edit_email');
    
    if(!document.getElementById('edit_classroom_id').value) showError('edit_classroom_id');
    
    const pass = document.getElementById('edit_password').value;
    const passConf = document.getElementById('edit_password_confirm').value;
    
    if(pass) {
        if(pass.length < 6) showError('edit_password');
        if(pass !== passConf) showError('edit_password_confirm');
    }
    
    if(!isValid) return;

    const btn = document.getElementById('btn_submit_edit');
    const originalText = btn.innerHTML;
    btn.innerHTML = `<i data-lucide="loader-2" class="w-4 h-4 mr-2 animate-spin"></i> Saving...`;
    btn.disabled = true;
    lucide.createIcons();
    
    const formData = new FormData(e.target);
    const params = new URLSearchParams();
    for (const pair of formData) {
        params.append(pair[0], pair[1]);
    }
    params.append('csrf_token', csrfToken);
    
    try {
        const res = await fetch('../ajax/update_student.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: params.toString()
        });
        const data = await res.json();
        if(data.success) {
            closeModal('modal_edit');
            showSuccess('Student profile updated successfully!');
        } else {
            alert('Error: ' + data.message);
            btn.innerHTML = originalText;
            btn.disabled = false;
            lucide.createIcons();
        }
    } catch(err) {
        alert('An unexpected error occurred.');
        btn.innerHTML = originalText;
        btn.disabled = false;
        lucide.createIcons();
    }
}

function viewStudent(s) {
    document.getElementById('view_name').textContent = s.username;
    document.getElementById('view_fullname_val').textContent = s.username;
    document.getElementById('view_username').textContent = s.username;
    
    document.getElementById('view_id').textContent = s.student_id || 'NO ID';
    document.getElementById('view_student_id_val').textContent = s.student_id || 'N/A';
    document.getElementById('view_roll_number_val').textContent = s.roll_number || 'N/A';
    
    document.getElementById('view_email').textContent = s.email;
    document.getElementById('view_phone').textContent = s.phone || 'N/A';
    
    document.getElementById('view_year_level').textContent = s.year_level || 'N/A';
    document.getElementById('view_major').textContent = s.major || 'N/A';
    document.getElementById('view_classroom').textContent = s.classroom_name || 'N/A';
    
    document.getElementById('view_created').textContent = s.created_at;
    
    document.getElementById('view_avatar').textContent = s.username.charAt(0).toUpperCase();
    
    const badge = document.getElementById('view_status_badge');
    badge.textContent = s.status;
    if (s.status === 'Active') badge.className = 'inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border bg-green-50 text-green-700 border-green-200';
    else if (s.status === 'Suspended') badge.className = 'inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border bg-red-50 text-red-700 border-red-200';
    else if (s.status === 'Graduated') badge.className = 'inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border bg-purple-50 text-purple-700 border-purple-200';
    else badge.className = 'inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border bg-gray-100 text-gray-500 border-gray-200';
    
    openModal('modal_view');
}

async function submitStudent(e) {
    e.preventDefault();
    const btn = document.getElementById('btn_submit');
    const originalText = btn.innerHTML;
    btn.innerHTML = `<i data-lucide="loader-2" class="w-4 h-4 mr-2 animate-spin"></i> Saving...`;
    btn.disabled = true;
    lucide.createIcons();
    
    const formData = new FormData(e.target);
    const params = new URLSearchParams();
    for (const pair of formData) {
        params.append(pair[0], pair[1]);
    }
    params.append('csrf_token', csrfToken);
    
    // Add student_id explicitly if it's disabled (so it's omitted by FormData)
    if (document.getElementById('form_student_id').disabled) {
        params.append('student_id', document.getElementById('form_student_id').value);
    }
    
    const isEdit = document.getElementById('form_id').value !== '';
    const endpoint = isEdit ? '../ajax/update_student.php' : '../ajax/create_student.php';

    try {
        const res = await fetch(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: params.toString()
        });
        const data = await res.json();
        if(data.success) {
            showSuccess(isEdit ? 'Student updated successfully.' : 'Student created successfully.');
        } else {
            alert('Error: ' + data.message);
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    } catch(err) {
        alert('An unexpected error occurred.');
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
}

async function requestToggleStatus(id, newStatus) {
    if (!confirm(`Are you sure you want to mark this student as ${newStatus}?`)) return;
    
    try {
        const res = await fetch('../ajax/toggle_student_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${id}&status=${newStatus}&csrf_token=${csrfToken}`
        });
        const data = await res.json();
        if(data.success) {
            showSuccess(data.message);
        } else {
            alert('Error: ' + data.message);
        }
    } catch(err) {
        alert('An unexpected error occurred.');
    }
}

let deleteStudentId = null;

function requestDeleteStudent(id, name) {
    deleteStudentId = id;
    openModal('modal_delete');
}

async function confirmDeleteStudent() {
    if (!deleteStudentId) return;
    
    const btn = document.getElementById('btn_confirm_delete');
    const originalText = btn.innerHTML;
    btn.innerHTML = `<i data-lucide="loader-2" class="w-4 h-4 mr-2 animate-spin"></i> Deleting...`;
    btn.disabled = true;
    lucide.createIcons();
    
    try {
        const res = await fetch('../ajax/delete_student.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${deleteStudentId}&csrf_token=${csrfToken}`
        });
        const data = await res.json();
        if(data.success) {
            closeModal('modal_delete');
            showSuccess(data.message || 'Student deleted successfully.');
        } else {
            alert('Error: ' + data.message);
            btn.innerHTML = originalText;
            btn.disabled = false;
            lucide.createIcons();
        }
    } catch(err) {
        alert('An unexpected error occurred.');
        btn.innerHTML = originalText;
        btn.disabled = false;
        lucide.createIcons();
    }
}

let resetId = null;
function requestPasswordReset(id, name) {
    resetId = id;
    document.getElementById('reset_student_name').textContent = name;
    document.getElementById('temp_password_container').classList.add('hidden');
    const btn = document.getElementById('btn_confirm_reset');
    btn.classList.remove('hidden');
    btn.disabled = false;
    openModal('modal_reset');
}

document.getElementById('btn_confirm_reset').addEventListener('click', async function() {
    if (!resetId) return;
    const btn = this;
    const originalText = btn.innerHTML;
    btn.innerHTML = `<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>`;
    btn.disabled = true;
    lucide.createIcons();
    
    try {
        const res = await fetch('../ajax/reset_password.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${resetId}&csrf_token=${csrfToken}`
        });
        const data = await res.json();
        if(data.success) {
            document.getElementById('temp_password_container').classList.remove('hidden');
            document.getElementById('new_temp_password').textContent = data.temp_password;
            btn.classList.add('hidden');
        } else {
            alert('Error: ' + data.message);
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    } catch(err) {
        alert('An unexpected error occurred.');
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
});

function openImportModal() {
    document.getElementById('form_import').reset();
    openModal('modal_import');
}

async function submitImport(e) {
    e.preventDefault();
    const btn = document.getElementById('btn_import');
    const originalText = btn.innerHTML;
    btn.innerHTML = `<i data-lucide="loader-2" class="w-4 h-4 mr-2 animate-spin"></i> Uploading...`;
    btn.disabled = true;
    lucide.createIcons();
    
    const formData = new FormData(e.target);
    formData.append('csrf_token', csrfToken);
    
    try {
        const res = await fetch('../ajax/import_students.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if(data.success) {
            closeModal('modal_import');
            showSuccess(data.message);
        } else {
            alert('Error: ' + data.message);
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    } catch(err) {
        alert('An unexpected error occurred.');
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
}

function exportCSV() {
    window.location.href = '../ajax/export_students.php';
}

function showSuccess(msg) {
    closeModal('modal_form');
    const alertBox = document.getElementById('top_success_alert');
    document.getElementById('top_success_message').textContent = msg;
    alertBox.classList.remove('hidden');
    alertBox.classList.add('flex');
    window.scrollTo({top: 0, behavior: 'smooth'});
    
    setTimeout(() => {
        window.location.reload();
    }, 1500);
}

let sortState = { col: null, dir: 'asc' };
function sortTable(col) {
    const grid = document.getElementById('student_grid');
    const rows = Array.from(grid.querySelectorAll('tr.student-card'));
    
    if (sortState.col === col) {
        sortState.dir = sortState.dir === 'asc' ? 'desc' : 'asc';
    } else {
        sortState.col = col;
        sortState.dir = 'asc';
    }

    rows.sort((a, b) => {
        let valA = (a.getAttribute('data-' + col) || '').toLowerCase();
        let valB = (b.getAttribute('data-' + col) || '').toLowerCase();
        
        if (valA < valB) return sortState.dir === 'asc' ? -1 : 1;
        if (valA > valB) return sortState.dir === 'asc' ? 1 : -1;
        return 0;
    });

    rows.forEach(row => grid.appendChild(row));
}
</script>

<?php include '../includes/footer.php'; ?>
