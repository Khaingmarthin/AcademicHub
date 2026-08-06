<?php
$base_dir = __DIR__;

$iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base_dir));
foreach ($iter as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $path = $file->getPathname();
        if (strpos($path, 'vendor') !== false || strpos($path, 'node_modules') !== false || strpos($path, 'fix_ay.php') !== false) {
            continue;
        }

        $content = file_get_contents($path);
        $original = $content;

        // 1. SELECT id, name FROM academic_years
        $content = str_replace(
            "SELECT id, name FROM academic_years",
            "SELECT id, year_name as name FROM academic_years",
            $content
        );

        // 2. SELECT * FROM academic_years
        $content = str_replace(
            "SELECT * FROM academic_years",
            "SELECT id, year_name as name, status, start_date, end_date, created_at, updated_at FROM academic_years",
            $content
        );

        // 3. ORDER BY name
        $content = str_replace(
            "ORDER BY name DESC",
            "ORDER BY year_name DESC",
            $content
        );
        $content = str_replace(
            "ORDER BY name ASC",
            "ORDER BY year_name ASC",
            $content
        );

        // 4. INSERT INTO academic_years (name, status)
        $content = str_replace(
            "INSERT INTO academic_years (name, status)",
            "INSERT INTO academic_years (year_name, status)",
            $content
        );

        // 5. UPDATE academic_years SET name = :name
        $content = str_replace(
            "UPDATE academic_years SET name = :name",
            "UPDATE academic_years SET year_name = :name",
            $content
        );

        // 6. Search.php specifically has SELECT id, year_name FROM academic_years
        // Which might cause an issue if it assumes year_name but we don't alias it to name?
        // Let's check if there is SELECT id, year_name FROM academic_years
        $content = str_replace(
            "SELECT id, year_name FROM academic_years",
            "SELECT id, year_name as name FROM academic_years",
            $content
        );

        if ($content !== $original) {
            file_put_contents($path, $content);
            echo "Updated $path\n";
        }
    }
}
echo "Done fixing DB queries!\n";
