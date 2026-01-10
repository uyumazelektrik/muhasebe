<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name'] ?? '');
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = sanitize($_POST['role'] ?? 'personel');
    $hourly_rate = floatval($_POST['hourly_rate'] ?? 0);
    $annual_leave_days = 14; // Default

    if (empty($full_name) || empty($username) || empty($password)) {
        die("Ad, Kullanıcı Adı ve Şifre alanları zorunludur.");
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        $pdo->beginTransaction();

        // 1. Önce inv_entities tarafında oluştur (veya bul)
        require_once __DIR__ . '/../../Models/EntityModel.php';
        $entityModel = new EntityModel($pdo);
        $entity = $entityModel->findOrCreate($full_name, null, 'staff');
        $entity_id = $entity['id'];

        // 2. users tablosuna ekle
        $stmt = $pdo->prepare("INSERT INTO users (entity_id, full_name, username, password, role, hourly_rate, annual_leave_days) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$entity_id, $full_name, $username, $hashed_password, $role, $hourly_rate, $annual_leave_days]);
        
        $pdo->commit();
        redirect(public_url('users'));
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        die("Hata: " . $e->getMessage());
    }
} else {
    redirect(public_url('users'));
}
?>
