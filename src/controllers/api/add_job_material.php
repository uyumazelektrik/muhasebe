<?php
require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/../../../src/helpers.php';
require_once __DIR__ . '/../../../src/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $is_id = intval($_POST['is_id']);
    $stok_id = intval($_POST['stok_id']);
    $kullanilan_miktar = floatval($_POST['miktar']);

    if ($is_id <= 0 || $stok_id <= 0 || $kullanilan_miktar <= 0) {
        redirect_with_message(public_url('job-detail?id=' . $is_id), 'error', 'Geçersiz veri girişi.');
    }

    try {
        $pdo->beginTransaction();

        // 1. Stok bilgisini al
        $stmt = $pdo->prepare("SELECT satis_fiyat, miktar, urun_adi FROM stoklar WHERE id = ? FOR UPDATE");
        $stmt->execute([$stok_id]);
        $stok = $stmt->fetch();

        if (!$stok) {
            throw new Exception("Ürün bulunamadı.");
        }

        $warning = "";
        // Hard-stop kontrolü (Sadece Admin için kalsın mı? Kullanıcı 'Personel için uyarı' dedi)
        if ($stok['miktar'] < $kullanilan_miktar) {
            if (current_role() === 'admin') {
                // Admin her zaman yapabilir veya o da uyarı alabilir. 
                // Ama kullanıcı 'Personel için eksi stok izni' dediği için:
                $warning = " (Stok eksiye düştü!)";
            } else {
                // Personel için de izin veriyoruz ama uyarı mesajı ile.
                $warning = " (Stok yetersizdi, eksiye düşüldü!)";
            }
        }

        $birim_fiyat = $stok['satis_fiyat'];
        $toplam_eklenecek = $birim_fiyat * $kullanilan_miktar;

        // 2. is_sarfiyat'a kaydet
        $stmt = $pdo->prepare("INSERT INTO is_sarfiyat (is_id, stok_id, kullanilan_miktar, birim_fiyat) VALUES (?, ?, ?, ?)");
        $stmt->execute([$is_id, $stok_id, $kullanilan_miktar, $birim_fiyat]);

        // 3. Stoktan düş
        $stmt = $pdo->prepare("UPDATE stoklar SET miktar = miktar - ? WHERE id = ?");
        $stmt->execute([$kullanilan_miktar, $stok_id]);

        // 4. İş tutarını güncelle
        $stmt = $pdo->prepare("UPDATE isler SET toplam_tutar = toplam_tutar + ? WHERE id = ?");
        $stmt->execute([$toplam_eklenecek, $is_id]);

        $pdo->commit();
        redirect_with_message(public_url('job-detail?id=' . $is_id), 'success', 'Malzeme eklendi.' . $warning);
    } catch (Exception $e) {
        $pdo->rollBack();
        redirect_with_message(public_url('job-detail?id=' . $is_id), 'error', 'Hata: ' . $e->getMessage());
    }
}
?>
