<?php
require_once '../config/session.php';
require_admin();
require_once '../config/db.php';
require_once '../config/functions.php';

// Settings are cached globally by get_setting()
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>
<?php include '../includes/navbar.php'; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">System Settings</h1>
        <p class="mt-2 text-sm text-gray-600">Manage the core configuration and appearance of the application.</p>
    </div>

    <div class="flex flex-col md:flex-row gap-8">
        
        <!-- Settings Tabs Navigation -->
        <div class="w-full md:w-1/4">
            <nav class="flex md:flex-col space-x-2 md:space-x-0 md:space-y-2 overflow-x-auto pb-4 md:pb-0" aria-label="Tabs">
                <button onclick="switchTab('general')" id="tab-btn-general" class="bg-blue-50 text-blue-700 hover:text-blue-700 hover:bg-blue-50 px-4 py-3 font-medium text-sm rounded-lg whitespace-nowrap text-left transition-colors flex items-center group">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    General
                </button>
                <button onclick="switchTab('contact')" id="tab-btn-contact" class="text-gray-500 hover:text-gray-700 hover:bg-gray-50 px-4 py-3 font-medium text-sm rounded-lg whitespace-nowrap text-left transition-colors flex items-center group">
                    <svg class="w-5 h-5 mr-3 text-gray-400 group-hover:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                    Contact Info
                </button>
                <button onclick="switchTab('social')" id="tab-btn-social" class="text-gray-500 hover:text-gray-700 hover:bg-gray-50 px-4 py-3 font-medium text-sm rounded-lg whitespace-nowrap text-left transition-colors flex items-center group">
                    <svg class="w-5 h-5 mr-3 text-gray-400 group-hover:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                    Social Links
                </button>
                <button onclick="switchTab('backup')" id="tab-btn-backup" class="text-gray-500 hover:text-gray-700 hover:bg-gray-50 px-4 py-3 font-medium text-sm rounded-lg whitespace-nowrap text-left transition-colors flex items-center group">
                    <svg class="w-5 h-5 mr-3 text-gray-400 group-hover:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path></svg>
                    Backup & Maintenance
                </button>
            </nav>
        </div>

        <!-- Settings Content Area -->
        <div class="w-full md:w-3/4">
            <div id="alert-container" class="hidden p-4 mb-6 text-sm font-medium rounded-lg border"></div>

            <!-- General Tab -->
            <div id="tab-general" class="tab-content">
                <form class="settings-form bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <input type="hidden" name="section" value="general">
                    
                    <div class="p-8 space-y-6">
                        <h2 class="text-xl font-bold text-gray-900 border-b border-gray-100 pb-2">General Settings</h2>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">University Name</label>
                            <input type="text" name="site_name" value="<?php echo htmlspecialchars(get_setting('site_name')); ?>" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 px-4 py-2 border">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Primary Color (Hex)</label>
                            <div class="flex items-center space-x-4">
                                <input type="color" name="primary_color" value="<?php echo htmlspecialchars(get_setting('primary_color', '#2563eb')); ?>" class="h-10 w-10 border-0 rounded cursor-pointer p-0 bg-transparent">
                                <input type="text" value="<?php echo htmlspecialchars(get_setting('primary_color', '#2563eb')); ?>" class="w-32 border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 px-4 py-2 border" disabled>
                                <p class="text-xs text-gray-500">Used for global branding across the site.</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Site Logo</label>
                            <div class="mt-1 flex items-center space-x-6">
                                <?php $logo = get_setting('logo_path'); ?>
                                <?php if ($logo): ?>
                                    <img src="../<?php echo htmlspecialchars($logo); ?>" alt="Current Logo" class="h-16 w-auto object-contain bg-gray-50 rounded border p-2">
                                <?php else: ?>
                                    <div class="h-16 w-16 bg-gray-100 rounded border border-dashed flex items-center justify-center text-gray-400">No Logo</div>
                                <?php endif; ?>
                                <input type="file" name="logo" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-colors">
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 px-8 py-4 border-t border-gray-100 flex justify-end">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg shadow-sm transition-colors">Save General Settings</button>
                    </div>
                </form>
            </div>

            <!-- Contact Tab -->
            <div id="tab-contact" class="tab-content hidden">
                <form class="settings-form bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <input type="hidden" name="section" value="contact">
                    
                    <div class="p-8 space-y-6">
                        <h2 class="text-xl font-bold text-gray-900 border-b border-gray-100 pb-2">Contact Information</h2>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Official Email</label>
                            <input type="email" name="contact_email" value="<?php echo htmlspecialchars(get_setting('contact_email')); ?>" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 px-4 py-2 border">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                            <input type="text" name="contact_phone" value="<?php echo htmlspecialchars(get_setting('contact_phone')); ?>" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 px-4 py-2 border">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Main Campus Address</label>
                            <textarea name="contact_address" rows="3" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 px-4 py-2 border"><?php echo htmlspecialchars(get_setting('contact_address')); ?></textarea>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 px-8 py-4 border-t border-gray-100 flex justify-end">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg shadow-sm transition-colors">Save Contact Info</button>
                    </div>
                </form>
            </div>

            <!-- Social Tab -->
            <div id="tab-social" class="tab-content hidden">
                <form class="settings-form bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <input type="hidden" name="section" value="social">
                    
                    <div class="p-8 space-y-6">
                        <h2 class="text-xl font-bold text-gray-900 border-b border-gray-100 pb-2">Social Media Links</h2>
                        
                        <div class="relative">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Facebook URL</label>
                            <div class="flex rounded-md shadow-sm">
                                <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                                    <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.469h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.469h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                </span>
                                <input type="url" name="social_facebook" value="<?php echo htmlspecialchars(get_setting('social_facebook')); ?>" class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md focus:ring-blue-500 focus:border-blue-500 sm:text-sm border-gray-300 border">
                            </div>
                        </div>

                        <div class="relative">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Twitter URL</label>
                            <div class="flex rounded-md shadow-sm">
                                <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                                    <svg class="w-5 h-5 text-blue-400" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723 10.054 10.054 0 01-3.127 1.195 4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                                </span>
                                <input type="url" name="social_twitter" value="<?php echo htmlspecialchars(get_setting('social_twitter')); ?>" class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md focus:ring-blue-500 focus:border-blue-500 sm:text-sm border-gray-300 border">
                            </div>
                        </div>

                        <div class="relative">
                            <label class="block text-sm font-medium text-gray-700 mb-1">LinkedIn URL</label>
                            <div class="flex rounded-md shadow-sm">
                                <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                                    <svg class="w-5 h-5 text-blue-700" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                                </span>
                                <input type="url" name="social_linkedin" value="<?php echo htmlspecialchars(get_setting('social_linkedin')); ?>" class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md focus:ring-blue-500 focus:border-blue-500 sm:text-sm border-gray-300 border">
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 px-8 py-4 border-t border-gray-100 flex justify-end">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg shadow-sm transition-colors">Save Social Links</button>
                    </div>
                </form>
            </div>

            <!-- Backup Tab -->
            <div id="tab-backup" class="tab-content hidden">
                <div class="bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden">
                    <div class="p-8">
                        <h2 class="text-xl font-bold text-gray-900 border-b border-gray-100 pb-2 mb-6">Database Backup</h2>
                        
                        <div class="bg-blue-50 border border-blue-100 rounded-lg p-6 text-center">
                            <svg class="mx-auto h-12 w-12 text-blue-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path></svg>
                            <h3 class="text-lg font-bold text-gray-900">Manual SQL Backup</h3>
                            <p class="text-gray-600 text-sm mt-2 max-w-md mx-auto mb-6">Download a complete snapshot of the database including schema, announcements, users, and settings. Keep this file secure.</p>
                            
                            <a href="backup.php" target="_blank" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition-colors">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                Download Database Backup
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
// Tab Switching Logic
function switchTab(tabId) {
    // Hide all contents
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    // Reset all buttons
    document.querySelectorAll('[id^="tab-btn-"]').forEach(btn => {
        btn.className = "text-gray-500 hover:text-gray-700 hover:bg-gray-50 px-4 py-3 font-medium text-sm rounded-lg whitespace-nowrap text-left transition-colors flex items-center group";
        const svg = btn.querySelector('svg');
        if(svg) svg.className = "w-5 h-5 mr-3 text-gray-400 group-hover:text-gray-500";
    });

    // Show target content
    document.getElementById('tab-' + tabId).classList.remove('hidden');
    // Highlight active button
    const activeBtn = document.getElementById('tab-btn-' + tabId);
    activeBtn.className = "bg-blue-50 text-blue-700 hover:text-blue-700 hover:bg-blue-50 px-4 py-3 font-medium text-sm rounded-lg whitespace-nowrap text-left transition-colors flex items-center group";
    const activeSvg = activeBtn.querySelector('svg');
    if(activeSvg) activeSvg.className = "w-5 h-5 mr-3";
    
    // Hide alert
    document.getElementById('alert-container').classList.add('hidden');
}

// AJAX Form Submissions
document.querySelectorAll('.settings-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const btn = this.querySelector('button[type="submit"]');
        const originalText = btn.innerText;
        btn.disabled = true;
        btn.innerText = 'Saving...';
        
        const formData = new FormData(this);
        
        fetch('../ajax/update_settings.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            const alert = document.getElementById('alert-container');
            alert.classList.remove('hidden', 'bg-red-50', 'text-red-700', 'border-red-200', 'bg-green-50', 'text-green-700', 'border-green-200');
            
            if(data.success) {
                alert.classList.add('bg-green-50', 'text-green-700', 'border-green-200');
                alert.innerText = 'Settings saved successfully.';
                // If logo was uploaded, maybe refresh image
                if (data.logo_url) {
                    const img = document.querySelector('img[alt="Current Logo"]');
                    if (img) img.src = '../' + data.logo_url;
                }
            } else {
                alert.classList.add('bg-red-50', 'text-red-700', 'border-red-200');
                alert.innerText = 'Error: ' + data.message;
            }
            
            // Auto hide success after 3s
            if(data.success) {
                setTimeout(() => alert.classList.add('hidden'), 3000);
            }
        })
        .catch(err => {
            console.error(err);
            alert('Failed to process request.');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerText = originalText;
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>
