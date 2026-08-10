<?php
require_once '../config/session.php';
require_admin();
require_once '../config/db.php';
require_once '../config/functions.php';

// Fetch all Academic Years
$academic_years = $pdo->query("SELECT id, year_name FROM academic_years ORDER BY id DESC")->fetchAll();

// Fetch all Classrooms
$classrooms_all = $pdo->query("SELECT id, classroom_name, academic_year_id FROM classrooms ORDER BY classroom_name ASC")->fetchAll();

$csrf_token = generate_csrf_token();
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>
<?php include '../includes/navbar.php'; ?>

<div class="min-h-screen bg-[#F8FAFC] pb-12">
    <div class="w-full px-4 sm:px-6 lg:px-8 pt-8">
        
        <!-- Page Header -->
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Promote Students</h1>
                <p class="mt-2 text-sm text-gray-500 font-medium">Bulk promote students to a new classroom or graduate them.</p>
            </div>
            
            <a href="students.php" class="inline-flex items-center gap-2 px-4 py-2 border border-gray-200 bg-white text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-50 transition-colors shadow-sm">
                <i data-lucide="arrow-left" class="w-4 h-4 text-gray-500"></i> Back to Students
            </a>
        </div>

        <!-- Success Alert -->
        <div id="top_success_alert" class="hidden mb-6 items-center justify-between px-5 py-3 rounded-xl bg-green-50/90 border border-green-200 shadow-sm backdrop-blur-sm transition-all duration-300">
            <div class="flex items-center gap-3">
                <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
                <p class="text-sm font-semibold text-green-700" id="top_success_message">Success!</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            <!-- Source Selection -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col">
                <div class="flex items-center gap-3 mb-6 border-b border-gray-100 pb-4">
                    <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center">
                        <i data-lucide="users" class="w-5 h-5 text-[#2563EB]"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Source Classroom</h3>
                        <p class="text-xs text-gray-500">Select the classroom to promote students FROM.</p>
                    </div>
                </div>

                <div class="space-y-4 mb-6">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Academic Year</label>
                        <select id="source_ay" onchange="filterSourceClassrooms()" class="block w-full py-2.5 px-3 border border-gray-200 bg-gray-50 rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors">
                            <option value="">Select Academic Year...</option>
                            <?php foreach ($academic_years as $ay): ?>
                            <option value="<?php echo $ay['id']; ?>"><?php echo htmlspecialchars($ay['year_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Classroom</label>
                        <select id="source_classroom" onchange="loadStudents()" class="block w-full py-2.5 px-3 border border-gray-200 bg-gray-50 rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors">
                            <option value="">Select Classroom...</option>
                        </select>
                    </div>
                </div>

                <div class="flex-1 bg-gray-50 rounded-xl border border-gray-100 p-4">
                    <div class="flex items-center justify-between mb-3 pb-2 border-b border-gray-200">
                        <span class="text-xs font-bold text-gray-500 uppercase">Students to Promote</span>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="check_all" onchange="toggleAllStudents()" class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                            <label for="check_all" class="text-xs font-semibold text-gray-700">Select All</label>
                        </div>
                    </div>
                    <div id="students_list" class="max-h-64 overflow-y-auto space-y-2">
                        <!-- Loaded via AJAX -->
                        <div class="text-sm text-gray-500 text-center py-8">Select a classroom to view students.</div>
                    </div>
                </div>
            </div>

            <!-- Target Selection -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col relative">
                <!-- Decorative Arrow connecting the two panels -->
                <div class="absolute -left-6 top-1/2 transform -translate-y-1/2 hidden lg:flex items-center justify-center w-12 h-12 bg-white rounded-full border border-gray-100 shadow-sm z-10">
                    <i data-lucide="arrow-right" class="w-6 h-6 text-gray-400"></i>
                </div>

                <div class="flex items-center gap-3 mb-6 border-b border-gray-100 pb-4">
                    <div class="w-10 h-10 rounded-full bg-purple-50 flex items-center justify-center">
                        <i data-lucide="arrow-up-right" class="w-5 h-5 text-purple-600"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Target Destination</h3>
                        <p class="text-xs text-gray-500">Select where the students will be promoted TO.</p>
                    </div>
                </div>

                <div class="space-y-6">
                    <!-- Action Type -->
                    <div class="grid grid-cols-2 gap-4">
                        <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none border-blue-600 ring-1 ring-blue-600">
                            <input type="radio" name="action_type" value="promote" class="sr-only" checked onchange="toggleActionType()">
                            <div class="flex w-full items-center justify-between">
                                <div class="flex items-center">
                                    <div class="text-sm">
                                        <p class="font-bold text-gray-900">Promote</p>
                                        <p class="text-xs text-gray-500">Move to next year</p>
                                    </div>
                                </div>
                                <i data-lucide="check-circle" class="w-5 h-5 text-blue-600 text-opacity-100"></i>
                            </div>
                        </label>
                        
                        <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none border-gray-300">
                            <input type="radio" name="action_type" value="graduate" class="sr-only" onchange="toggleActionType()">
                            <div class="flex w-full items-center justify-between">
                                <div class="flex items-center">
                                    <div class="text-sm">
                                        <p class="font-bold text-gray-900">Graduate</p>
                                        <p class="text-xs text-gray-500">Mark as graduated</p>
                                    </div>
                                </div>
                                <i data-lucide="check-circle" class="w-5 h-5 text-blue-600 opacity-0 transition-opacity"></i>
                            </div>
                        </label>
                    </div>

                    <div id="target_selection_area" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Target Academic Year</label>
                            <select id="target_ay" onchange="filterTargetClassrooms()" class="block w-full py-2.5 px-3 border border-gray-200 bg-gray-50 rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors">
                                <option value="">Select Academic Year...</option>
                                <?php foreach ($academic_years as $ay): ?>
                                <option value="<?php echo $ay['id']; ?>"><?php echo htmlspecialchars($ay['year_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Target Classroom</label>
                            <select id="target_classroom" class="block w-full py-2.5 px-3 border border-gray-200 bg-gray-50 rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-colors">
                                <option value="">Select Classroom...</option>
                            </select>
                        </div>
                    </div>

                    <div id="graduate_info_area" class="hidden bg-purple-50 rounded-xl p-4 border border-purple-100">
                        <div class="flex items-start gap-3">
                            <i data-lucide="graduation-cap" class="w-5 h-5 text-purple-600 mt-0.5"></i>
                            <div>
                                <h4 class="text-sm font-bold text-purple-900">Graduation Notice</h4>
                                <p class="text-xs text-purple-700 mt-1">Selected students will have their status changed to "Graduated". Their classroom assignment will be removed, and they will no longer be able to log in. Their historical data will be preserved.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-auto pt-8">
                    <button onclick="executePromotion()" id="btn_execute" class="w-full flex items-center justify-center gap-2 py-3.5 rounded-xl border border-transparent shadow-md bg-gradient-to-r from-blue-600 to-indigo-600 text-base font-bold text-white hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all">
                        <i data-lucide="zap" class="w-5 h-5"></i> Execute Action
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
const csrfToken = '<?php echo $csrf_token; ?>';
const allClassrooms = <?php echo json_encode($classrooms_all); ?>;

function filterSourceClassrooms() {
    const ay = document.getElementById('source_ay').value;
    const select = document.getElementById('source_classroom');
    select.innerHTML = '<option value="">Select Classroom...</option>';
    
    if (ay) {
        allClassrooms.forEach(c => {
            if (c.academic_year_id == ay) {
                const opt = document.createElement('option');
                opt.value = c.id;
                opt.textContent = c.classroom_name;
                select.appendChild(opt);
            }
        });
    }
    loadStudents(); // Clear students list
}

function filterTargetClassrooms() {
    const ay = document.getElementById('target_ay').value;
    const select = document.getElementById('target_classroom');
    select.innerHTML = '<option value="">Select Classroom...</option>';
    
    if (ay) {
        allClassrooms.forEach(c => {
            if (c.academic_year_id == ay) {
                const opt = document.createElement('option');
                opt.value = c.id;
                opt.textContent = c.classroom_name;
                select.appendChild(opt);
            }
        });
    }
}

async function loadStudents() {
    const cid = document.getElementById('source_classroom').value;
    const list = document.getElementById('students_list');
    
    if (!cid) {
        list.innerHTML = '<div class="text-sm text-gray-500 text-center py-8">Select a classroom to view students.</div>';
        return;
    }
    
    list.innerHTML = '<div class="flex justify-center py-8"><i data-lucide="loader-2" class="w-6 h-6 animate-spin text-gray-400"></i></div>';
    lucide.createIcons();
    
    try {
        const res = await fetch(`../ajax/fetch_classroom_students.php?id=${cid}`);
        const data = await res.json();
        
        if (data.success) {
            if (data.students.length === 0) {
                list.innerHTML = '<div class="text-sm text-gray-500 text-center py-8">No students found in this classroom.</div>';
            } else {
                let html = '';
                data.students.forEach(s => {
                    html += `
                    <label class="flex items-center gap-3 p-2 hover:bg-white rounded-lg cursor-pointer border border-transparent hover:border-gray-200 transition-colors">
                        <input type="checkbox" name="student_ids[]" value="${s.id}" class="student-checkbox w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                        <div class="flex flex-col">
                            <span class="text-sm font-semibold text-gray-900">${s.username}</span>
                            <span class="text-[10px] text-gray-500 font-medium">${s.email}</span>
                        </div>
                    </label>
                    `;
                });
                list.innerHTML = html;
                document.getElementById('check_all').checked = false;
            }
        } else {
            list.innerHTML = '<div class="text-sm text-red-500 text-center py-8">Error loading students.</div>';
        }
    } catch (err) {
        list.innerHTML = '<div class="text-sm text-red-500 text-center py-8">Connection error.</div>';
    }
}

function toggleAllStudents() {
    const checked = document.getElementById('check_all').checked;
    document.querySelectorAll('.student-checkbox').forEach(cb => {
        cb.checked = checked;
    });
}

function toggleActionType() {
    const action = document.querySelector('input[name="action_type"]:checked').value;
    const labels = document.querySelectorAll('label.cursor-pointer');
    
    labels.forEach(l => {
        l.classList.remove('border-blue-600', 'ring-1', 'ring-blue-600');
        l.classList.add('border-gray-300');
        l.querySelector('i.text-blue-600').classList.remove('opacity-100');
        l.querySelector('i.text-blue-600').classList.add('opacity-0');
    });
    
    const activeLabel = document.querySelector(`input[value="${action}"]`).closest('label');
    activeLabel.classList.remove('border-gray-300');
    activeLabel.classList.add('border-blue-600', 'ring-1', 'ring-blue-600');
    activeLabel.querySelector('i.text-blue-600').classList.remove('opacity-0');
    activeLabel.querySelector('i.text-blue-600').classList.add('opacity-100');
    
    if (action === 'promote') {
        document.getElementById('target_selection_area').classList.remove('hidden');
        document.getElementById('graduate_info_area').classList.add('hidden');
    } else {
        document.getElementById('target_selection_area').classList.add('hidden');
        document.getElementById('graduate_info_area').classList.remove('hidden');
    }
}

async function executePromotion() {
    const studentCheckboxes = document.querySelectorAll('.student-checkbox:checked');
    if (studentCheckboxes.length === 0) {
        alert('Please select at least one student to process.');
        return;
    }
    
    const action = document.querySelector('input[name="action_type"]:checked').value;
    let target_classroom = '';
    
    if (action === 'promote') {
        target_classroom = document.getElementById('target_classroom').value;
        if (!target_classroom) {
            alert('Please select a target classroom.');
            return;
        }
    }
    
    if (!confirm(`Are you sure you want to ${action} ${studentCheckboxes.length} student(s)?`)) {
        return;
    }
    
    const studentIds = Array.from(studentCheckboxes).map(cb => cb.value);
    
    const btn = document.getElementById('btn_execute');
    const originalContent = btn.innerHTML;
    btn.innerHTML = '<i data-lucide="loader-2" class="w-5 h-5 animate-spin"></i> Processing...';
    btn.disabled = true;
    lucide.createIcons();
    
    const params = new URLSearchParams();
    params.append('csrf_token', csrfToken);
    params.append('action_type', action);
    if (action === 'promote') {
        params.append('target_classroom_id', target_classroom);
    }
    studentIds.forEach(id => params.append('student_ids[]', id));
    
    try {
        const res = await fetch('../ajax/promote_students.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: params.toString()
        });
        const data = await res.json();
        
        if (data.success) {
            const alertBox = document.getElementById('top_success_alert');
            document.getElementById('top_success_message').textContent = data.message;
            alertBox.classList.remove('hidden');
            alertBox.classList.add('flex');
            window.scrollTo({top: 0, behavior: 'smooth'});
            
            // Reload list
            loadStudents();
            
            setTimeout(() => {
                alertBox.classList.add('hidden');
                alertBox.classList.remove('flex');
            }, 3000);
        } else {
            alert('Error: ' + data.message);
        }
    } catch(err) {
        alert('An unexpected error occurred.');
    } finally {
        btn.innerHTML = originalContent;
        btn.disabled = false;
        lucide.createIcons();
    }
}
</script>

<?php include '../includes/footer.php'; ?>
