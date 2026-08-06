<?php
require_once '../config/session.php';
require_admin();
require_once '../config/db.php';
require_once '../config/functions.php';

// Fetch all courses with their associated faculties
$stmt = $pdo->query("
    SELECT c.*, f.faculty_name, f.faculty_code 
    FROM courses c 
    LEFT JOIN faculties f ON c.faculty_id = f.id 
    ORDER BY c.year_level ASC, c.major ASC, c.course_code ASC
");
$courses = $stmt->fetchAll();

// Fetch faculties for filter and dropdowns
$faculties = $pdo->query("SELECT id, faculty_name, faculty_code FROM faculties ORDER BY faculty_name ASC")->fetchAll();

// Unique Majors, Years, Semesters for filters
$majors = array_unique(array_filter(array_column($courses, 'major')));
$years = array_unique(array_filter(array_column($courses, 'year_level')));
$semesters = array_unique(array_filter(array_column($courses, 'semester')));

sort($majors);
$year_order = ['First Year' => 1, 'Second Year' => 2, 'Third Year' => 3, 'Fourth Year' => 4, 'Fifth Year' => 5];
usort($years, function($a, $b) use ($year_order) {
    return ($year_order[$a] ?? 99) <=> ($year_order[$b] ?? 99);
});

$csrf_token = generate_csrf_token();
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>
<?php include '../includes/navbar.php'; ?>

<div class="min-h-screen bg-[#F8FAFC] pb-12">
    <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 pt-8">
        
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Course Management</h1>
            <p class="mt-2 text-sm text-gray-500 font-medium">Manage all university courses and subjects.</p>
        </div>

        <div class="flex flex-col space-y-6">
            <!-- Success Alert (Hidden by default) -->
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
                        <input type="text" id="filter_search" placeholder="Search by name, code..." 
                            class="block w-full pl-9 pr-3 py-2.5 border border-gray-200 rounded-lg leading-5 bg-gray-50 placeholder-gray-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] sm:text-sm font-medium transition-all duration-200 text-gray-800">
                    </div>
                    
                    <!-- Filters -->
                    <div class="grid grid-cols-2 md:flex gap-3 w-full md:w-auto">
                        <select id="filter_faculty" class="block w-full py-2.5 pl-3 pr-8 border border-gray-200 bg-gray-50 rounded-lg text-sm font-medium text-gray-700 focus:bg-white focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors cursor-pointer appearance-none">
                            <option value="">All Faculties</option>
                            <?php foreach ($faculties as $f): ?>
                            <option value="<?php echo htmlspecialchars($f['faculty_code']); ?>"><?php echo htmlspecialchars($f['faculty_code']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select id="filter_major" class="block w-full py-2.5 pl-3 pr-8 border border-gray-200 bg-gray-50 rounded-lg text-sm font-medium text-gray-700 focus:bg-white focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors cursor-pointer appearance-none">
                            <option value="">All Majors</option>
                            <?php foreach ($majors as $m): ?>
                            <option value="<?php echo htmlspecialchars($m); ?>"><?php echo htmlspecialchars($m); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <button onclick="openAddModal()" class="w-full xl:w-auto flex-shrink-0 flex items-center justify-center gap-2 px-5 py-2.5 border border-transparent text-sm font-bold rounded-lg text-white bg-[#2563EB] hover:bg-blue-700 shadow-sm hover:shadow-md transition-all duration-200">
                    <i data-lucide="plus" class="w-4 h-4"></i> Add Course
                </button>
            </div>

            <!-- Year Selection UI -->
            <div class="bg-gradient-to-br from-[#2563EB] to-[#1E3A8A] p-8 sm:p-12 rounded-2xl shadow-lg mb-8 flex flex-col items-center justify-center text-center relative overflow-hidden">
                <!-- Decorative background elements -->
                <div class="absolute top-0 left-0 w-full h-full pointer-events-none opacity-20">
                    <div class="absolute -top-16 -right-16 w-64 h-64 rounded-full bg-white blur-3xl"></div>
                    <div class="absolute -bottom-16 -left-16 w-64 h-64 rounded-full bg-blue-400 blur-3xl"></div>
                </div>
                
                <div class="relative z-10 w-full max-w-2xl mx-auto">
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-white mb-3 tracking-tight">Manage Courses by Year</h2>
                    <p class="text-blue-100 mb-8 font-medium text-lg">Select an academic year to instantly view and manage its associated courses.</p>
                    
                    <div class="relative w-full max-w-lg mx-auto group">
                        <select id="main_year_filter" class="block w-full px-6 py-4 border-2 border-transparent bg-white/95 backdrop-blur-md rounded-2xl text-lg font-bold text-gray-800 hover:bg-white focus:bg-white focus:ring-4 focus:ring-white/30 focus:border-white transition-all cursor-pointer appearance-none shadow-xl hover:shadow-2xl outline-none">
                            <option value="">-- Choose Academic Year --</option>
                            <?php foreach ($years as $y): ?>
                            <option value="<?php echo htmlspecialchars($y); ?>"><?php echo htmlspecialchars($y); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-5 flex items-center pointer-events-none">
                            <i data-lucide="chevron-down" class="w-6 h-6 text-gray-500 group-focus-within:text-blue-600 transition-colors"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Initial Empty State -->
            <div id="year_empty_state" class="flex flex-col items-center justify-center py-24 px-4 bg-white rounded-2xl shadow-sm border border-gray-100 text-center">
                <div class="w-24 h-24 bg-blue-50/50 rounded-full flex items-center justify-center mb-6">
                    <i data-lucide="book-open" class="w-10 h-10 text-blue-300"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2 tracking-tight">Awaiting Selection</h3>
                <p class="text-base text-gray-500 font-medium max-w-md mx-auto">Please select an academic year from the dropdown above to display the corresponding courses.</p>
            </div>

            <!-- View Course Detail Panel (Hidden by default) -->
            <div id="view_panel" class="hidden">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-gradient-to-r from-indigo-600 to-blue-600 px-6 py-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                                <i data-lucide="book-open" class="w-5 h-5 text-white"></i>
                            </div>
                            <h3 class="text-lg font-bold text-white">Course Details</h3>
                        </div>
                        <button onclick="closePanel('view_panel')" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/20 hover:bg-white/30 text-white transition-colors">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="text-sm font-bold text-indigo-700 bg-indigo-50 px-3 py-1.5 rounded-lg border border-indigo-100" id="view_code">CODE</span>
                            <span class="text-xs font-bold uppercase tracking-wider px-2.5 py-1 rounded-lg border" id="view_status">Status</span>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-6" id="view_name">Course Name</h2>
                        
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
                            <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                                <span class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Major</span>
                                <span class="text-sm font-semibold text-gray-900" id="view_major">-</span>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                                <span class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Year Level</span>
                                <span class="text-sm font-semibold text-gray-900" id="view_year">-</span>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                                <span class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Semester</span>
                                <span class="text-sm font-semibold text-gray-900" id="view_semester">-</span>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                                <span class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Credits</span>
                                <span class="text-sm font-semibold text-gray-900" id="view_credits">-</span>
                            </div>
                        </div>

                        <div class="mb-6">
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Description</h4>
                            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 text-sm text-gray-700 leading-relaxed" id="view_desc"></div>
                        </div>
                        
                        <div class="mb-6">
                            <span class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Faculty / Department</span>
                            <span class="text-sm font-medium text-gray-900" id="view_faculty"></span>
                        </div>

                        <div class="flex justify-end">
                            <button onclick="closePanel('view_panel')" class="px-6 py-2.5 bg-gray-900 text-white text-sm font-bold rounded-xl hover:bg-gray-800 transition-colors">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Course Panel (Hidden by default) -->
            <div id="edit_panel" class="hidden">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div id="edit_panel_header" class="bg-gradient-to-r from-amber-500 to-orange-500 px-6 py-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                                <i data-lucide="pencil" class="w-5 h-5 text-white"></i>
                            </div>
                            <h3 class="text-lg font-bold text-white" id="form_title">Edit Course</h3>
                        </div>
                        <button onclick="closePanel('edit_panel')" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/20 hover:bg-white/30 text-white transition-colors">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                    <form id="form_course" onsubmit="submitCourse(event)">
                        <input type="hidden" id="form_id" name="id">
                        <div class="p-6">
                            <div class="space-y-4">
                                <div class="grid grid-cols-3 gap-4">
                                    <div class="col-span-3 sm:col-span-2">
                                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Course Name <span class="text-red-500">*</span></label>
                                        <input type="text" id="form_name" name="name" required class="block w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] sm:text-sm font-medium text-gray-800 placeholder-gray-400">
                                    </div>
                                    <div class="col-span-3 sm:col-span-1">
                                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Course Code <span class="text-red-500">*</span></label>
                                        <input type="text" id="form_code" name="code" required class="block w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] sm:text-sm font-medium text-gray-800 placeholder-gray-400">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Faculty / Department <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <select id="form_faculty" name="faculty_id" required class="block w-full py-2.5 pl-3 pr-10 border border-gray-200 bg-gray-50 rounded-xl text-sm font-medium text-gray-700 focus:bg-white focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors cursor-pointer appearance-none">
                                            <option value="">Select Faculty...</option>
                                            <?php foreach ($faculties as $f): ?>
                                            <option value="<?php echo $f['id']; ?>"><?php echo htmlspecialchars($f['faculty_name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400"><i data-lucide="chevron-down" class="w-4 h-4"></i></div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                                    <div class="col-span-2">
                                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Major</label>
                                        <div class="relative">
                                            <select id="form_major" name="major" class="block w-full py-2.5 pl-3 pr-10 border border-gray-200 bg-gray-50 rounded-xl text-sm font-medium text-gray-700 focus:bg-white focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors cursor-pointer appearance-none">
                                                <option value="">N/A</option>
                                                <option value="Computer Science">Computer Science</option>
                                                <option value="Computer Technology">Computer Technology</option>
                                            </select>
                                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400"><i data-lucide="chevron-down" class="w-4 h-4"></i></div>
                                        </div>
                                    </div>
                                    <div class="col-span-2 sm:col-span-1">
                                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Year Level</label>
                                        <div class="relative">
                                            <select id="form_year" name="year_level" class="block w-full py-2.5 pl-3 pr-10 border border-gray-200 bg-gray-50 rounded-xl text-sm font-medium text-gray-700 focus:bg-white focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors cursor-pointer appearance-none">
                                                <option value="">N/A</option>
                                                <option value="First Year">First Year</option>
                                                <option value="Second Year">Second Year</option>
                                                <option value="Third Year">Third Year</option>
                                                <option value="Fourth Year">Fourth Year</option>
                                                <option value="Fifth Year">Fifth Year</option>
                                            </select>
                                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400"><i data-lucide="chevron-down" class="w-4 h-4"></i></div>
                                        </div>
                                    </div>
                                    <div class="col-span-2 sm:col-span-1">
                                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Semester</label>
                                        <div class="relative">
                                            <select id="form_semester" name="semester" class="block w-full py-2.5 pl-3 pr-10 border border-gray-200 bg-gray-50 rounded-xl text-sm font-medium text-gray-700 focus:bg-white focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors cursor-pointer appearance-none">
                                                <option value="">N/A</option>
                                                <option value="First Semester">First Semester</option>
                                                <option value="Second Semester">Second Semester</option>
                                            </select>
                                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400"><i data-lucide="chevron-down" class="w-4 h-4"></i></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Credits</label>
                                        <input type="number" id="form_credits" name="credits" class="block w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] sm:text-sm font-medium text-gray-800 placeholder-gray-400">
                                    </div>
                                    <div>
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
                                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Description</label>
                                    <textarea id="form_desc" name="description" rows="2" class="block w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] sm:text-sm font-medium text-gray-800 resize-none placeholder-gray-400" placeholder="Description..."></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse gap-3 border-t border-gray-100 rounded-b-2xl">
                            <button type="submit" id="btn_submit" class="inline-flex justify-center rounded-xl border border-transparent shadow-sm px-6 py-2.5 bg-[#2563EB] text-sm font-bold text-white hover:bg-blue-700 transition-colors">
                                Save
                            </button>
                            <button type="button" onclick="closePanel('edit_panel')" class="inline-flex justify-center rounded-xl border border-gray-200 shadow-sm px-6 py-2.5 bg-white text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Delete Confirmation Panel (Hidden by default) -->
            <div id="delete_panel" class="hidden">
                <div class="bg-white rounded-2xl shadow-sm border border-red-200 overflow-hidden border-t-4 border-t-red-500">
                    <div class="p-6 text-center">
                        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-50 mb-5">
                            <i data-lucide="alert-triangle" class="h-8 w-8 text-red-600"></i>
                        </div>
                        <h3 class="text-xl leading-6 font-bold text-gray-900 mb-2">Delete Course?</h3>
                        <p class="text-sm text-gray-500 mb-4 px-4">
                            Are you sure you want to delete this course? This action cannot be undone.
                        </p>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse gap-3 border-t border-gray-100 rounded-b-2xl">
                        <button type="button" id="btn_confirm_delete" class="inline-flex justify-center rounded-xl border border-transparent shadow-sm px-6 py-2.5 bg-red-600 text-sm font-bold text-white hover:bg-red-700 transition-colors">
                            Delete
                        </button>
                        <button type="button" onclick="closePanel('delete_panel')" class="inline-flex justify-center rounded-xl border border-gray-200 shadow-sm px-6 py-2.5 bg-white text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>

            <!-- Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5" id="course_grid" style="display: none;">
                <?php foreach ($courses as $c): ?>
                <div class="course-card group relative flex flex-col bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md hover:-translate-y-1 transition-all duration-300" 
                     data-search="<?php echo strtolower(htmlspecialchars($c['course_name'] . ' ' . $c['course_code'])); ?>"
                     data-faculty="<?php echo htmlspecialchars($c['faculty_code'] ?? ''); ?>"
                     data-major="<?php echo htmlspecialchars($c['major'] ?? ''); ?>"
                     data-year="<?php echo htmlspecialchars($c['year_level'] ?? ''); ?>">
                    
                    <div class="p-5 flex-1 flex flex-col">
                        <!-- Header / Status -->
                        <div class="flex justify-between items-start mb-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-black tracking-widest bg-blue-50 text-[#2563EB] border border-blue-100">
                                <?php echo htmlspecialchars($c['course_code']); ?>
                            </span>
                            
                            <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider border <?php echo $c['status'] === 'Active' ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200'; ?>">
                                <?php echo htmlspecialchars($c['status']); ?>
                            </span>
                        </div>
                        
                        <!-- Title -->
                        <h3 class="text-lg font-bold text-gray-900 mb-4 leading-tight line-clamp-2" title="<?php echo htmlspecialchars($c['course_name']); ?>">
                            <?php echo htmlspecialchars($c['course_name']); ?>
                        </h3>
                        
                        <!-- Badges -->
                        <div class="mt-auto flex flex-wrap gap-2">
                            <?php if ($c['major']): ?>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-gray-50 text-gray-600 border border-gray-200">
                                <i data-lucide="award" class="w-3.5 h-3.5 text-gray-400"></i> <?php echo htmlspecialchars($c['major']); ?>
                            </span>
                            <?php endif; ?>
                            
                            <?php if ($c['year_level']): ?>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-gray-50 text-gray-600 border border-gray-200">
                                <i data-lucide="graduation-cap" class="w-3.5 h-3.5 text-gray-400"></i> <?php echo htmlspecialchars($c['year_level']); ?>
                            </span>
                            <?php endif; ?>

                            <?php if ($c['semester']): ?>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-gray-50 text-gray-600 border border-gray-200">
                                <i data-lucide="clock" class="w-3.5 h-3.5 text-gray-400"></i> <?php echo htmlspecialchars($c['semester']); ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="bg-gray-50/50 border-t border-gray-100 p-2 grid grid-cols-3 gap-2">
                        <button class="btn-view-course flex items-center justify-center gap-1.5 py-2 rounded-lg text-[#2563EB] hover:bg-blue-50 transition-colors text-xs font-bold"
                            data-course="<?php echo htmlspecialchars(json_encode([
                                'code' => $c['course_code'],
                                'name' => $c['course_name'],
                                'major' => $c['major'],
                                'year' => $c['year_level'],
                                'semester' => $c['semester'],
                                'credits' => $c['credits'],
                                'faculty' => $c['faculty_name'],
                                'desc' => $c['description'],
                                'status' => $c['status']
                            ], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>">
                            <i data-lucide="eye" class="w-3.5 h-3.5"></i> View
                        </button>
                        <button class="btn-edit-course flex items-center justify-center gap-1.5 py-2 rounded-lg text-amber-600 hover:bg-amber-50 transition-colors text-xs font-bold"
                            data-course="<?php echo htmlspecialchars(json_encode([
                                'id' => $c['id'],
                                'code' => $c['course_code'],
                                'name' => $c['course_name'],
                                'faculty_id' => $c['faculty_id'],
                                'major' => $c['major'],
                                'year' => $c['year_level'],
                                'semester' => $c['semester'],
                                'credits' => $c['credits'],
                                'desc' => $c['description'],
                                'status' => $c['status']
                            ], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>">
                            <i data-lucide="pencil" class="w-3.5 h-3.5"></i> Edit
                        </button>
                        <button onclick="requestDelete(<?php echo $c['id']; ?>)" class="flex items-center justify-center gap-1.5 py-2 rounded-lg text-red-600 hover:bg-red-50 transition-colors text-xs font-bold">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete
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
                <h3 class="text-lg font-bold text-gray-900 mb-1">No courses found</h3>
                <p class="text-sm text-gray-500">Try adjusting your search or filters.</p>
            </div>
            
            <?php if (count($courses) === 0): ?>
            <div class="flex flex-col items-center justify-center py-20 px-4 bg-white rounded-xl shadow-sm border border-gray-100 text-center">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                    <i data-lucide="book-open" class="w-8 h-8 text-gray-400"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">No courses found.</h3>
                <p class="text-sm text-gray-500">Start by adding a new course to the system.</p>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<script>
const csrfToken = '<?php echo $csrf_token; ?>';
let deleteCourseId = null;

// Simple show/hide panel functions - NO modals, NO blur
function showPanel(id) {
    document.getElementById(id).classList.remove('hidden');
    document.getElementById(id).scrollIntoView({ behavior: 'smooth', block: 'center' });
    lucide.createIcons();
}

function closePanel(id) {
    document.getElementById(id).classList.add('hidden');
}

function viewCourse(c) {
    // Hide edit panel if open
    closePanel('edit_panel');
    closePanel('delete_panel');
    
    document.getElementById('view_name').textContent = c.name;
    document.getElementById('view_code').textContent = c.code;
    document.getElementById('view_major').textContent = c.major || '-';
    document.getElementById('view_year').textContent = c.year || '-';
    document.getElementById('view_semester').textContent = c.semester || '-';
    document.getElementById('view_credits').textContent = c.credits || '-';
    document.getElementById('view_faculty').textContent = c.faculty || '-';
    document.getElementById('view_desc').textContent = c.desc || 'No description provided.';
    
    const sEl = document.getElementById('view_status');
    sEl.textContent = c.status;
    if (c.status === 'Active') {
        sEl.className = 'text-xs font-bold uppercase tracking-wider px-2.5 py-1 rounded-lg border bg-green-50 text-green-700 border-green-200';
    } else {
        sEl.className = 'text-xs font-bold uppercase tracking-wider px-2.5 py-1 rounded-lg border bg-red-50 text-red-700 border-red-200';
    }

    showPanel('view_panel');
}

function openAddModal() {
    // Hide other panels
    closePanel('view_panel');
    closePanel('delete_panel');
    
    document.getElementById('form_title').textContent = 'Add New Course';
    document.getElementById('edit_panel_header').className = 'bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4 flex items-center justify-between';
    
    document.getElementById('form_course').reset();
    document.getElementById('form_id').value = '';
    
    const btn = document.getElementById('btn_submit');
    btn.className = 'inline-flex justify-center rounded-xl border border-transparent shadow-sm px-6 py-2.5 bg-[#2563EB] text-sm font-bold text-white hover:bg-blue-700 transition-colors';
    btn.innerHTML = 'Save';

    showPanel('edit_panel');
}

function openEditModal(c) {
    // Hide other panels
    closePanel('view_panel');
    closePanel('delete_panel');
    
    document.getElementById('form_title').textContent = 'Edit Course';
    document.getElementById('edit_panel_header').className = 'bg-gradient-to-r from-amber-500 to-orange-500 px-6 py-4 flex items-center justify-between';
    
    document.getElementById('form_id').value = c.id;
    document.getElementById('form_name').value = c.name;
    document.getElementById('form_code').value = c.code;
    document.getElementById('form_faculty').value = c.faculty_id;
    document.getElementById('form_major').value = c.major;
    document.getElementById('form_year').value = c.year;
    document.getElementById('form_semester').value = c.semester;
    document.getElementById('form_credits').value = c.credits;
    document.getElementById('form_status').value = c.status;
    document.getElementById('form_desc').value = c.desc;
    
    const btn = document.getElementById('btn_submit');
    btn.className = 'inline-flex justify-center rounded-xl border border-transparent shadow-sm px-6 py-2.5 bg-amber-600 text-sm font-bold text-white hover:bg-amber-700 transition-colors';
    btn.innerHTML = 'Save Changes';

    showPanel('edit_panel');
}

async function submitCourse(e) {
    e.preventDefault();
    const btn = document.getElementById('btn_submit');
    const originalText = btn.innerHTML;
    btn.innerHTML = 'Saving...';
    btn.disabled = true;
    
    const formData = new FormData(e.target);
    const params = new URLSearchParams();
    for (const pair of formData) {
        params.append(pair[0], pair[1]);
    }
    params.append('csrf_token', csrfToken);
    
    const isEdit = document.getElementById('form_id').value !== '';
    const endpoint = isEdit ? '../ajax/update_course.php' : '../ajax/create_course.php';

    try {
        const res = await fetch(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: params.toString()
        });
        const data = await res.json();
        if(data.success) {
            closePanel('edit_panel');
            const alertBox = document.getElementById('top_success_alert');
            document.getElementById('top_success_message').textContent = isEdit ? 'Course updated successfully.' : 'Course created successfully.';
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
    closePanel('view_panel');
    closePanel('edit_panel');
    deleteCourseId = id;
    showPanel('delete_panel');
}

document.getElementById('btn_confirm_delete').addEventListener('click', async function() {
    if (!deleteCourseId) return;
    
    const btn = this;
    const originalText = btn.innerHTML;
    btn.innerHTML = 'Deleting...';
    btn.disabled = true;
    
    try {
        const res = await fetch('../ajax/delete_course.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${deleteCourseId}&csrf_token=${csrfToken}`
        });
        const data = await res.json();
        if(data.success) {
            closePanel('delete_panel');
            const alertBox = document.getElementById('top_success_alert');
            document.getElementById('top_success_message').textContent = 'Course deleted successfully.';
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
            
            // Close delete panel if it's a conflict
            if (data.message.includes('students are currently assigned')) {
                closePanel('delete_panel');
            }
        }
    } catch(err) {
        alert('An unexpected error occurred.');
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
});

// Search and Filter Logic
function applyFilters() {
    const term = document.getElementById('filter_search').value.toLowerCase();
    const facFilter = document.getElementById('filter_faculty').value;
    const majorFilter = document.getElementById('filter_major').value;
    const yearFilter = document.getElementById('main_year_filter').value;
    
    const cards = document.querySelectorAll('.course-card');
    const courseGrid = document.getElementById('course_grid');
    const yearEmptyState = document.getElementById('year_empty_state');
    const searchEmptyState = document.getElementById('search_empty_state');
    let visibleCount = 0;
    
    if (!yearFilter) {
        courseGrid.style.display = 'none';
        yearEmptyState.style.display = 'flex';
        searchEmptyState.classList.add('hidden');
        searchEmptyState.classList.remove('flex');
        return;
    } else {
        courseGrid.style.display = '';
        yearEmptyState.style.display = 'none';
    }
    
    cards.forEach(card => {
        const searchData = card.getAttribute('data-search');
        const facData = card.getAttribute('data-faculty');
        const majorData = card.getAttribute('data-major');
        const yearData = card.getAttribute('data-year');
        
        let match = true;
        if (term && !searchData.includes(term)) match = false;
        if (facFilter && facData !== facFilter) match = false;
        if (majorFilter && majorData !== majorFilter) match = false;
        if (yearFilter && yearData !== yearFilter) match = false;
        
        if (match) {
            card.classList.remove('hidden');
            visibleCount++;
        } else {
            card.classList.add('hidden');
        }
    });
    
    if (visibleCount === 0 && cards.length > 0) {
        searchEmptyState.classList.remove('hidden');
        searchEmptyState.classList.add('flex');
    } else {
        searchEmptyState.classList.add('hidden');
        searchEmptyState.classList.remove('flex');
    }
}

document.getElementById('filter_search').addEventListener('input', applyFilters);
document.getElementById('filter_faculty').addEventListener('change', applyFilters);
document.getElementById('filter_major').addEventListener('change', applyFilters);
document.getElementById('main_year_filter').addEventListener('change', applyFilters);

// Initial call to set state
applyFilters();

// Event delegation for View and Edit buttons (using data attributes instead of inline onclick)
document.addEventListener('click', function(e) {
    const viewBtn = e.target.closest('.btn-view-course');
    if (viewBtn) {
        const data = JSON.parse(viewBtn.getAttribute('data-course'));
        viewCourse(data);
        return;
    }
    const editBtn = e.target.closest('.btn-edit-course');
    if (editBtn) {
        const data = JSON.parse(editBtn.getAttribute('data-course'));
        openEditModal(data);
        return;
    }
});
</script>

<?php include '../includes/footer.php'; ?>
