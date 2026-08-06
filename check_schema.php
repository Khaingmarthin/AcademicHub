<?php
require_once 'config/db.php';

try {
    $stmt = $pdo->query("SHOW CREATE TABLE users");
    echo "USERS SCHEMA:\n" . $stmt->fetch(PDO::FETCH_ASSOC)['Create Table'] . "\n\n";

    $stmt = $pdo->query("SHOW CREATE TABLE timetables");
    echo "TIMETABLES SCHEMA:\n" . $stmt->fetch(PDO::FETCH_ASSOC)['Create Table'] . "\n\n";

    $stmt = $pdo->query("SHOW CREATE TABLE classrooms");
    echo "CLASSROOMS SCHEMA:\n" . $stmt->fetch(PDO::FETCH_ASSOC)['Create Table'] . "\n\n";
    
    $stmt = $pdo->query("SELECT * FROM majors");
    echo "MAJORS:\n";
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
