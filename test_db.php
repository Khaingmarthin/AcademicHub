<?php
require 'c:/wamp64/www/academichub/config/db.php';
try {
    $stmt = $pdo->prepare("INSERT INTO users (student_id, roll_number, classroom_id, username, email, password, role, status) VALUES ('TEST', 'ROLL', 1, 'Test', 'test@test.com', 'pass', 'student', 'Active')");
    $stmt->execute();
    echo 'SUCCESS';
} catch(PDOException $e) {
    echo $e->getMessage();
}
?>
