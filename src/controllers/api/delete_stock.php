<?php
require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/../../../src/helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);

    try {
        $pdo->beginTransaction();

        // Önce eşleştirme tablosundaki (inv_mapping) kayıtları sil
        $stmt = $pdo->prepare("DELETE FROM inv_mapping WHERE product_id = ?");
        $stmt->execute([$id]);

        // İlişkili stok hareketlerini sil (inv_movements)
        $stmt = $pdo->prepare("DELETE FROM inv_movements WHERE product_id = ?");
        $stmt->execute([$id]);

        // Sonra ürünü sil
        $stmt = $pdo->prepare("DELETE FROM inv_products WHERE id = ?");
        $stmt->execute([$id]);
        
        $pdo->commit();
        
        redirect_with_message(public_url('inventory'), 'success', 'Ürün ve ilişkili tüm kayıtlar (hareketler, fatura kalemleri) başarıyla silindi.');
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        
        // Yabancı anahtar hatası kontrolü (örn: faturada kullanılmışsa)
        if ($e->getCode() == '23000') {
             redirect_with_message(public_url('inventory'), 'error', 'Bu ürün faturalarda veya hareketlerde kullanıldığı için silinemez.');
        } else {
             redirect_with_message(public_url('inventory'), 'error', 'Hata: ' . $e->getMessage());
        }
    }
}
?>
