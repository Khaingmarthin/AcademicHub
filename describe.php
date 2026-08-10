<?php require_once "config/db.php"; $stmt = $pdo->query("DESCRIBE courses"); foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) echo $col["Field"] . " - " . $col["Type"] . "\n";
