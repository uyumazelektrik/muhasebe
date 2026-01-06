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
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, username = ?, password = ?, role = ?, hourly_rate = ? WHERE id = ?");
            $stmt->execute([$full_name, $username, $hashed_password, $role, $hourly_rate, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, username = ?, role = ?, hourly_rate = ? WHERE id = ?");
            $stmt->execute([$full_name, $username, $role, $hourly_rate, $id]);
        }
        
        redirect(public_url('users'));
    } catch (PDOException $e) {
        die("Güncelleme Hatası: " . $e->getMessage());
    }
} else {
    redirect(public_url('users'));
}
?>
