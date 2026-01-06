<?php
// Vardiyaları çek
try {
    $stmt = $pdo->query("SELECT * FROM shifts ORDER BY id ASC");
    $shifts = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Vardiya bilgileri alınamadı: " . $e->getMessage());
}

// Genel ayarları çek
try {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
    $settingsRaw = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // Varsayılan değerler
    $settings = [
        'late_penalty_multiplier' => $settingsRaw['late_penalty_multiplier'] ?? '2.00',
        'overtime_multiplier' => $settingsRaw['overtime_multiplier'] ?? '1.50',
        'holiday_multiplier' => $settingsRaw['holiday_multiplier'] ?? '2.00'
    ];
    
} catch (PDOException $e) {
    die("Ayarlar alınamadı: " . $e->getMessage());
}

view('settings', ['shifts' => $shifts, 'settings' => $settings]);
?>
