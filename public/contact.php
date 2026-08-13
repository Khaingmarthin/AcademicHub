<?php
require_once '../config/db.php';
require_once '../config/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process contact form (mock)
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $message = sanitize_input($_POST['message']);
    
    // Ideally save to DB or send email
    set_flash_message('success', 'Thank you for your message. We will get back to you shortly.');
}
?>
<?php
// University contact information — uses existing system data when available,
// otherwise the values already used across the project's public pages.
$uni_name = 'University of Computer Studies (Meiktila)';
$uni_address = 'Meiktila-Tharzi Road, Pan Taw Sat Village, TawMa Village Group, Meiktila, Mandalay Division.';
$uni_phone = '(+95) 64 53 2005';
$uni_email = 'studentaffair@ucsmtla.edu.mm, ucsmtla_admin@ucsmtla.edu.mm';
$uni_website = 'https://www.ucsmtla.edu.mm';
try {
    $stmt = $pdo->query("SELECT university_name, address, phone, email, website FROM system_settings ORDER BY id ASC LIMIT 1");
    $sys = $stmt->fetch();
    if ($sys) {
        if (!empty($sys['university_name'])) $uni_name = $sys['university_name'];
        if (!empty($sys['address'])) $uni_address = $sys['address'];
        if (!empty($sys['phone'])) $uni_phone = $sys['phone'];
        if (!empty($sys['email'])) $uni_email = $sys['email'];
        if (!empty($sys['website'])) $uni_website = $sys['website'];
    }
} catch (PDOException $e) {
    // Settings table unavailable — fall back to the default campus information above.
}

// Office hours — kept as the values already used across the project's public pages.
// No office-hours configuration exists in system_settings, so these stay static.
$contact_today = (int)date('N'); // 1 = Monday ... 7 = Sunday
$oh_rows = [
    ['days' => 'Monday &ndash; Friday', 'time' => '9:00 AM &ndash; 4:00 PM', 'open' => true,  'is_today' => $contact_today >= 1 && $contact_today <= 5],
    ['days' => 'Saturday',              'time' => 'Closed',                  'open' => false, 'is_today' => $contact_today === 6],
    ['days' => 'Sunday',                'time' => 'Closed',                  'open' => false, 'is_today' => $contact_today === 7],
];
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
            Contact Us
        </h1>
        <p class="mt-6 max-w-3xl mx-auto text-xl text-blue-100 animate-fade-in-up" style="animation-delay: 0.1s;">
            Have questions or feedback? <br class="hidden sm:block">
            Send us a message and we'll respond as soon as possible.
        </p>
    </div>
</div>

<div class="bg-[#F5F7FB] py-16 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- MAIN LAYOUT: Two-Column -->
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-8 lg:gap-10 items-start">
            
            <!-- LEFT COLUMN: Contact Form (60%) -->
            <div class="lg:col-span-3">
                <div class="bg-white rounded-3xl shadow-lg border border-blue-100 p-8 sm:p-10">
                    <h3 class="text-2xl font-extrabold text-gray-900 mb-2 flex items-center">
                        <i data-lucide="send" class="w-7 h-7 text-blue-600 mr-3"></i>
                        Send a Message
                    </h3>
                    <p class="text-gray-500 mb-8">Fill out the form below and our team will get back to you shortly.</p>

                    <!-- Notification Area -->
                    <div id="contact-notification" class="hidden mb-6 p-4 rounded-xl flex items-start">
                        <i id="notif-icon" data-lucide="check-circle" class="w-5 h-5 mr-3 mt-0.5"></i>
                        <p id="notif-message" class="text-sm font-medium"></p>
                    </div>

                    <form id="contact-form" class="space-y-6" novalidate>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Full Name <span class="text-red-500">*</span></label>
                                <input type="text" name="name" id="name" autocomplete="name" class="w-full px-4 py-3 bg-[#F8FAFC] border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-gray-900 placeholder-gray-400" placeholder="John Doe">
                                <p id="name-error" class="hidden mt-1.5 text-sm text-red-500 font-medium"></p>
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email Address <span class="text-red-500">*</span></label>
                                <input type="email" name="email" id="email" autocomplete="email" class="w-full px-4 py-3 bg-[#F8FAFC] border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-gray-900 placeholder-gray-400" placeholder="john@example.com">
                                <p id="email-error" class="hidden mt-1.5 text-sm text-red-500 font-medium"></p>
                            </div>
                        </div>
                        
                        <div>
                            <label for="subject" class="block text-sm font-semibold text-gray-700 mb-2">Subject <span class="text-red-500">*</span></label>
                            <input type="text" name="subject" id="subject" class="w-full px-4 py-3 bg-[#F8FAFC] border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-gray-900 placeholder-gray-400" placeholder="How can we help you?">
                            <p id="subject-error" class="hidden mt-1.5 text-sm text-red-500 font-medium"></p>
                        </div>
                        
                        <div>
                            <label for="message" class="block text-sm font-semibold text-gray-700 mb-2">Message <span class="text-red-500">*</span></label>
                            <textarea id="message" name="message" rows="6" class="w-full px-4 py-3 bg-[#F8FAFC] border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-gray-900 placeholder-gray-400 resize-none" placeholder="Write your message here..."></textarea>
                            <p id="message-error" class="hidden mt-1.5 text-sm text-red-500 font-medium"></p>
                        </div>
                        
                        <div>
                            <button type="submit" id="submit-btn" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-4 border border-transparent rounded-xl shadow-md text-base font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-300 transform hover:-translate-y-0.5">
                                <i data-lucide="send" class="w-5 h-5"></i>
                                Send Message
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- RIGHT COLUMN: Office Hours & Contact Information (40%) -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- Office Hours Card -->
                <div class="bg-white rounded-3xl shadow-lg border border-blue-100 p-8">
                    <div class="flex items-center mb-6">
                        <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mr-4 flex-shrink-0">
                            <i data-lucide="clock" class="w-7 h-7"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl font-extrabold text-gray-900 leading-tight">Office Hours</h3>
                            <p class="text-sm text-gray-500">University Hours</p>
                        </div>
                    </div>
                    <ul class="divide-y divide-gray-100">
                        <?php foreach ($oh_rows as $row): ?>
                            <li class="flex items-center justify-between gap-3 py-4 <?php echo $row['is_today'] ? 'bg-blue-50 rounded-xl px-4 -mx-4' : ''; ?>">
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="w-2.5 h-2.5 rounded-full <?php echo $row['open'] ? 'bg-green-500' : 'bg-gray-300'; ?> flex-shrink-0"></span>
                                    <span class="text-sm font-semibold text-gray-800">
                                        <?php echo $row['days']; ?>
                                    </span>
                                    <?php if ($row['is_today']): ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-blue-600 text-white text-[10px] font-bold uppercase tracking-wider">
                                            <i data-lucide="star" class="w-3 h-3"></i>
                                            Today
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <span class="text-sm font-bold whitespace-nowrap <?php echo $row['open'] ? 'text-blue-700 bg-blue-50 px-3 py-1 rounded-lg' : 'text-gray-500 bg-gray-100 px-3 py-1 rounded-lg'; ?>">
                                    <?php echo $row['time']; ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Contact Information Card -->
                <div class="bg-white rounded-3xl shadow-lg border border-blue-100 p-8">
                    <div class="flex items-center mb-6">
                        <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mr-4 flex-shrink-0">
                            <i data-lucide="info" class="w-7 h-7"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl font-extrabold text-gray-900 leading-tight">Contact Information</h3>
                            <p class="text-sm text-gray-500">Reach us directly</p>
                        </div>
                    </div>
                    <ul class="space-y-5">
                        <li class="flex items-start">
                            <i data-lucide="phone" class="w-5 h-5 text-blue-600 mr-3 mt-0.5 flex-shrink-0"></i>
                            <div class="min-w-0">
                                <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-0.5">Phone</p>
                                <a href="tel:<?php echo htmlspecialchars(preg_replace('/[^0-9+]/', '', $uni_phone)); ?>" class="text-blue-600 font-bold hover:text-blue-800 transition-colors break-words"><?php echo htmlspecialchars($uni_phone); ?></a>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <i data-lucide="mail" class="w-5 h-5 text-blue-600 mr-3 mt-0.5 flex-shrink-0"></i>
                            <div class="min-w-0">
                                <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-0.5">Email</p>
                                <a href="mailto:<?php echo htmlspecialchars($uni_email); ?>" class="text-blue-600 font-bold hover:text-blue-800 transition-colors break-words"><?php echo htmlspecialchars($uni_email); ?></a>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <i data-lucide="map-pin" class="w-5 h-5 text-blue-600 mr-3 mt-0.5 flex-shrink-0"></i>
                            <div class="min-w-0">
                                <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-0.5">Address</p>
                                <p class="text-gray-700 font-medium leading-relaxed"><?php echo htmlspecialchars($uni_address); ?></p>
                            </div>
                        </li>
                    </ul>
                </div>

            </div>
            
        </div>
    </div>
</div>



<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    const contactForm = document.getElementById('contact-form');
    if (!contactForm) return;

    const submitBtn = document.getElementById('submit-btn');
    const notification = document.getElementById('contact-notification');
    const notifMessage = document.getElementById('notif-message');
    const notifIcon = document.getElementById('notif-icon');

    const contactFields = ['name', 'email', 'subject', 'message'];

    function setFieldError(fieldId, message) {
        const input = document.getElementById(fieldId);
        const error = document.getElementById(fieldId + '-error');
        if (!input || !error) return;
        if (message) {
            input.classList.add('border-red-400', 'ring-2', 'ring-red-100');
            input.setAttribute('aria-invalid', 'true');
            error.textContent = message;
            error.classList.remove('hidden');
        } else {
            input.classList.remove('border-red-400', 'ring-2', 'ring-red-100');
            input.removeAttribute('aria-invalid');
            error.textContent = '';
            error.classList.add('hidden');
        }
    }

    function fieldMessage(fieldId, value) {
        if (value === '') return 'This field is required.';
        if (fieldId === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) return 'Please enter a valid email address.';
        return '';
    }

    function validateContactForm() {
        let valid = true;
        contactFields.forEach(function (id) {
            const input = document.getElementById(id);
            const msg = fieldMessage(id, input ? input.value.trim() : '');
            setFieldError(id, msg);
            if (msg) valid = false;
        });
        return valid;
    }

    contactFields.forEach(function (id) {
        const input = document.getElementById(id);
        if (!input) return;
        input.addEventListener('input', function () { setFieldError(id, ''); });
        input.addEventListener('blur', function () {
            setFieldError(id, fieldMessage(id, input.value.trim()));
        });
    });

    contactForm.addEventListener('submit', function(e) {
        e.preventDefault();

        if (!validateContactForm()) return;

        // UI update during submission
        const originalBtnHtml = submitBtn.innerHTML;
        submitBtn.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Sending...';
        submitBtn.disabled = true;
        
        const formData = new FormData(contactForm);
        
        fetch('ajax_submit_contact.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            notification.classList.remove('hidden', 'bg-red-50', 'text-red-800', 'bg-green-50', 'text-green-800');
            
            if (data.success) {
                notification.classList.add('bg-green-50', 'text-green-800');
                notifIcon.setAttribute('data-lucide', 'check-circle');
                notifIcon.classList.remove('text-red-500');
                notifIcon.classList.add('text-green-500');
                notifMessage.innerText = data.message;
                contactForm.reset();
                validateContactForm();
            } else {
                notification.classList.add('bg-red-50', 'text-red-800');
                notifIcon.setAttribute('data-lucide', 'alert-circle');
                notifIcon.classList.remove('text-green-500');
                notifIcon.classList.add('text-red-500');
                notifMessage.innerText = data.message || 'An error occurred. Please try again.';
            }
            
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
            
            // Revert button
            submitBtn.innerHTML = originalBtnHtml;
            submitBtn.disabled = false;
        })
        .catch(error => {
            console.error('Error:', error);
            notification.classList.remove('hidden', 'bg-green-50', 'text-green-800');
            notification.classList.add('bg-red-50', 'text-red-800');
            notifIcon.setAttribute('data-lucide', 'alert-circle');
            notifIcon.classList.remove('text-green-500');
            notifIcon.classList.add('text-red-500');
            notifMessage.innerText = 'Network error. Please try again later.';
            
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
            
            submitBtn.innerHTML = originalBtnHtml;
            submitBtn.disabled = false;
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>
