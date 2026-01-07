<?php
require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/../../../src/helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $urun_adi = sanitize($_POST['urun_adi']);
    $barcode = sanitize($_POST['barcode'] ?? '');
    $birim = sanitize($_POST['birim']);
    $miktar = floatval($_POST['miktar']);
    $kritik_esik = floatval($_POST['kritik_esik']);
    $alis_fiyat = floatval($_POST['alis_fiyat']);
    $satis_fiyat = floatval($_POST['satis_fiyat']);
    $gorsel = $_POST['gorsel'] ?? null;
    $kaynak = sanitize($_POST['kaynak'] ?? 'Manuel');

    try {
        $stmt = $pdo->prepare("INSERT INTO inv_products (name, barcode, unit, stock_quantity, critical_level, avg_cost, satis_fiyat, gorsel, kaynak) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$urun_adi, $barcode, $birim, $miktar, $kritik_esik, $alis_fiyat, $satis_fiyat, $gorsel, $kaynak]);
        
        redirect_with_message(public_url('inventory'), 'success', 'Ürün başarıyla eklendi.');
    } catch (PDOException $e) {
        redirect_with_message(public_url('inventory'), 'error', 'Hata: ' . $e->getMessage());
    }
}
?>
