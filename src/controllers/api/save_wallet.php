<?php
// src/controllers/api/save_wallet.php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['name']) || empty($data['owner_name'])) {
    echo json_encode(['status' => 'error', 'message' => 'Eksik veri gönderildi.']);
    exit;
}

try {
    require_once __DIR__ . '/../../Models/WalletModel.php';
    require_once __DIR__ . '/../../Models/EntityModel.php';
    $walletModel = new WalletModel($pdo);
    $entityModel = new EntityModel($pdo);

    // Sahibi ismiyle bul veya oluştur (manuel giriş desteği)
    $ownerEntity = $entityModel->findOrCreate($data['owner_name'], null, 'company');

    $walletId = intval($data['id'] ?? 0);
    
    if ($walletId > 0) {
        $success = $walletModel->update($walletId, [
            'owner_entity_id' => intval($ownerEntity['id']),
            'name' => $data['name'],
            'wallet_type' => $data['wallet_type'] ?? 'CASH',
            'asset_type' => $data['asset_type'] ?? 'TL',
            'limit_amount' => floatval($data['limit_amount'] ?? 0),
            'statement_day' => intval($data['statement_day'] ?? 1)
        ]);
    } else {
        $success = $walletModel->create([
            'owner_entity_id' => intval($ownerEntity['id']),
            'name' => $data['name'],
            'wallet_type' => $data['wallet_type'] ?? 'CASH',
            'asset_type' => $data['asset_type'] ?? 'TL',
            'limit_amount' => floatval($data['limit_amount'] ?? 0),
            'balance' => 0,
            'statement_day' => intval($data['statement_day'] ?? 1)
        ]);
    }

    if ($success) {
        echo json_encode(['status' => 'success', 'message' => 'Cüzdan başarıyla ' . ($walletId > 0 ? 'güncellendi' : 'oluşturuldu') . '.']);
    } else {
        throw new Exception('Cüzdan kaydedilemedi.');
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
