<?php
require 'config/db.php';
// Let's manually insert a second timetable
$pdo->exec("INSERT INTO timetables (classroom_id, academic_year_id, academic_year_level_id, semester, title, file_path, uploaded_by) VALUES (3, 1, 1, 'second', 'Test Title', 'test/path.pdf', 1)");
$stmt = $pdo->query("SELECT * FROM timetables");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
