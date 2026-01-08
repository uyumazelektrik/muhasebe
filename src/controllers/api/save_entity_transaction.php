<?php
// src/controllers/api/save_entity_transaction.php

require_once __DIR__ . '/../../Models/EntityModel.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['status' => 'error', 'message' => 'Geçersiz veri.']);
    exit;
}

try {
    $entityModel = new EntityModel($pdo);
    
    $entityId = intval($data['entity_id'] ?? 0);
    $walletIdRaw = $data['wallet_id'] ?? '';
    $assetType = $data['asset_type'] ?? 'TL';
    $walletId = null;

    require_once __DIR__ . '/../../Models/WalletModel.php';
    $walletModel = new WalletModel($pdo);

    $walletId = $walletIdRaw !== '' && $walletIdRaw !== 'transfer' ? intval($walletIdRaw) : null;
    
    // Eğer kredi kartı cüzdanı seçildiyse varlık tipini de kredi kartı yapalım.
    if ($walletId) {
        $wallet = $walletModel->find($walletId);
        if ($wallet && $wallet['wallet_type'] === 'CREDIT_CARD') {
            $assetType = 'CREDIT_CARD';
        }
    }

    $transferEntityId = isset($data['transfer_entity_id']) && $data['transfer_entity_id'] !== '' ? intval($data['transfer_entity_id']) : null;
    $amount = floatval($data['amount'] ?? 0);
    $rate = floatval($data['exchange_rate'] ?? 1.0);
    $type = $data['type'] ?? 'diger';
    $description = $data['description'] ?? '';
    $date = $data['transaction_date'] ?? date('Y-m-d');
    $dueDate = $data['due_date'] ?? $date;
    $installmentCount = intval($data['installment_count'] ?? 1);
    $commissionFee = floatval($data['commission_fee'] ?? 0);

    if ($entityId <= 0) {
        throw new Exception('Geçersiz cari ID.');
    }

    if ($amount == 0) {
        throw new Exception('Tutar sıfır olamaz.');
    }

    $pdo->beginTransaction();
    
    $res = $entityModel->updateAssetBalance(
        $entityId, 
        $amount, 
        $assetType, 
        $type, 
        $description, 
        $date, 
        null, // Document No (optional)
        $rate,
        $walletId,
        false, // isOffsetProcess
        $installmentCount,
        $commissionFee,
        $transferEntityId,
        $dueDate
    );

    if ($res) {
        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'İşlem başarıyla kaydedildi.']);
    } else {
        throw new Exception('İşlem kaydedilemedi.');
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
