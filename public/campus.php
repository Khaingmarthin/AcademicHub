<?php
require_once '../config/db.php';
require_once '../config/functions.php';
?>
<?php 
$is_public_area = true;
include '../includes/header.php'; 
?>
<?php include '../includes/navbar.php'; ?>

<!-- PAGE HEADER -->
<div class="relative bg-gradient-to-br from-blue-900 via-blue-800 to-indigo-900 overflow-hidden">
    <div class="absolute inset-0 opacity-20 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] mix-blend-overlay"></div>
    <div class="relative max-w-7xl mx-auto py-24 px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl lg:text-6xl animate-fade-in-up">
            Campus Information
        </h1>
        <p class="mt-6 max-w-3xl mx-auto text-xl text-blue-100 animate-fade-in-up" style="animation-delay: 0.1s;">
            Everything you need to know about the University of Computer Studies (Meiktila).
        </p>
    </div>
</div>

<div class="bg-[#F5F7FB] py-16 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- MAIN CONTENT: Two-Column Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-16">
            
            <!-- LEFT COLUMN: Information Card -->
            <div class="bg-white/70 backdrop-blur-xl rounded-2xl shadow-lg border border-white/40 p-8 hover:shadow-xl transition-all duration-300 relative group overflow-hidden">
                <div class="absolute -inset-1 bg-gradient-to-br from-blue-400 to-indigo-400 rounded-2xl blur opacity-10 group-hover:opacity-20 transition duration-500 -z-10"></div>
                
                <h2 class="text-2xl font-extrabold text-gray-900 mb-8 flex items-center">
                    <i data-lucide="info" class="w-7 h-7 text-blue-600 mr-3"></i>
                    University Information
                </h2>
                
                <ul class="space-y-6">
                    <li class="flex items-start group/item">
                        <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-xl bg-blue-50 text-blue-600 group-hover/item:bg-blue-600 group-hover/item:text-white transition-colors duration-300">
                            <i data-lucide="graduation-cap" class="w-6 h-6"></i>
                        </div>
                        <div class="ml-5">
                            <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-1">University Name</p>
                            <p class="text-lg font-bold text-gray-900">University of Computer Studies (Meiktila)</p>
                        </div>
                    </li>
                    <li class="flex items-start group/item">
                        <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-xl bg-blue-50 text-blue-600 group-hover/item:bg-blue-600 group-hover/item:text-white transition-colors duration-300">
                            <i data-lucide="map-pin" class="w-6 h-6"></i>
                        </div>
                        <div class="ml-5">
                            <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-1">Address</p>
                            <p class="text-lg font-bold text-gray-900">Meiktila-Tharzi Road, Pan Taw Sat Village, TawMa Village Group, Meiktila, Mandalay Division.</p>
                        </div>
                    </li>
                    <li class="flex items-start group/item">
                        <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-xl bg-blue-50 text-blue-600 group-hover/item:bg-blue-600 group-hover/item:text-white transition-colors duration-300">
                            <i data-lucide="phone" class="w-6 h-6"></i>
                        </div>
                        <div class="ml-5">
                            <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-1">Phone</p>
                            <p class="text-lg font-bold text-gray-900">(+95) 64 53 2005</p>
                        </div>
                    </li>
                    <li class="flex items-start group/item">
                        <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-xl bg-blue-50 text-blue-600 group-hover/item:bg-blue-600 group-hover/item:text-white transition-colors duration-300">
                            <i data-lucide="mail" class="w-6 h-6"></i>
                        </div>
                        <div class="ml-5">
                            <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-1">Email</p>
                            <a href="mailto:studentaffair@ucsmtla.edu.mm" class="text-lg font-bold text-blue-600 hover:text-blue-800 transition-colors">studentaffair@ucsmtla.edu.mm<br>ucsmtla_admin@ucsmtla.edu.mm</a>
                        </div>
                    </li>
                    <li class="flex items-start group/item">
                        <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-xl bg-blue-50 text-blue-600 group-hover/item:bg-blue-600 group-hover/item:text-white transition-colors duration-300">
                            <i data-lucide="globe" class="w-6 h-6"></i>
                        </div>
                        <div class="ml-5">
                            <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-1">Website</p>
                            <a href="https://www.ucsmtla.edu.mm" target="_blank" rel="noopener noreferrer" class="text-lg font-bold text-blue-600 hover:text-blue-800 transition-colors inline-flex items-center">
                                www.ucsmtla.edu.mm
                                <i data-lucide="external-link" class="w-4 h-4 ml-1.5"></i>
                            </a>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- RIGHT COLUMN: Embedded Map -->
            <div class="bg-white/70 backdrop-blur-xl rounded-2xl shadow-lg border border-white/40 p-8 hover:shadow-xl transition-all duration-300 flex flex-col h-full">
                <h2 class="text-2xl font-extrabold text-gray-900 mb-6 flex items-center">
                    <i data-lucide="map" class="w-7 h-7 text-blue-600 mr-3"></i>
                    Find Us on the Map
                </h2>
                <div class="flex-1 w-full min-h-[350px] rounded-xl overflow-hidden shadow-inner border border-gray-100 bg-gray-100">
                    <!-- Google Maps Iframe -->
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3744.1723555543163!2d95.88210341538356!3d20.893874986071855!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x30cb673ce2030e49%3A0xc3f58a36faea9127!2sUniversity%20of%20Computer%20Studies%20(Meiktila)!5e0!3m2!1sen!2smm!4v1684305374483!5m2!1sen!2smm" 
                        class="w-full h-full border-0 min-h-[350px]" 
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>
            
        </div>

        <!-- BOTTOM SECTION: Feature Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <!-- Feature 1 -->
            <div class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-lg border border-gray-100 transform transition-all duration-300 hover:-translate-y-1 group">
                <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform duration-300">
                    <i data-lucide="building-2" class="w-7 h-7"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-blue-600 transition-colors">Campus Facilities</h3>
                <p class="text-gray-600 text-sm leading-relaxed">Experience our state-of-the-art computer labs, comprehensive digital library, and engaging student activity centers.</p>
            </div>

            <!-- Feature 2 -->
            <div class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-lg border border-gray-100 transform transition-all duration-300 hover:-translate-y-1 group">
                <div class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform duration-300">
                    <i data-lucide="monitor-play" class="w-7 h-7"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-indigo-600 transition-colors">Modern Learning Environment</h3>
                <p class="text-gray-600 text-sm leading-relaxed">Our campus features fully air-conditioned, smart classrooms equipped with high-speed Wi-Fi and interactive technologies.</p>
            </div>

            <!-- Feature 3 -->
            <div class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-lg border border-gray-100 transform transition-all duration-300 hover:-translate-y-1 group">
                <div class="w-14 h-14 bg-teal-50 text-teal-600 rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform duration-300">
                    <i data-lucide="users" class="w-7 h-7"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-teal-600 transition-colors">Student Services</h3>
                <p class="text-gray-600 text-sm leading-relaxed">Dedicated administrative staff providing support with registration, financial aid, and a central IT helpdesk for your needs.</p>
            </div>

        </div>

    </div>
</div>



<script>
// Initialize Lucide icons if the library is present
document.addEventListener('DOMContentLoaded', function() {
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});
</script>

<?php include '../includes/footer.php'; ?>
