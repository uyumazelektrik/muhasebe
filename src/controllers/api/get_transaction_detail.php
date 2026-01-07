<?php
// src/controllers/api/get_transaction_detail.php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/../../../src/helpers.php';

$id = $_GET['id'] ?? 0;

if (!$id) {
    echo json_encode(['status' => 'error', 'message' => 'ID gerekli']);
    exit;
}

try {
    // 1. İşlem detayını çek
    $stmt = $pdo->prepare("
        SELECT t.*, e.name as entity_name 
        FROM inv_entity_transactions t
        LEFT JOIN inv_entities e ON t.entity_id = e.id
        WHERE t.id = ?
    ");
    $stmt->execute([$id]);
    $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$transaction) {
        echo json_encode(['status' => 'error', 'message' => 'İşlem bulunamadı']);
        exit;
    }

    $response = [
        'status' => 'success',
        'transaction' => $transaction,
        'items' => []
    ];

    // 2. Eğer fatura ise kalemleri çek (inv_movements tablosundan)
    // Fatura numarası document_no alanında tutulur
    if ($transaction['type'] === 'fatura' && !empty($transaction['document_no'])) {
        /*
         * NOT: inv_movements tablosunda 'document_no' faturanın numarasıdır.
         * Fatura kalemleri bu numara ve entity_id ile eşleşir.
         * Ayrıca stock girişleri olduğu için type genellikle 'stock_in' veya 'purchase' olabilir.
         * Ama en garantisi document_no eşleşmesidir.
         */
        $stmtItems = $pdo->prepare("
            SELECT m.*, p.name as product_name, p.unit 
            FROM inv_movements m
            LEFT JOIN inv_products p ON m.product_id = p.id
            WHERE m.document_no = ? 
            ORDER BY m.id ASC
        ");
        $stmtItems->execute([$transaction['document_no']]);
        $response['items'] = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
    }

    echo json_encode($response);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
