<?php

function file_replace($path, $search, $replace) {
    if (!file_exists($path)) return;
    $content = file_get_contents($path);
    $new_content = str_replace($search, $replace, $content);
    if ($content !== $new_content) {
        file_put_contents($path, $new_content);
        echo "Updated $path\n";
    }
}

function preg_replace_file($path, $pattern, $replace) {
    if (!file_exists($path)) return;
    $content = file_get_contents($path);
    $new_content = preg_replace($pattern, $replace, $content);
    if ($content !== $new_content) {
        file_put_contents($path, $new_content);
        echo "Updated $path\n";
    }
}

// admin/_announcement_form.php
$form = 'admin/_announcement_form.php';
file_replace($form, '$is_pinned = $announcement[\'is_pinned\'] ?? 0;', '');
preg_replace_file($form, '/<div class="flex items-center">\s*<input id="is_pinned" name="is_pinned" type="checkbox"[^>]+>\s*<label for="is_pinned" class="ml-2 block text-sm text-gray-900">Pin to top<\/label>\s*<\/div>/s', '');

// admin/view_announcement.php
$view = 'admin/view_announcement.php';
preg_replace_file($view, '/<\?php if \(\$announcement\[\'is_pinned\'\]\): \?>\s*<span class="px-2 py-1 text-xs font-bold bg-blue-100 text-blue-800 rounded-full">Pinned<\/span>\s*<\?php endif; \?>/s', '');
file_replace($view, '<?php if (!$announcement[\'is_pinned\'] && !$announcement[\'is_urgent\'] && !$announcement[\'is_featured\']): ?>', '<?php if (!$announcement[\'is_urgent\'] && !$announcement[\'is_featured\']): ?>');

// ajax/create_announcement.php
$create = 'ajax/create_announcement.php';
file_replace($create, '$is_pinned = isset($_POST[\'is_pinned\']) ? 1 : 0;', '');
file_replace($create, 
    'INSERT INTO announcements (title, content, author_id, category_id, academic_year_id, status, publish_date, expire_date, is_urgent, is_featured, is_pinned, attachment_path)',
    'INSERT INTO announcements (title, content, user_id, category_id, academic_year_id, status, publish_date, expire_date, is_urgent, is_featured)'
);
file_replace($create, 
    'VALUES (:title, :content, :author_id, :category_id, :academic_year_id, :status, :publish_date, :expire_date, :is_urgent, :is_featured, :is_pinned, :attachment_path)',
    'VALUES (:title, :content, :user_id, :category_id, :academic_year_id, :status, :publish_date, :expire_date, :is_urgent, :is_featured)'
);
file_replace($create, '\'author_id\' => $author_id,', '\'user_id\' => $author_id,');
file_replace($create, '\'is_pinned\' => $is_pinned,', '');
file_replace($create, '\'attachment_path\' => $attachment_path', '');

// Add attachment logic after insert
$create_content = file_get_contents($create);
if (strpos($create_content, 'INSERT INTO attachments') === false) {
    $attachment_insert = '
    if ($attachment_path) {
        $att_stmt = $pdo->prepare("INSERT INTO attachments (announcement_id, file_name, file_type, file_size) VALUES (?, ?, ?, ?)");
        $att_stmt->execute([$announcement_id, basename($attachment_path), mime_content_type("../../" . $attachment_path) ?: "application/octet-stream", filesize("../../" . $attachment_path) ?: 0]);
    }
    ';
    $create_content = str_replace('$announcement_id = $pdo->lastInsertId();', '$announcement_id = $pdo->lastInsertId();' . $attachment_insert, $create_content);
    file_put_contents($create, $create_content);
}

// ajax/update_announcement.php
$update = 'ajax/update_announcement.php';
file_replace($update, '$is_pinned = isset($_POST[\'is_pinned\']) ? 1 : 0;', '');
file_replace($update, ', is_pinned = :is_pinned', '');
file_replace($update, ', attachment_path = :attachment_path', '');
file_replace($update, '\'is_pinned\' => $is_pinned,', '');
file_replace($update, '\'attachment_path\' => $attachment_path,', '');

// Add attachment logic after update
$update_content = file_get_contents($update);
if (strpos($update_content, 'INSERT INTO attachments') === false) {
    $attachment_update = '
    if (isset($_FILES[\'attachment\']) && $_FILES[\'attachment\'][\'error\'] === UPLOAD_ERR_OK && $attachment_path) {
        $pdo->prepare("DELETE FROM attachments WHERE announcement_id = ?")->execute([$id]);
        $att_stmt = $pdo->prepare("INSERT INTO attachments (announcement_id, file_name, file_type, file_size) VALUES (?, ?, ?, ?)");
        $att_stmt->execute([$id, basename($attachment_path), mime_content_type("../../" . $attachment_path) ?: "application/octet-stream", filesize("../../" . $attachment_path) ?: 0]);
    }
    ';
    $update_content = preg_replace('/\$stmt->execute\(\[.*?\]\);/s', '$0' . "\n" . $attachment_update, $update_content);
    file_put_contents($update, $update_content);
}

// Fix the SELECT attachment_path in update_announcement.php
file_replace($update, 
    'SELECT attachment_path FROM announcements WHERE id = :id', 
    'SELECT file_name as attachment_path FROM attachments WHERE announcement_id = :id LIMIT 1'
);

echo "Done fixing is_pinned and announcements schema mismatch!\n";
