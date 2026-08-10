<?php
require 'c:/wamp64/www/academichub/config/db.php';
$stmt = $pdo->query('DESCRIBE academic_years');
foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
    echo $r['Field'] . " | " . $r['Type'] . "\n";
}
?>
