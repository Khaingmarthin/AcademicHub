<?php
require_once 'config/db.php';

try {
    $stmt = $pdo->query("SELECT * FROM majors");
    echo "MAJORS:\n";
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

    $stmt = $pdo->query("SELECT * FROM academic_year_levels");
    echo "ACADEMIC YEAR LEVELS:\n";
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
