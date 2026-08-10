<?php
require_once '../config/session.php';
require_admin();
require_once '../config/db.php';
require_once '../config/functions.php';

// Fetch all faculties with course counts
$stmt = $pdo->query("
    SELECT f.*, COUNT(c.id) as course_count 
    FROM faculties f 
    LEFT JOIN courses c ON f.id = c.faculty_id 
    GROUP BY f.id 
    ORDER BY f.faculty_name ASC
");
$faculties = $stmt->fetchAll();

$csrf_token = generate_csrf_token();

$academic_codes = ['FCS', 'FIS', 'FCST', 'ITSM', 'PHYSICS', 'LANGUAGE', 'MATH'];
$admin_codes = ['ADMIN', 'FINANCE', 'STUDENT_AFFAIRS', 'LIBRARY'];

$academic_faculties = [];
$admin_departments = [];
$active_count = 0;
$faculties_data = [];

foreach ($faculties as $fac) {
    if ($fac['status'] === 'Active') {
        $active_count++;
    }
    if (in_array(strtoupper($fac['faculty_code']), $academic_codes)) {
        $academic_faculties[] = $fac;
    } else {
        $admin_departments[] = $fac;
    }
}
$stats = [
    'total' => count($faculties),
    'academic' => count($academic_faculties),
    'admin' => count($admin_departments),
    'active' => $active_count
];
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>
<?php include '../includes/navbar.php'; ?>

<style>
/* For modal transitions */
.modal-enter { opacity: 0; transform: scale(0.95); }
.modal-enter-active { opacity: 1; transform: scale(1); transition: opacity 200ms, transform 200ms; }
.modal-leave { opacity: 1; transform: scale(1); }
.modal-leave-active { opacity: 0; transform: scale(0.95); transition: opacity 200ms, transform 200ms; }
</style>

<div class="min-h-screen bg-[#F8FAFC] pb-12">
    <div class="w-full px-4 sm:px-6 lg:px-8 pt-8">
        
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Faculty & Department Management</h1>
            <p class="mt-2 text-sm text-gray-500 font-medium">Manage all university faculties, departments and administrative offices.</p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Total Units</p>
                    <h3 class="text-2xl font-black text-gray-800"><?php echo $stats['total']; ?></h3>
                </div>
                <div class="w-12 h-12 bg-blue-50 text-[#2563EB] rounded-xl flex items-center justify-center">
                    <i data-lucide="building-2" class="w-6 h-6"></i>
                </div>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Academic</p>
                    <h3 class="text-2xl font-black text-gray-800"><?php echo $stats['academic']; ?></h3>
                </div>
                <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center">
                    <i data-lucide="graduation-cap" class="w-6 h-6"></i>
                </div>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Administrative</p>
                    <h3 class="text-2xl font-black text-gray-800"><?php echo $stats['admin']; ?></h3>
                </div>
                <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center">
                    <i data-lucide="briefcase" class="w-6 h-6"></i>
                </div>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Active Units</p>
                    <h3 class="text-2xl font-black text-gray-800"><?php echo $stats['active']; ?></h3>
                </div>
                <div class="w-12 h-12 bg-green-50 text-green-600 rounded-xl flex items-center justify-center">
                    <i data-lucide="check-circle" class="w-6 h-6"></i>
                </div>
            </div>
        </div>

        <!-- Success Alert -->
        <div id="top_success_alert" class="hidden items-center justify-between px-5 py-3 rounded-xl bg-green-50/90 border border-green-200 shadow-sm transition-all duration-300 mb-6">
            <div class="flex items-center gap-3">
                <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
                <p class="text-sm font-semibold text-green-700" id="top_success_message">Success!</p>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 mb-8 flex flex-col lg:flex-row gap-4 items-center justify-between">
            <div class="flex flex-col sm:flex-row gap-4 w-full lg:w-auto flex-1">
                <div class="relative w-full sm:max-w-xs">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="search" class="w-4 h-4 text-gray-400"></i>
                    </div>
                    <input type="text" id="search_units" placeholder="Search units..." 
                        class="block w-full pl-9 pr-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] text-gray-800">
                </div>
                <select id="filter_type" class="block w-full sm:w-48 px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] text-gray-800">
                    <option value="all">All Types</option>
                    <option value="academic">Academic Faculty</option>
                    <option value="admin">Administrative Department</option>
                </select>
                <select id="filter_status" class="block w-full sm:w-40 px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] text-gray-800">
                    <option value="all">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                <select id="sort_order" class="block w-full sm:w-48 px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] text-gray-800">
                    <option value="az">A–Z</option>
                    <option value="za">Z–A</option>
                    <option value="newest">Recently Added</option>
                </select>
            </div>
            <button onclick="openAddModal()" class="w-full lg:w-auto flex-shrink-0 flex items-center justify-center gap-2 px-5 py-2.5 bg-[#2563EB] hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-sm hover:shadow-md transition-all duration-200">
                <i data-lucide="plus" class="w-4 h-4"></i> Add Unit
            </button>
        </div>

        <?php
        // Helper function for cards
        function renderCard($fac) {
            global $faculties_data;
            $code = strtoupper($fac['faculty_code']);
            // Color Mapping
            $colors = [
                'FCS' => 'bg-blue-50 text-blue-600 border-blue-100',
                'FIS' => 'bg-indigo-50 text-indigo-600 border-indigo-100',
                'FCST' => 'bg-purple-50 text-purple-600 border-purple-100',
                'ITSM' => 'bg-cyan-50 text-cyan-600 border-cyan-100',
                'PHYSICS' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                'LANGUAGE' => 'bg-orange-50 text-orange-600 border-orange-100',
                'MATH' => 'bg-amber-50 text-amber-600 border-amber-100',
                'ADMIN' => 'bg-slate-50 text-slate-600 border-slate-100',
                'FINANCE' => 'bg-green-50 text-green-600 border-green-100',
                'STUDENT_AFFAIRS' => 'bg-teal-50 text-teal-600 border-teal-100',
                'LIBRARY' => 'bg-rose-50 text-rose-600 border-rose-100'
            ];
            $badgeColor = $colors[$code] ?? 'bg-gray-50 text-gray-600 border-gray-100';
            
            $icon = 'building-2';
            $typeClass = 'admin';
            $academic_codes = ['FCS', 'FIS', 'FCST', 'ITSM', 'PHYSICS', 'LANGUAGE', 'MATH'];
            if (in_array($code, $academic_codes)) {
                $icon = 'graduation-cap';
                $typeClass = 'academic';
            }
            $statusClass = strtolower($fac['status']) === 'active' ? 'active' : 'inactive';
            
            $facId = (int)$fac['id'];
            $faculties_data[$facId] = [
                'id' => $fac['id'],
                'name' => $fac['faculty_name'],
                'code' => $fac['faculty_code'],
                'type' => $fac['faculty_type'],
                'vision' => $fac['vision'],
                'mission' => $fac['mission'],
                'description' => $fac['description'],
                'status' => $fac['status'],
                'courses' => $fac['course_count'],
                'created_at' => date('M d, Y', strtotime($fac['created_at'])),
                'updated_at' => date('M d, Y', strtotime($fac['updated_at'])),
                'icon' => $icon
            ];
            
            $desc = htmlspecialchars($fac['description'] ?: ($fac['vision'] ?: 'No description provided.'));
            $name = htmlspecialchars($fac['faculty_name']);
            $status = htmlspecialchars($fac['status']);
            $statusBadge = $status === 'Active' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700';
            $displayType = htmlspecialchars($fac['faculty_type'] ?: 'Unit');
            $dateTimestamp = strtotime($fac['created_at']);
            $courseCount = (int)$fac['course_count'];
            $nameLower = strtolower($name);
            $codeLower = strtolower($code);
            
            
            echo '<div class="unit-card group flex flex-col bg-white rounded-2xl shadow-sm hover:shadow-md hover:-translate-y-1 hover:border-[#2563EB] border border-transparent transition-all duration-300 overflow-hidden" 
                 data-name="' . $nameLower . '" 
                 data-code="' . $codeLower . '"
                 data-type="' . $typeClass . '"
                 data-status="' . $statusClass . '"
                 data-date="' . $dateTimestamp . '">
                 
                <div class="p-5 flex-1 flex flex-col border-b border-gray-50">
                    <div class="flex justify-between items-start mb-4">
                        <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-md text-[11px] font-bold uppercase tracking-wider border ' . $badgeColor . '">
                            ' . $code . '
                        </span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider ' . $statusBadge . ' border border-white/0">
                            ' . $status . '
                        </span>
                    </div>
                    
                    <h3 class="text-[17px] font-bold text-gray-900 leading-snug mb-1.5">' . $name . '</h3>
                    <p class="text-[11px] font-semibold text-gray-400 mb-3 uppercase tracking-wider">' . $displayType . '</p>
                    
                    <p class="text-[13px] text-gray-500 line-clamp-2 flex-1 leading-relaxed">
                        ' . $desc . '
                    </p>
                </div>
                
                <div class="bg-gray-50/50 p-3 grid grid-cols-3 gap-2">
                    <button type="button" onclick="viewFacultyById(' . $facId . ')" class="flex justify-center items-center gap-1.5 py-2 rounded-xl text-blue-600 hover:bg-blue-100 active:bg-blue-200 transition-colors text-xs font-bold cursor-pointer" title="View Details">
                        <i data-lucide="eye" class="w-4 h-4"></i>
                        <span>View</span>
                    </button>
                    <button type="button" onclick="editFacultyById(' . $facId . ')" class="flex justify-center items-center gap-1.5 py-2 rounded-xl text-amber-600 hover:bg-amber-100 active:bg-amber-200 transition-colors text-xs font-bold cursor-pointer" title="Edit Faculty">
                        <i data-lucide="pencil" class="w-4 h-4"></i>
                        <span>Edit</span>
                    </button>
                    <button type="button" onclick="requestDelete(' . $facId . ', ' . $courseCount . ')" class="flex justify-center items-center gap-1.5 py-2 rounded-xl text-red-600 hover:bg-red-100 active:bg-red-200 transition-colors text-xs font-bold cursor-pointer" title="Delete Faculty">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                        <span>Delete</span>
                    </button>
                </div>
            </div>';
        }
        ?>

        <div id="sections_container" class="space-y-10">
            <!-- Section 1: Academic Faculties -->
            <div class="section-container" data-section-type="academic">
                <div class="mb-5 flex items-center border-b border-gray-200 pb-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#2563EB] flex items-center justify-center mr-3">
                        <i data-lucide="graduation-cap" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Academic Faculties</h2>
                        <p class="text-[13px] text-gray-500 font-medium">Manage all teaching and academic faculties.</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5 items-stretch section-grid">
                    <?php 
                    foreach ($academic_faculties as $fac) {
                        renderCard($fac);
                    }
                    if(empty($academic_faculties)) echo "<p class='col-span-full text-gray-500 text-sm py-4'>No academic faculties found.</p>";
                    ?>
                </div>
            </div>

            <!-- Section 2: Administrative Departments -->
            <div class="section-container" data-section-type="admin">
                <div class="mb-5 flex items-center border-b border-gray-200 pb-3">
                    <div class="w-10 h-10 rounded-xl bg-slate-50 text-slate-600 flex items-center justify-center mr-3">
                        <i data-lucide="building" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Administrative Departments</h2>
                        <p class="text-[13px] text-gray-500 font-medium">Support services for university administration and student operations.</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5 items-stretch section-grid">
                    <?php 
                    foreach ($admin_departments as $fac) {
                        renderCard($fac);
                    }
                    if(empty($admin_departments)) echo "<p class='col-span-full text-gray-500 text-sm py-4'>No administrative departments found.</p>";
                    ?>
                </div>
            </div>
        </div>
        
        <!-- Empty State -->
        <div id="search_empty_state" class="hidden flex-col items-center justify-center py-20 px-4 bg-white rounded-2xl shadow-sm border border-gray-100 text-center">
            <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                <i data-lucide="search-x" class="w-8 h-8 text-gray-400"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-1">No units found</h3>
            <p class="text-sm text-gray-500">We couldn't find any units matching your filters.</p>
        </div>

    </div>
</div>

<!-- Modals -->

<!-- View Modal -->
<div id="modal_view" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/40 transition-opacity" aria-hidden="true" onclick="closeModal('modal_view')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="modal-panel inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-50 text-[#2563EB] sm:mx-0 sm:h-10 sm:w-10">
                        <i id="view_icon" data-lucide="building-2" class="h-6 w-6"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-xs font-bold text-[#2563EB] bg-blue-50 px-2 py-0.5 rounded" id="view_code">CODE</span>
                            <span class="text-xs font-bold text-gray-500 bg-gray-100 px-2 py-0.5 rounded" id="view_type">Type</span>
                        </div>
                        <h3 class="text-xl leading-6 font-bold text-gray-900 mb-4" id="view_name">Faculty Name</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Vision</h4>
                                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 text-sm text-gray-700" id="view_vision"></div>
                            </div>
                            <div>
                                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Mission</h4>
                                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 text-sm text-gray-700" id="view_mission"></div>
                            </div>
                            <div>
                                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Description</h4>
                                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 text-sm text-gray-700" id="view_desc"></div>
                            </div>
                        </div>

                        <div class="mt-5 grid grid-cols-3 gap-4 border-t border-gray-100 pt-5">
                            <div>
                                <span class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Courses Offered</span>
                                <span class="text-sm font-bold text-gray-900 flex items-center gap-1.5"><i data-lucide="book-open" class="w-4 h-4 text-gray-400"></i> <span id="view_courses">0</span></span>
                            </div>
                            <div>
                                <span class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Created Date</span>
                                <span class="text-sm font-bold text-gray-900 flex items-center gap-1.5"><i data-lucide="calendar" class="w-4 h-4 text-gray-400"></i> <span id="view_created">Date</span></span>
                            </div>
                            <div>
                                <span class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Updated Date</span>
                                <span class="text-sm font-bold text-gray-900 flex items-center gap-1.5"><i data-lucide="calendar-clock" class="w-4 h-4 text-gray-400"></i> <span id="view_updated">Date</span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-100">
                <button type="button" onclick="closeModal('modal_view')" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-5 py-2.5 bg-gray-900 text-base font-medium text-white hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="modal_form" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeModal('modal_form')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="modal-panel inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl w-full">
            <form id="form_faculty" onsubmit="submitFaculty(event)">
                <input type="hidden" id="form_id" name="id">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-center gap-3 mb-5 border-b border-gray-100 pb-4">
                        <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center">
                            <i id="form_icon" data-lucide="plus" class="w-5 h-5 text-[#2563EB]"></i>
                        </div>
                        <h3 class="text-xl leading-6 font-bold text-gray-900" id="form_title">Add Faculty</h3>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="col-span-2 sm:col-span-1">
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Faculty Name <span class="text-red-500">*</span></label>
                                <input type="text" id="form_name" name="name" required class="block w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] sm:text-sm font-medium text-gray-800 placeholder-gray-400" placeholder="e.g. Faculty of Computer Science">
                            </div>
                            <div class="col-span-2 sm:col-span-1">
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Faculty Code <span class="text-red-500">*</span></label>
                                <input type="text" id="form_code" name="code" required class="block w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] sm:text-sm font-medium text-gray-800 placeholder-gray-400" placeholder="e.g. FCS">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="col-span-2 sm:col-span-1">
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Faculty Type</label>
                                <div class="relative">
                                    <select id="form_type" name="type" class="block w-full py-2.5 pl-3 pr-10 border border-gray-200 bg-gray-50 rounded-xl text-sm font-medium text-gray-700 focus:bg-white focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors cursor-pointer appearance-none">
                                        <option value="Academic Faculty">Academic Faculty</option>
                                        <option value="Department">Department</option>
                                        <option value="Administrative Office">Administrative Office</option>
                                        <option value="Support Unit">Support Unit</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400"><i data-lucide="chevron-down" class="w-4 h-4"></i></div>
                                </div>
                            </div>
                            <div class="col-span-2 sm:col-span-1">
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Status</label>
                                <div class="relative">
                                    <select id="form_status" name="status" class="block w-full py-2.5 pl-3 pr-10 border border-gray-200 bg-gray-50 rounded-xl text-sm font-medium text-gray-700 focus:bg-white focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors cursor-pointer appearance-none">
                                        <option value="Active">Active</option>
                                        <option value="Inactive">Inactive</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400"><i data-lucide="chevron-down" class="w-4 h-4"></i></div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Vision</label>
                            <textarea id="form_vision" name="vision" rows="2" class="block w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] sm:text-sm font-medium text-gray-800 resize-none placeholder-gray-400" placeholder="Vision..."></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Mission</label>
                            <textarea id="form_mission" name="mission" rows="2" class="block w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] sm:text-sm font-medium text-gray-800 resize-none placeholder-gray-400" placeholder="Mission..."></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Description</label>
                            <textarea id="form_desc" name="description" rows="2" class="block w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] sm:text-sm font-medium text-gray-800 resize-none placeholder-gray-400" placeholder="Description..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-100 rounded-b-2xl">
                    <button type="submit" id="btn_submit" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-5 py-2.5 bg-[#2563EB] text-base font-bold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#2563EB] sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                        Save
                    </button>
                    <button type="button" onclick="closeModal('modal_form')" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-200 shadow-sm px-5 py-2.5 bg-white text-base font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Cannot Delete Modal -->
<div id="modal_cannot_delete" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeModal('modal_cannot_delete')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="modal-panel inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full border-t-4 border-amber-500">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-amber-100 mb-5">
                    <i data-lucide="shield-alert" class="h-8 w-8 text-amber-600"></i>
                </div>
                <h3 class="text-xl leading-6 font-bold text-gray-900 mb-2">Cannot Delete Faculty</h3>
                <p class="text-sm text-gray-500 mb-4 px-4">
                    This faculty contains courses and cannot be deleted.
                </p>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-center border-t border-gray-100 rounded-b-2xl">
                <button type="button" onclick="closeModal('modal_cannot_delete')" class="w-full sm:w-auto inline-flex justify-center rounded-xl border border-transparent shadow-sm px-8 py-2.5 bg-gray-900 text-base font-bold text-white hover:bg-gray-800 sm:text-sm transition-colors">
                    OK
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="modal_delete" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeModal('modal_delete')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="modal-panel inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full border-t-4 border-red-500">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-50 mb-5">
                    <i data-lucide="alert-triangle" class="h-8 w-8 text-red-600"></i>
                </div>
                <h3 class="text-xl leading-6 font-bold text-gray-900 mb-2">Delete Faculty?</h3>
                <p class="text-sm text-gray-500 mb-4 px-4">
                    Are you sure you want to delete this faculty? This action cannot be undone.
                </p>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-100 rounded-b-2xl">
                <button type="button" id="btn_confirm_delete" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-5 py-2.5 bg-red-600 text-base font-bold text-white hover:bg-red-700 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                    Delete
                </button>
                <button type="button" onclick="closeModal('modal_delete')" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-200 shadow-sm px-5 py-2.5 bg-white text-base font-semibold text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const csrfToken = '<?php echo $csrf_token; ?>';
const facultiesData = <?php echo json_encode($faculties_data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
let deleteFacultyId = null;

function openModal(id) {
    const el = document.getElementById(id);
    el.classList.remove('hidden');
    const panel = el.querySelector('.modal-panel');
    if (panel) {
        panel.classList.add('relative', 'z-10');
        setTimeout(() => {
            panel.style.opacity = '1';
            panel.style.transform = 'scale(1)';
        }, 10);
    }
}

function closeModal(id) {
    const el = document.getElementById(id);
    const panel = el.querySelector('.modal-panel');
    if (panel) {
        panel.style.opacity = '0';
        panel.style.transform = 'scale(0.95)';
    }
    setTimeout(() => {
        el.classList.add('hidden');
    }, 200);
}

document.querySelectorAll('.modal-panel').forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'scale(0.95)';
    el.style.transition = 'all 200ms ease-out';
});

function viewFaculty(fac) {
    try {
        document.getElementById('view_name').textContent = fac.name || 'Unknown';
        document.getElementById('view_code').textContent = fac.code || 'N/A';
        document.getElementById('view_type').textContent = fac.type || 'N/A';
        document.getElementById('view_vision').textContent = fac.vision || 'N/A';
        document.getElementById('view_mission').textContent = fac.mission || 'N/A';
        document.getElementById('view_desc').textContent = fac.description || 'N/A';
        document.getElementById('view_courses').textContent = fac.courses || '0';
        document.getElementById('view_created').textContent = fac.created_at || 'N/A';
        document.getElementById('view_updated').textContent = fac.updated_at || 'N/A';
        
        const iconEl = document.getElementById('view_icon');
        if (iconEl) {
            const iconParent = iconEl.parentElement;
            iconParent.innerHTML = `<i id="view_icon" data-lucide="${fac.icon || 'building-2'}" class="h-6 w-6"></i>`;
            if (window.lucide) lucide.createIcons({ root: iconParent });
        }
        
        openModal('modal_view');
    } catch(err) {
        console.error(err);
        alert('Error viewing faculty.');
    }
}

function openAddModal() {
    document.getElementById('form_title').textContent = 'Add Faculty';
    const iconEl = document.getElementById('form_icon');
    const iconParent = iconEl.parentElement;
    iconParent.innerHTML = `<i id="form_icon" data-lucide="plus" class="w-5 h-5 text-[#2563EB]"></i>`;
    iconParent.className = 'w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center';
    lucide.createIcons({ root: iconParent });
    
    document.getElementById('form_faculty').reset();
    document.getElementById('form_id').value = '';
    
    const btn = document.getElementById('btn_submit');
    btn.className = 'w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-5 py-2.5 bg-[#2563EB] text-base font-bold text-white hover:bg-blue-700 sm:ml-3 sm:w-auto sm:text-sm transition-colors';
    btn.innerHTML = 'Save';

    openModal('modal_form');
}

function openEditModal(fac) {
    try {
        document.getElementById('form_title').textContent = 'Edit Faculty';
        const iconEl = document.getElementById('form_icon');
        if (iconEl) {
            const iconParent = iconEl.parentElement;
            iconParent.innerHTML = `<i id="form_icon" data-lucide="pencil" class="w-5 h-5 text-amber-600"></i>`;
            iconParent.className = 'w-10 h-10 rounded-full bg-amber-50 flex items-center justify-center';
            if (window.lucide) lucide.createIcons({ root: iconParent });
        }
        
        document.getElementById('form_id').value = fac.id;
        document.getElementById('form_name').value = fac.name || '';
        document.getElementById('form_code').value = fac.code || '';
        document.getElementById('form_type').value = fac.type || 'Academic Faculty';
        document.getElementById('form_status').value = fac.status || 'Active';
        document.getElementById('form_vision').value = fac.vision || '';
        document.getElementById('form_mission').value = fac.mission || '';
        document.getElementById('form_desc').value = fac.description || '';
        
        const btn = document.getElementById('btn_submit');
        btn.className = 'w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-5 py-2.5 bg-amber-600 text-base font-bold text-white hover:bg-amber-700 sm:ml-3 sm:w-auto sm:text-sm transition-colors';
        btn.innerHTML = 'Save Changes';

        openModal('modal_form');
    } catch(err) {
        console.error(err);
        alert('Error loading edit form.');
    }
}

async function submitFaculty(e) {
    e.preventDefault();
    const btn = document.getElementById('btn_submit');
    const originalText = btn.innerHTML;
    btn.innerHTML = `<i data-lucide="loader-2" class="w-4 h-4 mr-2 animate-spin"></i> Saving...`;
    btn.disabled = true;
    
    const formData = new FormData(e.target);
    const params = new URLSearchParams();
    for (const pair of formData) {
        params.append(pair[0], pair[1]);
    }
    params.append('csrf_token', csrfToken);
    
    const isEdit = document.getElementById('form_id').value !== '';
    const endpoint = isEdit ? '../ajax/update_faculty.php' : '../ajax/create_faculty.php';

    try {
        const res = await fetch(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: params.toString()
        });
        const data = await res.json();
        if(data.success) {
            closeModal('modal_form');
            const alertBox = document.getElementById('top_success_alert');
            document.getElementById('top_success_message').textContent = isEdit ? 'Faculty updated successfully.' : 'Faculty created successfully.';
            alertBox.classList.remove('hidden');
            alertBox.classList.add('flex');
            window.scrollTo({top: 0, behavior: 'smooth'});
            
            setTimeout(() => {
                window.location.reload();
            }, 1500);
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

function requestDelete(id, courseCount) {
    if (courseCount > 0) {
        openModal('modal_cannot_delete');
    } else {
        deleteFacultyId = id;
        openModal('modal_delete');
    }
}

document.getElementById('btn_confirm_delete').addEventListener('click', async function() {
    if (!deleteFacultyId) return;
    
    const btn = this;
    const originalText = btn.innerHTML;
    btn.innerHTML = `<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>`;
    btn.disabled = true;
    
    try {
        const res = await fetch('../ajax/delete_faculty.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${deleteFacultyId}&csrf_token=${csrfToken}`
        });
        const data = await res.json();
        if(data.success) {
            closeModal('modal_delete');
            const alertBox = document.getElementById('top_success_alert');
            document.getElementById('top_success_message').textContent = 'Faculty deleted successfully.';
            alertBox.classList.remove('hidden');
            alertBox.classList.add('flex');
            window.scrollTo({top: 0, behavior: 'smooth'});
            
            setTimeout(() => {
                window.location.reload();
            }, 1500);
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

// Search, Filter, Sort Logic
const searchInput = document.getElementById('search_units');
const typeFilter = document.getElementById('filter_type');
const statusFilter = document.getElementById('filter_status');
const sortOrder = document.getElementById('sort_order');
const allCards = Array.from(document.querySelectorAll('.unit-card'));
const emptyState = document.getElementById('search_empty_state');
const sections = document.querySelectorAll('.section-container');

function applyFiltersAndSort() {
    const term = searchInput.value.toLowerCase();
    const type = typeFilter.value;
    const status = statusFilter.value;
    const sort = sortOrder.value;

    let visibleCount = 0;

    // Filter
    allCards.forEach(card => {
        const name = card.getAttribute('data-name');
        const code = card.getAttribute('data-code');
        const cType = card.getAttribute('data-type');
        const cStatus = card.getAttribute('data-status');
        
        const matchesSearch = name.includes(term) || code.includes(term);
        const matchesType = type === 'all' || cType === type;
        const matchesStatus = status === 'all' || cStatus === status;

        if (matchesSearch && matchesType && matchesStatus) {
            card.classList.remove('hidden');
            visibleCount++;
        } else {
            card.classList.add('hidden');
        }
    });

    // Sort within each section
    sections.forEach(section => {
        const grid = section.querySelector('.section-grid');
        const cardsInSection = Array.from(grid.querySelectorAll('.unit-card'));
        
        cardsInSection.sort((a, b) => {
            if (sort === 'az') {
                return a.getAttribute('data-name').localeCompare(b.getAttribute('data-name'));
            } else if (sort === 'za') {
                return b.getAttribute('data-name').localeCompare(a.getAttribute('data-name'));
            } else if (sort === 'newest') {
                return parseInt(b.getAttribute('data-date')) - parseInt(a.getAttribute('data-date'));
            }
            return 0;
        });
        
        cardsInSection.forEach(card => grid.appendChild(card)); // Reattach in new order
        
        // Hide section if no visible cards
        const visibleInSection = cardsInSection.filter(c => !c.classList.contains('hidden')).length;
        if (visibleInSection === 0) {
            section.classList.add('hidden');
        } else {
            section.classList.remove('hidden');
        }
    });

    // Empty state
    if (visibleCount === 0 && allCards.length > 0) {
        emptyState.classList.remove('hidden');
        emptyState.classList.add('flex');
    } else {
        emptyState.classList.add('hidden');
        emptyState.classList.remove('flex');
    }
}

if (searchInput) {
    searchInput.addEventListener('input', applyFiltersAndSort);
    typeFilter.addEventListener('change', applyFiltersAndSort);
    statusFilter.addEventListener('change', applyFiltersAndSort);
    sortOrder.addEventListener('change', applyFiltersAndSort);

    // Initial sort
    applyFiltersAndSort();
}

// Event delegation for card buttons
function viewFacultyById(id) {
    const fac = facultiesData[id];
    if(fac) viewFaculty(fac);
}

function editFacultyById(id) {
    const fac = facultiesData[id];
    if(fac) openEditModal(fac);
}
</script>

<?php include '../includes/footer.php'; ?>
