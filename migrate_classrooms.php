<?php
require_once 'config/db.php';

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 1. Drop existing unique index on classroom_name
    try {
        $pdo->exec("ALTER TABLE classrooms DROP INDEX classroom_name");
        echo "Dropped existing unique index on classroom_name.\n";
    } catch (PDOException $e) {
        echo "Index classroom_name might not exist or already dropped: " . $e->getMessage() . "\n";
    }

    // 2. Add new unique index spanning classroom_name and academic_year_id
    try {
        $pdo->exec("ALTER TABLE classrooms ADD UNIQUE KEY `classroom_name_ay_unique` (`classroom_name`, `academic_year_id`)");
        echo "Added unique index `classroom_name_ay_unique`.\n";
    } catch (PDOException $e) {
        echo "Failed to add new unique index (might already exist): " . $e->getMessage() . "\n";
    }

    // 3. Update 'General' to 'Common' in majors table
    $stmt = $pdo->prepare("UPDATE majors SET major_name = 'Common' WHERE major_name = 'General'");
    $stmt->execute();
    echo "Updated 'General' to 'Common' in majors.\n";
    
    echo "Migration completed successfully.\n";

} catch (PDOException $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
}
