<?php
require_once '../config/session.php';
require_admin();
require_once '../config/db.php';
require_once '../config/functions.php';

// Fetch filter options for initial rendering
$categories = $pdo->query("SELECT id, category_name FROM categories ORDER BY category_name ASC")->fetchAll();
$academic_years = $pdo->query("SELECT id, year_name as name FROM academic_years ORDER BY year_name DESC")->fetchAll();
$current_academic_year_id = $_SESSION['current_academic_year_id'] ?? '';
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>
<?php include '../includes/navbar.php'; ?>

<!-- Page Content Background -->
<div class="min-h-screen bg-[#F5F7FB] pb-12">
    <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 pt-8">
        
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Announcements</h1>
            <p class="mt-2 text-sm text-gray-600 font-medium">Manage all university announcements efficiently.</p>
        </div>

        <!-- Top Toolbar -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <!-- Search Bar -->
            <div class="relative w-full md:w-96 group flex">
                <div class="relative flex-1">
                    <input type="text" id="filter_search" placeholder="Search announcements..." 
                        class="block w-full px-4 py-2.5 border border-gray-200 rounded-l-xl leading-5 bg-white shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 sm:text-sm transition-all duration-200">
                </div>
                <button id="btn_search" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-r-xl shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500/20 border border-blue-600 border-l-0">
                    Search
                </button>
            </div>

            <!-- Add Button -->
            <a href="create_announcement.php" class="inline-flex items-center justify-center px-5 py-2.5 border border-transparent text-sm font-semibold rounded-xl text-white bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 shadow-md hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 w-full md:w-auto">
                <i data-lucide="plus" class="w-5 h-5 mr-2"></i>
                Add Announcement
            </a>
        </div>

        <!-- Filter Section -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                
                <!-- Category -->
                <div>
                    <label for="filter_category" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Category</label>
                    <div class="relative">
                        <select id="filter_category" class="block w-full py-2 pl-3 pr-10 border border-gray-200 bg-gray-50 rounded-lg text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors cursor-pointer appearance-none">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                            <i data-lucide="chevron-down" class="w-4 h-4"></i>
                        </div>
                    </div>
                </div>

                <!-- Status -->
                <div>
                    <label for="filter_status" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Status</label>
                    <div class="relative">
                        <select id="filter_status" class="block w-full py-2 pl-3 pr-10 border border-gray-200 bg-gray-50 rounded-lg text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors cursor-pointer appearance-none">
                            <option value="">All Statuses</option>
                            <option value="published">Published</option>
                            <option value="draft">Draft</option>
                            <option value="archived">Archived</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                            <i data-lucide="chevron-down" class="w-4 h-4"></i>
                        </div>
                    </div>
                </div>

                <!-- Date From -->
                <div>
                    <label for="filter_date_from" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">From Date</label>
                    <input type="date" id="filter_date_from" class="block w-full py-2 px-3 border border-gray-200 bg-gray-50 rounded-lg text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors cursor-pointer text-gray-700">
                </div>

                <!-- Date To -->
                <div>
                    <label for="filter_date_to" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">To Date</label>
                    <input type="date" id="filter_date_to" class="block w-full py-2 px-3 border border-gray-200 bg-gray-50 rounded-lg text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors cursor-pointer text-gray-700">
                </div>
            </div>

            <!-- Bottom Filter Actions -->
            <div class="mt-5 pt-4 border-t border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <div class="flex items-center space-x-2">
                        <span class="text-sm font-medium text-gray-600">Sort:</span>
                        <div class="relative">
                            <select id="filter_sort" class="py-1.5 pl-3 pr-8 border border-gray-200 bg-gray-50 rounded-lg text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors cursor-pointer appearance-none font-medium">
                                <option value="newest">Newest First</option>
                                <option value="oldest">Oldest First</option>
                                <option value="most_viewed">Most Viewed</option>
                                <option value="recently_updated">Recently Updated</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                                <i data-lucide="chevron-down" class="w-4 h-4"></i>
                            </div>
                        </div>
                    </div>
                    <div class="h-6 w-px bg-gray-200 hidden sm:block"></div>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm font-medium text-gray-600">Show:</span>
                        <div class="relative">
                            <select id="filter_limit" class="py-1.5 pl-3 pr-8 border border-gray-200 bg-gray-50 rounded-lg text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors cursor-pointer appearance-none font-medium">
                                <option value="10">10</option>
                                <option value="20">20</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                                <i data-lucide="chevron-down" class="w-4 h-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <button id="btn_reset" class="flex-1 sm:flex-none px-4 py-2 border border-gray-200 text-gray-600 bg-white hover:bg-gray-50 rounded-lg text-sm font-medium transition-colors shadow-sm">
                        Reset Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Announcements Grid Container -->
        <div class="relative min-h-[300px] mb-6">
            <!-- Loading Indicator (Hidden by default) -->
            <div id="loading_state" class="absolute inset-0 flex items-center justify-center bg-gray-50/50 backdrop-blur-sm z-10 hidden rounded-2xl">
                <div class="flex flex-col items-center bg-white p-4 rounded-xl shadow-lg border border-gray-100">
                    <i data-lucide="loader-2" class="w-8 h-8 text-blue-600 animate-spin mb-2"></i>
                    <span class="text-sm font-bold text-gray-800">Loading announcements...</span>
                </div>
            </div>
            
            <div id="announcements_grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Dynamic Content Injected Here -->
            </div>
        </div>

        <!-- Empty State (Hidden by default) -->
        <div id="empty_state" class="hidden flex-col items-center justify-center py-20 px-4 bg-white rounded-2xl shadow-sm border border-gray-100 text-center col-span-full">
            <div class="w-24 h-24 bg-blue-50 rounded-full flex items-center justify-center mb-5">
                <i data-lucide="inbox" class="w-10 h-10 text-[#2563EB]"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">No announcements available.</h3>
            <p class="text-sm text-gray-500 mb-6 max-w-sm mx-auto leading-relaxed">It looks like there are no announcements matching your filters, or none have been created yet.</p>
            <div class="flex gap-3">
                <button onclick="document.getElementById('btn_reset').click()" class="px-5 py-2.5 border border-gray-200 text-gray-700 bg-white hover:bg-gray-50 rounded-xl text-sm font-semibold transition-colors shadow-sm">
                    Clear Filters
                </button>
                <a href="create_announcement.php" class="inline-flex items-center justify-center px-5 py-2.5 border border-transparent text-sm font-semibold rounded-xl text-white bg-[#2563EB] hover:bg-blue-700 shadow-sm hover:shadow-md transition-all duration-200">
                    <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                    Create Announcement
                </a>
            </div>
        </div>

        <!-- Pagination -->
        <div id="pagination_container" class="mt-8 flex flex-col sm:flex-row items-center justify-between gap-4 bg-white p-4 rounded-xl shadow-sm border border-gray-100 hidden">
            <div class="text-sm text-gray-600 font-medium" id="pagination_text">
                <!-- Showing 1-10 of 125 announcements -->
            </div>
            <div class="flex items-center space-x-1" id="pagination_links">
                <!-- Buttons Injected Here -->
            </div>
        </div>

    </div>
</div>

<script>
// State
let currentPage = 1;

// Elements
// Elements
const gridContainer = document.getElementById('announcements_grid');
const loading = document.getElementById('loading_state');
const emptyState = document.getElementById('empty_state');
const paginationContainer = document.getElementById('pagination_container');
const paginationText = document.getElementById('pagination_text');
const paginationLinks = document.getElementById('pagination_links');

const filters = {
    search: document.getElementById('filter_search'),
    category_id: document.getElementById('filter_category'),
    status_filter: document.getElementById('filter_status'),
    date_from: document.getElementById('filter_date_from'),
    date_to: document.getElementById('filter_date_to'),
    sort: document.getElementById('filter_sort'),
    limit: document.getElementById('filter_limit')
};

// Event Listeners for Live Filtering
let debounceTimer;
Object.values(filters).forEach(el => {
    if (el) {
        el.addEventListener(el.tagName === 'INPUT' && el.type === 'text' ? 'input' : 'change', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                currentPage = 1;
                fetchAnnouncements();
            }, 300);
        });
    }
});

document.getElementById('btn_reset').addEventListener('click', () => {
    Object.values(filters).forEach(el => {
        if(el && el.id !== 'filter_limit' && el.id !== 'filter_sort') {
            el.value = '';
        }
    });
    // Reset defaults
    filters.sort.value = 'newest';
    filters.limit.value = '10';
    currentPage = 1;
    fetchAnnouncements();
});

document.getElementById('btn_search').addEventListener('click', () => {
    currentPage = 1;
    fetchAnnouncements();
});

// Fetch function
async function fetchAnnouncements() {
    loading.classList.remove('hidden');
    emptyState.classList.add('hidden');
    gridContainer.innerHTML = '';

    const params = new URLSearchParams({
        page: currentPage,
        search: filters.search.value,
        category_id: filters.category_id.value,
        status_filter: filters.status_filter.value,
        date_from: filters.date_from.value,
        date_to: filters.date_to.value,
        sort: filters.sort.value,
        limit: filters.limit.value,
        csrf_token: '<?php echo generate_csrf_token(); ?>'
    });

    try {
        const response = await fetch(`../ajax/fetch_announcements.php?${params.toString()}`);
        const data = await response.json();
        
        loading.classList.add('hidden');

        if(data.success) {
            renderGrid(data.announcements);
            renderPagination(data.pagination);
            lucide.createIcons();
        } else {
            console.error(data.message);
            showEmptyState();
        }
    } catch (error) {
        console.error('Error fetching announcements:', error);
        loading.classList.add('hidden');
        showEmptyState();
    }
}

function showEmptyState() {
    gridContainer.classList.add('hidden');
    paginationContainer.classList.add('hidden');
    emptyState.classList.remove('hidden');
    emptyState.classList.add('flex');
}

function renderGrid(items) {
    if(!items || items.length === 0) {
        showEmptyState();
        return;
    }
    
    gridContainer.classList.remove('hidden');
    emptyState.classList.add('hidden');
    emptyState.classList.remove('flex');
    
    items.forEach(item => {
        gridContainer.appendChild(createCard(item));
    });
}

function createCard(a) {
    const card = document.createElement('div');
    card.className = 'group relative flex flex-col bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md hover:-translate-y-1 transition-all duration-300';
    
    // Category colors
    let catColor = 'bg-gray-100 text-gray-700';
    const catName = a.category_name ? a.category_name.toLowerCase() : '';
    if (catName.includes('event')) catColor = 'bg-purple-100 text-purple-700';
    else if (catName.includes('exam')) catColor = 'bg-red-100 text-red-700';
    else if (catName.includes('timetable')) catColor = 'bg-orange-100 text-orange-700';
    else if (catName.includes('general')) catColor = 'bg-[#2563EB]/10 text-[#2563EB]';
    else catColor = 'bg-gray-100 text-gray-700';

    // Status Badge
    let statusBadge = '';
    if (a.status_text === 'Draft') {
        statusBadge = `<span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold bg-gray-100 text-gray-600 uppercase tracking-wider">Draft</span>`;
    } else if (a.status_text === 'Published') {
        statusBadge = `<span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold bg-[#22C55E]/10 text-[#22C55E] uppercase tracking-wider">Published</span>`;
    } else if (a.status_text === 'Archived') {
        statusBadge = `<span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold bg-gray-100 text-gray-500 uppercase tracking-wider">Archived</span>`;
    }

    // Urgent Ribbon
    let urgentRibbon = '';
    if (a.is_urgent == 1) {
        urgentRibbon = `
            <div class="absolute top-4 -right-10 w-32 rotate-45 bg-[#EF4444] text-white text-[10px] font-bold py-1 text-center shadow-sm z-10 tracking-widest">
                URGENT
            </div>
        `;
    }
    
    // Attachments logic
    let attachmentsHtml = '';
    if (a.attachment_count > 0) {
        const attName = a.first_attachment_name || 'Attachment file';
        const moreCount = a.attachment_count - 1;
        attachmentsHtml = `
            <div class="mt-4 flex items-center gap-2">
                <div class="flex items-center gap-1.5 px-2.5 py-1.5 bg-[#F8FAFC] border border-gray-100 rounded-lg max-w-[200px]">
                    <i data-lucide="file-text" class="w-3.5 h-3.5 text-gray-400 flex-shrink-0"></i>
                    <span class="text-xs text-gray-600 font-medium truncate">${escapeHtml(attName)}</span>
                </div>
                ${moreCount > 0 ? `<span class="text-[10px] font-bold text-gray-500 bg-gray-100 px-2 py-1 rounded-md">+${moreCount} more</span>` : ''}
            </div>
        `;
    }

    // Strip HTML from content for preview
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = a.content || '';
    const plainTextContent = tempDiv.textContent || tempDiv.innerText || '';
    
    card.innerHTML = `
        ${urgentRibbon}
        <div class="p-5 flex-1 flex flex-col">
            <!-- Header -->
            <div class="flex justify-between items-start mb-3">
                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider ${catColor}">
                    ${escapeHtml(a.category_name)}
                </span>
                ${statusBadge}
            </div>
            
            <!-- Body -->
            <h3 class="text-base font-bold text-gray-900 leading-tight mb-2 line-clamp-2" title="${escapeHtml(a.title)}">
                ${escapeHtml(a.title)}
            </h3>
            
            <p class="text-sm text-gray-500 line-clamp-3 mb-4 flex-1">
                ${escapeHtml(plainTextContent)}
            </p>
            
            ${attachmentsHtml}
            
            <!-- Footer Info -->
            <div class="mt-5 pt-4 border-t border-gray-50 grid grid-cols-2 gap-y-2 gap-x-4">
                <div class="flex items-center gap-1.5 text-xs text-gray-500 font-medium">
                    <i data-lucide="calendar" class="w-3.5 h-3.5 text-gray-400"></i>
                    ${a.publish_date_formatted}
                </div>
                <div class="flex items-center gap-1.5 text-xs text-gray-500 font-medium truncate">
                    <i data-lucide="user" class="w-3.5 h-3.5 text-gray-400"></i>
                    ${escapeHtml(a.author_name || 'Admin')}
                </div>
                <div class="flex items-center gap-1.5 text-xs text-gray-500 font-medium">
                    <i data-lucide="eye" class="w-3.5 h-3.5 text-gray-400"></i>
                    ${a.view_count || 0} Views
                </div>
                <div class="flex items-center gap-1.5 text-xs text-gray-500 font-medium">
                    <i data-lucide="message-square" class="w-3.5 h-3.5 text-gray-400"></i>
                    ${a.comment_count || 0} Comments
                </div>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="bg-gray-50/50 border-t border-gray-100 p-2 grid grid-cols-3 gap-2 opacity-100 transition-opacity duration-200">
            <a href="view_announcement.php?id=${a.id}" class="flex items-center justify-center gap-1.5 py-2 rounded-lg text-[#2563EB] hover:bg-blue-50 transition-colors text-xs font-bold">
                <i data-lucide="eye" class="w-3.5 h-3.5"></i> View
            </a>
            <a href="edit_announcement.php?id=${a.id}" class="flex items-center justify-center gap-1.5 py-2 rounded-lg text-[#F59E0B] hover:bg-amber-50 transition-colors text-xs font-bold">
                <i data-lucide="pencil" class="w-3.5 h-3.5"></i> Edit
            </a>
            <button onclick="deleteAnnouncement(${a.id})" class="flex items-center justify-center gap-1.5 py-2 rounded-lg text-[#EF4444] hover:bg-red-50 transition-colors text-xs font-bold">
                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete
            </button>
        </div>
    `;
    return card;
}

function renderPagination(pg) {
    if(pg.total_items === 0) {
        paginationContainer.classList.add('hidden');
        return;
    }
    
    paginationContainer.classList.remove('hidden');
    
    const end = Math.min(pg.offset + pg.limit, pg.total_items);
    const start = pg.offset + 1;
    paginationText.innerHTML = `Showing <span class="font-bold text-gray-900">${start}–${end}</span> of <span class="font-bold text-gray-900">${pg.total_items}</span> announcements`;
    
    let html = '';
    
    // Prev
    const prevDisabled = pg.current_page <= 1;
    html += `<button onclick="changePage(${pg.current_page - 1})" ${prevDisabled ? 'disabled' : ''} 
                class="px-4 py-2 rounded-xl border border-gray-200 bg-white text-sm font-semibold text-gray-600 ${prevDisabled ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50 hover:text-[#2563EB] shadow-sm hover:shadow'} transition-all duration-200 flex items-center">
                <i data-lucide="chevron-left" class="w-4 h-4 mr-1.5"></i> Prev
             </button>`;
             
    // Page Numbers (Simple window)
    let startPage = Math.max(1, pg.current_page - 2);
    let endPage = Math.min(pg.total_pages, startPage + 4);
    if(endPage - startPage < 4) startPage = Math.max(1, endPage - 4);
    
    for(let i = startPage; i <= endPage; i++) {
        const active = i === pg.current_page;
        const cls = active ? 
            'bg-[#2563EB] text-white font-bold shadow-md hover:bg-blue-700' : 
            'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50 font-semibold shadow-sm hover:shadow hover:text-[#2563EB]';
        html += `<button onclick="changePage(${i})" class="w-9 h-9 flex items-center justify-center rounded-xl text-sm transition-all duration-200 ${cls}">${i}</button>`;
    }
    
    // Next
    const nextDisabled = pg.current_page >= pg.total_pages;
    html += `<button onclick="changePage(${pg.current_page + 1})" ${nextDisabled ? 'disabled' : ''} 
                class="px-4 py-2 rounded-xl border border-gray-200 bg-white text-sm font-semibold text-gray-600 ${nextDisabled ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50 hover:text-[#2563EB] shadow-sm hover:shadow'} transition-all duration-200 flex items-center">
                Next <i data-lucide="chevron-right" class="w-4 h-4 ml-1.5"></i>
             </button>`;
             
    paginationLinks.innerHTML = html;
}

function changePage(p) {
    currentPage = p;
    fetchAnnouncements();
    window.scrollTo({top: 0, behavior: 'smooth'});
}

function deleteAnnouncement(id) {
    if (confirm('Are you sure you want to delete this announcement? This action cannot be undone.')) {
        fetch('../ajax/delete_announcement.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id=' + id + '&csrf_token=<?php echo generate_csrf_token(); ?>'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                fetchAnnouncements(); // Refresh grid
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An unexpected error occurred.');
        });
    }
}

function escapeHtml(unsafe) {
    if(!unsafe) return '';
    return unsafe
         .toString()
         .replace(/&/g, "&amp;")
         .replace(/</g, "&lt;")
         .replace(/>/g, "&gt;")
         .replace(/"/g, "&quot;")
         .replace(/'/g, "&#039;");
}

// Initial Load
document.addEventListener('DOMContentLoaded', fetchAnnouncements);
</script>

<?php include '../includes/footer.php'; ?>
