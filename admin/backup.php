<?php
require_once '../config/session.php';
require_admin();
require_once '../config/db.php';

// Turn off output buffering to prevent memory exhaustion
while (ob_get_level()) {
    ob_end_clean();
}

$db_name = "academichub"; // Ideally fetch from config, but this is known
$filename = "backup_" . $db_name . "_" . date("Y-m-d_H-i-s") . ".sql";

header('Content-Type: application/sql');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

echo "-- Database Backup for $db_name\n";
echo "-- Generated: " . date("Y-m-d H:i:s") . "\n\n";
echo "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
echo "START TRANSACTION;\n";
echo "SET time_zone = \"+06:30\";\n\n";

try {
    // Get all tables
    $tables = [];
    $stmt = $pdo->query("SHOW TABLES");
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }

    foreach ($tables as $table) {
        // Table structure
        echo "-- Table structure for table `$table`\n";
        echo "DROP TABLE IF EXISTS `$table`;\n";
        
        $create_stmt = $pdo->query("SHOW CREATE TABLE `$table`");
        $create_row = $create_stmt->fetch(PDO::FETCH_NUM);
        echo $create_row[1] . ";\n\n";

        // Table data
        $data_stmt = $pdo->query("SELECT * FROM `$table`");
        $rowCount = $data_stmt->rowCount();
        
        if ($rowCount > 0) {
            echo "-- Dumping data for table `$table`\n";
            echo "INSERT INTO `$table` VALUES \n";
            
            $counter = 0;
            while ($row = $data_stmt->fetch(PDO::FETCH_ASSOC)) {
                $counter++;
                $values = [];
                foreach ($row as $val) {
                    if ($val === null) {
                        $values[] = "NULL";
                    } else {
                        $values[] = $pdo->quote($val);
                    }
                }
                echo "(" . implode(", ", $values) . ")";
                if ($counter < $rowCount) {
                    echo ",\n";
                } else {
                    echo ";\n";
                }
            }
            echo "\n";
        }
    }
    
    echo "COMMIT;\n";
} catch (Exception $e) {
    echo "\n-- Error during backup: " . $e->getMessage() . "\n";
}
exit;
?>
