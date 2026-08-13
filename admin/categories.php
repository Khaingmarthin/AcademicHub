<?php
require_once '../config/session.php';
require_admin();
require_once '../config/db.php';
require_once '../config/functions.php';

// Fetch all categories with announcement counts
$stmt = $pdo->query("
    SELECT c.*, COUNT(a.id) as announcement_count 
    FROM categories c 
    LEFT JOIN announcements a ON c.id = a.category_id 
    GROUP BY c.id 
    ORDER BY c.category_name ASC
");
$categories = $stmt->fetchAll();

$system_categories = ['General', 'Event', 'Exam', 'Timetable'];
$csrf_token = generate_csrf_token();
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>
<?php include '../includes/navbar.php'; ?>

<div class="min-h-screen bg-[#F8FAFC] pb-12">
    <div class="w-full px-4 sm:px-6 lg:px-8 pt-8">
        
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Categories</h1>
            <p class="mt-2 text-sm text-gray-500 font-medium">Manage announcement categories used throughout the system.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">


            <!-- LEFT COLUMN: Category Grid -->
            <div class="lg:col-span-8 flex flex-col space-y-6">
                
                <!-- Success Alert (Hidden by default) -->
                <div id="top_success_alert" class="hidden items-center justify-between px-5 py-3 rounded-xl bg-green-50/90 border border-green-200 shadow-sm backdrop-blur-sm transition-all duration-300">
                    <div class="flex items-center gap-3">
                        <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
                        <p class="text-sm font-semibold text-green-700">Category updated successfully!</p>
                    </div>
                </div>
                <!-- Search Bar -->
                <div class="relative w-full max-w-md">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i data-lucide="search" class="w-4 h-4 text-gray-400"></i>
                    </div>
                    <input type="text" id="search_categories" placeholder="Search categories..." 
                        class="block w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl leading-5 bg-white shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] sm:text-sm font-medium transition-all duration-200 text-gray-800">
                </div>

                <!-- Cards Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5" id="category_grid">
                    <?php 
                    $has_custom = false;
                    foreach ($categories as $cat): 
                        $is_system = in_array($cat['category_name'], $system_categories);
                        if (!$is_system) $has_custom = true;
                        
                        $cat_color = $cat['color'] ?? '#2563EB';
                        $cat_icon = $cat['icon'] ?? 'folder';
                        
                        // Enforce system colors
                        if ($is_system) {
                            if ($cat['category_name'] === 'General') $cat_color = '#2563EB';
                            if ($cat['category_name'] === 'Event') $cat_color = '#22C55E';
                            if ($cat['category_name'] === 'Exam') $cat_color = '#F59E0B';
                            if ($cat['category_name'] === 'Timetable') $cat_color = '#9333EA';
                        }
                    ?>
                    <div class="category-card group relative flex flex-col bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md hover:-translate-y-1 transition-all duration-300" data-name="<?php echo strtolower(htmlspecialchars($cat['category_name'])); ?>">
                        

                        
                        <div class="p-5 flex-1 flex flex-col">
                            <!-- Header -->
                            <div class="flex justify-between items-start mb-4">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-gray-50 border border-gray-100" style="color: <?php echo $cat_color; ?>">
                                    <i data-lucide="<?php echo $cat_icon; ?>" class="w-5 h-5"></i>
                                </div>
                                
                                <?php if ($is_system): ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700 uppercase tracking-wider border border-blue-100 shadow-sm">
                                        System Category
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold bg-gray-50 text-gray-500 uppercase tracking-wider border border-gray-200">
                                        Custom Category
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Body -->
                            <h3 class="text-lg font-bold text-gray-900 mb-1 leading-tight">
                                <?php echo htmlspecialchars($cat['category_name']); ?>
                            </h3>
                            
                            <p class="text-sm text-gray-500 line-clamp-2 mb-4 flex-1">
                                <?php echo htmlspecialchars($cat['description'] ?: 'No description provided.'); ?>
                            </p>
                            
                            <!-- Count -->
                            <div class="mt-auto flex items-center gap-1.5 text-xs font-semibold text-gray-500 bg-gray-50/50 w-fit px-2.5 py-1.5 rounded-lg border border-gray-100">
                                <i data-lucide="megaphone" class="w-3.5 h-3.5 text-gray-400"></i>
                                <?php echo $cat['announcement_count']; ?> Announcements
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="bg-gray-50/50 border-t border-gray-100 p-2 grid grid-cols-3 gap-2 opacity-100 transition-opacity duration-200">
                            <button onclick="viewCategory(<?php echo htmlspecialchars(json_encode([
                                'id' => $cat['id'],
                                'name' => $cat['category_name'],
                                'description' => $cat['description'],
                                'color' => $cat_color,
                                'icon' => $cat_icon,
                                'count' => $cat['announcement_count'],
                                'is_system' => $is_system,
                                'created_at' => date('M d, Y', strtotime($cat['created_at']))
                            ])); ?>)" class="flex items-center justify-center gap-1.5 py-2 rounded-lg text-[#2563EB] hover:bg-blue-50 transition-colors text-xs font-bold">
                                <i data-lucide="eye" class="w-3.5 h-3.5"></i> View
                            </button>
                            <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode([
                                'id' => $cat['id'],
                                'name' => $cat['category_name'],
                                'description' => $cat['description'],
                                'color' => $cat_color,
                                'icon' => $cat_icon
                            ])); ?>)" class="flex items-center justify-center gap-1.5 py-2 rounded-lg text-amber-600 hover:bg-amber-50 transition-colors text-xs font-bold">
                                <i data-lucide="pencil" class="w-3.5 h-3.5"></i> Edit
                            </button>
                            <button onclick="requestDelete(<?php echo $cat['id']; ?>, <?php echo $is_system ? 'true' : 'false'; ?>)" class="flex items-center justify-center gap-1.5 py-2 rounded-lg text-red-600 hover:bg-red-50 transition-colors text-xs font-bold">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Empty State for Search -->
                <div id="search_empty_state" class="hidden flex-col items-center justify-center py-20 px-4 bg-white rounded-xl shadow-sm border border-gray-100 text-center">
                    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                        <i data-lucide="search-x" class="w-8 h-8 text-gray-400"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">No matches found</h3>
                    <p class="text-sm text-gray-500">We couldn't find any categories matching your search.</p>
                </div>
                
                <?php if (!$has_custom && count($categories) > 0): ?>
                <div class="flex flex-col items-center justify-center py-12 px-4 bg-gray-50/50 rounded-xl border border-gray-200 border-dashed text-center mt-4">
                    <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center mb-3">
                        <i data-lucide="shield-check" class="w-8 h-8 text-blue-500"></i>
                    </div>
                    <h3 class="text-md font-bold text-gray-900 mb-1">Only system categories are currently available.</h3>
                    <p class="text-sm text-gray-500 max-w-sm">Use the form on the left to create custom categories for your announcements.</p>
                </div>
                <?php endif; ?>

            </div>
        
            <!-- RIGHT COLUMN: Add Form -->
            <div class="lg:col-span-4">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sticky top-24">
                    <h2 class="text-lg font-bold text-gray-900 mb-5 flex items-center gap-2">
                        <i data-lucide="folder-plus" class="w-5 h-5 text-[#2563EB]"></i> Add New Category
                    </h2>
                    
                    <form id="form_create_category" class="space-y-4" onsubmit="createCategory(event)">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Category Name <span class="text-red-500">*</span></label>
                            <input type="text" id="create_name" name="name" required class="block w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] sm:text-sm transition-all duration-200 text-gray-800 placeholder-gray-400" placeholder="e.g. Club Activities">
                        </div>
                        
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Description</label>
                            <textarea id="create_desc" name="description" rows="3" class="block w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] sm:text-sm transition-all duration-200 text-gray-800 resize-none placeholder-gray-400" placeholder="Optional description..."></textarea>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Color</label>
                                <div class="flex items-center gap-2">
                                    <input type="color" id="create_color" name="color" value="#2563EB" class="block w-10 h-10 p-0.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#2563EB]/20 cursor-pointer bg-white">
                                    <span class="text-xs text-gray-500 font-medium" id="color_hex">#2563EB</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Icon</label>
                                <div class="relative">
                                    <select id="create_icon" name="icon" class="block w-full py-2.5 pl-3 pr-10 border border-gray-200 bg-gray-50 rounded-xl text-sm font-medium text-gray-700 focus:bg-white focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors cursor-pointer appearance-none">
                                        <option value="folder">Folder</option>
                                        <option value="tag">Tag</option>
                                        <option value="bookmark">Bookmark</option>
                                        <option value="calendar">Calendar</option>
                                        <option value="bell">Bell</option>
                                        <option value="award">Award</option>
                                        <option value="book">Book</option>
                                        <option value="hash">Hash</option>
                                        <option value="star">Star</option>
                                        <option value="megaphone">Megaphone</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400">
                                        <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" id="btn_submit_create" class="w-full mt-6 flex items-center justify-center px-5 py-2.5 border border-transparent text-sm font-bold rounded-xl text-white bg-[#2563EB] hover:bg-blue-700 shadow-sm hover:shadow-md transition-all duration-200">
                            Create Category
                        </button>
                    </form>
                </div>
            </div>
</div>
    </div>
</div>

<!-- Modals -->

<!-- View Modal -->
<div id="modal_view" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeModal('modal_view')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div id="view_icon_container" class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full sm:mx-0 sm:h-10 sm:w-10">
                        <i id="view_icon" data-lucide="folder" class="h-6 w-6"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-xl leading-6 font-bold text-gray-900 mb-1" id="view_name">Category Name</h3>
                        <div id="view_badge_container" class="mb-4"></div>
                        <div class="mt-2 bg-gray-50 rounded-xl p-4 border border-gray-100">
                            <p class="text-sm text-gray-700" id="view_desc">Description goes here.</p>
                        </div>
                        <div class="mt-5 grid grid-cols-2 gap-4 border-t border-gray-100 pt-5">
                            <div>
                                <span class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Announcements</span>
                                <span class="text-sm font-bold text-gray-900 flex items-center gap-1.5"><i data-lucide="megaphone" class="w-4 h-4 text-gray-400"></i> <span id="view_count">0</span></span>
                            </div>
                            <div>
                                <span class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Created Date</span>
                                <span class="text-sm font-bold text-gray-900 flex items-center gap-1.5"><i data-lucide="calendar" class="w-4 h-4 text-gray-400"></i> <span id="view_date">Date</span></span>
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

<!-- Edit Modal -->
<div id="modal_edit" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeModal('modal_edit')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
            <form id="form_edit_category" onsubmit="updateCategory(event)">
                <input type="hidden" id="edit_id" name="id">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-center gap-3 mb-5 border-b border-gray-100 pb-4">
                        <div class="w-10 h-10 rounded-full bg-amber-50 flex items-center justify-center">
                            <i data-lucide="pencil" class="w-5 h-5 text-amber-600"></i>
                        </div>
                        <h3 class="text-xl leading-6 font-bold text-gray-900">Edit Category</h3>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Category Name <span class="text-red-500">*</span></label>
                            <input type="text" id="edit_name" name="name" required class="block w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 sm:text-sm font-medium text-gray-800">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Description</label>
                            <textarea id="edit_desc" name="description" rows="3" class="block w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 sm:text-sm font-medium text-gray-800 resize-none"></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Color</label>
                                <div class="flex items-center gap-2">
                                    <input type="color" id="edit_color" name="color" class="block w-10 h-10 p-0.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-500/20 cursor-pointer bg-white">
                                    <span class="text-xs text-gray-500 font-medium" id="edit_color_hex">#000000</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Icon</label>
                                <div class="relative">
                                    <select id="edit_icon" name="icon" class="block w-full py-2.5 pl-3 pr-10 border border-gray-200 bg-gray-50 rounded-xl text-sm font-medium text-gray-700 focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-colors cursor-pointer appearance-none">
                                        <option value="folder">Folder</option>
                                        <option value="tag">Tag</option>
                                        <option value="bookmark">Bookmark</option>
                                        <option value="calendar">Calendar</option>
                                        <option value="bell">Bell</option>
                                        <option value="award">Award</option>
                                        <option value="book">Book</option>
                                        <option value="hash">Hash</option>
                                        <option value="star">Star</option>
                                        <option value="megaphone">Megaphone</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400">
                                        <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-100 rounded-b-2xl">
                    <button type="submit" id="btn_submit_edit" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-5 py-2.5 bg-amber-600 text-base font-bold text-white hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                        Save Changes
                    </button>
                    <button type="button" onclick="closeModal('modal_edit')" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-200 shadow-sm px-5 py-2.5 bg-white text-base font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Cannot Delete System Category Modal -->
<div id="modal_system_warning" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeModal('modal_system_warning')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full border-t-4 border-amber-500">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-amber-100 mb-5">
                    <i data-lucide="shield-alert" class="h-8 w-8 text-amber-600"></i>
                </div>
                <h3 class="text-xl leading-6 font-bold text-gray-900 mb-2">Cannot Delete Category</h3>
                <p class="text-sm text-gray-500 mb-4 px-4">
                    This is a built-in system category and cannot be deleted because it is required for the proper operation of the Academic Hub.
                </p>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-center border-t border-gray-100 rounded-b-2xl">
                <button type="button" onclick="closeModal('modal_system_warning')" class="w-full sm:w-auto inline-flex justify-center rounded-xl border border-transparent shadow-sm px-8 py-2.5 bg-gray-900 text-base font-bold text-white hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900 sm:text-sm transition-colors">
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
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full border-t-4 border-red-500">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-50 mb-5">
                    <i data-lucide="alert-triangle" class="h-8 w-8 text-red-600"></i>
                </div>
                <h3 class="text-xl leading-6 font-bold text-gray-900 mb-2">Delete Category?</h3>
                <p class="text-sm text-gray-500 mb-4 px-4">
                    Are you sure you want to delete this category? Announcements assigned to this category will have their category unset. This action cannot be undone.
                </p>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-100 rounded-b-2xl">
                <button type="button" id="btn_confirm_delete" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-5 py-2.5 bg-red-600 text-base font-bold text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                    Delete
                </button>
                <button type="button" onclick="closeModal('modal_delete')" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-200 shadow-sm px-5 py-2.5 bg-white text-base font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// CSRF Token
const csrfToken = '<?php echo $csrf_token; ?>';
let deleteCategoryId = null;

// Color Hex update listeners
document.getElementById('create_color').addEventListener('input', function(e) {
    document.getElementById('color_hex').textContent = e.target.value.toUpperCase();
});
document.getElementById('edit_color').addEventListener('input', function(e) {
    document.getElementById('edit_color_hex').textContent = e.target.value.toUpperCase();
});

// Modal Utilities
function openModal(id) {
    const el = document.getElementById(id);
    el.classList.remove('hidden');
    // slight delay for transition
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

// Initialization to make modals start with hidden scale
document.querySelectorAll('.inline-block.align-bottom').forEach(el => {
    el.classList.add('scale-95', 'opacity-0', 'transition-all', 'duration-200');
});

// View Logic
function viewCategory(cat) {
    document.getElementById('view_name').textContent = cat.name;
    document.getElementById('view_desc').textContent = cat.description || 'No description provided.';
    document.getElementById('view_count').textContent = cat.count;
    document.getElementById('view_date').textContent = cat.created_at;
    
    document.getElementById('view_icon_container').style.backgroundColor = cat.color + '1A'; // 10% opacity
    document.getElementById('view_icon_container').style.color = cat.color;
    
    // Replace icon
    const iconEl = document.getElementById('view_icon');
    iconEl.setAttribute('data-lucide', cat.icon);
    lucide.createIcons();
    
    // Badge
    const badgeContainer = document.getElementById('view_badge_container');
    if (cat.is_system) {
        badgeContainer.innerHTML = `<span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700 uppercase tracking-wider border border-blue-100">System Category</span>`;
    } else {
        badgeContainer.innerHTML = `<span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold bg-gray-50 text-gray-500 uppercase tracking-wider border border-gray-200">Custom Category</span>`;
    }
    
    openModal('modal_view');
}

// Edit Logic
function openEditModal(cat) {
    document.getElementById('edit_id').value = cat.id;
    document.getElementById('edit_name').value = cat.name;
    document.getElementById('edit_desc').value = cat.description;
    document.getElementById('edit_color').value = cat.color;
    document.getElementById('edit_color_hex').textContent = cat.color.toUpperCase();
    document.getElementById('edit_icon').value = cat.icon;
    
    openModal('modal_edit');
}

async function updateCategory(e) {
    e.preventDefault();
    const btn = document.getElementById('btn_submit_edit');
    const originalText = btn.innerHTML;
    btn.innerHTML = `<i data-lucide="loader-2" class="w-4 h-4 mr-2 animate-spin"></i> Saving...`;
    btn.disabled = true;
    
    const formData = new FormData(e.target);
    formData.append('csrf_token', csrfToken);
    
    try {
        const res = await fetch('../ajax/update_category.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if(data.success) {
            closeModal('modal_edit');
            const alertBox = document.getElementById('top_success_alert');
            alertBox.classList.remove('hidden');
            alertBox.classList.add('flex');
            window.scrollTo({top: 0, behavior: 'smooth'});
            
            setTimeout(() => {
                window.location.reload();
            }, 2000);
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

// Create Logic
async function createCategory(e) {
    e.preventDefault();
    const btn = document.getElementById('btn_submit_create');
    const originalText = btn.innerHTML;
    btn.innerHTML = `<i data-lucide="loader-2" class="w-4 h-4 mr-2 animate-spin"></i> Creating...`;
    btn.disabled = true;
    
    const formData = new FormData(e.target);
    const params = new URLSearchParams();
    for (const pair of formData) {
        params.append(pair[0], pair[1]);
    }
    params.append('csrf_token', csrfToken);
    
    try {
        const res = await fetch('../ajax/create_category.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: params.toString()
        });
        const data = await res.json();
        if(data.success) {
            window.location.reload();
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

// Delete Logic
function requestDelete(id, isSystem) {
    if (isSystem) {
        openModal('modal_system_warning');
    } else {
        deleteCategoryId = id;
        openModal('modal_delete');
    }
}

document.getElementById('btn_confirm_delete').addEventListener('click', async function() {
    if (!deleteCategoryId) return;
    
    const btn = this;
    const originalText = btn.innerHTML;
    btn.innerHTML = `<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>`;
    btn.disabled = true;
    
    try {
        const res = await fetch('../ajax/delete_category.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${deleteCategoryId}&csrf_token=${csrfToken}`
        });
        const data = await res.json();
        if(data.success) {
            // Because we don't have complex client side state, just reload the page to refresh everything cleanly
            window.location.reload();
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

// Search Logic
document.getElementById('search_categories').addEventListener('input', function(e) {
    const term = e.target.value.toLowerCase();
    const cards = document.querySelectorAll('.category-card');
    let visibleCount = 0;
    
    cards.forEach(card => {
        const name = card.getAttribute('data-name');
        if (name.includes(term)) {
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
});
</script>

<?php include '../includes/footer.php'; ?>
