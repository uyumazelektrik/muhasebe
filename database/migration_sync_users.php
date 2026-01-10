<?php
require_once __DIR__ . '/../config/db.php';

try {
    // 1. users tablosuna entity_id ekle
    $pdo->exec("ALTER TABLE users ADD COLUMN entity_id INT NULL AFTER id");
    
    // 2. Foreign Key ekle
    $pdo->exec("ALTER TABLE users ADD CONSTRAINT fk_user_entity FOREIGN KEY (entity_id) REFERENCES inv_entities(id) ON DELETE SET NULL");

    // 3. Mevcut personelleri eşleştir (İsim bazlı basit eşleştirme - opsiyonel ama iyi olur)
    $users = $pdo->query("SELECT id, full_name FROM users WHERE role != 'admin'")->fetchAll();
    foreach ($users as $user) {
        // inv_entities'de bu isimde personel var mı bak
        $stmt = $pdo->prepare("SELECT id FROM inv_entities WHERE name = ? AND type = 'staff'");
        $stmt->execute([$user['full_name']]);
        $entityId = $stmt->fetchColumn();
        
        if ($entityId) {
            $pdo->prepare("UPDATE users SET entity_id = ? WHERE id = ?")->execute([$entityId, $user['id']]);
        }
    }

    echo "Migration completed: users table updated with entity_id.\n";
} catch (PDOException $e) {
    die("Hata: " . $e->getMessage());
}
