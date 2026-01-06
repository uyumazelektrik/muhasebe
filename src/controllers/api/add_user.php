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
        $stmt = $pdo->prepare("INSERT INTO users (full_name, username, password, role, hourly_rate, annual_leave_days) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$full_name, $username, $hashed_password, $role, $hourly_rate, $annual_leave_days]);
        
        redirect(public_url('users'));
    } catch (PDOException $e) {
        die("Hata: " . $e->getMessage());
    }
} else {
    redirect(public_url('users'));
}
?>
