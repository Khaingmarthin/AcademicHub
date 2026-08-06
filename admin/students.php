<?php
require_once '../config/session.php';
require_admin();
require_once '../config/db.php';
require_once '../config/functions.php';

// Fetch Active Academic Year
$active_ay_id = $_SESSION['current_academic_year_id'] ?? 0;
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

<style>
.modal-enter { opacity: 0; transform: scale(0.95); }
.modal-enter-active { opacity: 1; transform: scale(1); transition: opacity 200ms, transform 200ms; }
.modal-leave { opacity: 1; transform: scale(1); }
.modal-leave-active { opacity: 0; transform: scale(0.95); transition: opacity 200ms, transform 200ms; }
</style>

<div class="min-h-screen bg-[#F8FAFC] pb-12">
    <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 pt-8">
        
        <!-- Page Header -->
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Student Management</h1>
                <p class="mt-2 text-sm text-gray-500 font-medium">Manage student accounts and classroom assignments.</p>
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

        <div class="flex flex-col space-y-6">
            <!-- Success Alert -->
            <div id="top_success_alert" class="hidden items-center justify-between px-5 py-3 rounded-xl bg-green-50/90 border border-green-200 shadow-sm backdrop-blur-sm transition-all duration-300">
                <div class="flex items-center gap-3">
                    <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
                    <p class="text-sm font-semibold text-green-700" id="top_success_message">Success!</p>
                </div>
            </div>

            <!-- Toolbar -->
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-col xl:flex-row gap-4 items-start xl:items-center justify-between">
                
                <div class="flex flex-col md:flex-row w-full xl:w-auto gap-4 flex-1">
                    <!-- Search Bar -->
                    <div class="relative w-full md:w-64">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="search" class="w-4 h-4 text-gray-400"></i>
                        </div>
                        <input type="text" id="filter_search" placeholder="Search students..." 
                            class="block w-full pl-9 pr-3 py-2.5 border border-gray-200 rounded-lg leading-5 bg-gray-50 placeholder-gray-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] sm:text-sm font-medium transition-all duration-200 text-gray-800">
                    </div>
                    
                    <!-- Filters -->
                    <div class="grid grid-cols-2 lg:flex gap-3 w-full lg:w-auto overflow-x-auto pb-2 lg:pb-0 hide-scrollbar">
                        <select id="filter_ay" class="block w-full lg:w-36 py-2.5 pl-3 pr-8 border border-gray-200 bg-gray-50 rounded-lg text-sm font-medium text-gray-700 focus:bg-white focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors cursor-pointer appearance-none">
                            <option value="">All Academic Years</option>
                            <?php foreach ($academic_years as $ay): ?>
                            <option value="<?php echo $ay['id']; ?>" <?php echo $ay['id'] == $active_ay_id ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($ay['year_name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <select id="filter_major" class="block w-full lg:w-36 py-2.5 pl-3 pr-8 border border-gray-200 bg-gray-50 rounded-lg text-sm font-medium text-gray-700 focus:bg-white focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors cursor-pointer appearance-none">
                            <option value="">All Majors</option>
                            <?php foreach ($majors as $m): ?>
                            <option value="<?php echo $m['id']; ?>"><?php echo htmlspecialchars($m['major_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select id="filter_year" class="block w-full lg:w-32 py-2.5 pl-3 pr-8 border border-gray-200 bg-gray-50 rounded-lg text-sm font-medium text-gray-700 focus:bg-white focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors cursor-pointer appearance-none">
                            <option value="">All Years</option>
                            <?php foreach ($year_levels as $yl): ?>
                            <option value="<?php echo $yl['id']; ?>"><?php echo htmlspecialchars($yl['level_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select id="filter_classroom" class="block w-full lg:w-36 py-2.5 pl-3 pr-8 border border-gray-200 bg-gray-50 rounded-lg text-sm font-medium text-gray-700 focus:bg-white focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors cursor-pointer appearance-none">
                            <option value="">All Classrooms</option>
                            <!-- Populated via JS based on above filters if needed, or just all classrooms -->
                            <?php foreach ($classrooms_all as $cl): ?>
                            <option value="<?php echo $cl['id']; ?>" data-major="<?php echo $cl['major_id']; ?>" data-ay="<?php echo $cl['academic_year_id']; ?>" data-year="<?php echo $cl['academic_year_level_id']; ?>"><?php echo htmlspecialchars($cl['classroom_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select id="filter_status" class="block w-full lg:w-32 py-2.5 pl-3 pr-8 border border-gray-200 bg-gray-50 rounded-lg text-sm font-medium text-gray-700 focus:bg-white focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors cursor-pointer appearance-none">
                            <option value="">All Statuses</option>
                            <option value="Active">Active</option>
                            <option value="Suspended">Suspended</option>
                            <option value="Inactive">Inactive</option>
                            <option value="Graduated">Graduated</option>
                        </select>
                    </div>
                </div>
                
                <button onclick="openAddModal()" class="w-full xl:w-auto flex-shrink-0 flex items-center justify-center gap-2 px-5 py-2.5 border border-transparent text-sm font-bold rounded-lg text-white bg-[#2563EB] hover:bg-blue-700 shadow-sm hover:shadow-md transition-all duration-200">
                    <i data-lucide="plus" class="w-4 h-4"></i> Add Student
                </button>
            </div>

            <!-- Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5" id="student_grid">
                <?php foreach ($students as $s): 
                    $initials = strtoupper(substr($s['username'], 0, 1));
                    $status_color = 'bg-gray-50 text-gray-700 border-gray-200';
                    if ($s['status'] === 'Active') $status_color = 'bg-green-50 text-green-700 border-green-200';
                    if ($s['status'] === 'Suspended') $status_color = 'bg-red-50 text-red-700 border-red-200';
                    if ($s['status'] === 'Graduated') $status_color = 'bg-purple-50 text-purple-700 border-purple-200';
                    if ($s['status'] === 'Inactive') $status_color = 'bg-gray-100 text-gray-500 border-gray-200';
                ?>
                <div class="student-card group relative flex flex-col bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md hover:-translate-y-1 transition-all duration-300" 
                     data-search="<?php echo strtolower(htmlspecialchars($s['username'] . ' ' . $s['student_id'] . ' ' . $s['email'] . ' ' . $s['classroom_name'])); ?>"
                     data-ay="<?php echo $s['academic_year_id']; ?>"
                     data-major="<?php echo $s['major_id']; ?>"
                     data-year="<?php echo $s['academic_year_level_id']; ?>"
                     data-classroom="<?php echo $s['classroom_id']; ?>"
                     data-status="<?php echo htmlspecialchars($s['status']); ?>">
                    
                    <div class="p-5 flex-1 flex flex-col">
                        <!-- Header / Status -->
                        <div class="flex justify-between items-start mb-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-black tracking-widest bg-blue-50 text-[#2563EB] border border-blue-100">
                                <?php echo htmlspecialchars($s['student_id'] ?: 'NO ID'); ?>
                            </span>
                            
                            <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider border <?php echo $status_color; ?>">
                                <?php echo htmlspecialchars($s['status']); ?>
                            </span>
                        </div>
                        
                        <!-- Profile -->
                        <div class="flex items-center gap-4 mb-5">
                            <?php if (!empty($s['avatar'])): ?>
                                <img src="../<?php echo htmlspecialchars($s['avatar']); ?>" class="w-14 h-14 rounded-full object-cover border-2 border-white shadow-sm">
                            <?php else: ?>
                                <div class="w-14 h-14 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-xl shadow-sm border-2 border-white">
                                    <?php echo $initials; ?>
                                </div>
                            <?php endif; ?>
                            <div class="flex-1 overflow-hidden">
                                <h3 class="text-lg font-bold text-gray-900 truncate"><?php echo htmlspecialchars($s['username']); ?></h3>
                                <p class="text-xs text-gray-500 truncate"><?php echo htmlspecialchars($s['email']); ?></p>
                            </div>
                        </div>
                        
                        <!-- Badges Grid -->
                        <div class="grid grid-cols-2 gap-2 mb-4 text-xs font-semibold">
                            <div class="bg-gray-50 p-2 rounded-lg border border-gray-100 flex items-center gap-2">
                                <i data-lucide="book-open" class="w-3.5 h-3.5 text-indigo-500"></i>
                                <span class="truncate text-gray-700"><?php echo htmlspecialchars($s['major'] ?: 'N/A'); ?></span>
                            </div>
                            <div class="bg-gray-50 p-2 rounded-lg border border-gray-100 flex items-center gap-2">
                                <i data-lucide="layout-template" class="w-3.5 h-3.5 text-blue-500"></i>
                                <span class="truncate text-gray-700"><?php echo htmlspecialchars($s['classroom_name'] ?: 'No Class'); ?></span>
                            </div>
                            <div class="bg-gray-50 p-2 rounded-lg border border-gray-100 flex items-center gap-2 col-span-2">
                                <i data-lucide="calendar" class="w-3.5 h-3.5 text-emerald-500"></i>
                                <span class="truncate text-gray-700">AY: <?php echo htmlspecialchars($s['academic_year'] ?: 'N/A'); ?></span>
                            </div>
                        </div>
                        
                        <!-- Last Login -->
                        <div class="mt-auto text-[10px] font-medium text-gray-400 flex items-center justify-between border-t border-gray-50 pt-2">
                            <span>Last Login: <?php echo $s['last_login'] ? date('M d, Y H:i', strtotime($s['last_login'])) : 'Never'; ?></span>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="bg-gray-50/50 border-t border-gray-100 p-2 grid grid-cols-4 gap-1">
                        <button onclick='viewStudent(<?php echo json_encode($s); ?>)' class="flex flex-col items-center justify-center py-2 rounded-lg text-[#2563EB] hover:bg-blue-50 transition-colors text-[10px] font-bold">
                            <i data-lucide="eye" class="w-4 h-4 mb-1"></i> View
                        </button>
                        <button onclick='openEditModal(<?php echo json_encode($s); ?>)' class="flex flex-col items-center justify-center py-2 rounded-lg text-amber-600 hover:bg-amber-50 transition-colors text-[10px] font-bold">
                            <i data-lucide="pencil" class="w-4 h-4 mb-1"></i> Edit
                        </button>
                        <button onclick="requestPasswordReset(<?php echo $s['id']; ?>, '<?php echo addslashes($s['username']); ?>')" class="flex flex-col items-center justify-center py-2 rounded-lg text-indigo-600 hover:bg-indigo-50 transition-colors text-[10px] font-bold">
                            <i data-lucide="key-round" class="w-4 h-4 mb-1"></i> Reset
                        </button>
                        <?php if ($s['status'] === 'Active'): ?>
                        <button onclick="requestToggleStatus(<?php echo $s['id']; ?>, 'Suspended')" class="flex flex-col items-center justify-center py-2 rounded-lg text-red-600 hover:bg-red-50 transition-colors text-[10px] font-bold">
                            <i data-lucide="ban" class="w-4 h-4 mb-1"></i> Suspend
                        </button>
                        <?php else: ?>
                        <button onclick="requestToggleStatus(<?php echo $s['id']; ?>, 'Active')" class="flex flex-col items-center justify-center py-2 rounded-lg text-emerald-600 hover:bg-emerald-50 transition-colors text-[10px] font-bold">
                            <i data-lucide="check-circle" class="w-4 h-4 mb-1"></i> Activate
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Empty State -->
            <div id="search_empty_state" class="hidden flex-col items-center justify-center py-20 px-4 bg-white rounded-xl shadow-sm border border-gray-100 text-center">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                    <i data-lucide="users" class="w-8 h-8 text-gray-400"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">No students found</h3>
                <p class="text-sm text-gray-500">Try adjusting your search or filters.</p>
            </div>
            
            <?php if (count($students) === 0): ?>
            <div class="flex flex-col items-center justify-center py-20 px-4 bg-white rounded-xl shadow-sm border border-gray-100 text-center">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                    <i data-lucide="users" class="w-8 h-8 text-gray-400"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">No students enrolled yet.</h3>
                <p class="text-sm text-gray-500">Start by adding a new student or importing from CSV.</p>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="modal_form" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeModal('modal_form')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full">
            <form id="form_student" onsubmit="submitStudent(event)">
                <input type="hidden" id="form_id" name="id">
                <div class="bg-white px-6 pt-6 pb-4 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center">
                            <i id="form_icon" data-lucide="plus" class="w-5 h-5 text-[#2563EB]"></i>
                        </div>
                        <h3 class="text-xl leading-6 font-bold text-gray-900" id="form_title">Add Student</h3>
                    </div>
                </div>
                
                <div class="bg-white px-6 py-6 max-h-[60vh] overflow-y-auto hide-scrollbar space-y-6">
                    <!-- Personal Info -->
                    <div>
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4 border-b border-gray-100 pb-2">Personal Information</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Student ID <span class="text-red-500">*</span></label>
                                <input type="text" id="form_student_id" name="student_id" required placeholder="e.g. 1CS-01" class="block w-full px-3 py-2.5 border border-gray-200 bg-gray-50 rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Full Name <span class="text-red-500">*</span></label>
                                <input type="text" id="form_username" name="username" required placeholder="John Doe" class="block w-full px-3 py-2.5 border border-gray-200 bg-gray-50 rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Email Address <span class="text-red-500">*</span></label>
                                <input type="email" id="form_email" name="email" required placeholder="john@student.edu" class="block w-full px-3 py-2.5 border border-gray-200 bg-gray-50 rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Phone Number</label>
                                <input type="text" id="form_phone" name="phone" placeholder="09xxxxxxxxx" class="block w-full px-3 py-2.5 border border-gray-200 bg-gray-50 rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Gender</label>
                                <div class="relative">
                                    <select id="form_gender" name="gender" class="block w-full py-2.5 pl-3 pr-10 border border-gray-200 bg-gray-50 rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors cursor-pointer appearance-none">
                                        <option value="">Select Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400"><i data-lucide="chevron-down" class="w-4 h-4"></i></div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Date of Birth</label>
                                <input type="date" id="form_dob" name="date_of_birth" class="block w-full px-3 py-2.5 border border-gray-200 bg-gray-50 rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors">
                            </div>
                        </div>
                    </div>

                    <!-- Academic Info -->
                    <div>
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4 border-b border-gray-100 pb-2">Academic Information</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Classroom <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <select id="form_classroom_id" name="classroom_id" required class="block w-full py-2.5 pl-3 pr-10 border border-gray-200 bg-gray-50 rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors cursor-pointer appearance-none">
                                        <option value="">Select Classroom...</option>
                                        <?php foreach ($classrooms_all as $cl): ?>
                                        <option value="<?php echo $cl['id']; ?>"><?php echo htmlspecialchars($cl['classroom_name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400"><i data-lucide="chevron-down" class="w-4 h-4"></i></div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Status <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <select id="form_status" name="status" required class="block w-full py-2.5 pl-3 pr-10 border border-gray-200 bg-gray-50 rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors cursor-pointer appearance-none">
                                        <option value="Active">Active</option>
                                        <option value="Suspended">Suspended</option>
                                        <option value="Inactive">Inactive</option>
                                        <option value="Graduated">Graduated</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400"><i data-lucide="chevron-down" class="w-4 h-4"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Password Info (Only on Create) -->
                    <div id="password_section">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4 border-b border-gray-100 pb-2">Security</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Password <span class="text-red-500">*</span></label>
                                <input type="password" id="form_password" name="password" minlength="6" placeholder="******" class="block w-full px-3 py-2.5 border border-gray-200 bg-gray-50 rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Confirm Password <span class="text-red-500">*</span></label>
                                <input type="password" id="form_password_confirm" name="password_confirm" minlength="6" placeholder="******" class="block w-full px-3 py-2.5 border border-gray-200 bg-gray-50 rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors">
                            </div>
                        </div>
                    </div>

                </div>
                <div class="bg-gray-50 px-6 py-4 sm:flex sm:flex-row-reverse rounded-b-2xl">
                    <button type="submit" id="btn_submit" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-6 py-2.5 bg-[#2563EB] text-base font-bold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#2563EB] sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                        Save Student
                    </button>
                    <button type="button" onclick="closeModal('modal_form')" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-200 shadow-sm px-6 py-2.5 bg-white text-base font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Modal -->
<div id="modal_view" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeModal('modal_view')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl w-full">
            <div class="bg-white px-6 pt-6 pb-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-xl leading-6 font-bold text-gray-900">Student Profile</h3>
                <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider border" id="view_status_badge">Active</span>
            </div>
            <div class="px-6 py-6 space-y-6">
                <div class="flex items-center gap-4">
                    <div id="view_avatar" class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-2xl shadow-sm border-2 border-white"></div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900" id="view_name">Name</h3>
                        <p class="text-sm font-medium text-blue-600" id="view_id">ID</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                        <span class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Email</span>
                        <span class="text-sm font-semibold text-gray-900" id="view_email">-</span>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                        <span class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Phone</span>
                        <span class="text-sm font-semibold text-gray-900" id="view_phone">-</span>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                        <span class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Gender</span>
                        <span class="text-sm font-semibold text-gray-900" id="view_gender">-</span>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                        <span class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Date of Birth</span>
                        <span class="text-sm font-semibold text-gray-900" id="view_dob">-</span>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-xl border border-gray-100 col-span-2">
                        <span class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Classroom / Major / AY</span>
                        <span class="text-sm font-semibold text-gray-900" id="view_academic">-</span>
                    </div>
                </div>
                
                <div class="text-xs font-medium text-gray-400 text-center">
                    Last Login: <span id="view_last_login">-</span> | Created: <span id="view_created">-</span>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 sm:flex sm:flex-row-reverse rounded-b-2xl">
                <button type="button" onclick="closeModal('modal_view')" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-6 py-2.5 bg-gray-900 text-base font-bold text-white hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900 sm:w-auto sm:text-sm transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

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
<script>
const csrfToken = '<?php echo $csrf_token; ?>';

function openModal(id) {
    const el = document.getElementById(id);
    el.classList.remove('hidden');
    setTimeout(() => {
        el.querySelector('div.inline-block').classList.remove('scale-95', 'opacity-0');
        el.querySelector('div.inline-block').classList.add('scale-100', 'opacity-100');
    }, 10);
}

function closeModal(id) {
    const el = document.getElementById(id);
    el.querySelector('div.inline-block').classList.remove('scale-100', 'opacity-100');
    el.querySelector('div.inline-block').classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        el.classList.add('hidden');
    }, 200);
}

document.querySelectorAll('.inline-block.align-bottom').forEach(el => {
    el.classList.add('scale-95', 'opacity-0', 'transition-all', 'duration-200');
});

function applyFilters() {
    if (!document.getElementById('filter_search')) return;
    
    const term = document.getElementById('filter_search').value.toLowerCase();
    const ayFilter = document.getElementById('filter_ay').value;
    const majorFilter = document.getElementById('filter_major').value;
    const yearFilter = document.getElementById('filter_year').value;
    const classroomFilter = document.getElementById('filter_classroom').value;
    const statusFilter = document.getElementById('filter_status').value;
    
    const cards = document.querySelectorAll('.student-card');
    let visibleCount = 0;
    
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
            card.classList.remove('hidden');
            visibleCount++;
        } else {
            card.classList.add('hidden');
        }
    });
    
    if (visibleCount === 0 && cards.length > 0) {
        document.getElementById('search_empty_state').classList.remove('hidden');
        document.getElementById('search_empty_state').classList.add('flex');
    } else {
        document.getElementById('search_empty_state').classList.add('hidden');
        document.getElementById('search_empty_state').classList.remove('flex');
    }
}

document.getElementById('filter_search').addEventListener('input', applyFilters);
document.getElementById('filter_ay').addEventListener('change', applyFilters);
document.getElementById('filter_major').addEventListener('change', applyFilters);
document.getElementById('filter_year').addEventListener('change', applyFilters);
document.getElementById('filter_classroom').addEventListener('change', applyFilters);
document.getElementById('filter_status').addEventListener('change', applyFilters);

document.addEventListener('DOMContentLoaded', applyFilters);

// Add logic for modals and CRUD operations below (to be implemented)
function openAddModal() {
    document.getElementById('form_title').textContent = 'Add Student';
    document.getElementById('form_id').value = '';
    document.getElementById('form_student').reset();
    document.getElementById('form_student_id').disabled = false;
    document.getElementById('password_section').classList.remove('hidden');
    document.getElementById('form_password').required = true;
    document.getElementById('form_password_confirm').required = true;
    openModal('modal_form');
}

function openEditModal(student) {
    document.getElementById('form_title').textContent = 'Edit Student';
    document.getElementById('form_id').value = student.id;
    document.getElementById('form_student_id').value = student.student_id || '';
    document.getElementById('form_student_id').disabled = true; // Cannot edit student ID
    document.getElementById('form_username').value = student.username || '';
    document.getElementById('form_email').value = student.email || '';
    document.getElementById('form_phone').value = student.phone || '';
    document.getElementById('form_gender').value = student.gender || '';
    document.getElementById('form_dob').value = student.date_of_birth || '';
    document.getElementById('form_classroom_id').value = student.classroom_id || '';
    document.getElementById('form_status').value = student.status;
    
    document.getElementById('password_section').classList.add('hidden');
    document.getElementById('form_password').required = false;
    document.getElementById('form_password_confirm').required = false;
    
    openModal('modal_form');
}

function viewStudent(s) {
    document.getElementById('view_name').textContent = s.username;
    document.getElementById('view_id').textContent = s.student_id || 'NO ID';
    document.getElementById('view_email').textContent = s.email;
    document.getElementById('view_phone').textContent = s.phone || 'N/A';
    document.getElementById('view_gender').textContent = s.gender || 'N/A';
    document.getElementById('view_dob').textContent = s.date_of_birth || 'N/A';
    document.getElementById('view_academic').textContent = `${s.classroom_name || 'N/A'} / ${s.major || 'N/A'} / ${s.academic_year || 'N/A'}`;
    document.getElementById('view_last_login').textContent = s.last_login ? s.last_login : 'Never';
    document.getElementById('view_created').textContent = s.created_at;
    
    document.getElementById('view_avatar').textContent = s.username.charAt(0).toUpperCase();
    
    const badge = document.getElementById('view_status_badge');
    badge.textContent = s.status;
    if (s.status === 'Active') badge.className = 'inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider border bg-green-50 text-green-700 border-green-200';
    else if (s.status === 'Suspended') badge.className = 'inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider border bg-red-50 text-red-700 border-red-200';
    else if (s.status === 'Graduated') badge.className = 'inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider border bg-purple-50 text-purple-700 border-purple-200';
    else badge.className = 'inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider border bg-gray-100 text-gray-500 border-gray-200';
    
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
</script>

<?php include '../includes/footer.php'; ?>
