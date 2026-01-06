<?php
require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/../../../src/helpers.php';
require_once __DIR__ . '/../../../src/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['user_id'] ?? 0);
    
    // Personel ise sadece kendine kayıt atabilir
    if (current_role() === 'personel') {
        $user_id = current_user_id();
    }

    if ($user_id <= 0) {
        die("Lütfen bir personel seçiniz.");
    }

    $date = sanitize($_POST['date'] ?? date('Y-m-d'));
    
    // Zaten bugün için bir kayıt var mı? (Saatli giriş-çıkışlar için)
    $stmt = $pdo->prepare("SELECT id, clock_in, clock_out, shift_id, status FROM attendance WHERE user_id = ? AND date = ?");
    $stmt->execute([$user_id, $date]);
    $existing = $stmt->fetch();

    if ($existing && !isset($_POST['status'])) {
        // GÜNCELLEME (Otomatik Clock Out)
        $clock_out = date('H:i:s');
        $shift_id = $existing['shift_id'];
        
        $shiftStmt = $pdo->prepare("SELECT start_time, end_time FROM shifts WHERE id = ?");
        $shiftStmt->execute([$shift_id]);
        $shift = $shiftStmt->fetch();
        if (!$shift) $shift = ['start_time' => '09:00:00', 'end_time' => '17:00:00'];

        $todayStr = date('Y-m-d');
        $sEnd = strtotime("$todayStr " . $shift['end_time']);
        $uIn = strtotime("$todayStr " . $existing['clock_in']);
        $uOut = strtotime("$todayStr $clock_out");
        if ($uOut < $uIn) $uOut += 24*3600;

        $overtime = 0;
        if ($uOut > $sEnd) {
             $effectiveOvertimeStart = max($uIn, $sEnd);
             $overtimeSeconds = $uOut - $effectiveOvertimeStart;
             if ($overtimeSeconds > 0) $overtime = $overtimeSeconds / 3600;
        }

        $stmt = $pdo->prepare("UPDATE attendance SET clock_out = ?, overtime_hours = ? WHERE id = ?");
        $stmt->execute([$clock_out, $overtime, $existing['id']]);
        
    } else {
        // YENİ KAYIT veya MANUEL GİRİŞ
        $shift_id = intval($_POST['shift_id'] ?? 1);
        $status = sanitize($_POST['status'] ?? 'present');
        $note = sanitize($_POST['note'] ?? '');
        
        // Eğer manuel olarak saat gönderildiyse kullan, yoksa şu ankini kullan
        $clock_in = isset($_POST['clock_in']) ? sanitize($_POST['clock_in']) : date('H:i:s');
        $clock_out = isset($_POST['clock_out']) ? sanitize($_POST['clock_out']) : '00:00:00';

        $shiftStmt = $pdo->prepare("SELECT start_time, end_time FROM shifts WHERE id = ?");
        $shiftStmt->execute([$shift_id]);
        $shift = $shiftStmt->fetch();
        if (!$shift) $shift = ['start_time' => '09:00:00', 'end_time' => '17:00:00'];

        $is_late = 0;
        if ($status == 'present' && $clock_in !== '-') {
            $sStart = strtotime(date('Y-m-d') . " " . $shift['start_time']);
            $uIn = strtotime(date('Y-m-d') . " $clock_in");
            if ($uIn > ($sStart + 900)) $is_late = 1;
        }

        $stmt = $pdo->prepare("INSERT INTO attendance (user_id, date, shift_id, clock_in, clock_out, status, is_late, overtime_hours, note) VALUES (?, ?, ?, ?, ?, ?, ?, 0, ?)");
        $stmt->execute([$user_id, $date, $shift_id, $clock_in, $clock_out, $status, $is_late, $note]);
    }
    
    redirect(public_url('attendance-logs'));
}
?>
