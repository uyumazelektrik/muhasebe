<?php
require_once __DIR__ . '/config/db.php';
$tables = ['inv_movements', 'inv_expense_categories', 'inv_invoice_items'];
foreach ($tables as $table) {
    echo "--- $table ---\n";
    try {
        $stmt = $pdo->query("DESCRIBE $table");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "{$row['Field']} - {$row['Type']}\n";
        }
    } catch (Exception $e) {
        echo "Error describing $table: " . $e->getMessage() . "\n";
    }
}
