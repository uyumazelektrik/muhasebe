<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $start_time = $_POST['start_time'] ?? '';
    $end_time = $_POST['end_time'] ?? '';

    if (empty($name) || empty($start_time) || empty($end_time)) {
        die("Lütfen tüm alanları doldurun.");
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO shifts (name, start_time, end_time) VALUES (?, ?, ?)");
        $stmt->execute([$name, $start_time, $end_time]);
        
        redirect(public_url('settings'));
    } catch (PDOException $e) {
        die("Vardiya eklenirken hata: " . $e->getMessage());
    }
} else {
    redirect(public_url('settings'));
}
?>
