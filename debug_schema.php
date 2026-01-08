<?php
require_once __DIR__ . '/config/db.php';
$table = 'inv_movements';
$stmt = $pdo->query("DESCRIBE $table");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($columns as $col) {
    echo $col['Field'] . " (" . $col['Type'] . ")\n";
}
