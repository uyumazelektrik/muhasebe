<?php
require __DIR__ . '/config/db.php';
try {
    $pdo->exec("ALTER TABLE attendance ADD COLUMN note TEXT NULL AFTER overtime_hours");
    echo "Column 'note' added successfully.";
} catch (PDOException $e) {
    echo "Error adding column: " . $e->getMessage();
}
