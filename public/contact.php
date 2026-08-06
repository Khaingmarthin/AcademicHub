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
<?php include '../includes/header.php'; ?>
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
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-10">
            
            <!-- LEFT COLUMN: Contact Form -->
            <div class="lg:col-span-3">
                <div class="bg-white/70 backdrop-blur-xl rounded-3xl shadow-lg border border-white/40 p-8 sm:p-10 relative overflow-hidden group">
                    <div class="absolute -inset-1 bg-gradient-to-br from-blue-400 to-indigo-400 rounded-3xl blur opacity-10 group-hover:opacity-20 transition duration-500 -z-10"></div>
                    
                    <h2 class="text-2xl font-extrabold text-gray-900 mb-8 flex items-center">
                        <i data-lucide="send" class="w-7 h-7 text-blue-600 mr-3"></i>
                        Send a Message
                    </h2>

                    <!-- Notification Area -->
                    <div id="contact-notification" class="hidden mb-6 p-4 rounded-xl flex items-start">
                        <i id="notif-icon" data-lucide="check-circle" class="w-5 h-5 mr-3 mt-0.5"></i>
                        <p id="notif-message" class="text-sm font-medium"></p>
                    </div>

                    <form id="contact-form" class="space-y-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Full Name</label>
                                <input type="text" name="name" id="name" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-gray-900 placeholder-gray-400" placeholder="John Doe">
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email Address</label>
                                <input type="email" name="email" id="email" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-gray-900 placeholder-gray-400" placeholder="john@example.com">
                            </div>
                        </div>
                        
                        <div>
                            <label for="subject" class="block text-sm font-semibold text-gray-700 mb-2">Subject</label>
                            <input type="text" name="subject" id="subject" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-gray-900 placeholder-gray-400" placeholder="How can we help you?">
                        </div>
                        
                        <div>
                            <label for="message" class="block text-sm font-semibold text-gray-700 mb-2">Message</label>
                            <textarea id="message" name="message" rows="5" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-gray-900 placeholder-gray-400 resize-none" placeholder="Write your message here..."></textarea>
                        </div>
                        
                        <div>
                            <button type="submit" id="submit-btn" class="w-full flex justify-center items-center py-4 px-6 border border-transparent rounded-xl shadow-md text-base font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-300 transform hover:-translate-y-0.5">
                                <i data-lucide="send" class="w-5 h-5 mr-2"></i>
                                Send Message
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- RIGHT COLUMN: Cards -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- Office Hours Card -->
                <div class="bg-white/70 backdrop-blur-xl rounded-3xl shadow-lg border border-white/40 p-8 relative overflow-hidden group">
                    <div class="absolute -inset-1 bg-gradient-to-br from-indigo-400 to-purple-400 rounded-3xl blur opacity-10 group-hover:opacity-20 transition duration-500 -z-10"></div>
                    <div class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center mb-6">
                        <i data-lucide="clock" class="w-7 h-7"></i>
                    </div>
                    <h3 class="text-2xl font-extrabold text-gray-900 mb-6">Office Hours</h3>
                    <ul class="space-y-4">
                        <li class="flex justify-between items-center pb-4 border-b border-gray-100">
                            <span class="text-gray-600 font-medium">Monday &ndash; Friday</span>
                            <span class="text-indigo-700 font-bold bg-indigo-50 px-3 py-1 rounded-lg">9:00 AM &ndash; 4:00 PM</span>
                        </li>
                        <li class="flex justify-between items-center pb-4 border-b border-gray-100">
                            <span class="text-gray-600 font-medium">Saturday</span>
                            <span class="text-gray-500 font-bold bg-gray-100 px-3 py-1 rounded-lg">Closed</span>
                        </li>
                        <li class="flex justify-between items-center">
                            <span class="text-gray-600 font-medium">Sunday</span>
                            <span class="text-gray-500 font-bold bg-gray-100 px-3 py-1 rounded-lg">Closed</span>
                        </li>
                    </ul>
                </div>

                <!-- System Support Card -->
                <div class="bg-white/70 backdrop-blur-xl rounded-3xl shadow-lg border border-white/40 p-8 relative overflow-hidden group">
                    <div class="absolute -inset-1 bg-gradient-to-br from-blue-400 to-cyan-400 rounded-3xl blur opacity-10 group-hover:opacity-20 transition duration-500 -z-10"></div>
                    <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mb-6">
                        <i data-lucide="life-buoy" class="w-7 h-7"></i>
                    </div>
                    <h3 class="text-2xl font-extrabold text-gray-900 mb-4">System Support</h3>
                    <p class="text-gray-600 mb-6">Need help using UCSMTLA Academic Hub?</p>
                    
                    <div class="bg-gray-50 rounded-xl p-5 mb-4 border border-gray-100 flex items-center">
                        <i data-lucide="mail" class="w-5 h-5 text-gray-400 mr-3"></i>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-0.5">Email</p>
                            <a href="mailto:support@ucsmtla.edu.mm" class="text-blue-600 font-bold hover:text-blue-800 transition-colors">support@ucsmtla.edu.mm</a>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 rounded-xl p-5 border border-gray-100 flex items-center">
                        <i data-lucide="zap" class="w-5 h-5 text-amber-500 mr-3"></i>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-0.5">Response Time</p>
                            <p class="text-gray-900 font-bold">Within 24&ndash;48 hours</p>
                        </div>
                    </div>
                </div>

            </div>
            
        </div>
    </div>
</div>

<style>
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.animate-fade-in-up {
    animation: fadeInUp 0.8s ease-out forwards;
    opacity: 0;
}
</style>

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
    
    contactForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
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
