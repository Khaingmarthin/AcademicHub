<?php
require_once '../config/session.php';
require_once '../config/db.php';
require_once '../config/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email']);
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!verify_csrf_token($csrf_token)) {
        set_flash_message('error', 'Invalid security token.');
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $updateStmt = $pdo->prepare("UPDATE users SET reset_token = :token, reset_expires = :expires WHERE id = :id");
            $updateStmt->execute([
                'token' => hash('sha256', $token),
                'expires' => $expires,
                'id' => $user['id']
            ]);

            // In a real application, you would send an email here with the link.
            // Example link: http://localhost/auth/reset-password.php?token=$token
            
            // For development purposes, we can just show the link in success message
            // or just say "Check your email".
            $resetLink = "/auth/reset-password.php?token=" . $token;
            set_flash_message('success', 'If your email exists, a password reset link has been sent. <br><a href="'.$resetLink.'" class="underline">Development Link</a>');
        } else {
            // We still show the same message to prevent email enumeration
            set_flash_message('success', 'If your email exists, a password reset link has been sent.');
        }
    }
}
?>
<?php 
$is_auth_page = true;
include '../includes/header.php'; 
?>
<div class="min-h-screen bg-gray-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 w-full">
    <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-xl shadow-lg">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Reset your password
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Enter your email address and we'll send you a link to reset your password.
            </p>
        </div>
        
        <?php display_flash_messages(); ?>

        <form class="mt-8 space-y-6" action="forgot-password.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <div class="rounded-md shadow-sm -space-y-px">
                <div>
                    <label for="email-address" class="sr-only">Email address</label>
                    <input id="email-address" name="email" type="email" autocomplete="email" required class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm" placeholder="Email address">
                </div>
            </div>

            <div>
                <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Send reset link
                </button>
            </div>
            
            <div class="text-sm text-center">
                <a href="login.php" class="font-medium text-blue-600 hover:text-blue-500">
                    Back to login
                </a>
            </div>
        </form>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
