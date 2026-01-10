<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['user_id'] ?? 0);

    if ($id <= 0) {
        die("Geçersiz ID.");
    }

    try {
        $pdo->beginTransaction();

        // 1. users tablosundan entity_id'yi al
        $stmt = $pdo->prepare("SELECT entity_id FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $entity_id = $stmt->fetchColumn();

        // 2. Eğer entity_id varsa onu pasife çek (is_active = 0)
        if ($entity_id) {
            $pdo->prepare("UPDATE inv_entities SET is_active = 0 WHERE id = ?")->execute([$entity_id]);
        }

        // 3. User tablosundan SİL (veya isterseniz bunu da pasife çekebilirsiniz ama mevcut yapı DELETE kullanıyor)
        // Not: Attendance ilişkisinden dolayı CASCADE silme yoksa hata alabiliriz. 
        // Ancak isteğiniz "yapıyı korumak" olduğu için mevcut DELETE işlemini devam ettiriyoruz.
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        
        $pdo->commit();
        redirect(public_url('users'));
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        die("Silme Hatası: " . $e->getMessage());
    }
} else {
    redirect(public_url('users'));
}
?>
