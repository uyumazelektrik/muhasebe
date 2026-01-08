<?php
// src/controllers/api/wallet_transfer.php
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
    $fromWalletId = intval($data['from_wallet_id'] ?? 0);
    $toWalletId = intval($data['to_wallet_id'] ?? 0);
    $amount = floatval($data['amount'] ?? 0);
    $date = $data['transaction_date'] ?? date('Y-m-d');
    $description = $data['description'] ?? 'Hesaplar Arası Transfer (Virman)';

    if ($fromWalletId <= 0 || $toWalletId <= 0 || $amount <= 0) {
        throw new Exception('Lütfen geçerli hesaplar ve tutar giriniz.');
    }

    if ($fromWalletId === $toWalletId) {
        throw new Exception('Aynı hesaplar arası transfer yapılamaz.');
    }

    $pdo->beginTransaction();

    $walletModel = new WalletModel($pdo);
    $entityModel = new EntityModel($pdo);

    $fromWallet = $walletModel->find($fromWalletId);
    $toWallet = $walletModel->find($toWalletId);

    if (!$fromWallet || !$toWallet) {
        throw new Exception('Hesap bulunamadı.');
    }

    // 1. Çıkış hesabı için işlem (Entity = Hesap Sahibi, Cüzdan = Çıkış Hesabı)
    // Tutar negatif olmalı (Para çıkıyor)
    $entityModel->updateAssetBalance(
        $fromWallet['owner_entity_id'],
        -$amount,
        $fromWallet['asset_type'],
        'virman',
        $description . " (" . $toWallet['name'] . " hesabına)",
        $date,
        'V-'.time(),
        1.0,
        $fromWalletId,
        true // Doğrudan cüzdan hareketi olarak işle (ters çevirme yapma)
    );

    // 2. Giriş hesabı için işlem (Entity = Hesap Sahibi, Cüzdan = Giriş Hesabı)
    // Tutar pozitif olmalı (Para giriyor)
    $entityModel->updateAssetBalance(
        $toWallet['owner_entity_id'],
        $amount,
        $toWallet['asset_type'],
        'virman',
        $description . " (" . $fromWallet['name'] . " hesabından)",
        $date,
        'V-'.time(),
        1.0,
        $toWalletId,
        true // Doğrudan cüzdan hareketi olarak işle
    );

    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'Transfer başarıyla tamamlandı.']);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
