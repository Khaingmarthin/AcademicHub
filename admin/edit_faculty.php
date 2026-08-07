<?php
require_once '../config/session.php';
require_admin();
require_once '../config/db.php';
require_once '../config/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch the faculty
$stmt = $pdo->prepare("SELECT * FROM faculties WHERE id = :id");
$stmt->execute(['id' => $id]);
$faculty = $stmt->fetch();

if (!$faculty) {
    set_flash_message('error', 'Faculty not found.');
    header('Location: faculties.php');
    exit;
}

$csrf_token = generate_csrf_token();

// Determine icon
$academic_codes = ['FCS', 'FIS', 'FCST', 'ITSM', 'PHYSICS', 'LANGUAGE', 'MATH'];
$code = strtoupper($faculty['faculty_code']);
$is_academic = in_array($code, $academic_codes);
$icon = $is_academic ? 'graduation-cap' : 'building-2';
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>
<?php include '../includes/navbar.php'; ?>

<div class="min-h-screen bg-[#F8FAFC] pb-12">
    <div class="max-w-[800px] mx-auto px-4 sm:px-6 lg:px-8 pt-8">

        <!-- Breadcrumb & Back Navigation -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3 text-sm">
                <a href="faculties.php" class="inline-flex items-center gap-1.5 text-gray-500 hover:text-[#2563EB] font-medium transition-colors duration-200 group">
                    <i data-lucide="arrow-left" class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform duration-200"></i>
                    Back to Faculties
                </a>
                <span class="text-gray-300">/</span>
                <span class="text-gray-700 font-semibold">Edit Faculty</span>
            </div>
        </div>

        <!-- Page Title Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
            <div class="p-6 sm:p-8">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center flex-shrink-0">
                        <i data-lucide="pencil" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Edit Faculty</h1>
                        <p class="text-sm text-gray-500 font-medium mt-0.5">
                            Editing: <span class="text-gray-700 font-semibold"><?php echo htmlspecialchars($faculty['faculty_name']); ?></span>
                            <span class="inline-flex items-center ml-2 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-gray-100 text-gray-500"><?php echo htmlspecialchars($code); ?></span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success / Error Alert -->
        <div id="alert_container" class="hidden mb-6 transition-all duration-300">
            <div id="alert_box" class="flex items-center gap-3 px-5 py-4 rounded-2xl border shadow-sm">
                <i id="alert_icon" data-lucide="check-circle" class="w-5 h-5 flex-shrink-0"></i>
                <p id="alert_message" class="text-sm font-semibold flex-1"></p>
                <button onclick="document.getElementById('alert_container').classList.add('hidden')" class="text-current opacity-60 hover:opacity-100 transition-opacity">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        <!-- Edit Form Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                <div class="w-8 h-8 bg-blue-50 text-[#2563EB] rounded-lg flex items-center justify-center">
                    <i data-lucide="file-edit" class="w-4 h-4"></i>
                </div>
                <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Faculty Information</h2>
            </div>

            <form id="edit_faculty_form" class="p-6 sm:p-8" novalidate>
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <!-- Preserve existing code as hidden field since user only edits name, description, vision & mission -->
                <input type="hidden" name="code" value="<?php echo htmlspecialchars($faculty['faculty_code']); ?>">

                <div class="space-y-6">
                    <!-- Faculty Name -->
                    <div>
                        <label for="edit_name" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                            Faculty Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="edit_name"
                            name="name"
                            value="<?php echo htmlspecialchars($faculty['faculty_name']); ?>"
                            required
                            class="block w-full px-4 py-3 border border-gray-200 rounded-xl text-sm font-medium text-gray-800 placeholder-gray-400 focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] transition-all duration-200"
                            placeholder="e.g. Faculty of Computer Science"
                        >
                        <p id="error_name" class="mt-1.5 text-xs font-semibold text-red-500 hidden">
                            <i data-lucide="alert-circle" class="w-3.5 h-3.5 inline-block mr-1 align-text-bottom"></i>
                            Faculty Name is required.
                        </p>
                    </div>

                    <!-- Vision -->
                    <div>
                        <label for="edit_vision" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                            Vision
                        </label>
                        <textarea
                            id="edit_vision"
                            name="vision"
                            rows="3"
                            class="block w-full px-4 py-3 border border-gray-200 rounded-xl text-sm font-medium text-gray-800 placeholder-gray-400 focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] resize-none transition-all duration-200"
                            placeholder="Enter faculty vision..."
                        ><?php echo htmlspecialchars($faculty['vision'] ?? ''); ?></textarea>
                    </div>

                    <!-- Mission -->
                    <div>
                        <label for="edit_mission" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                            Mission
                        </label>
                        <textarea
                            id="edit_mission"
                            name="mission"
                            rows="3"
                            class="block w-full px-4 py-3 border border-gray-200 rounded-xl text-sm font-medium text-gray-800 placeholder-gray-400 focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] resize-none transition-all duration-200"
                            placeholder="Enter faculty mission..."
                        ><?php echo htmlspecialchars($faculty['mission'] ?? ''); ?></textarea>
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="edit_description" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                            Description <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            id="edit_description"
                            name="description"
                            rows="5"
                            required
                            class="block w-full px-4 py-3 border border-gray-200 rounded-xl text-sm font-medium text-gray-800 placeholder-gray-400 focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] resize-none transition-all duration-200"
                            placeholder="Enter faculty description..."
                        ><?php echo htmlspecialchars($faculty['description'] ?? ''); ?></textarea>
                        <p id="error_description" class="mt-1.5 text-xs font-semibold text-red-500 hidden">
                            <i data-lucide="alert-circle" class="w-3.5 h-3.5 inline-block mr-1 align-text-bottom"></i>
                            Description is required.
                        </p>
                    </div>
                </div>


                <!-- Form Actions -->
                <div class="mt-8 pt-6 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-end gap-3">
                    <a href="faculties.php"
                       class="inline-flex items-center justify-center gap-2 w-full sm:w-auto px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-bold rounded-xl transition-all duration-200">
                        <i data-lucide="x" class="w-4 h-4"></i>
                        Cancel
                    </a>
                    <button
                        type="submit"
                        id="btn_update"
                        class="inline-flex items-center justify-center gap-2 w-full sm:w-auto px-6 py-2.5 bg-[#2563EB] hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-sm hover:shadow-md transition-all duration-200">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        Update Faculty
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>

<script>
document.getElementById('edit_faculty_form').addEventListener('submit', async function(e) {
    e.preventDefault();

    // Reset validation states
    const nameInput = document.getElementById('edit_name');
    const descInput = document.getElementById('edit_description');
    const errorName = document.getElementById('error_name');
    const errorDesc = document.getElementById('error_description');
    const alertContainer = document.getElementById('alert_container');
    const alertBox = document.getElementById('alert_box');
    const alertIcon = document.getElementById('alert_icon');
    const alertMessage = document.getElementById('alert_message');
    const btn = document.getElementById('btn_update');

    let isValid = true;

    // Clear previous errors
    errorName.classList.add('hidden');
    errorDesc.classList.add('hidden');
    nameInput.classList.remove('border-red-400', 'ring-2', 'ring-red-100');
    descInput.classList.remove('border-red-400', 'ring-2', 'ring-red-100');

    // Validate Faculty Name
    if (nameInput.value.trim() === '') {
        errorName.classList.remove('hidden');
        nameInput.classList.add('border-red-400', 'ring-2', 'ring-red-100');
        isValid = false;
    }

    // Validate Description
    if (descInput.value.trim() === '') {
        errorDesc.classList.remove('hidden');
        descInput.classList.add('border-red-400', 'ring-2', 'ring-red-100');
        isValid = false;
    }

    if (!isValid) {
        // Scroll to the first error
        const firstError = document.querySelector('.border-red-400');
        if (firstError) firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        // Re-render lucide icons for error messages
        if (typeof lucide !== 'undefined') lucide.createIcons();
        return;
    }

    // Disable button and show loading
    const originalHTML = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> Updating...';
    if (typeof lucide !== 'undefined') lucide.createIcons({ root: btn });

    try {
        const formData = new FormData(this);
        const params = new URLSearchParams();
        for (const [key, value] of formData) {
            params.append(key, value);
        }

        const res = await fetch('../ajax/update_faculty.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: params.toString()
        });
        const data = await res.json();

        if (data.success) {
            // Show success alert
            alertContainer.classList.remove('hidden');
            alertBox.className = 'flex items-center gap-3 px-5 py-4 rounded-2xl border shadow-sm bg-green-50 border-green-200 text-green-700';
            alertIcon.setAttribute('data-lucide', 'check-circle');
            alertMessage.textContent = 'Faculty updated successfully! Redirecting to Faculty List...';
            if (typeof lucide !== 'undefined') lucide.createIcons({ root: alertContainer });

            window.scrollTo({ top: 0, behavior: 'smooth' });

            // Redirect after delay
            setTimeout(() => {
                window.location.href = 'faculties.php';
            }, 1500);
        } else {
            // Show error alert
            alertContainer.classList.remove('hidden');
            alertBox.className = 'flex items-center gap-3 px-5 py-4 rounded-2xl border shadow-sm bg-red-50 border-red-200 text-red-700';
            alertIcon.setAttribute('data-lucide', 'alert-triangle');
            alertMessage.textContent = 'Error: ' + (data.message || 'An unexpected error occurred.');
            if (typeof lucide !== 'undefined') lucide.createIcons({ root: alertContainer });

            window.scrollTo({ top: 0, behavior: 'smooth' });

            btn.disabled = false;
            btn.innerHTML = originalHTML;
            if (typeof lucide !== 'undefined') lucide.createIcons({ root: btn });
        }
    } catch (err) {
        console.error(err);
        alertContainer.classList.remove('hidden');
        alertBox.className = 'flex items-center gap-3 px-5 py-4 rounded-2xl border shadow-sm bg-red-50 border-red-200 text-red-700';
        alertIcon.setAttribute('data-lucide', 'alert-triangle');
        alertMessage.textContent = 'A network error occurred. Please try again.';
        if (typeof lucide !== 'undefined') lucide.createIcons({ root: alertContainer });

        window.scrollTo({ top: 0, behavior: 'smooth' });

        btn.disabled = false;
        btn.innerHTML = originalHTML;
        if (typeof lucide !== 'undefined') lucide.createIcons({ root: btn });
    }
});

// Clear inline error on input
document.getElementById('edit_name').addEventListener('input', function() {
    this.classList.remove('border-red-400', 'ring-2', 'ring-red-100');
    document.getElementById('error_name').classList.add('hidden');
});
document.getElementById('edit_description').addEventListener('input', function() {
    this.classList.remove('border-red-400', 'ring-2', 'ring-red-100');
    document.getElementById('error_description').classList.add('hidden');
});
</script>

<?php include '../includes/footer.php'; ?>
