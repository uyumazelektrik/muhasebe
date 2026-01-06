<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/../../../src/helpers.php';

$query = $_GET['q'] ?? '';

if (strlen($query) < 2) {
    echo json_encode(['status' => 'error', 'message' => 'En az 2 karakter giriniz.']);
    exit;
}

try {
    // Önce tam barkod eşleşmesi, yoksa isim araması
    $stmt = $pdo->prepare("SELECT urun_adi, barcode, satis_fiyat, miktar, birim, kritik_esik FROM stoklar WHERE barcode = ? LIMIT 1");
    $stmt->execute([$query]);
    $item = $stmt->fetch();

    if (!$item) {
        $stmt = $pdo->prepare("SELECT urun_adi, barcode, satis_fiyat, miktar, birim, kritik_esik FROM stoklar WHERE urun_adi LIKE ? LIMIT 1");
        $stmt->execute(["%$query%"]);
        $item = $stmt->fetch();
    }

    if ($item) {
        echo json_encode(['status' => 'success', 'item' => $item]);
    } else {
        echo json_encode(['status' => 'not_found']);
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
