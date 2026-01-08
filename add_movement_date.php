<?php
require_once __DIR__ . '/config/db.php';
try {
    $pdo->exec("ALTER TABLE inv_movements ADD COLUMN movement_date DATE AFTER type");
    // Mevcut verileri created_at'ten güncelle
    $pdo->exec("UPDATE inv_movements SET movement_date = DATE(created_at) WHERE movement_date IS NULL");
    echo "Column movement_date added successfully.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
