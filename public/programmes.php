<?php
require_once '../config/db.php';
require_once '../config/functions.php';
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<div class="max-w-7xl mx-auto py-8 sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6 text-center">
            <h1 class="text-3xl font-bold text-gray-900 mb-6">Academic Programmes</h1>
            <div class="prose max-w-4xl mx-auto text-left text-gray-700">
                <p class="mb-8 text-center">We offer various undergraduate and postgraduate programs designed to prepare students for successful careers in IT.</p>
                
                <div class="space-y-6">
                    <div class="border-l-4 border-blue-600 pl-4 py-2 bg-blue-50 rounded-r-lg">
                        <h3 class="text-xl font-bold text-gray-900">B.C.Sc (Bachelor of Computer Science)</h3>
                        <p class="mt-2 text-sm">Focuses on software engineering, artificial intelligence, and computing theories.</p>
                    </div>
                    <div class="border-l-4 border-blue-600 pl-4 py-2 bg-blue-50 rounded-r-lg">
                        <h3 class="text-xl font-bold text-gray-900">B.C.Tech (Bachelor of Computer Technology)</h3>
                        <p class="mt-2 text-sm">Focuses on hardware architecture, networking, and embedded systems.</p>
                    </div>
                    <div class="border-l-4 border-gray-600 pl-4 py-2 bg-gray-100 rounded-r-lg">
                        <h3 class="text-xl font-bold text-gray-900">Postgraduate Diplomas</h3>
                        <p class="mt-2 text-sm">Specialized diplomas in web engineering, network security, and data science.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
