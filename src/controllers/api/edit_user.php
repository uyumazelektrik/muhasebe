<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['user_id'] ?? 0);
    $full_name = sanitize($_POST['full_name'] ?? '');
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = sanitize($_POST['role'] ?? 'personel');
    $hourly_rate = floatval($_POST['hourly_rate'] ?? 0.0);

    if ($id <= 0 || empty($full_name) || empty($username)) {
        die("Geçersiz veri.");
    }

    try {
        $pdo->beginTransaction();

        // 1. users tablosundan entity_id'yi al
        $stmt = $pdo->prepare("SELECT entity_id FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $entity_id = $stmt->fetchColumn();

        // 2. Eğer entity_id yoksa oluştur (eski kayıtlar için)
        if (!$entity_id) {
            require_once __DIR__ . '/../../Models/EntityModel.php';
            $entityModel = new EntityModel($pdo);
            $entity = $entityModel->findOrCreate($full_name, null, 'staff');
            $entity_id = $entity['id'];
            
            // User tablosunu güncelle
            $pdo->prepare("UPDATE users SET entity_id = ? WHERE id = ?")->execute([$entity_id, $id]);
        } else {
            // Mevcut entity ismini güncelle
            $pdo->prepare("UPDATE inv_entities SET name = ? WHERE id = ?")->execute([$full_name, $entity_id]);
        }

        // 3. User tablosunu güncelle
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, username = ?, password = ?, role = ?, hourly_rate = ? WHERE id = ?");
            $stmt->execute([$full_name, $username, $hashed_password, $role, $hourly_rate, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, username = ?, role = ?, hourly_rate = ? WHERE id = ?");
            $stmt->execute([$full_name, $username, $role, $hourly_rate, $id]);
        }
        
        $pdo->commit();
        redirect(public_url('users'));
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        die("Güncelleme Hatası: " . $e->getMessage());
    }
} else {
    redirect(public_url('users'));
}
?>
