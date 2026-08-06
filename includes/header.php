<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(get_setting('site_name', 'UCSMTLA Academic Hub')); ?></title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Tailwind CSS Output -->
    <link rel="stylesheet" href="<?php echo htmlspecialchars(base_url('/assets/css/output.css')) . '?v=' . filemtime(__DIR__ . '/../assets/css/output.css'); ?>">
    <!-- Admin Custom Layout CSS -->
    <link rel="stylesheet" href="<?php echo htmlspecialchars(base_url('/assets/css/admin-layout.css')) . '?v=' . (file_exists(__DIR__ . '/../assets/css/admin-layout.css') ? filemtime(__DIR__ . '/../assets/css/admin-layout.css') : time()); ?>">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root {
            --primary-color: <?php echo htmlspecialchars(get_setting('primary_color', '#2563eb')); ?>;
        }
    </style>
    <!-- Prevent dark mode & sidebar flash -->
    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
        if (localStorage.getItem('sidebarState') === 'collapsed') {
            document.documentElement.classList.add('sidebar-collapsed');
        } else {
            document.documentElement.classList.remove('sidebar-collapsed');
        }
    </script>
</head>
<body class="bg-[#F5F7FB] dark:bg-gray-900 text-gray-800 dark:text-gray-100 font-sans antialiased flex flex-col min-h-screen transition-colors duration-300">
<?php $is_auth_page = $is_auth_page ?? false; ?>
<?php if (!$is_auth_page): ?>
    <div class="flex flex-1 h-screen overflow-hidden">
<?php endif; ?>
