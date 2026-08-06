<?php
require_once 'config/db.php';

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    // Clear existing to avoid duplicates if re-running
    $pdo->exec("TRUNCATE TABLE classrooms");
    $pdo->exec("TRUNCATE TABLE majors");
    
    // We need an active academic year
    $stmt = $pdo->query("SELECT id FROM academic_years WHERE status = 'active' LIMIT 1");
    $ay_id = $stmt->fetchColumn();
    if (!$ay_id) $ay_id = 1;

    // We need a dummy course id for the majors table (due to the original schema constraint)
    $stmt = $pdo->query("SELECT id FROM courses LIMIT 1");
    $course_id = $stmt->fetchColumn() ?: 1;

    // Insert Majors
    $stmt = $pdo->prepare("INSERT INTO majors (course_id, major_name, description) VALUES (?, ?, ?)");
    $stmt->execute([$course_id, 'General', 'First Year Foundation']);
    $major_general_id = $pdo->lastInsertId();

    $stmt->execute([$course_id, 'Computer Science', 'CS Major']);
    $major_cs_id = $pdo->lastInsertId();

    $stmt->execute([$course_id, 'Computer Technology', 'CT Major']);
    $major_ct_id = $pdo->lastInsertId();

    // Map year level strings to academic_year_levels IDs
    $levels = [
        'First Year' => 1,
        'Second Year' => 2,
        'Third Year' => 3,
        'Fourth Year' => 4,
        'Fifth Year' => 5
    ];

    $classrooms = [
        ['First Year (A)', 'First Year', 'A', $major_general_id],
        ['First Year (B)', 'First Year', 'B', $major_general_id],
        ['First Year (C)', 'First Year', 'C', $major_general_id],
        ['Second Year CS (A)', 'Second Year', 'CS (A)', $major_cs_id],
        ['Second Year CS (B)', 'Second Year', 'CS (B)', $major_cs_id],
        ['Second Year CT', 'Second Year', 'CT', $major_ct_id],
        ['Third Year CS (A)', 'Third Year', 'CS (A)', $major_cs_id],
        ['Third Year CS (B)', 'Third Year', 'CS (B)', $major_cs_id],
        ['Third Year CT', 'Third Year', 'CT', $major_ct_id],
        ['Fourth Year CS (A)', 'Fourth Year', 'CS (A)', $major_cs_id],
        ['Fourth Year CS (B)', 'Fourth Year', 'CS (B)', $major_cs_id],
        ['Fourth Year CT', 'Fourth Year', 'CT', $major_ct_id],
        ['Fifth Year CS (A)', 'Fifth Year', 'CS (A)', $major_cs_id],
        ['Fifth Year CS (B)', 'Fifth Year', 'CS (B)', $major_cs_id],
        ['Fifth Year CT', 'Fifth Year', 'CT', $major_ct_id],
    ];

    $stmt = $pdo->prepare("INSERT INTO classrooms (academic_year_id, academic_year_level_id, major_id, section, classroom_name, status) VALUES (?, ?, ?, ?, ?, 'Active')");

    foreach ($classrooms as $c) {
        $name = $c[0];
        $year_level_name = $c[1];
        $section = $c[2];
        $major_id = $c[3];
        $ay_level_id = $levels[$year_level_name];

        $stmt->execute([
            $ay_id,
            $ay_level_id,
            $major_id,
            $section,
            $name
        ]);
    }

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    echo "Successfully inserted classroom data.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
