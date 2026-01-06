<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $late_penalty = floatval($_POST['late_penalty_multiplier'] ?? 2.0);
    $overtime = floatval($_POST['overtime_multiplier'] ?? 1.5);
    $holiday = floatval($_POST['holiday_multiplier'] ?? 2.0);

    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        
        $stmt->execute(['late_penalty_multiplier', $late_penalty, $late_penalty]);
        $stmt->execute(['overtime_multiplier', $overtime, $overtime]);
        $stmt->execute(['holiday_multiplier', $holiday, $holiday]);
        
        $pdo->commit();
        
        redirect(public_url('settings'));
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Genel ayarlar güncellenirken hata oluştu: " . $e->getMessage());
    }
} else {
    redirect(public_url('settings'));
}
?>
