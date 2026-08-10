<?php $is_auth_page = $is_auth_page ?? false; ?>
<?php 
$is_public_area = strpos($_SERVER['PHP_SELF'], '/public/') !== false || (strpos($_SERVER['PHP_SELF'], '/index.php') !== false && !isset($_SESSION['user_id']));
?>
<?php if (!$is_auth_page): ?>
            </main>
            
            <?php if ($is_public_area): ?>
            <!-- Public Footer -->
            <footer class="bg-[#0f172a] text-gray-300 pt-16 pb-8 border-t border-gray-800 mt-auto">
                <div class="w-full max-w-[1400px] mx-auto px-6 sm:px-8 lg:px-10 xl:px-12">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-12">
                        
                        <!-- Brand & Info -->
                        <div class="space-y-4">
                            <a href="<?php echo htmlspecialchars(base_url('/public/index.php')); ?>" class="flex items-center gap-3 group">
                                <img class="h-10 w-auto transform group-hover:scale-105 transition-transform duration-300" src="<?php echo htmlspecialchars(base_url('/assets/images/ucsmtla logo.png')); ?>" alt="UCSMTLA Logo">
                                <span class="font-bold text-xl text-white tracking-tight group-hover:text-blue-400 transition-colors">UCSMTLA<br><span class="text-[10px] font-bold text-blue-500 leading-none uppercase tracking-wider block mt-0.5">Academic Hub</span></span>
                            </a>
                            <p class="text-sm text-gray-400 mt-4 leading-relaxed">
                                A centralized platform where students, faculty, and visitors can easily access university announcements and academic schedules.
                            </p>
                            <div class="pt-4 flex space-x-4">
                                <?php if ($fb = get_setting('social_facebook', 'https://facebook.com')): ?>
                                    <a href="<?php echo htmlspecialchars($fb); ?>" target="_blank" class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center text-gray-400 hover:bg-blue-600 hover:text-white transition-all duration-300 transform hover:-translate-y-1">
                                        <i data-lucide="facebook" class="w-5 h-5"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if ($tw = get_setting('social_twitter', 'https://twitter.com')): ?>
                                    <a href="<?php echo htmlspecialchars($tw); ?>" target="_blank" class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center text-gray-400 hover:bg-blue-400 hover:text-white transition-all duration-300 transform hover:-translate-y-1">
                                        <i data-lucide="twitter" class="w-5 h-5"></i>
                                    </a>
                                <?php endif; ?>
                                <a href="mailto:info@ucsmtla.edu.mm" class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center text-gray-400 hover:bg-red-500 hover:text-white transition-all duration-300 transform hover:-translate-y-1">
                                    <i data-lucide="mail" class="w-5 h-5"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Quick Links -->
                        <div>
                            <h3 class="text-white font-bold text-lg mb-6 relative inline-block">
                                Quick Links
                                <span class="absolute -bottom-2 left-0 w-12 h-1 bg-blue-500 rounded-full"></span>
                            </h3>
                            <ul class="space-y-3">
                                <li><a href="<?php echo htmlspecialchars(base_url('/public/index.php')); ?>" class="text-gray-400 hover:text-white hover:translate-x-1 transition-transform inline-flex items-center gap-2 text-sm"><i data-lucide="chevron-right" class="w-4 h-4 text-blue-500"></i> Home</a></li>
                                <li><a href="<?php echo htmlspecialchars(base_url('/public/search.php')); ?>" class="text-gray-400 hover:text-white hover:translate-x-1 transition-transform inline-flex items-center gap-2 text-sm"><i data-lucide="chevron-right" class="w-4 h-4 text-blue-500"></i> Announcements</a></li>
                                <li><a href="<?php echo htmlspecialchars(base_url('/public/campus.php')); ?>" class="text-gray-400 hover:text-white hover:translate-x-1 transition-transform inline-flex items-center gap-2 text-sm"><i data-lucide="chevron-right" class="w-4 h-4 text-blue-500"></i> Campus</a></li>
                                <li><a href="<?php echo htmlspecialchars(base_url('/public/about.php')); ?>" class="text-gray-400 hover:text-white hover:translate-x-1 transition-transform inline-flex items-center gap-2 text-sm"><i data-lucide="chevron-right" class="w-4 h-4 text-blue-500"></i> About</a></li>
                                <li><a href="<?php echo htmlspecialchars(base_url('/public/contact.php')); ?>" class="text-gray-400 hover:text-white hover:translate-x-1 transition-transform inline-flex items-center gap-2 text-sm"><i data-lucide="chevron-right" class="w-4 h-4 text-blue-500"></i> Contact</a></li>
                            </ul>
                        </div>

                        <!-- Contact Information -->
                        <div>
                            <h3 class="text-white font-bold text-lg mb-6 relative inline-block">
                                Contact Us
                                <span class="absolute -bottom-2 left-0 w-12 h-1 bg-blue-500 rounded-full"></span>
                            </h3>
                            <ul class="space-y-4">
                                <li class="flex items-start gap-3">
                                    <i data-lucide="map-pin" class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5"></i>
                                    <span class="text-gray-400 text-sm leading-relaxed">University of Computer Studies (Meiktila)<br>Meiktila, Mandalay Region, Myanmar</span>
                                </li>
                                <li class="flex items-center gap-3">
                                    <i data-lucide="phone" class="w-5 h-5 text-blue-500 flex-shrink-0"></i>
                                    <span class="text-gray-400 text-sm">09-xxxxxxxxx</span>
                                </li>
                                <li class="flex items-center gap-3">
                                    <i data-lucide="mail" class="w-5 h-5 text-blue-500 flex-shrink-0"></i>
                                    <span class="text-gray-400 text-sm">info@ucsmtla.edu.mm</span>
                                </li>
                            </ul>
                        </div>
                        
                        <!-- Action / Info -->
                        <div>
                            <h3 class="text-white font-bold text-lg mb-6 relative inline-block">
                                Student Access
                                <span class="absolute -bottom-2 left-0 w-12 h-1 bg-blue-500 rounded-full"></span>
                            </h3>
                            <p class="text-sm text-gray-400 mb-6 leading-relaxed">
                                Log in to view your timetables, bookmarks, and participate in discussions.
                            </p>
                            <a href="<?php echo htmlspecialchars(base_url('/auth/login.php')); ?>" class="inline-flex items-center justify-center w-full px-6 py-3 border border-transparent rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-lg hover:shadow-blue-900/50 transition-all duration-300 gap-2 transform hover:-translate-y-0.5">
                                <i data-lucide="log-in" class="w-4 h-4"></i> Student Login
                            </a>
                            
                            <?php if (isset($current_academic_year) && $current_academic_year !== 'Not Set'): ?>
                            <div class="mt-6 inline-flex items-center gap-2 bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2.5 w-full">
                                <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse shadow-[0_0_8px_rgba(34,197,94,0.6)]"></span>
                                <span class="text-xs font-semibold text-gray-300">Academic Year: <span class="text-white"><?php echo htmlspecialchars($current_academic_year); ?></span></span>
                            </div>
                            <?php endif; ?>
                        </div>

                    </div>
                    
                    <div class="pt-8 border-t border-gray-800/80 flex flex-col md:flex-row justify-between items-center gap-4">
                        <p class="text-sm text-gray-500 text-center md:text-left">
                            &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars(get_setting('site_name', 'UCSMTLA Academic Hub')); ?>. All rights reserved.
                        </p>
                        <button id="back-to-top" class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center text-gray-400 hover:text-white hover:bg-blue-600 transition-all duration-300 focus:outline-none transform hover:-translate-y-1 shadow-lg hover:shadow-blue-900/50">
                            <i data-lucide="arrow-up" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>
            </footer>
            
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const backToTopBtn = document.getElementById('back-to-top');
                    const mainContent = document.getElementById('public-main-content');
                    
                    if (backToTopBtn && mainContent) {
                        backToTopBtn.addEventListener('click', () => {
                            mainContent.scrollTo({
                                top: 0,
                                behavior: 'smooth'
                            });
                        });
                    }
                });
            </script>
            
            <?php else: ?>
            <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 mt-auto transition-colors duration-300">
                <div class="px-6 py-4 flex items-center justify-between">
                    <p class="text-sm text-gray-500">
                        &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars(get_setting('site_name', 'UCSMTLA Academic Hub')); ?>. All rights reserved.
                    </p>
                    <div class="flex space-x-4 items-center">
                        <?php if ($fb = get_setting('social_facebook')): ?>
                            <a href="<?php echo htmlspecialchars($fb); ?>" target="_blank" class="text-gray-400 hover:text-blue-600">
                                <span class="sr-only">Facebook</span>
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" /></svg>
                            </a>
                        <?php endif; ?>
                        <?php if ($tw = get_setting('social_twitter')): ?>
                            <a href="<?php echo htmlspecialchars($tw); ?>" target="_blank" class="text-gray-400 hover:text-blue-400">
                                <span class="sr-only">Twitter</span>
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84"/></svg>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </footer>
            <?php endif; ?>
            
        <?php if (!$is_public_area): ?>
        </div>
    </div>
        <?php endif; ?>
<?php endif; ?>
    <!-- Scripts -->
    <script src="<?php echo htmlspecialchars(base_url('/assets/js/main.js?v=' . time())); ?>"></script>
    <script src="<?php echo htmlspecialchars(base_url('/assets/js/sidebar.js?v=' . time())); ?>"></script>
    <script src="<?php echo htmlspecialchars(base_url('/assets/js/header-clock.js')); ?>"></script>
    <script>
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    </script>
</body>
</html>
