<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['shift_id'] ?? 0);
    $name = sanitize($_POST['name'] ?? '');
    $start_time = $_POST['start_time'] ?? '';
    $end_time = $_POST['end_time'] ?? '';

    if ($id <= 0 || empty($name) || empty($start_time) || empty($end_time)) {
        die("Geçersiz veri.");
    }

    try {
        $stmt = $pdo->prepare("UPDATE shifts SET name = ?, start_time = ?, end_time = ? WHERE id = ?");
        $stmt->execute([$name, $start_time, $end_time, $id]);
        
        redirect(public_url('settings'));
    } catch (PDOException $e) {
        die("Vardiya güncellenirken hata: " . $e->getMessage());
    }
} else {
    redirect(public_url('settings'));
}
?>
