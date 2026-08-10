<?php
require_once '../config/db.php';
require_once '../config/functions.php';

// Fetch Statistics
$stmt_students = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'");
$student_count = $stmt_students->fetchColumn();

$stmt_ay = $pdo->query("SELECT COUNT(*) FROM academic_years");
$ay_count = $stmt_ay->fetchColumn();

$department_count = 5; // Hardcoded as representative value
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
            About UCSMTLA
        </h1>
        <p class="mt-6 max-w-3xl mx-auto text-xl text-blue-100 animate-fade-in-up" style="animation-delay: 0.1s;">
            Dedicated to producing highly qualified IT professionals to meet the nation's growing technological needs. 
            We blend rigorous academic study with practical innovation.
        </p>
    </div>
</div>

<div class="bg-[#F5F7FB] py-16 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-24">
        
        <!-- University Introduction & Vision / Mission -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div class="space-y-8 animate-fade-in-up" style="animation-delay: 0.2s;">
                <h2 class="text-3xl font-extrabold text-gray-900 flex items-center">
                    <i data-lucide="building" class="w-8 h-8 text-blue-600 mr-3"></i>
                    University Introduction
                </h2>
                <p class="text-lg text-gray-600 leading-relaxed">
                    The University of Computer Studies (Meiktila) is a premier institution dedicated to providing high-quality education in computer science and technology. Established to cater to the increasing demand for IT professionals, UCSMTLA fosters a culture of innovation, rigorous research, and technical excellence to prepare our students for the rapidly evolving digital era.
                </p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4">
                    <div class="bg-white/70 backdrop-blur-xl rounded-2xl shadow-lg border border-white/40 p-6 relative overflow-hidden group">
                        <div class="absolute -inset-1 bg-gradient-to-br from-blue-400 to-indigo-400 rounded-2xl blur opacity-10 group-hover:opacity-20 transition duration-500 -z-10"></div>
                        <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center mb-4">
                            <i data-lucide="eye" class="w-6 h-6"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Vision</h3>
                        <p class="text-sm text-gray-600">To become a world-class IT university producing excellent human resources and innovative leaders for the global society.</p>
                    </div>
                    
                    <div class="bg-white/70 backdrop-blur-xl rounded-2xl shadow-lg border border-white/40 p-6 relative overflow-hidden group">
                        <div class="absolute -inset-1 bg-gradient-to-br from-indigo-400 to-purple-400 rounded-2xl blur opacity-10 group-hover:opacity-20 transition duration-500 -z-10"></div>
                        <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center mb-4">
                            <i data-lucide="target" class="w-6 h-6"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Mission</h3>
                        <p class="text-sm text-gray-600">Produce competent computer scientists and engineers. Promote R&D in IT, and provide quality IT services to the community.</p>
                    </div>
                </div>
            </div>
            <div class="relative h-[500px] rounded-3xl overflow-hidden shadow-2xl animate-fade-in-up" style="animation-delay: 0.3s;">
                <img src="../assets/images/about_hero.png" alt="University Campus" class="absolute inset-0 w-full h-full object-cover">
                <div class="absolute inset-0 bg-blue-900 opacity-20"></div>
            </div>
        </div>

        <!-- Academic Programs -->
        <div>
            <div class="text-center mb-12">
                <h2 class="text-3xl font-extrabold text-gray-900 flex items-center justify-center">
                    <i data-lucide="book-open" class="w-8 h-8 text-blue-600 mr-3"></i>
                    Academic Programs
                </h2>
                <p class="mt-4 text-lg text-gray-500">Comprehensive degree programs tailored for the modern tech landscape.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-5xl mx-auto">
                <!-- Computer Science -->
                <div class="bg-white rounded-3xl shadow-lg border border-gray-100 overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
                    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-8 text-white relative overflow-hidden">
                        <i data-lucide="code-2" class="w-24 h-24 absolute -bottom-4 -right-4 opacity-20 transform group-hover:scale-110 transition-transform duration-500"></i>
                        <h3 class="text-2xl font-bold relative z-10">Computer Science</h3>
                        <p class="text-blue-100 mt-2 relative z-10 font-medium">B.C.Sc. (Bachelor of Computer Science)</p>
                    </div>
                    <div class="p-8">
                        <p class="text-gray-600 mb-6">Focuses on software engineering, artificial intelligence, database systems, and core computational theories. Graduates are equipped to become top-tier software developers and data analysts.</p>
                        <ul class="space-y-3">
                            <li class="flex items-center text-sm text-gray-700 font-medium"><i data-lucide="check-circle" class="w-5 h-5 text-blue-500 mr-3"></i> Software Engineering</li>
                            <li class="flex items-center text-sm text-gray-700 font-medium"><i data-lucide="check-circle" class="w-5 h-5 text-blue-500 mr-3"></i> Artificial Intelligence</li>
                            <li class="flex items-center text-sm text-gray-700 font-medium"><i data-lucide="check-circle" class="w-5 h-5 text-blue-500 mr-3"></i> Database Management</li>
                        </ul>
                    </div>
                </div>

                <!-- Computer Technology -->
                <div class="bg-white rounded-3xl shadow-lg border border-gray-100 overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
                    <div class="bg-gradient-to-r from-teal-500 to-emerald-500 p-8 text-white relative overflow-hidden">
                        <i data-lucide="cpu" class="w-24 h-24 absolute -bottom-4 -right-4 opacity-20 transform group-hover:scale-110 transition-transform duration-500"></i>
                        <h3 class="text-2xl font-bold relative z-10">Computer Technology</h3>
                        <p class="text-teal-100 mt-2 relative z-10 font-medium">B.C.Tech. (Bachelor of Computer Technology)</p>
                    </div>
                    <div class="p-8">
                        <p class="text-gray-600 mb-6">Emphasizes hardware architecture, embedded systems, networking, and network security. Designed for students passionate about the physical infrastructure that powers modern IT.</p>
                        <ul class="space-y-3">
                            <li class="flex items-center text-sm text-gray-700 font-medium"><i data-lucide="check-circle" class="w-5 h-5 text-teal-500 mr-3"></i> Network Engineering</li>
                            <li class="flex items-center text-sm text-gray-700 font-medium"><i data-lucide="check-circle" class="w-5 h-5 text-teal-500 mr-3"></i> Embedded Systems</li>
                            <li class="flex items-center text-sm text-gray-700 font-medium"><i data-lucide="check-circle" class="w-5 h-5 text-teal-500 mr-3"></i> Cyber Security</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Campus Life & Administration -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Campus Life -->
            <div class="bg-white/70 backdrop-blur-xl rounded-3xl shadow-lg border border-white/40 p-10 relative overflow-hidden group">
                <div class="absolute -inset-1 bg-gradient-to-br from-orange-400 to-amber-400 rounded-3xl blur opacity-10 group-hover:opacity-20 transition duration-500 -z-10"></div>
                <div class="w-14 h-14 bg-orange-50 text-orange-600 rounded-2xl flex items-center justify-center mb-6">
                    <i data-lucide="coffee" class="w-7 h-7"></i>
                </div>
                <h3 class="text-2xl font-extrabold text-gray-900 mb-4">Campus Life</h3>
                <p class="text-gray-600 leading-relaxed mb-6">
                    UCSMTLA offers a vibrant campus life that extends beyond the classroom. We host numerous extracurricular activities, coding bootcamps, sports tournaments, and cultural events. Students have access to a massive central library, modern cafeterias, and collaborative student lounges that foster teamwork and creativity.
                </p>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-50 rounded-xl p-4 flex items-center border border-gray-100">
                        <i data-lucide="users" class="w-5 h-5 text-gray-400 mr-3"></i>
                        <span class="text-sm font-semibold text-gray-700">Student Clubs</span>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4 flex items-center border border-gray-100">
                        <i data-lucide="monitor-play" class="w-5 h-5 text-gray-400 mr-3"></i>
                        <span class="text-sm font-semibold text-gray-700">Hackathons</span>
                    </div>
                </div>
            </div>

            <!-- Administration -->
            <div class="bg-white/70 backdrop-blur-xl rounded-3xl shadow-lg border border-white/40 p-10 relative overflow-hidden group">
                <div class="absolute -inset-1 bg-gradient-to-br from-rose-400 to-pink-400 rounded-3xl blur opacity-10 group-hover:opacity-20 transition duration-500 -z-10"></div>
                <div class="w-14 h-14 bg-rose-50 text-rose-600 rounded-2xl flex items-center justify-center mb-6">
                    <i data-lucide="briefcase" class="w-7 h-7"></i>
                </div>
                <h3 class="text-2xl font-extrabold text-gray-900 mb-4">Administration</h3>
                <p class="text-gray-600 leading-relaxed mb-6">
                    Our administrative team is dedicated to providing robust support systems for our students and faculty. The administration comprises the Rector's Office, Academic Affairs, Student Services, and Financial Aid departments, ensuring a smooth, fully supported academic journey for every student.
                </p>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-50 rounded-xl p-4 flex items-center border border-gray-100">
                        <i data-lucide="file-text" class="w-5 h-5 text-gray-400 mr-3"></i>
                        <span class="text-sm font-semibold text-gray-700">Registrar</span>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4 flex items-center border border-gray-100">
                        <i data-lucide="life-buoy" class="w-5 h-5 text-gray-400 mr-3"></i>
                        <span class="text-sm font-semibold text-gray-700">Student Affairs</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- University Statistics -->
        <div>
            <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-12 relative overflow-hidden">
                <div class="absolute inset-0 bg-blue-600 opacity-[0.03]"></div>
                <div class="text-center mb-10 relative z-10">
                    <h2 class="text-3xl font-extrabold text-gray-900">University Statistics</h2>
                    <p class="mt-4 text-lg text-gray-500">A glance at our academic footprint.</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative z-10">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i data-lucide="users" class="w-8 h-8"></i>
                        </div>
                        <div class="text-4xl font-black text-gray-900 mb-2"><?php echo number_format($student_count); ?>+</div>
                        <div class="text-sm font-bold text-gray-500 uppercase tracking-widest">Active Students</div>
                    </div>
                    <div class="text-center">
                        <div class="w-16 h-16 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i data-lucide="layers" class="w-8 h-8"></i>
                        </div>
                        <div class="text-4xl font-black text-gray-900 mb-2"><?php echo $department_count; ?></div>
                        <div class="text-sm font-bold text-gray-500 uppercase tracking-widest">Departments</div>
                    </div>
                    <div class="text-center">
                        <div class="w-16 h-16 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i data-lucide="calendar" class="w-8 h-8"></i>
                        </div>
                        <div class="text-4xl font-black text-gray-900 mb-2"><?php echo $ay_count; ?></div>
                        <div class="text-sm font-bold text-gray-500 uppercase tracking-widest">Academic Years</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer CTA -->
        <div class="text-center pb-8">
            <a href="search.php" class="inline-flex items-center justify-center px-8 py-4 border border-transparent text-lg font-bold rounded-xl text-white bg-blue-600 hover:bg-blue-700 shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                Explore Announcements
                <i data-lucide="arrow-right" class="w-5 h-5 ml-2"></i>
            </a>
        </div>

    </div>
</div>



<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});
</script>

<?php include '../includes/footer.php'; ?>
