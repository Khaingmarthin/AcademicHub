<?php
require_once '../config/session.php';
require_admin();
require_once '../config/db.php';
require_once '../config/functions.php';

// Fetch all academic years
$academic_years = $pdo->query("SELECT * FROM academic_years ORDER BY id DESC")->fetchAll();
$active_ay = array_filter($academic_years, function($ay) { return $ay['status'] === 'active'; });
$active_ay_id = !empty($active_ay) ? reset($active_ay)['id'] : '';

// Fetch all majors
$majors = $pdo->query("SELECT * FROM majors ORDER BY major_name ASC")->fetchAll();

// Fetch all academic year levels
$year_levels = $pdo->query("SELECT * FROM academic_year_levels ORDER BY id ASC")->fetchAll();

// Fetch all classrooms with relations and counts
$stmt = $pdo->query("
    SELECT 
        c.*, 
        ay.year_name as academic_year, 
        m.major_name as major, 
        ayl.level_name as year_level_name,
        (SELECT COUNT(*) FROM users u WHERE u.classroom_id = c.id AND u.role = 'student') as student_count,
        (SELECT COUNT(*) FROM timetables t WHERE t.classroom_id = c.id) as timetable_count
    FROM classrooms c
    LEFT JOIN academic_years ay ON c.academic_year_id = ay.id
    LEFT JOIN majors m ON c.major_id = m.id
    LEFT JOIN academic_year_levels ayl ON c.academic_year_level_id = ayl.id
    ORDER BY ay.year_name DESC, ayl.id ASC, m.major_name ASC, c.section ASC
");
$classrooms = $stmt->fetchAll();

$csrf_token = generate_csrf_token();
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>
<?php include '../includes/navbar.php'; ?>

<div class="min-h-screen bg-[#F8FAFC] pb-12">
    <div class="w-full px-4 sm:px-6 lg:px-8 pt-8">
        
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Classroom Management</h1>
            <p class="mt-2 text-sm text-gray-500 font-medium">Manage classrooms by academic year, major, year level and section.</p>
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
                        <input type="text" id="filter_search" placeholder="Search classroom..." 
                            class="block w-full pl-9 pr-3 py-2.5 border border-gray-200 rounded-lg leading-5 bg-gray-50 placeholder-gray-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] sm:text-sm font-medium transition-all duration-200 text-gray-800">
                    </div>
                    
                    <!-- Filters -->
                    <div class="grid grid-cols-2 md:flex gap-3 w-full md:w-auto">

                        <select id="filter_major" class="block w-full py-2.5 pl-3 pr-8 border border-gray-200 bg-gray-50 rounded-lg text-sm font-medium text-gray-700 focus:bg-white focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors cursor-pointer appearance-none">
                            <option value="">All Majors</option>
                            <?php foreach ($majors as $m): ?>
                            <option value="<?php echo $m['id']; ?>"><?php echo htmlspecialchars($m['major_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select id="filter_year" class="block w-full py-2.5 pl-3 pr-8 border border-gray-200 bg-gray-50 rounded-lg text-sm font-medium text-gray-700 focus:bg-white focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors cursor-pointer appearance-none">
                            <option value="">All Years</option>
                            <?php foreach ($year_levels as $yl): ?>
                            <option value="<?php echo $yl['id']; ?>"><?php echo htmlspecialchars($yl['level_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select id="filter_status" class="block w-full py-2.5 pl-3 pr-8 border border-gray-200 bg-gray-50 rounded-lg text-sm font-medium text-gray-700 focus:bg-white focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors cursor-pointer appearance-none">
                            <option value="">All Statuses</option>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                
                <button onclick="openAddModal()" class="w-full xl:w-auto flex-shrink-0 flex items-center justify-center gap-2 px-5 py-2.5 border border-transparent text-sm font-bold rounded-lg text-white bg-[#2563EB] hover:bg-blue-700 shadow-sm hover:shadow-md transition-all duration-200">
                    <i data-lucide="plus" class="w-4 h-4"></i> Add Classroom
                </button>
            </div>

            <!-- Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5" id="classroom_grid">
                <?php foreach ($classrooms as $c): ?>
                <div class="classroom-card group relative flex flex-col bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md hover:-translate-y-1 transition-all duration-300" 
                     data-search="<?php echo strtolower(htmlspecialchars($c['classroom_name'] . ' ' . $c['academic_year'] . ' ' . $c['major'] . ' ' . $c['year_level_name'] . ' ' . $c['section'])); ?>"
                     data-ay="<?php echo $c['academic_year_id']; ?>"
                     data-major="<?php echo $c['major_id']; ?>"
                     data-year="<?php echo $c['academic_year_level_id']; ?>"
                     data-status="<?php echo htmlspecialchars($c['status']); ?>">
                    
                    <div class="p-5 flex-1 flex flex-col">
                        <!-- Header / Status -->
                        <div class="flex justify-between items-start mb-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-black tracking-widest bg-blue-50 text-[#2563EB] border border-blue-100">
                                <?php echo htmlspecialchars($c['academic_year']); ?>
                            </span>
                            
                            <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider border <?php echo $c['status'] === 'Active' ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200'; ?>">
                                <?php echo htmlspecialchars($c['status']); ?>
                            </span>
                        </div>
                        
                        <!-- Title -->
                        <h3 class="text-xl font-bold text-gray-900 mb-4 leading-tight">
                            <?php echo htmlspecialchars($c['classroom_name']); ?>
                        </h3>
                        
                        <!-- Details Grid -->
                        <div class="grid grid-cols-2 gap-3 mb-4">
                            <div>
                                <span class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-0.5">Major</span>
                                <span class="text-sm font-semibold text-gray-800"><?php echo htmlspecialchars($c['major']); ?></span>
                            </div>
                            <div>
                                <span class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-0.5">Year Level</span>
                                <span class="text-sm font-semibold text-gray-800"><?php echo htmlspecialchars($c['year_level_name']); ?></span>
                            </div>
                        </div>

                        <!-- Badges -->
                        <div class="mt-auto flex flex-wrap gap-2 pt-2 border-t border-gray-50">
                            <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded text-xs font-semibold <?php echo $c['student_count'] > 0 ? 'bg-indigo-50 text-indigo-700' : 'bg-gray-50 text-gray-500'; ?>">
                                <i data-lucide="users" class="w-3.5 h-3.5"></i> <?php echo $c['student_count']; ?> Students
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded text-xs font-semibold <?php echo $c['timetable_count'] > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'; ?>">
                                <i data-lucide="calendar" class="w-3.5 h-3.5"></i> 
                                <?php echo $c['timetable_count'] > 0 ? 'Available' : 'Not Uploaded'; ?>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="bg-gray-50/50 border-t border-gray-100 p-4 flex flex-wrap items-center justify-between gap-2">
                        <div class="flex gap-2">
                            <button class="btn-view-classroom inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-200 text-gray-700 rounded-lg text-xs font-semibold hover:bg-gray-50 hover:text-blue-600 transition-colors shadow-sm"
                                data-classroom="<?php echo htmlspecialchars(json_encode([
                                    'name' => $c['classroom_name'],
                                    'ay' => $c['academic_year'],
                                    'major' => $c['major'],
                                    'year' => $c['year_level_name'],
                                    'section' => $c['section'] ?: 'N/A',
                                    'students' => $c['student_count'],
                                    'timetable' => $c['timetable_count'] > 0 ? 'Available' : 'Not Uploaded',
                                    'status' => $c['status'],
                                    'created' => date('M d, Y', strtotime($c['created_at'])),
                                    'updated' => date('M d, Y', strtotime($c['updated_at']))
                                ], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>">
                                <i data-lucide="eye" class="w-3.5 h-3.5"></i> View
                            </button>
                            <button class="btn-edit-classroom inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-200 text-gray-700 rounded-lg text-xs font-semibold hover:bg-gray-50 hover:text-amber-600 transition-colors shadow-sm"
                                data-classroom="<?php echo htmlspecialchars(json_encode([
                                    'id' => $c['id'],
                                    'ay_id' => $c['academic_year_id'],
                                    'major_id' => $c['major_id'],
                                    'year_id' => $c['academic_year_level_id'],
                                    'section' => $c['section'],
                                    'status' => $c['status']
                                ], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>">
                                <i data-lucide="pencil" class="w-3.5 h-3.5"></i> Edit
                            </button>
                            <button onclick="requestDelete(<?php echo $c['id']; ?>)" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-200 text-gray-700 rounded-lg text-xs font-semibold hover:bg-gray-50 hover:text-red-600 transition-colors shadow-sm">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete
                            </button>
                        </div>
                        <button onclick="viewStudents(<?php echo $c['id']; ?>, '<?php echo addslashes($c['classroom_name']); ?>')" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 border border-blue-100 text-blue-700 rounded-lg text-xs font-bold hover:bg-blue-100 transition-colors shadow-sm ml-auto">
                            <i data-lucide="users" class="w-3.5 h-3.5"></i> Students
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Empty State -->
            <div id="search_empty_state" class="hidden flex-col items-center justify-center py-20 px-4 bg-white rounded-xl shadow-sm border border-gray-100 text-center">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                    <i data-lucide="search-x" class="w-8 h-8 text-gray-400"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">No classrooms found</h3>
                <p class="text-sm text-gray-500">Try adjusting your search or filters.</p>
            </div>
            
            <?php if (count($classrooms) === 0): ?>
            <div class="flex flex-col items-center justify-center py-20 px-4 bg-white rounded-xl shadow-sm border border-gray-100 text-center">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                    <i data-lucide="presentation" class="w-8 h-8 text-gray-400"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">No classrooms found.</h3>
                <p class="text-sm text-gray-500">Start by adding a new classroom to the system.</p>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- Modals -->

<!-- View Modal -->
<div id="modal_view" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeModal('modal_view')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-50 text-indigo-600 sm:mx-0 sm:h-10 sm:w-10">
                        <i data-lucide="presentation" class="h-6 w-6"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded" id="view_ay">Academic Year</span>
                            <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded border" id="view_status">Status</span>
                        </div>
                        <h3 class="text-2xl leading-7 font-bold text-gray-900 mb-5" id="view_name">Classroom Name</h3>
                        
                        <div class="grid grid-cols-2 gap-4 mb-5">
                            <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                                <span class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Major</span>
                                <span class="text-sm font-semibold text-gray-900" id="view_major">-</span>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                                <span class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Year Level</span>
                                <span class="text-sm font-semibold text-gray-900" id="view_year">-</span>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                                <span class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Section</span>
                                <span class="text-sm font-semibold text-gray-900" id="view_section">-</span>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                                <span class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Total Students</span>
                                <span class="text-sm font-semibold text-gray-900" id="view_students">-</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 text-xs text-gray-500 font-medium">
                            <div>
                                <span class="block uppercase tracking-wider text-[10px] mb-0.5">Timetable</span>
                                <span class="text-gray-900" id="view_timetable">-</span>
                            </div>
                            <div class="text-right">
                                <span class="block uppercase tracking-wider text-[10px] mb-0.5">Last Updated</span>
                                <span class="text-gray-900" id="view_updated">-</span>
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
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full">
            <form id="form_classroom" onsubmit="submitClassroom(event)">
                <input type="hidden" id="form_id" name="id">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-center gap-3 mb-5 border-b border-gray-100 pb-4">
                        <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center">
                            <i id="form_icon" data-lucide="plus" class="w-5 h-5 text-[#2563EB]"></i>
                        </div>
                        <h3 class="text-xl leading-6 font-bold text-gray-900" id="form_title">Add Classroom</h3>
                    </div>
                    
                    <div class="space-y-4">
                        <input type="hidden" id="form_ay" name="academic_year_id" value="<?php echo $active_ay_id; ?>">

                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Major <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select id="form_major_id" name="major_id" required class="block w-full py-2.5 pl-3 pr-10 border border-gray-200 bg-gray-50 rounded-xl text-sm font-medium text-gray-700 focus:bg-white focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors cursor-pointer appearance-none">
                                    <option value="">Select Major...</option>
                                    <?php foreach ($majors as $m): ?>
                                    <option value="<?php echo $m['id']; ?>"><?php echo htmlspecialchars($m['major_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400"><i data-lucide="chevron-down" class="w-4 h-4"></i></div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Year Level <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select id="form_year_id" name="academic_year_level_id" required class="block w-full py-2.5 pl-3 pr-10 border border-gray-200 bg-gray-50 rounded-xl text-sm font-medium text-gray-700 focus:bg-white focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors cursor-pointer appearance-none">
                                    <option value="">Select Year Level...</option>
                                    <?php foreach ($year_levels as $yl): ?>
                                    <option value="<?php echo $yl['id']; ?>"><?php echo htmlspecialchars($yl['level_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400"><i data-lucide="chevron-down" class="w-4 h-4"></i></div>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Section <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <select id="form_section" name="section" required class="block w-full py-2.5 pl-3 pr-10 border border-gray-200 bg-gray-50 rounded-xl text-sm font-medium text-gray-700 focus:bg-white focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors cursor-pointer appearance-none">
                                        <option value="">Select Section...</option>
                                        <option value="A">A</option>
                                        <option value="B">B</option>
                                        <option value="C">C</option>
                                        <option value="None">None</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400"><i data-lucide="chevron-down" class="w-4 h-4"></i></div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Status</label>
                                <div class="relative">
                                    <select id="form_status_val" name="status" class="block w-full py-2.5 pl-3 pr-10 border border-gray-200 bg-gray-50 rounded-xl text-sm font-medium text-gray-700 focus:bg-white focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors cursor-pointer appearance-none">
                                        <option value="Active">Active</option>
                                        <option value="Inactive">Inactive</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400"><i data-lucide="chevron-down" class="w-4 h-4"></i></div>
                                </div>
                            </div>
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

<!-- Delete Confirmation Modal -->
<div id="modal_delete" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeModal('modal_delete')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full border-t-4 border-red-500">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-50 mb-5">
                    <i data-lucide="alert-triangle" class="h-8 w-8 text-red-600"></i>
                </div>
                <h3 class="text-xl leading-6 font-bold text-gray-900 mb-2">Delete Classroom?</h3>
                <p class="text-sm text-gray-500 mb-4 px-4">
                    Are you sure you want to delete this classroom? This action cannot be undone.
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

<!-- View Students Modal -->
<div id="modal_students" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeModal('modal_students')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="flex items-center gap-3 mb-5 border-b border-gray-100 pb-4">
                    <div class="w-10 h-10 rounded-full bg-purple-50 flex items-center justify-center">
                        <i data-lucide="users" class="w-5 h-5 text-purple-600"></i>
                    </div>
                    <div>
                        <h3 class="text-xl leading-6 font-bold text-gray-900">Students in Classroom</h3>
                        <p class="text-sm text-gray-500 font-medium" id="students_classroom_name"></p>
                    </div>
                </div>
                
                <div id="students_list_container" class="max-h-96 overflow-y-auto">
                    <!-- Loaded via AJAX -->
                    <div class="flex justify-center py-8">
                        <i data-lucide="loader-2" class="w-8 h-8 animate-spin text-gray-400"></i>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-100 rounded-b-2xl">
                <button type="button" onclick="closeModal('modal_students')" class="w-full inline-flex justify-center rounded-xl border border-gray-200 shadow-sm px-5 py-2.5 bg-white text-base font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const csrfToken = '<?php echo $csrf_token; ?>';
let deleteClassroomId = null;

function openModal(id) {
    const el = document.getElementById(id);
    // Move modal to document.body to escape overflow-hidden parent
    if (el.parentElement !== document.body) {
        document.body.appendChild(el);
    }
    el.classList.remove('hidden');
    // Find the modal content div and show it
    const box = el.querySelector('div.inline-block');
    if (box) {
        box.style.opacity = '1';
        box.style.transform = 'scale(1)';
        box.style.transition = 'opacity 200ms, transform 200ms';
    }
}

function closeModal(id) {
    const el = document.getElementById(id);
    const box = el.querySelector('div.inline-block');
    if (box) {
        box.style.opacity = '0';
        box.style.transform = 'scale(0.95)';
    }
    setTimeout(() => {
        el.classList.add('hidden');
    }, 200);
}

function viewClassroom(c) {
    document.getElementById('view_name').textContent = c.name;
    document.getElementById('view_ay').textContent = c.ay;
    document.getElementById('view_major').textContent = c.major;
    document.getElementById('view_year').textContent = c.year;
    document.getElementById('view_section').textContent = c.section;
    document.getElementById('view_students').textContent = c.students;
    document.getElementById('view_timetable').textContent = c.timetable;
    document.getElementById('view_updated').textContent = c.updated;
    
    const sEl = document.getElementById('view_status');
    sEl.textContent = c.status;
    if (c.status === 'Active') {
        sEl.className = 'text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded border bg-green-50 text-green-700 border-green-200';
    } else {
        sEl.className = 'text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded border bg-red-50 text-red-700 border-red-200';
    }

    openModal('modal_view');
}

function openAddModal() {
    document.getElementById('form_title').textContent = 'Add Classroom';
    const iconEl = document.getElementById('form_icon');
    iconEl.setAttribute('data-lucide', 'plus');
    iconEl.className = 'w-5 h-5 text-[#2563EB]';
    iconEl.parentElement.className = 'w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center';
    lucide.createIcons();
    
    document.getElementById('form_classroom').reset();
    document.getElementById('form_id').value = '';
    // Set default AY
    document.getElementById('form_ay').value = '<?php echo $active_ay_id; ?>';
    
    const btn = document.getElementById('btn_submit');
    btn.className = 'w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-5 py-2.5 bg-[#2563EB] text-base font-bold text-white hover:bg-blue-700 sm:ml-3 sm:w-auto sm:text-sm transition-colors';
    btn.innerHTML = 'Save';

    openModal('modal_form');
}

function openEditModal(c) {
    document.getElementById('form_title').textContent = 'Edit Classroom';
    const iconEl = document.getElementById('form_icon');
    iconEl.setAttribute('data-lucide', 'pencil');
    iconEl.className = 'w-5 h-5 text-amber-600';
    iconEl.parentElement.className = 'w-10 h-10 rounded-full bg-amber-50 flex items-center justify-center';
    lucide.createIcons();
    
    document.getElementById('form_id').value = c.id;
    document.getElementById('form_ay').value = c.ay_id;
    document.getElementById('form_major_id').value = c.major_id;
    document.getElementById('form_year_id').value = c.year_id;
    document.getElementById('form_section').value = c.section === '' || c.section == null ? 'None' : c.section;
    document.getElementById('form_status_val').value = c.status;
    
    const btn = document.getElementById('btn_submit');
    btn.className = 'w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-5 py-2.5 bg-amber-600 text-base font-bold text-white hover:bg-amber-700 sm:ml-3 sm:w-auto sm:text-sm transition-colors';
    btn.innerHTML = 'Save Changes';

    openModal('modal_form');
}

async function submitClassroom(e) {
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
    const endpoint = isEdit ? '../ajax/update_classroom.php' : '../ajax/create_classroom.php';

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
            document.getElementById('top_success_message').textContent = isEdit ? 'Classroom updated successfully.' : 'Classroom created successfully.';
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

function requestDelete(id) {
    deleteClassroomId = id;
    openModal('modal_delete');
}

document.getElementById('btn_confirm_delete').addEventListener('click', async function() {
    if (!deleteClassroomId) return;
    
    const btn = this;
    const originalText = btn.innerHTML;
    btn.innerHTML = `<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>`;
    btn.disabled = true;
    
    try {
        const res = await fetch('../ajax/delete_classroom.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${deleteClassroomId}&csrf_token=${csrfToken}`
        });
        const data = await res.json();
        if(data.success) {
            closeModal('modal_delete');
            const alertBox = document.getElementById('top_success_alert');
            document.getElementById('top_success_message').textContent = 'Classroom deleted successfully.';
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
            
            if (data.message.includes('contains students')) {
                closeModal('modal_delete');
            }
        }
    } catch(err) {
        alert('An unexpected error occurred.');
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
});

async function viewStudents(id, name) {
    document.getElementById('students_classroom_name').textContent = name;
    const container = document.getElementById('students_list_container');
    container.innerHTML = `<div class="flex justify-center py-8"><i data-lucide="loader-2" class="w-8 h-8 animate-spin text-gray-400"></i></div>`;
    lucide.createIcons();
    openModal('modal_students');
    
    try {
        const res = await fetch(`../ajax/fetch_classroom_students.php?id=${id}`);
        const data = await res.json();
        if(data.success) {
            if(data.students.length === 0) {
                container.innerHTML = `
                <div class="flex flex-col items-center justify-center py-10 text-center">
                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-3">
                        <i data-lucide="users" class="w-8 h-8 text-gray-300"></i>
                    </div>
                    <p class="text-sm text-gray-500 font-medium">No students are currently assigned to this classroom.</p>
                </div>`;
            } else {
                let html = '<ul class="divide-y divide-gray-100">';
                data.students.forEach(s => {
                    html += `
                    <li class="py-3 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs">
                                ${s.username.charAt(0).toUpperCase()}
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900">${s.username}</p>
                                <p class="text-xs text-gray-500">${s.email}</p>
                            </div>
                        </div>
                    </li>`;
                });
                html += '</ul>';
                container.innerHTML = html;
            }
            lucide.createIcons();
        } else {
            container.innerHTML = `<p class="text-sm text-red-500 text-center py-4">Failed to load students.</p>`;
        }
    } catch(err) {
        container.innerHTML = `<p class="text-sm text-red-500 text-center py-4">An error occurred while fetching students.</p>`;
    }
}

// Search and Filter Logic
function applyFilters() {
    // Check if initial load is done to avoid clearing filters incorrectly
    if (!document.getElementById('filter_search')) return;
    
    const term = document.getElementById('filter_search').value.toLowerCase();
    const majorFilter = document.getElementById('filter_major').value;
    const yearFilter = document.getElementById('filter_year').value;
    const statusFilter = document.getElementById('filter_status').value;
    
    const cards = document.querySelectorAll('.classroom-card');
    let visibleCount = 0;
    
    cards.forEach(card => {
        const searchData = card.getAttribute('data-search');
        const majorData = card.getAttribute('data-major');
        const yearData = card.getAttribute('data-year');
        const statusData = card.getAttribute('data-status');
        
        let match = true;
        if (term && !searchData.includes(term)) match = false;
        if (majorFilter && majorData !== majorFilter) match = false;
        if (yearFilter && yearData !== yearFilter) match = false;
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
document.getElementById('filter_major').addEventListener('change', applyFilters);
document.getElementById('filter_year').addEventListener('change', applyFilters);
document.getElementById('filter_status').addEventListener('change', applyFilters);

// Trigger initial filter application (to respect default active academic year)
document.addEventListener('DOMContentLoaded', applyFilters);

// Event delegation for View and Edit buttons (using data attributes instead of inline onclick)
document.addEventListener('click', function(e) {
    const viewBtn = e.target.closest('.btn-view-classroom');
    if (viewBtn) {
        const data = JSON.parse(viewBtn.getAttribute('data-classroom'));
        viewClassroom(data);
        return;
    }
    const editBtn = e.target.closest('.btn-edit-classroom');
    if (editBtn) {
        const data = JSON.parse(editBtn.getAttribute('data-classroom'));
        openEditModal(data);
        return;
    }
});
</script>

<?php include '../includes/footer.php'; ?>
