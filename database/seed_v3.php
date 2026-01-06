<?php
require __DIR__ . '/../config/db.php';

try {
    $pdo->exec("INSERT INTO stoklar (urun_adi, birim, miktar, kritik_esik, alis_fiyat, satis_fiyat) VALUES 
    ('Profil Boru 40x40', 'Metre', 120.00, 20.00, 45.00, 65.00), 
    ('Kaynak Elektrodu', 'Paket', 3.00, 5.00, 150.00, 220.00), 
    ('M8 Civata', 'Adet', 450.00, 100.00, 1.20, 2.50), 
    ('Antipas Boya 15KG', 'Adet', 2.00, 3.00, 850.00, 1100.00)");
    echo "Dummy data inserted successfully.";
} catch (PDOException $e) {
    echo "Error inserting dummy data: " . $e->getMessage();
}
?>
