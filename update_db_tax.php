<?php
require_once __DIR__ . '/config/db.php';

try {
    echo "Veritabanı güncelleniyor...\n";

    // 1. inv_movements tablosuna KDV bilgileri ekle (Ürün bazlı)
    try {
        $pdo->exec("ALTER TABLE inv_movements ADD COLUMN tax_rate DECIMAL(5,2) DEFAULT 0 AFTER unit_price");
        $pdo->exec("ALTER TABLE inv_movements ADD COLUMN tax_amount DECIMAL(15,4) DEFAULT 0 AFTER tax_rate");
        echo "Başarılı: inv_movements tablosuna tax_rate ve tax_amount eklendi.\n";
    } catch (PDOException $e) { /* Zaten varsa geç */ }

    // 2. inv_entity_transactions tablosuna Toplam KDV ekle (Fatura bazlı)
    try {
        $pdo->exec("ALTER TABLE inv_entity_transactions ADD COLUMN tax_total DECIMAL(15,4) DEFAULT 0 AFTER amount");
        // İskonto toplamı da faydalı olabilir
        $pdo->exec("ALTER TABLE inv_entity_transactions ADD COLUMN discount_total DECIMAL(15,4) DEFAULT 0 AFTER tax_total");
        echo "Başarılı: inv_entity_transactions tablosuna tax_total ve discount_total eklendi.\n";
    } catch (PDOException $e) { /* Zaten varsa geç */ }
    
    // 3. inv_products tablosunu kontrol et (Varsayılan KDV oranı için)
    try {
        $pdo->exec("ALTER TABLE inv_products ADD COLUMN tax_rate DECIMAL(5,2) DEFAULT 20 AFTER satis_fiyat");
         echo "Başarılı: inv_products tablosuna tax_rate eklendi.\n";
    } catch (PDOException $e) { /* Zaten varsa geç */ }


    echo "Veritabanı güncellemesi tamamlandı.\n";

} catch (PDOException $e) {
    echo "Hata: " . $e->getMessage() . "\n";
}
