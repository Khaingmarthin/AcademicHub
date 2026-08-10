<?php
http_response_code(404);
require_once '../config/db.php';
require_once '../config/functions.php';
?>
<?php 
$is_public_area = true;
include '../includes/header.php'; 
?>
<?php include '../includes/navbar.php'; ?>

<div class="min-h-[80vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
    <div class="max-w-md w-full space-y-8 text-center">
        <div>
            <h1 class="text-9xl font-extrabold text-blue-600 dark:text-blue-500 tracking-widest animate-pulse">404</h1>
            <div class="bg-blue-600 dark:bg-blue-500 px-2 text-sm rounded rotate-12 absolute shadow-lg py-1">
                <span class="text-white font-bold tracking-widest">Page Not Found</span>
            </div>
        </div>
        
        <div class="mt-8">
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-4">
                Oops! You've lost your way.
            </h2>
            <p class="text-gray-500 dark:text-gray-400 mb-8">
                The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.
            </p>
            
            <a href="/public/index.php" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-full text-white bg-blue-600 hover:bg-blue-700 shadow-lg transform hover:-translate-y-1 transition-all">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                Return Home
            </a>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
