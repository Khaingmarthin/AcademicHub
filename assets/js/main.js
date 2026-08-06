/**
 * main.js - Core JavaScript for UCSMTLA Academic Hub
 * Handles Toasts, Modals, Dark Mode, and micro-interactions
 */

document.addEventListener('DOMContentLoaded', () => {
    initDarkMode();
    initMobileMenu();
    setupToasts();
});

// --- Dark Mode System ---
function initDarkMode() {
    const toggleBtn = document.getElementById('dark-mode-toggle');
    const htmlEl = document.documentElement;
    
    // Check local storage or system preference
    if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        htmlEl.classList.add('dark');
        updateToggleIcon(true);
    } else {
        htmlEl.classList.remove('dark');
        updateToggleIcon(false);
    }

    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            if (htmlEl.classList.contains('dark')) {
                htmlEl.classList.remove('dark');
                localStorage.theme = 'light';
                updateToggleIcon(false);
            } else {
                htmlEl.classList.add('dark');
                localStorage.theme = 'dark';
                updateToggleIcon(true);
            }
        });
    }
}

function updateToggleIcon(isDark) {
    const icon = document.getElementById('dark-mode-icon');
    if (!icon) return;
    
    if (isDark) {
        // Sun icon for dark mode (click to switch to light)
        icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />`;
    } else {
        // Moon icon for light mode (click to switch to dark)
        icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />`;
    }
}

// --- Mobile Menu ---
function initMobileMenu() {
    const btn = document.getElementById('mobile-menu-btn');
    const sidebar = document.querySelector('aside');
    if (btn && sidebar) {
        btn.addEventListener('click', () => {
            sidebar.classList.toggle('hidden');
            // If we are showing it on mobile, make it fixed overlay
            if(!sidebar.classList.contains('hidden')) {
                sidebar.classList.add('fixed', 'z-50', 'w-64');
            } else {
                sidebar.classList.remove('fixed', 'z-50');
            }
        });
    }
}

// --- Custom Toast Notification System ---
window.Toast = {
    show: function(title, message, type = 'info') {
        const container = document.getElementById('toast-container') || this.createContainer();
        
        const toast = document.createElement('div');
        let colorClasses = 'border-l-4 border-blue-500 bg-white text-gray-800 dark:bg-gray-800 dark:text-gray-100';
        let icon = '<svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
        
        if (type === 'success') {
            colorClasses = 'border-l-4 border-green-500 bg-white text-gray-800 dark:bg-gray-800 dark:text-gray-100';
            icon = '<svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
        } else if (type === 'error') {
            colorClasses = 'border-l-4 border-red-500 bg-white text-gray-800 dark:bg-gray-800 dark:text-gray-100';
            icon = '<svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
        } else if (type === 'warning') {
            colorClasses = 'border-l-4 border-yellow-500 bg-white text-gray-800 dark:bg-gray-800 dark:text-gray-100';
            icon = '<svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>';
        }

        toast.className = `transform transition-all duration-300 -translate-y-2 opacity-0 w-full shadow-lg rounded-xl pointer-events-auto flex ring-1 ring-black/5 overflow-hidden ${colorClasses} mt-4`;
        
        toast.innerHTML = `
            <div class="p-4 w-full flex items-start">
                <div class="flex-shrink-0">
                    ${icon}
                </div>
                <div class="ml-3 flex-1 pt-0.5 min-w-0">
                    <p class="text-sm font-bold text-gray-900">${title}</p>
                    <p class="mt-1 text-sm text-gray-600 leading-normal break-words">${message}</p>
                </div>
                <div class="ml-4 flex-shrink-0 flex">
                    <button class="inline-flex text-gray-400 hover:text-gray-500 focus:outline-none" onclick="this.parentElement.parentElement.parentElement.remove()">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                    </button>
                </div>
            </div>
        `;

        container.appendChild(toast);
        
        // Trigger animation
        requestAnimationFrame(() => {
            toast.classList.remove('-translate-y-2', 'opacity-0');
            toast.classList.add('translate-y-0', 'opacity-100');
        });

        // Auto remove
        setTimeout(() => {
            toast.classList.remove('translate-y-0', 'opacity-100');
            toast.classList.add('-translate-y-2', 'opacity-0');
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    },
    
    createContainer: function() {
        const container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'fixed top-20 right-0 p-4 pointer-events-none flex flex-col items-end';
        container.style.zIndex = '99999';
        container.style.width = '100%';
        container.style.maxWidth = '380px';
        document.body.appendChild(container);
        return container;
    }
};

// Convert PHP Flash messages into JS Toasts
function setupToasts() {
    const flashMessages = document.querySelectorAll('.php-flash-msg');
    flashMessages.forEach(msg => {
        const type = msg.dataset.type;
        const text = msg.innerText;
        Toast.show(type.charAt(0).toUpperCase() + type.slice(1), text, type);
        msg.remove(); // Remove raw HTML element
    });
}

// --- Custom Confirmation Modal System ---
window.ConfirmModal = {
    show: function(title, message, confirmText = 'Confirm', confirmClass = 'bg-red-600 hover:bg-red-700', onConfirm) {
        // Remove existing modal if any
        const existing = document.getElementById('confirm-modal');
        if(existing) existing.remove();

        const modal = document.createElement('div');
        modal.id = 'confirm-modal';
        modal.className = 'fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0 opacity-0 transition-opacity duration-300';
        
        modal.innerHTML = `
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="ConfirmModal.close()"></div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl overflow-hidden shadow-2xl transform transition-all sm:max-w-lg sm:w-full scale-95 duration-300 p-6 z-10 border border-gray-100 dark:border-gray-700">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-red-600 dark:text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-bold text-gray-900 dark:text-white" id="modal-title">${title}</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500 dark:text-gray-300">${message}</p>
                        </div>
                    </div>
                </div>
                <div class="mt-6 sm:mt-5 sm:flex sm:flex-row-reverse">
                    <button type="button" id="confirm-btn" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 ${confirmClass} text-base font-medium text-white sm:ml-3 sm:w-auto sm:text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 transition-transform active:scale-95">
                        ${confirmText}
                    </button>
                    <button type="button" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-700 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 sm:mt-0 sm:w-auto sm:text-sm focus:outline-none transition-colors" onclick="ConfirmModal.close()">
                        Cancel
                    </button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Trigger animations
        requestAnimationFrame(() => {
            modal.classList.remove('opacity-0');
            modal.classList.add('opacity-100');
            modal.querySelector('div.scale-95').classList.remove('scale-95');
            modal.querySelector('div.scale-95').classList.add('scale-100');
        });

        document.getElementById('confirm-btn').addEventListener('click', () => {
            if(onConfirm) onConfirm();
            ConfirmModal.close();
        });
    },
    
    close: function() {
        const modal = document.getElementById('confirm-modal');
        if(modal) {
            modal.classList.remove('opacity-100');
            modal.classList.add('opacity-0');
            modal.querySelector('div.scale-100').classList.remove('scale-100');
            modal.querySelector('div.scale-100').classList.add('scale-95');
            setTimeout(() => modal.remove(), 300);
        }
    }
};
