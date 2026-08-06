<?php
require_once '../config/db.php';
require_once '../config/functions.php';

$initial_query = $_GET['q'] ?? '';

// Fetch categories for filter
$catStmt = $pdo->query("SELECT id, category_name as name FROM categories ORDER BY category_name ASC");
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch academic years for filter
$ayStmt = $pdo->query("SELECT id, year_name as name FROM academic_years ORDER BY start_date DESC");
$academic_years = $ayStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Sidebar Filters -->
            <div class="w-full lg:w-1/4">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sticky top-8">
                    <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                        Filters
                    </h2>
                    
                    <form id="search-filters-form" class="space-y-6">
                        <!-- Search Keyword (Hidden desktop, used for mobile or sync) -->
                        <div>
                            <label for="q_filter" class="block text-sm font-medium text-gray-700 mb-1">Keyword</label>
                            <input type="text" id="q_filter" name="q" value="<?php echo htmlspecialchars($initial_query); ?>" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm p-2 border">
                        </div>

                        <!-- Category -->
                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <select id="category" name="category" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm p-2 border bg-white">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Type -->
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Announcement Type</label>
                            <select id="type" name="type" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm p-2 border bg-white">
                                <option value="">All Types</option>
                                <option value="normal">Normal</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>

                        <!-- Date Range -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                            <div class="flex space-x-2">
                                <input type="date" name="date_from" class="w-1/2 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm p-2 border" title="From Date">
                                <input type="date" name="date_to" class="w-1/2 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm p-2 border" title="To Date">
                            </div>
                        </div>

                        <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Apply Filters
                        </button>
                        <button type="button" id="reset-filters" class="w-full mt-2 flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Reset Filters
                        </button>
                    </form>
                </div>
            </div>

            <!-- Search Results Area -->
            <div class="w-full lg:w-3/4">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                        <h1 class="text-2xl font-bold text-gray-900">Search Results</h1>
                        <span id="results-count" class="text-sm text-gray-500 font-medium bg-white px-3 py-1 rounded-full border shadow-sm">0 found</span>
                    </div>

                    <!-- Main Search Bar -->
                    <div class="p-6 border-b border-gray-100">
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                            <input type="text" id="main-search-input" value="<?php echo htmlspecialchars($initial_query); ?>" class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 sm:text-lg border-gray-300 rounded-md p-4 border shadow-sm" placeholder="Search announcements by title or content...">
                        </div>
                    </div>
                    
                    <div class="relative min-h-[400px]">
                        <!-- Loading Spinner -->
                        <div id="loading-spinner" class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center z-10 hidden">
                            <svg class="animate-spin h-10 w-10 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>

                        <!-- Results Container -->
                        <div id="results-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
                            <!-- Populated by JS -->
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div id="pagination-container" class="bg-gray-50 px-6 py-4 flex items-center justify-between border-t border-gray-200 hidden">
                        <!-- Populated by JS -->
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentPage = 1;
    let searchTimeout;

    const resultsContainer = document.getElementById('results-container');
    const paginationContainer = document.getElementById('pagination-container');
    const loadingSpinner = document.getElementById('loading-spinner');
    const resultsCount = document.getElementById('results-count');
    
    const filterForm = document.getElementById('search-filters-form');
    const mainSearchInput = document.getElementById('main-search-input');
    const sidebarSearchInput = document.getElementById('q_filter');

    // Sync search inputs
    mainSearchInput.addEventListener('input', function() {
        sidebarSearchInput.value = this.value;
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentPage = 1;
            fetchResults();
        }, 400); // 400ms debounce
    });

    filterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        mainSearchInput.value = sidebarSearchInput.value;
        currentPage = 1;
        fetchResults();
    });

    document.getElementById('reset-filters').addEventListener('click', function() {
        filterForm.reset();
        mainSearchInput.value = '';
        sidebarSearchInput.value = '';
        currentPage = 1;
        fetchResults();
    });

    function fetchResults() {
        // Inject Skeletons
        resultsContainer.innerHTML = `
            <div class="h-[400px] bg-gray-100 rounded-3xl animate-pulse"></div>
            <div class="h-[400px] bg-gray-100 rounded-3xl animate-pulse hidden md:block"></div>
            <div class="h-[400px] bg-gray-100 rounded-3xl animate-pulse hidden lg:block"></div>
        `;
        paginationContainer.classList.add('hidden');

        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData);
        params.append('page', currentPage);

        fetch(`../ajax/search_results.php?${params.toString()}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    renderResults(data.results);
                    renderPagination(data.pagination);
                    resultsCount.textContent = `${data.pagination.total_items} found`;
                } else {
                    resultsContainer.innerHTML = `<div class="p-8 text-center text-red-500">Error: ${data.message}</div>`;
                }
            })
            .catch(err => {
                resultsContainer.innerHTML = `<div class="p-8 text-center text-red-500">Failed to fetch results.</div>`;
            })
            .finally(() => {
                // Done
            });
    }

    function renderResults(results) {
        if (results.length === 0) {
            resultsContainer.innerHTML = `
                <div class="col-span-1 md:col-span-2 lg:col-span-3 bg-white p-10 rounded-2xl shadow-sm border border-gray-100 text-center">
                    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">No announcements found</h3>
                    <p class="text-gray-500">Try adjusting your filters or search term to find what you are looking for.</p>
                </div>
            `;
            return;
        }

        let html = '';
        results.forEach(r => {
            const urgent_class = r.is_urgent == 1 ? 'border-red-200 ring-2 ring-red-500/20' : 'border-gray-100';
            
            html += `<a href="announcement.php?id=${r.id}" class="group bg-white rounded-3xl shadow-sm hover:shadow-xl border overflow-hidden flex flex-col transform transition-all duration-300 hover:-translate-y-2 ${urgent_class}">`;
            
            html += `<div class="h-48 bg-gray-100 relative overflow-hidden flex items-center justify-center border-b border-gray-100">`;
            if (r.is_urgent == 1) {
                html += `<div class="absolute top-4 left-4 bg-red-500 text-white text-[10px] font-bold px-2 py-1 rounded shadow-md uppercase tracking-wide z-10 animate-pulse flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            Urgent
                         </div>`;
            }
            if (r.image_path) {
                html += `<img src="../${r.image_path}" alt="Announcement Thumbnail" class="w-full h-full object-cover transform transition-transform duration-700 group-hover:scale-105">`;
            } else {
                html += `<div class="absolute inset-0 bg-gradient-to-br from-blue-50 to-indigo-50"></div>
                         <svg class="w-16 h-16 text-blue-200 z-0 transform transition-transform duration-700 group-hover:scale-110" fill="currentColor" viewBox="0 0 20 20"><path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z"></path></svg>`;
            }
            html += `</div>`;
            
            html += `<div class="p-5 sm:p-6 flex-1 flex flex-col">`;
            html += `<div class="flex items-center justify-between mb-4">
                        <span class="text-xs font-bold tracking-wider uppercase text-blue-700 bg-blue-50 px-2.5 py-1 rounded-md">${r.category_name || 'General'}</span>
                        <span class="text-[11px] sm:text-xs text-gray-500 font-medium flex items-center">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            ${r.formatted_date}
                        </span>
                     </div>`;
            
            html += `<h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-3 group-hover:text-blue-600 transition-colors line-clamp-2 leading-snug">${r.title}</h3>`;
            html += `<p class="text-gray-600 text-sm mb-6 line-clamp-3 flex-1">${r.snippet}</p>`;
            
            html += `<div class="mt-auto pt-4 border-t border-gray-100">
                        <span class="inline-flex items-center text-sm font-bold text-blue-600 group-hover:text-blue-800 transition-colors">
                            Read More
                            <svg class="w-4 h-4 ml-1.5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </span>
                     </div>`;
            html += `</div></a>`;
        });
        resultsContainer.innerHTML = html;
    }

    function renderPagination(pg) {
        if (pg.total_pages <= 1) {
            paginationContainer.classList.add('hidden');
            return;
        }
        paginationContainer.classList.remove('hidden');

        let html = `
        <div class="flex-1 flex justify-between sm:hidden">
            <button ${pg.current_page === 1 ? 'disabled' : ''} onclick="changePage(${pg.current_page - 1})" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50">Previous</button>
            <button ${pg.current_page === pg.total_pages ? 'disabled' : ''} onclick="changePage(${pg.current_page + 1})" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50">Next</button>
        </div>
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-700">Page <span class="font-medium">${pg.current_page}</span> of <span class="font-medium">${pg.total_pages}</span></p>
            </div>
            <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">`;

        // Prev
        html += `<button ${pg.current_page === 1 ? 'disabled' : ''} onclick="changePage(${pg.current_page - 1})" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50">
                    <span class="sr-only">Previous</span>
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                </button>`;

        // Pages (Simplified for brevity, just showing a few around current)
        for (let i = 1; i <= pg.total_pages; i++) {
            if (i === 1 || i === pg.total_pages || (i >= pg.current_page - 1 && i <= pg.current_page + 1)) {
                if (i === pg.current_page) {
                    html += `<button aria-current="page" class="z-10 bg-blue-50 border-blue-500 text-blue-600 relative inline-flex items-center px-4 py-2 border text-sm font-medium">${i}</button>`;
                } else {
                    html += `<button onclick="changePage(${i})" class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium">${i}</button>`;
                }
            } else if (i === pg.current_page - 2 || i === pg.current_page + 2) {
                html += `<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>`;
            }
        }

        // Next
        html += `<button ${pg.current_page === pg.total_pages ? 'disabled' : ''} onclick="changePage(${pg.current_page + 1})" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50">
                    <span class="sr-only">Next</span>
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
                </button>
                </nav>
            </div>
        </div>`;

        paginationContainer.innerHTML = html;
    }

    // Attach to window so onclick attributes work
    window.changePage = function(p) {
        currentPage = p;
        fetchResults();
        // Scroll to top of results smoothly
        window.scrollTo({ top: document.querySelector('.bg-gray-50').offsetTop, behavior: 'smooth' });
    };

    // Initial Fetch
    fetchResults();
});
</script>

<?php include '../includes/footer.php'; ?>
