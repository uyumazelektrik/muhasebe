<?php
// src/controllers/api/delete_entity_transaction.php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/../../../src/helpers.php';

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? 0;

if (!$id) {
    echo json_encode(['status' => 'error', 'message' => 'ID gerekli']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. İşlemi bul
    $stmt = $pdo->prepare("SELECT entity_id, amount, type FROM inv_entity_transactions WHERE id = ?");
    $stmt->execute([$id]);
    $trans = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$trans) {
        throw new Exception("İşlem bulunamadı");
    }

    // 2. Bakiyeyi geri al (Tersi işlem yapılır)
    // Eğer işlem +100 ise (Cariye borçlanmışız demektir), silinince bakiye 100 azalmalı.
    // Eğer işlem -100 ise (Cariye ödeme yapmışız/borç düşmüş), silinince bakiye -(-100) = +100 artmalı.
    // Yani her durumda: balance = balance - amount mantığı doğrudur.
    
    $updateStmt = $pdo->prepare("UPDATE inv_entities SET balance = balance - ? WHERE id = ?");
    $updateStmt->execute([$trans['amount'], $trans['entity_id']]);

    // 3. İşlemi sil
    $delStmt = $pdo->prepare("DELETE FROM inv_entity_transactions WHERE id = ?");
    $delStmt->execute([$id]);

    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'İşlem silindi ve bakiye güncellendi']);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
