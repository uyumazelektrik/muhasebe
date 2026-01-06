<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $user_id = intval($_POST['user_id'] ?? 0);
    $date = sanitize($_POST['date'] ?? date('Y-m-d'));
    $shift_id = intval($_POST['shift_id'] ?? 1);
    $clock_in = sanitize($_POST['clock_in'] ?? '09:00');
    $clock_out = sanitize($_POST['clock_out'] ?? '17:00');
    $clock_out = sanitize($_POST['clock_out'] ?? '17:00');
    $status = sanitize($_POST['status'] ?? 'present');
    $note = sanitize($_POST['note'] ?? '');

    if ($id <= 0) {
        die("Geçersiz kayıt ID.");
    }

    // Vardiya bilgilerini çek
    try {
        $shiftStmt = $pdo->prepare("SELECT start_time, end_time FROM shifts WHERE id = ?");
        $shiftStmt->execute([$shift_id]);
        $shift = $shiftStmt->fetch();
        
        if (!$shift) {
            $shift = ['start_time' => '09:00:00', 'end_time' => '17:00:00'];
        }
    } catch (PDOException $e) {
        die("Vardiya bilgisi alınamadı.");
    }

    // 2. Mesai Kıyaslamaları için Timestamp Hazırlığı
    $todayStr = date('Y-m-d');
    
    // Vardiya Saatleri
    $sStart = strtotime("$todayStr " . $shift['start_time']);
    $sEnd = strtotime("$todayStr " . $shift['end_time']);
    if ($sEnd < $sStart) $sEnd += 24*3600; 

    // Kullanıcı Giriş-Çıkış
    $uIn = strtotime("$todayStr $clock_in");
    $uOut = strtotime("$todayStr $clock_out");
    if ($uOut < $uIn) $uOut += 24*3600; 

    // A) Geç Kalma (Late)
    $is_late = 0;
    if ($status == 'present') {
        if ($uIn > ($sStart + 15 * 60)) {
            $is_late = 1;
        }
    }

    // B) Fazla Mesai (Overtime)
    $overtime = 0;
    if ($status == 'weekly_leave') {
        // Haftalık İzin Günü: Tüm çalışma süresi fazla mesai sayılır
        $workedSeconds = $uOut - $uIn;
        if ($workedSeconds > 0) {
             $overtime = $workedSeconds / 3600;
        }
    } elseif ($uOut > $sEnd) {
        $effectiveOvertimeStart = max($uIn, $sEnd);
        $overtimeSeconds = $uOut - $effectiveOvertimeStart;
        
        if ($overtimeSeconds > 0) {
            $overtime = $overtimeSeconds / 3600;
        }
    }

    try {
        $stmt = $pdo->prepare("
            UPDATE attendance 
            SET date = ?, shift_id = ?, clock_in = ?, clock_out = ?, status = ?, is_late = ?, overtime_hours = ?, note = ?
            WHERE id = ?
        ");
        $stmt->execute([$date, $shift_id, $clock_in, $clock_out, $status, $is_late, $overtime, $note, $id]);
        
        redirect(public_url('dashboard'));
    } catch (PDOException $e) {
        die("Güncelleme hatası: " . $e->getMessage());
    }
} else {
    redirect(public_url('dashboard'));
}
?>
