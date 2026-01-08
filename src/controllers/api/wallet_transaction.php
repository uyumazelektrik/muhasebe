<?php
// src/controllers/api/wallet_transaction.php
header('Content-Type: application/json');

require_once __DIR__ . '/../../Models/WalletModel.php';
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
    $walletId = intval($data['wallet_id'] ?? 0);
    $amount = floatval($data['amount'] ?? 0);
    $type = $data['type'] ?? 'tahsilat'; // tahsilat (giriş) veya odeme (çıkış)
    $date = $data['transaction_date'] ?? date('Y-m-d');
    $description = $data['description'] ?? 'Kasa İşlemi';

    if ($walletId <= 0 || $amount <= 0) {
        throw new Exception('Lütfen geçerli bir hesap ve tutar giriniz.');
    }

    $pdo->beginTransaction();

    $walletModel = new WalletModel($pdo);
    $entityModel = new EntityModel($pdo);

    $wallet = $walletModel->find($walletId);
    if (!$wallet) {
        throw new Exception('Hesap bulunamadı.');
    }

    // İşlem tipine göre tutarı ayarla
    // Giriş (tahsilat) -> +tutar
    // Çıkış (odeme) -> -tutar
    $finalAmount = ($type === 'tahsilat' || $type === 'in') ? $amount : -$amount;

    // Kasa sahibi (owner_entity_id) için işlem kaydı atıyoruz.
    // Bu sayede hem cüzdan bakiyesi güncellenir hem de sahibin ekstresinde görünür.
    $entityModel->updateAssetBalance(
        $wallet['owner_entity_id'],
        $finalAmount,
        $wallet['asset_type'],
        $type,
        $description,
        $date,
        'K-' . time(),
        1.0,
        $walletId,
        true // Doğrudan cüzdan hareketi olarak işle
    );

    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'Kasa işlemi başarıyla kaydedildi.']);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
