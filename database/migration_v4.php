<?php
require_once __DIR__ . '/../config/db.php';

try {
    // 1. users tablosunu güncelle (role enum değişikliği ve password/username ekleme)
    $pdo->exec("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'personel') DEFAULT 'personel'");
    
    // Username ve Password ekle (eğer yoksa)
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN username VARCHAR(50) UNIQUE AFTER full_name");
    } catch (Exception $e) {} // Zaten varsa hata alma
    
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN password VARCHAR(255) AFTER username");
    } catch (Exception $e) {} // Zaten varsa hata alma

    // 2. Örnek bir admin oluştur (eğer hiç admin yoksa)
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
    if ($stmt->fetchColumn() == 0) {
        $pass = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->exec("INSERT INTO users (full_name, username, password, role) VALUES ('Admin Kullanıcı', 'admin', '$pass', 'admin')");
        echo "Varsayılan admin oluşturuldu: admin / admin123\n";
    }

    echo "Tablo güncellemeleri başarıyla tamamlandı.\n";
} catch (PDOException $e) {
    die("Hata: " . $e->getMessage());
}
