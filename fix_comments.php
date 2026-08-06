<?php
$base_dir = __DIR__;

$iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base_dir));
foreach ($iter as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $path = $file->getPathname();
        if (strpos($path, 'vendor') !== false || strpos($path, 'node_modules') !== false || strpos($path, 'fix_') !== false) {
            continue;
        }

        $content = file_get_contents($path);
        $original = $content;

        // Replace c.content with c.comment as content
        $content = str_replace("c.content,", "c.comment as content,", $content);
        $content = str_replace("c.content ", "c.comment as content ", $content);
        
        // Replace u.name as user_name with u.username as user_name
        $content = str_replace("u.name as user_name", "u.username as user_name", $content);
        
        // Replace u.full_name as author_name with u.username as author_name
        $content = str_replace("u.full_name as author_name", "u.username as author_name", $content);

        // Replace u.name as author_name with u.username as author_name
        $content = str_replace("u.name as author_name", "u.username as author_name", $content);

        if (strpos($path, 'post_comment.php') !== false) {
            // Remove parent_id validation
            $content = preg_replace('/\/\/ Validate parent exists if provided.*?\/\/ Insert comment/s', '// Insert comment', $content);
            // Replace INSERT query
            $content = str_replace(
                "INSERT INTO comments (announcement_id, user_id, parent_id, content, status) VALUES (:aid, :uid, :pid, :content, 'approved')",
                "INSERT INTO comments (announcement_id, user_id, comment, status) VALUES (:aid, :uid, :content, 'approved')",
                $content
            );
            $content = str_replace("'pid' => \$parent_id,", "", $content);
        }

        if (strpos($path, 'fetch_comments.php') !== false) {
            // Remove parent_id from SELECT
            $content = str_replace("c.parent_id,", "", $content);
        }

        if ($content !== $original) {
            file_put_contents($path, $content);
            echo "Updated $path\n";
        }
    }
}
echo "Done fixing comments and users mismatches!\n";
