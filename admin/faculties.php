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
$faculties_data = [];

foreach ($faculties as $fac) {
    if (in_array(strtoupper($fac['faculty_code']), $academic_codes)) {
        $academic_faculties[] = $fac;
    } else {
        $admin_departments[] = $fac;
    }
}
$stats = [
    'total' => count($faculties),
    'academic' => count($academic_faculties),
    'admin' => count($admin_departments)
];
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>
<?php include '../includes/navbar.php'; ?>



<div class="min-h-screen bg-[#F8FAFC] pb-12">
    <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 pt-8">
        
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Faculty & Department Management</h1>
            <p class="mt-2 text-sm text-gray-500 font-medium">Manage all university faculties, departments and administrative offices.</p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-8">
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
        </div>

        <!-- Success Alert -->
        <div id="top_success_alert" class="hidden items-center justify-between px-5 py-3 rounded-xl bg-green-50/90 border border-green-200 shadow-sm transition-all duration-300 mb-6">
            <div class="flex items-center gap-3">
                <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
                <p class="text-sm font-semibold text-green-700" id="top_success_message">Success!</p>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 mb-6 flex flex-col xl:flex-row gap-4 items-start xl:items-center justify-between">
            <div class="relative w-full xl:w-96 flex-shrink-0">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i data-lucide="search" class="w-4 h-4 text-gray-400"></i>
                </div>
                <input type="text" id="search_units" placeholder="Search units..." 
                    class="block w-full pl-9 pr-3 py-2 border border-gray-200 rounded-lg bg-gray-50 placeholder-gray-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] text-sm font-medium transition-colors text-gray-900 h-[42px]">
            </div>
            
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 xl:flex-shrink-0 w-full xl:w-auto">
                <button onclick="openAddModal()" class="w-full sm:w-auto px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-lg shadow-sm transition-colors flex items-center justify-center gap-2 h-[42px]">
                    <i data-lucide="plus" class="w-4 h-4"></i> Add Unit
                </button>
            </div>
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
            
            $facId = (int)$fac['id'];
            $faculties_data[$facId] = [
                'id' => $fac['id'],
                'name' => $fac['faculty_name'],
                'code' => $fac['faculty_code'],
                'vision' => $fac['vision'],
                'mission' => $fac['mission'],
                'description' => $fac['description'],
                'courses' => $fac['course_count'],
                'created_at' => date('M d, Y', strtotime($fac['created_at'])),
                'updated_at' => date('M d, Y', strtotime($fac['updated_at'])),
                'icon' => $icon
            ];
            
            $desc = htmlspecialchars($fac['description'] ?: ($fac['vision'] ?: 'No description provided.'));
            $name = htmlspecialchars($fac['faculty_name']);
            $dateTimestamp = strtotime($fac['created_at']);
            $courseCount = (int)$fac['course_count'];
            $nameLower = strtolower($name);
            $codeLower = strtolower($code);
            
            
            echo '<div class="unit-card group flex flex-col bg-white rounded-2xl shadow-sm hover:shadow-md hover:-translate-y-1 hover:border-[#2563EB] border border-transparent transition-all duration-300 overflow-hidden" 
                 data-name="' . $nameLower . '" 
                 data-code="' . $codeLower . '"
                 data-type="' . $typeClass . '"
                 data-date="' . $dateTimestamp . '">
                 
                <div class="p-5 flex-1 flex flex-col border-b border-gray-50">
                    <div class="flex justify-between items-start mb-4">
                        <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-md text-[11px] font-bold uppercase tracking-wider border ' . $badgeColor . '">
                            ' . $code . '
                        </span>
                    </div>
                    
                    <h3 class="text-[17px] font-bold text-gray-900 leading-snug mb-3">' . $name . '</h3>
                    
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
                        </div>
                        <h3 class="text-xl leading-6 font-bold text-gray-900 mb-4" id="view_name">Faculty Name</h3>
                        
                        <div class="space-y-4" id="view_details_container">
                            <div id="view_vision_container">
                                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Vision</h4>
                                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 text-sm text-gray-700" id="view_vision"></div>
                            </div>
                            <div id="view_mission_container">
                                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Mission</h4>
                                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 text-sm text-gray-700" id="view_mission"></div>
                            </div>
                            <div id="view_desc_container">
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
        
        const visionContainer = document.getElementById('view_vision_container');
        if (fac.vision && fac.vision.trim() !== '') {
            document.getElementById('view_vision').textContent = fac.vision;
            visionContainer.classList.remove('hidden');
        } else {
            visionContainer.classList.add('hidden');
        }

        const missionContainer = document.getElementById('view_mission_container');
        if (fac.mission && fac.mission.trim() !== '') {
            document.getElementById('view_mission').textContent = fac.mission;
            missionContainer.classList.remove('hidden');
        } else {
            missionContainer.classList.add('hidden');
        }

        const descContainer = document.getElementById('view_desc_container');
        if (fac.description && fac.description.trim() !== '') {
            document.getElementById('view_desc').textContent = fac.description;
            descContainer.classList.remove('hidden');
        } else {
            descContainer.classList.add('hidden');
        }

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

// Search and Filter Logic
const searchInput = document.getElementById('search_units');
const allCards = Array.from(document.querySelectorAll('.unit-card'));
const emptyState = document.getElementById('search_empty_state');
const sections = document.querySelectorAll('.section-container');

function applyFiltersAndSort() {
    const term = searchInput.value.toLowerCase();

    let visibleCount = 0;

    // Filter
    allCards.forEach(card => {
        const name = card.getAttribute('data-name');
        const code = card.getAttribute('data-code');
        
        const matchesSearch = name.includes(term) || code.includes(term);

        if (matchesSearch) {
            card.classList.remove('hidden');
            visibleCount++;
        } else {
            card.classList.add('hidden');
        }
    });

    // Filter within each section
    sections.forEach(section => {
        const grid = section.querySelector('.section-grid');
        const cardsInSection = Array.from(grid.querySelectorAll('.unit-card'));
        
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

    // Initial filter
    applyFiltersAndSort();

    // Restore scroll position after navigation
    if (sessionStorage.getItem('facultiesScroll')) {
        window.scrollTo(0, parseInt(sessionStorage.getItem('facultiesScroll')));
        sessionStorage.removeItem('facultiesScroll');
    }

    // Show update success toast
    if (sessionStorage.getItem('facultyUpdateSuccess')) {
        const alertBox = document.getElementById('top_success_alert');
        if (alertBox) {
            document.getElementById('top_success_message').textContent = 'Faculty updated successfully.';
            alertBox.classList.remove('hidden');
            alertBox.classList.add('flex');
        }
        sessionStorage.removeItem('facultyUpdateSuccess');
    }
}

// Event delegation for card buttons
function viewFacultyById(id) {
    sessionStorage.setItem('facultiesScroll', window.scrollY);
    window.location.href = 'view_faculty.php?id=' + id;
}

function editFacultyById(id) {
    sessionStorage.setItem('facultiesScroll', window.scrollY);
    window.location.href = 'edit_faculty.php?id=' + id;
}
</script>

<?php include '../includes/footer.php'; ?>
