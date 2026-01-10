<?php
require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/../../../src/helpers.php';
require_once __DIR__ . '/../../../src/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);

    if ($id <= 0) {
        die(json_encode(['status' => 'error', 'message' => 'Geçersiz ID.']));
    }

    try {
        $pdo->beginTransaction();

        // 1. Önce mevcut bilgileri al (Stok miktarını geri iade etmek ve iş tutarından düşmek için)
        $stmt = $pdo->prepare("SELECT * FROM is_sarfiyat WHERE id = ?");
        $stmt->execute([$id]);
        $sarfiyat = $stmt->fetch();

        if (!$sarfiyat) {
            throw new Exception("Kayıt bulunamadı.");
        }

        $is_id = $sarfiyat['is_id'];
        $stok_id = $sarfiyat['stok_id'];
        $miktar = $sarfiyat['kullanilan_miktar'];
        $tutar = $miktar * $sarfiyat['birim_fiyat'];

        // 2. Stoğu geri iade et
        $stmt = $pdo->prepare("UPDATE inv_products SET stock_quantity = stock_quantity + ? WHERE id = ?");
        $stmt->execute([$miktar, $stok_id]);

        // 3. İş tutarından düş
        $stmt = $pdo->prepare("UPDATE isler SET toplam_tutar = toplam_tutar - ? WHERE id = ?");
        $stmt->execute([$tutar, $is_id]);

        // 4. Sarfiyatı sil
        $stmt = $pdo->prepare("DELETE FROM is_sarfiyat WHERE id = ?");
        $stmt->execute([$id]);

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Malzeme silindi ve stok iade edildi.']);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Hata: ' . $e->getMessage()]);
    }
}
