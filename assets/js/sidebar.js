/* assets/js/sidebar.js */
document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.querySelector('aside.admin-sidebar');
    const collapseBtns = document.querySelectorAll('.sidebar-collapse-btn');
    const mobileMenuBtn = document.getElementById('admin-mobile-menu-btn');
    const backdrop = document.getElementById('sidebar-backdrop');

    // Restore state from localStorage immediately if not done by header inline script
    const sidebarState = localStorage.getItem('sidebarState') || 'expanded';
    if (sidebarState === 'collapsed') {
        document.documentElement.classList.add('sidebar-collapsed');
    } else {
        document.documentElement.classList.remove('sidebar-collapsed');
    }

    // Toggle collapse state on desktop
    collapseBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            if (document.documentElement.classList.contains('sidebar-collapsed')) {
                document.documentElement.classList.remove('sidebar-collapsed');
                localStorage.setItem('sidebarState', 'expanded');
            } else {
                document.documentElement.classList.add('sidebar-collapsed');
                localStorage.setItem('sidebarState', 'collapsed');
            }
            // Trigger custom event so other components know sidebar changed size
            window.dispatchEvent(new Event('sidebar-resize'));
        });
    });

    // Mobile / Tablet drawer toggle
    if (mobileMenuBtn && sidebar) {
        mobileMenuBtn.addEventListener('click', () => {
            sidebar.classList.toggle('mobile-open');
            if (backdrop) {
                backdrop.classList.toggle('hidden');
                setTimeout(() => {
                    backdrop.classList.toggle('opacity-0');
                }, 10);
            }
        });
    }

    // Clicking backdrop closes sidebar drawer
    if (backdrop && sidebar) {
        backdrop.addEventListener('click', () => {
            sidebar.classList.remove('mobile-open');
            backdrop.classList.add('opacity-0');
            setTimeout(() => {
                backdrop.classList.add('hidden');
            }, 300);
        });
    }
});
