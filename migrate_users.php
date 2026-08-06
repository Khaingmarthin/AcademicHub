<?php
require_once 'config/db.php';

try {
    echo "Starting users migration...\n";

    // 1. Add student_id
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'student_id'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE users ADD COLUMN student_id VARCHAR(50) DEFAULT NULL AFTER id");
        $pdo->exec("ALTER TABLE users ADD UNIQUE KEY (student_id)");
        echo "Added student_id column.\n";
    }

    // 2. Add phone
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'phone'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE users ADD COLUMN phone VARCHAR(20) DEFAULT NULL AFTER email");
        echo "Added phone column.\n";
    }

    // 3. Add gender
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'gender'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE users ADD COLUMN gender VARCHAR(10) DEFAULT NULL AFTER phone");
        echo "Added gender column.\n";
    }

    // 4. Add date_of_birth
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'date_of_birth'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE users ADD COLUMN date_of_birth DATE DEFAULT NULL AFTER gender");
        echo "Added date_of_birth column.\n";
    }

    // 5. Add last_login
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'last_login'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE users ADD COLUMN last_login DATETIME DEFAULT NULL AFTER updated_at");
        echo "Added last_login column.\n";
    }
    
    // Also add avatar if it doesn't exist to store profile images if needed later
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'avatar'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE users ADD COLUMN avatar VARCHAR(255) DEFAULT NULL AFTER date_of_birth");
        echo "Added avatar column.\n";
    }

    echo "Migration completed successfully!\n";

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
