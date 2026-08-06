<?php
// migrate.php
$base_dir = __DIR__;

$moves = [
    // Admin
    'admin/announcements/index.php' => 'admin/announcements.php',
    'admin/announcements/create.php' => 'admin/create_announcement.php',
    'admin/announcements/edit.php' => 'admin/edit_announcement.php',
    'admin/announcements/view.php' => 'admin/view_announcement.php',
    'admin/announcements/_form.php' => 'admin/_announcement_form.php',

    'admin/categories/index.php' => 'admin/categories.php',
    'admin/categories/create.php' => 'admin/create_category.php',
    'admin/categories/edit.php' => 'admin/edit_category.php',
    'admin/categories/_form.php' => 'admin/_category_form.php',

    'admin/timetables/index.php' => 'admin/timetables.php',
    'admin/timetables/upload.php' => 'admin/upload_timetable.php',

    'admin/academic-years/index.php' => 'admin/academic_years.php',
    'admin/academic-years/create.php' => 'admin/create_academic_year.php',
    'admin/academic-years/edit.php' => 'admin/edit_academic_year.php',
    'admin/academic-years/_form.php' => 'admin/_academic_year_form.php',

    'admin/comments/index.php' => 'admin/comments.php',

    // Ajax
    'ajax/academic-years/create.php' => 'ajax/create_academic_year.php',
    'ajax/academic-years/set_status.php' => 'ajax/set_academic_year_status.php',
    'ajax/academic-years/update.php' => 'ajax/update_academic_year.php',

    'ajax/announcements/create.php' => 'ajax/create_announcement.php',
    'ajax/announcements/delete.php' => 'ajax/delete_announcement.php',
    'ajax/announcements/update.php' => 'ajax/update_announcement.php',

    'ajax/bookmarks/toggle.php' => 'ajax/toggle_bookmark.php',

    'ajax/categories/create.php' => 'ajax/create_category.php',
    'ajax/categories/delete.php' => 'ajax/delete_category.php',
    'ajax/categories/update.php' => 'ajax/update_category.php',

    'ajax/comments/delete.php' => 'ajax/delete_comment.php',
    'ajax/comments/fetch.php' => 'ajax/fetch_comments.php',
    'ajax/comments/post.php' => 'ajax/post_comment.php',

    'ajax/notifications/mark_read.php' => 'ajax/mark_notification_read.php',

    'ajax/search/results.php' => 'ajax/search_results.php',

    'ajax/settings/update.php' => 'ajax/update_settings.php',
    'ajax/settings/update_notifications.php' => 'ajax/update_notification_settings.php',

    'ajax/timetables/upload_handler.php' => 'ajax/upload_timetable.php',
];

// Perform file moves
foreach ($moves as $src => $dest) {
    $src_path = "$base_dir/$src";
    $dest_path = "$base_dir/$dest";
    if (file_exists($src_path)) {
        rename($src_path, $dest_path);
        echo "Moved $src to $dest\n";
    }
}

// Function to update content in a file
function update_file_content($file, $moves) {
    if (!file_exists($file)) return;
    $content = file_get_contents($file);
    $original = $content;

    // Replace include/require paths: ../../ -> ../ for files that were moved up one directory
    // Note: since we run this AFTER moving, we can't reliably know if THIS file was moved or not.
    // However, if the file is in 'admin/' or 'ajax/' directly, we update its paths!
    $dir_name = basename(dirname($file));
    if ($dir_name === 'admin' || $dir_name === 'ajax') {
        // Files now directly in admin/ or ajax/ should use ../ instead of ../../
        $content = preg_replace('/(\'|")\.\.\/\.\.\/(config|includes|ajax|admin)\//', '$1../$2/', $content);
    }

    // Replace ajax links
    foreach ([
        'ajax/academic-years/create.php' => 'ajax/create_academic_year.php',
        'ajax/academic-years/set_status.php' => 'ajax/set_academic_year_status.php',
        'ajax/academic-years/update.php' => 'ajax/update_academic_year.php',
        'ajax/announcements/create.php' => 'ajax/create_announcement.php',
        'ajax/announcements/delete.php' => 'ajax/delete_announcement.php',
        'ajax/announcements/update.php' => 'ajax/update_announcement.php',
        'ajax/bookmarks/toggle.php' => 'ajax/toggle_bookmark.php',
        'ajax/categories/create.php' => 'ajax/create_category.php',
        'ajax/categories/delete.php' => 'ajax/delete_category.php',
        'ajax/categories/update.php' => 'ajax/update_category.php',
        'ajax/comments/delete.php' => 'ajax/delete_comment.php',
        'ajax/comments/fetch.php' => 'ajax/fetch_comments.php',
        'ajax/comments/post.php' => 'ajax/post_comment.php',
        'ajax/notifications/mark_read.php' => 'ajax/mark_notification_read.php',
        'ajax/search/results.php' => 'ajax/search_results.php',
        'ajax/settings/update.php' => 'ajax/update_settings.php',
        'ajax/settings/update_notifications.php' => 'ajax/update_notification_settings.php',
        'ajax/timetables/upload_handler.php' => 'ajax/upload_timetable.php',
    ] as $oldAjax => $newAjax) {
        $content = str_replace("../../$oldAjax", "../$newAjax", $content);
        $content = str_replace("../$oldAjax", "../$newAjax", $content); // In case it was already 1 level up
        $content = str_replace("/$oldAjax", "/$newAjax", $content); // Absolute paths
    }

    // Now context-aware link replacements for internal links
    // e.g. if we are in admin/announcements.php, we need to replace "create.php" with "create_announcement.php"
    $filename = basename($file);
    if (strpos(str_replace('\\', '/', $file), '/admin/') !== false) {
        if ($filename === 'announcements.php' || $filename === 'create_announcement.php' || $filename === 'edit_announcement.php' || $filename === 'view_announcement.php' || $filename === '_announcement_form.php') {
            $content = str_replace('"create.php"', '"create_announcement.php"', $content);
            $content = str_replace("'create.php'", "'create_announcement.php'", $content);
            $content = str_replace('"edit.php?', '"edit_announcement.php?', $content);
            $content = str_replace("'edit.php?", "'edit_announcement.php?", $content);
            $content = str_replace('"view.php?', '"view_announcement.php?', $content);
            $content = str_replace("'view.php?", "'view_announcement.php?", $content);
            $content = str_replace('action="edit.php?id=', 'action="edit_announcement.php?id=', $content);
            $content = str_replace('action="create.php"', 'action="create_announcement.php"', $content);
            $content = str_replace('include \'_form.php\'', 'include \'_announcement_form.php\'', $content);
            $content = str_replace('include "_form.php"', 'include "_announcement_form.php"', $content);
            $content = str_replace('"index.php"', '"announcements.php"', $content);
            $content = str_replace("'index.php'", "'announcements.php'", $content);
        }
        
        if ($filename === 'categories.php' || $filename === 'create_category.php' || $filename === 'edit_category.php' || $filename === '_category_form.php') {
            $content = str_replace('"create.php"', '"create_category.php"', $content);
            $content = str_replace("'create.php'", "'create_category.php'", $content);
            $content = str_replace('"edit.php?', '"edit_category.php?', $content);
            $content = str_replace("'edit.php?", "'edit_category.php?", $content);
            $content = str_replace('action="edit.php?id=', 'action="edit_category.php?id=', $content);
            $content = str_replace('action="create.php"', 'action="create_category.php"', $content);
            $content = str_replace('include \'_form.php\'', 'include \'_category_form.php\'', $content);
            $content = str_replace('include "_form.php"', 'include "_category_form.php"', $content);
            $content = str_replace('"index.php"', '"categories.php"', $content);
            $content = str_replace("'index.php'", "'categories.php'", $content);
        }

        if ($filename === 'academic_years.php' || $filename === 'create_academic_year.php' || $filename === 'edit_academic_year.php' || $filename === '_academic_year_form.php') {
            $content = str_replace('"create.php"', '"create_academic_year.php"', $content);
            $content = str_replace("'create.php'", "'create_academic_year.php'", $content);
            $content = str_replace('"edit.php?', '"edit_academic_year.php?', $content);
            $content = str_replace("'edit.php?", "'edit_academic_year.php?", $content);
            $content = str_replace('action="edit.php?id=', 'action="edit_academic_year.php?id=', $content);
            $content = str_replace('action="create.php"', 'action="create_academic_year.php"', $content);
            $content = str_replace('include \'_form.php\'', 'include \'_academic_year_form.php\'', $content);
            $content = str_replace('include "_form.php"', 'include "_academic_year_form.php"', $content);
            $content = str_replace('"index.php"', '"academic_years.php"', $content);
            $content = str_replace("'index.php'", "'academic_years.php'", $content);
        }
        
        if ($filename === 'timetables.php' || $filename === 'upload_timetable.php') {
            $content = str_replace('"upload.php"', '"upload_timetable.php"', $content);
            $content = str_replace("'upload.php'", "'upload_timetable.php'", $content);
            $content = str_replace('action="upload.php"', 'action="upload_timetable.php"', $content);
            $content = str_replace('"index.php"', '"timetables.php"', $content);
            $content = str_replace("'index.php'", "'timetables.php'", $content);
        }
        
        if ($filename === 'comments.php') {
            $content = str_replace('"index.php"', '"comments.php"', $content);
            $content = str_replace("'index.php'", "'comments.php'", $content);
        }
    }

    // Fix sidebar links
    if ($filename === 'sidebar.php') {
        $content = str_replace('/admin/announcements/index.php', '/admin/announcements.php', $content);
        $content = str_replace('/admin/categories/index.php', '/admin/categories.php', $content);
        $content = str_replace('/admin/students/index.php', '/admin/students.php', $content);
        $content = str_replace('/admin/timetables/index.php', '/admin/timetables.php', $content);
        $content = str_replace('/admin/academic-years/index.php', '/admin/academic_years.php', $content);
        $content = str_replace('/admin/settings/index.php', '/admin/settings.php', $content);
        
        $content = preg_replace("/\\\$current_dir == 'announcements'/", "\$current_page == 'announcements.php'", $content);
        $content = preg_replace("/\\\$current_dir == 'categories'/", "\$current_page == 'categories.php'", $content);
        $content = preg_replace("/\\\$current_dir == 'students'/", "\$current_page == 'students.php'", $content);
        $content = preg_replace("/\\\$current_dir == 'timetables'/", "\$current_page == 'timetables.php'", $content);
        $content = preg_replace("/\\\$current_dir == 'academic-years'/", "\$current_page == 'academic_years.php'", $content);
        $content = preg_replace("/\\\$current_dir == 'settings'/", "\$current_page == 'settings.php'", $content);
    }

    if ($content !== $original) {
        file_put_contents($file, $content);
        echo "Updated references in $file\n";
    }
}

// Process all files in admin, ajax, includes, student
$iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base_dir));
foreach ($iter as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $path = $file->getPathname();
        // Don't process vendor or node_modules or migrate.php itself
        if (strpos($path, '/vendor/') !== false || strpos($path, '/node_modules/') !== false || strpos($path, 'migrate.php') !== false) {
            continue;
        }
        update_file_content($path, $moves);
    }
}

// Function to delete directory recursively if empty
function rrmdir($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object))
                    rrmdir($dir. DIRECTORY_SEPARATOR .$object);
                else
                    return; // directory not empty
            }
        }
        rmdir($dir);
        echo "Removed directory $dir\n";
    }
}

// Directories to attempt to remove
$dirs_to_remove = [
    'admin/announcements', 'admin/categories', 'admin/timetables', 'admin/academic-years', 'admin/comments',
    'admin/students', 'admin/classrooms', 'admin/majors', 'admin/bookmarks', 'admin/notifications', 'admin/activity-logs', 'admin/settings',
    'ajax/academic-years', 'ajax/announcements', 'ajax/bookmarks', 'ajax/categories', 'ajax/comments', 'ajax/notifications', 'ajax/search', 'ajax/settings', 'ajax/timetables'
];
foreach ($dirs_to_remove as $d) {
    rrmdir("$base_dir/$d");
}

echo "Migration complete.\n";
