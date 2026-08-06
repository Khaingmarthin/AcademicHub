<?php
require_once '../config/session.php';
require_student();
require_once '../config/db.php';
require_once '../config/functions.php';

$user_id = $_SESSION['user_id'];

// Fetch current settings
$stmt = $pdo->prepare("SELECT general_enabled as notify_new_announcement, urgent_enabled as notify_urgent_announcement, timetable_enabled as notify_timetable_update FROM notification_settings WHERE user_id = :uid");
$stmt->execute(['uid' => $user_id]);
$settings = $stmt->fetch();

if (!$settings) {
    // If not found, insert defaults dynamically
    $insert = $pdo->prepare("INSERT IGNORE INTO notification_settings (user_id) VALUES (:uid)");
    $insert->execute(['uid' => $user_id]);
    
    // Fetch again
    $stmt->execute(['uid' => $user_id]);
    $settings = $stmt->fetch();
    if (!$settings) {
        // Fallback defaults
        $settings = [
            'notify_new_announcement' => 1,
            'notify_urgent_announcement' => 1,
            'notify_timetable_update' => 1
        ];
    }
}
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>
<?php include '../includes/navbar.php'; ?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 flex items-center">
            <svg class="w-8 h-8 mr-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            Notification Settings
        </h1>
        <p class="mt-2 text-sm text-gray-600">Manage how and when you receive alerts from the university.</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div id="alert-container" class="hidden p-4 text-sm font-medium border-b"></div>
        
        <form id="settingsForm" class="divide-y divide-gray-100">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            
            <div class="p-6 hover:bg-gray-50 transition-colors">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">New Announcements</h3>
                        <p class="mt-1 text-sm text-gray-500">Get notified whenever a general announcement is published.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="notify_new_announcement" class="sr-only peer" <?php echo $settings['notify_new_announcement'] ? 'checked' : ''; ?>>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                </div>
            </div>

            <div class="p-6 hover:bg-gray-50 transition-colors">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 flex items-center">
                            Urgent Announcements
                            <span class="ml-2 bg-red-100 text-red-600 text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wide">Recommended</span>
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">Critical updates that require immediate attention.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="notify_urgent_announcement" class="sr-only peer" <?php echo $settings['notify_urgent_announcement'] ? 'checked' : ''; ?>>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-500"></div>
                    </label>
                </div>
            </div>

            <div class="p-6 hover:bg-gray-50 transition-colors">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Timetable Updates</h3>
                        <p class="mt-1 text-sm text-gray-500">Alerts when a new class schedule is posted for your major.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="notify_timetable_update" class="sr-only peer" <?php echo $settings['notify_timetable_update'] ? 'checked' : ''; ?>>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                    </label>
                </div>
            </div>

            <div class="p-6 bg-gray-50 border-t border-gray-100 flex justify-end">
                <button type="submit" id="saveBtn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Save Preferences
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('settingsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const btn = document.getElementById('saveBtn');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = 'Saving...';
    
    const formData = new FormData(this);
    
    fetch('../ajax/update_notification_settings.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        const alertContainer = document.getElementById('alert-container');
        alertContainer.classList.remove('hidden', 'bg-red-50', 'text-red-700', 'border-red-200', 'bg-green-50', 'text-green-700', 'border-green-200');
        
        if (data.success) {
            alertContainer.classList.add('bg-green-50', 'text-green-700', 'border-green-200');
            alertContainer.innerHTML = 'Preferences saved successfully.';
            setTimeout(() => alertContainer.classList.add('hidden'), 3000);
        } else {
            alertContainer.classList.add('bg-red-50', 'text-red-700', 'border-red-200');
            alertContainer.innerHTML = data.message;
        }
    })
    .catch(err => {
        console.error(err);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
});
</script>

<?php include '../includes/footer.php'; ?>
