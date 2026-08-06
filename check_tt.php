<?php
require 'config/db.php';
$stmt = $pdo->query("SELECT * FROM timetables");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
