<?php
require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/../../../src/helpers.php';
require_once __DIR__ . '/../../../src/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $yeni_miktar = floatval($_POST['miktar']);
    $yeni_fiyat = floatval($_POST['birim_fiyat']);

    if ($id <= 0 || $yeni_miktar <= 0 || $yeni_fiyat < 0) {
        die(json_encode(['status' => 'error', 'message' => 'Geçersiz veri girişi.']));
    }

    try {
        $pdo->beginTransaction();

        // 1. Mevcut sarfiyat bilgisini al
        $stmt = $pdo->prepare("SELECT * FROM is_sarfiyat WHERE id = ?");
        $stmt->execute([$id]);
        $old = $stmt->fetch();

        if (!$old) {
            throw new Exception("Kayıt bulunamadı.");
        }

        $is_id = $old['is_id'];
        $stok_id = $old['stok_id'];
        $eski_miktar = $old['kullanilan_miktar'];
        $eski_tutar = $eski_miktar * $old['birim_fiyat'];
        $yeni_tutar = $yeni_miktar * $yeni_fiyat;

        // 2. Stok güncelle (Fark kadar)
        $miktar_farki = $yeni_miktar - $eski_miktar;
        $stmt = $pdo->prepare("UPDATE inv_products SET stock_quantity = stock_quantity - ? WHERE id = ?");
        $stmt->execute([$miktar_farki, $stok_id]);

        // 3. İş tutarını güncelle (Fark kadar)
        $tutar_farki = $yeni_tutar - $eski_tutar;
        $stmt = $pdo->prepare("UPDATE isler SET toplam_tutar = toplam_tutar + ? WHERE id = ?");
        $stmt->execute([$tutar_farki, $is_id]);

        // 4. Kaydı güncelle
        $yeni_tarih = !empty($_POST['islem_tarihi']) ? sanitize($_POST['islem_tarihi']) : $old['islem_tarihi'];
        $stmt = $pdo->prepare("UPDATE is_sarfiyat SET kullanilan_miktar = ?, birim_fiyat = ?, islem_tarihi = ? WHERE id = ?");
        $stmt->execute([$yeni_miktar, $yeni_fiyat, $yeni_tarih, $id]);

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Malzeme sarfiyatı güncellendi.']);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Hata: ' . $e->getMessage()]);
    }
}
