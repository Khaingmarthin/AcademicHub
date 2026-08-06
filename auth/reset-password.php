<?php
require_once '../config/session.php';
require_once '../config/db.php';
require_once '../config/functions.php';

$token = $_GET['token'] ?? '';
$isValidToken = false;
$userId = null;

if (!empty($token)) {
    // Check if token exists and hasn't expired
    $hashedToken = hash('sha256', $token);
    $stmt = $pdo->prepare("SELECT id, reset_expires FROM users WHERE reset_token = :token");
    $stmt->execute(['token' => $hashedToken]);
    $user = $stmt->fetch();

    if ($user && strtotime($user['reset_expires']) > time()) {
        $isValidToken = true;
        $userId = $user['id'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postToken = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!verify_csrf_token($csrfToken)) {
        set_flash_message('error', 'Invalid security token.');
    } else if ($password !== $confirmPassword) {
        set_flash_message('error', 'Passwords do not match.');
        $isValidToken = true; // Ensure form still shows
        $token = $postToken;
    } else if (strlen($password) < 8) {
        set_flash_message('error', 'Password must be at least 8 characters long.');
        $isValidToken = true;
        $token = $postToken;
    } else {
        // Re-verify token on POST
        $hashedToken = hash('sha256', $postToken);
        $stmt = $pdo->prepare("SELECT id, reset_expires FROM users WHERE reset_token = :token");
        $stmt->execute(['token' => $hashedToken]);
        $user = $stmt->fetch();

        if ($user && strtotime($user['reset_expires']) > time()) {
            // Update password
            $newHashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $updateStmt = $pdo->prepare("UPDATE users SET password = :password, reset_token = NULL, reset_expires = NULL WHERE id = :id");
            $updateStmt->execute([
                'password' => $newHashedPassword,
                'id' => $user['id']
            ]);

            set_flash_message('success', 'Your password has been successfully reset. You can now login.');
            redirect('login.php');
        } else {
            set_flash_message('error', 'The reset link is invalid or has expired.');
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
                Create new password
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Please enter your new password below.
            </p>
        </div>
        
        <?php display_flash_messages(); ?>

        <?php if ($isValidToken): ?>
        <form class="mt-8 space-y-6" action="reset-password.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            
            <div class="rounded-md shadow-sm -space-y-px">
                <div>
                    <label for="password" class="sr-only">New Password</label>
                    <input id="password" name="password" type="password" required class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm" placeholder="New Password (min 8 chars)">
                </div>
                <div>
                    <label for="confirm-password" class="sr-only">Confirm Password</label>
                    <input id="confirm-password" name="confirm_password" type="password" required class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm" placeholder="Confirm Password">
                </div>
            </div>

            <div>
                <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Reset Password
                </button>
            </div>
        </form>
        <?php else: ?>
            <?php if (!isset($_SESSION['flash']['error'])): ?>
            <div class="rounded-md bg-red-50 p-4 mt-8">
                <div class="flex">
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Invalid or expired link</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <p>The password reset link you clicked is invalid or has expired. Please request a new one.</p>
                        </div>
                        <div class="mt-4">
                            <a href="forgot-password.php" class="font-medium text-red-800 hover:text-red-900">Request new link</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
