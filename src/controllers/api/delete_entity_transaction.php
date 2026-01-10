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
    $stmt = $pdo->prepare("SELECT * FROM inv_entity_transactions WHERE id = ?");
    $stmt->execute([$id]);
    $trans = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$trans) {
        throw new Exception("İşlem bulunamadı");
    }

    $parentTransactionId = $trans['parent_transaction_id'] ?? $trans['id'];

    // 2. Bu gruba ait tüm işlemleri bul (Virman, Taksit vb.)
    $stmt = $pdo->prepare("SELECT * FROM inv_entity_transactions WHERE parent_transaction_id = ? OR id = ?");
    $stmt->execute([$parentTransactionId, $parentTransactionId]);
    $linkedTransactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    require_once __DIR__ . '/../../Models/WalletModel.php';
    $walletModel = new WalletModel($pdo);

    foreach ($linkedTransactions as $lt) {
        // A. Ana bakiyeyi geri al
        $updateStmt = $pdo->prepare("UPDATE inv_entities SET balance = balance - ? WHERE id = ?");
        $updateStmt->execute([$lt['amount'], $lt['entity_id']]);

        // B. Varlık (Asset) bakiyesini geri al
        $assetAmount = $lt['asset_amount'] ?: $lt['amount'];
        $assetType = $lt['asset_type'] ?: 'TL';
        
        $assetUpdateStmt = $pdo->prepare("UPDATE inv_entity_balances SET amount = amount - ? WHERE entity_id = ? AND asset_type = ?");
        $assetUpdateStmt->execute([$assetAmount, $lt['entity_id'], $assetType]);

        // C. Cüzdan Bakiyesini Geri Al (Eğer varsa)
        if (!empty($lt['wallet_id'])) {
            // İşlem eklenirken +Amount eklenmişti, silerken -Amount yapıyoruz
            $walletModel->updateBalance($lt['wallet_id'], -$lt['amount']);
        }

        // D. Varsa Fatura Kalemlerini (inv_movements) Bul ve Stokları Geri Al (Revert)
        if (!empty($lt['document_no'])) {
            // Önce hareketleri çek
            $stmtMoves = $pdo->prepare("SELECT * FROM inv_movements WHERE document_no = ?");
            $stmtMoves->execute([$lt['document_no']]);
            $moves = $stmtMoves->fetchAll(PDO::FETCH_ASSOC);

            // DEBUG LOG
            file_put_contents(__DIR__ . '/../../../delete_debug.log', print_r($moves, true), FILE_APPEND);

            foreach ($moves as $move) {
                if (!$move['product_id']) continue;

                // Hareketi tersine çevir
                // Eğer satış (sale/out_invoice) ise, silindiğinde stok artmalı (+).
                // Eğer alış (purchase/in_invoice) ise, silindiğinde stok azalmalı (-).
                
                $qty = floatval($move['quantity']);
                
                if (in_array($move['type'], ['sale', 'out_invoice', 'production_out'])) {
                    $pdo->prepare("UPDATE inv_products SET stock_quantity = stock_quantity + ? WHERE id = ?")
                        ->execute([$qty, $move['product_id']]);
                } elseif (in_array($move['type'], ['purchase', 'in_invoice', 'stock_in'])) {
                    $pdo->prepare("UPDATE inv_products SET stock_quantity = stock_quantity - ? WHERE id = ?")
                        ->execute([$qty, $move['product_id']]);
                }
            }

            // Sonra hareketleri sil
            $delMovStmt = $pdo->prepare("DELETE FROM inv_movements WHERE document_no = ?");
            $delMovStmt->execute([$lt['document_no']]);
        }

        // E. İşlemi sil
        $delStmt = $pdo->prepare("DELETE FROM inv_entity_transactions WHERE id = ?");
        $delStmt->execute([$lt['id']]);
    }
    
    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'İşlem silindi ve stoklar geri alındı.']);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
