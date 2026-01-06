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
    $items = [];
    
    // Hem barkodda hem isimde LIKE araması yap (Kısmi eşleşme için)
    // Önce barkod başlangıcına göre ara
    $stmt = $pdo->prepare("SELECT urun_adi, barcode, satis_fiyat, miktar, birim, kritik_esik, gorsel FROM stoklar WHERE barcode LIKE ? OR urun_adi LIKE ? LIMIT 5");
    $stmt->execute([$query . "%", "%" . $query . "%"]);
    $items = $stmt->fetchAll();

    if (!empty($items)) {
        echo json_encode(['status' => 'success', 'items' => $items]);
    } else {
        echo json_encode(['status' => 'not_found']);
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
