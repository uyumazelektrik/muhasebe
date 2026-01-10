<?php
// src/controllers/api/debug_transaction.php
header('Content-Type: text/plain');
require_once __DIR__ . '/../../../config/db.php';

$id = $_GET['id'] ?? 0;

if (!$id) die("ID missing");

echo "Transaction ID: $id\n";
$stmt = $pdo->prepare("SELECT * FROM inv_entity_transactions WHERE id = ?");
$stmt->execute([$id]);
$trans = $stmt->fetch(PDO::FETCH_ASSOC);
print_r($trans);

if ($trans) {
    echo "\n----------------\nLinked Movements (by document_no: '{$trans['document_no']}'):\n";
    if ($trans['document_no']) {
        $stmt2 = $pdo->prepare("SELECT * FROM inv_movements WHERE document_no = ?");
        $stmt2->execute([$trans['document_no']]);
        $moves = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        print_r($moves);
    } else {
        echo "No document_no set.\n";
    }
}
