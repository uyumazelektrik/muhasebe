<?php
require __DIR__ . '/config/db.php';

try {
    // Add affects_balance column
    $pdo->exec("ALTER TABLE transactions ADD COLUMN affects_balance TINYINT(1) DEFAULT 1 AFTER type");
    echo "Column 'affects_balance' added successfully.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "Column 'affects_balance' already exists.\n";
    } else {
        die("Error: " . $e->getMessage());
    }
}
?>
