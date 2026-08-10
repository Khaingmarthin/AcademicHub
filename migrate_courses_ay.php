<?php
require_once 'config/db.php';

try {
    $pdo->beginTransaction();

    // 1. Get the current active academic year
    $stmt = $pdo->query("SELECT id FROM academic_years WHERE status = 'Active' LIMIT 1");
    $active_ay = $stmt->fetch();
    $active_ay_id = $active_ay ? $active_ay['id'] : null;

    if (!$active_ay_id) {
        // Fallback if no active academic year
        $stmt = $pdo->query("SELECT id FROM academic_years ORDER BY id DESC LIMIT 1");
        $latest_ay = $stmt->fetch();
        $active_ay_id = $latest_ay ? $latest_ay['id'] : null;
    }

    if (!$active_ay_id) {
        die("No academic years found in the database. Cannot migrate.\n");
    }

    // 2. Add academic_year_id column to courses if it doesn't exist
    $stmt = $pdo->query("SHOW COLUMNS FROM courses LIKE 'academic_year_id'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE courses ADD COLUMN academic_year_id INT AFTER id");
        echo "Added academic_year_id column.\n";
        
        // Populate existing courses
        $pdo->prepare("UPDATE courses SET academic_year_id = ?")->execute([$active_ay_id]);
        echo "Populated existing courses with academic_year_id = $active_ay_id.\n";
        
        // Add foreign key constraint
        $pdo->exec("ALTER TABLE courses ADD CONSTRAINT courses_ay_fk FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE ON UPDATE CASCADE");
        echo "Added foreign key constraint.\n";
    } else {
        echo "Column academic_year_id already exists.\n";
    }

    $pdo->commit();
    echo "Migration completed successfully.\n";

} catch (PDOException $e) {
    $pdo->rollBack();
    die("Migration failed: " . $e->getMessage() . "\n");
}
