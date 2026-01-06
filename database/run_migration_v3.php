<?php
require __DIR__ . '/../config/db.php';

try {
    echo "Starting migration v3...<br>";

    // 1. stoklar tablosu
    $sqlStoklar = "
    CREATE TABLE IF NOT EXISTS stoklar (
        id INT AUTO_INCREMENT PRIMARY KEY,
        urun_adi VARCHAR(255) NOT NULL,
        birim ENUM('Adet', 'Metre', 'KG', 'Paket') DEFAULT 'Adet',
        miktar DECIMAL(10,2) DEFAULT 0,
        kritik_esik DECIMAL(10,2) DEFAULT 5,
        alis_fiyat DECIMAL(10,2) DEFAULT 0,
        satis_fiyat DECIMAL(10,2) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ";
    $pdo->exec($sqlStoklar);
    echo "Table 'stoklar' created successfully.<br>";

    // 2. isler tablosu
    $sqlIsler = "
    CREATE TABLE IF NOT EXISTS isler (
        id INT AUTO_INCREMENT PRIMARY KEY,
        musteri_adi VARCHAR(255) NOT NULL,
        is_tanimi TEXT,
        personel_id INT,
        durum ENUM('Beklemede', 'Devam Ediyor', 'Tamamlandı', 'İptal') DEFAULT 'Beklemede',
        toplam_tutar DECIMAL(10,2) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX (personel_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ";
    // Not: Foreign key relation will be added separately to avoid existing table locking issues if any.
    $pdo->exec($sqlIsler);
    echo "Table 'isler' created successfully.<br>";

    // Add Foreign Key for isler.personel_id -> users.id
    try {
        $pdo->exec("ALTER TABLE isler ADD CONSTRAINT fk_isler_personel FOREIGN KEY (personel_id) REFERENCES users(id) ON DELETE SET NULL");
        echo "Foreign key constraint added to 'isler'.<br>";
    } catch (Exception $e) {
        echo "Constraint might already exist or skipped: " . $e->getMessage() . "<br>";
    }

    // 3. is_sarfiyat tablosu
    $sqlSarfiyat = "
    CREATE TABLE IF NOT EXISTS is_sarfiyat (
        id INT AUTO_INCREMENT PRIMARY KEY,
        is_id INT,
        stok_id INT,
        kullanilan_miktar DECIMAL(10,2) NOT NULL,
        birim_fiyat DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (is_id),
        INDEX (stok_id),
        CONSTRAINT fk_sarfiyat_is FOREIGN KEY (is_id) REFERENCES isler(id) ON DELETE CASCADE,
        CONSTRAINT fk_sarfiyat_stok FOREIGN KEY (stok_id) REFERENCES stoklar(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ";
    $pdo->exec($sqlSarfiyat);
    echo "Table 'is_sarfiyat' created successfully.<br>";

    echo "Migration completed successfully.";

} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}
?>
