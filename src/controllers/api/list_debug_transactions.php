<?php
// src/controllers/api/list_debug_transactions.php
header('Content-Type: text/plain');
require_once __DIR__ . '/../../../config/db.php';

$stmt = $pdo->query("SELECT id, type, amount, description, document_no, created_at FROM inv_entity_transactions ORDER BY id DESC LIMIT 5");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($rows as $r) {
    print_r($r);
    echo "Movements for {$r['document_no']}:\n";
    if ($r['document_no']) {
        $stmt2 = $pdo->prepare("SELECT id, type, quantity, product_id, prev_stock, new_stock FROM inv_movements WHERE document_no = ?");
        $stmt2->execute([$r['document_no']]);
        $moves = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        print_r($moves);
    }
    echo "--------------------------\n";
}
