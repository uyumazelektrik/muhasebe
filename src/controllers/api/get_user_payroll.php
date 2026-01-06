<?php
header('Content-Type: application/json');

$userId = intval($_GET['user_id'] ?? 0);
$month = intval($_GET['month'] ?? date('n'));
$year = intval($_GET['year'] ?? date('Y'));

if ($userId <= 0) {
    echo json_encode(['error' => 'Geçersiz kullanıcı ID']);
    exit;
}

// 1. Genel Ayarları Çek
try {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
    $settingsRaw = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    $multipliers = [
        'late_penalty' => floatval($settingsRaw['late_penalty_multiplier'] ?? 2.0),
        'overtime' => floatval($settingsRaw['overtime_multiplier'] ?? 1.5),
        'holiday' => floatval($settingsRaw['holiday_multiplier'] ?? 2.0)
    ];

    // Varsayılan Vardiya (Shift ID 1) Bilgisi (Vardiya saati olmayan günler için)
    $defaultShiftStmt = $pdo->query("SELECT start_time, end_time FROM shifts WHERE id = 1");
    $defaultShift = $defaultShiftStmt->fetch(PDO::FETCH_ASSOC) ?: ['start_time' => '09:00:00', 'end_time' => '17:00:00'];
} catch (PDOException $e) {
    echo json_encode(['error' => 'Ayarlar hatası']);
    exit;
}

// 2. Kullanıcı Bilgilerini Çek
try {
    $userStmt = $pdo->prepare("SELECT full_name, hourly_rate, salary_day FROM users WHERE id = ?");
    $userStmt->execute([$userId]);
    $user = $userStmt->fetch();

    if (!$user) {
        echo json_encode(['error' => 'Kullanıcı bulunamadı']);
        exit;
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'DB Hatası']);
    exit;
}

// 3. Tarih Aralığı Hesapla (Maaş Gününe Göre)
$salaryDay = $user['salary_day'] ?? 1;

if ($salaryDay == 1) {
    $startDate = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
    $endDate = date("Y-m-t", strtotime($startDate));
} else {
    $prevMonth = $month - 1;
    $prevYear = $year;
    if ($prevMonth == 0) {
        $prevMonth = 12;
        $prevYear--;
    }
    $prevSalaryDate = "$prevYear-" . str_pad($prevMonth, 2, '0', STR_PAD_LEFT) . "-" . str_pad($salaryDay, 2, '0', STR_PAD_LEFT);
    // Başlangıç: Önceki ayın maaş gününden sonraki gün
    $startDate = date("Y-m-d", strtotime($prevSalaryDate . " +1 day"));
    // Bitiş günü: Seçilen ayın maaş günü (Dahil)
    $endDate = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-" . str_pad($salaryDay, 2, '0', STR_PAD_LEFT);
}

// 4. Kayıtları Çek
try {
    $logsStmt = $pdo->prepare("
        SELECT a.*, s.start_time as shift_start, s.end_time as shift_end 
        FROM attendance a 
        LEFT JOIN shifts s ON a.shift_id = s.id 
        WHERE a.user_id = ? AND a.date BETWEEN ? AND ?
        ORDER BY a.date ASC
    ");
    $logsStmt->execute([$userId, $startDate, $endDate]);
    $logs = $logsStmt->fetchAll();
} catch (PDOException $e) {
    echo json_encode(['error' => 'Log hatası']);
    exit;
}

// 5. Hesaplamaları Yap
$dailyDetails = [];
$hourlyRate = floatval($user['hourly_rate']);

// Logları tarihe göre indexle
$logsByDate = [];
foreach ($logs as $l) {
    $logsByDate[$l['date']] = $l;
}

$currentDateTs = strtotime($startDate);
$endDateTs = strtotime($endDate);

while ($currentDateTs <= $endDateTs) {
    $dateStr = date('Y-m-d', $currentDateTs);
    $log = $logsByDate[$dateStr] ?? null;
    
    // Eğer kayıt yoksa ve Gün Pazar ise (veya haftalık izin günü)
    // Otomatik olarak 'weekly_leave' olarak kabul et (Giriş Yok, Normal Maaş Var)
    if (!$log && date('N', $currentDateTs) == 7) {
         $log = [
             'date' => $dateStr,
             'status' => 'weekly_leave',
             'clock_in' => '-',
             'clock_out' => '-',
             'shift_start' => $defaultShift['start_time'],
             'shift_end' => $defaultShift['end_time'],
             'is_late' => 0,
             'overtime_hours' => 0
         ];
    }

    // Eğer hala log yoksa (Logsuz normal gün), atla veya 'absent' varsay (Ama burada detay göstermiyoruz, atlıyoruz)
    // Kullanıcı takvimde boş günleri de görmek isteyebilir ama şimdilik sadece Loglu ve Pazar günlerini ekleyelim.
    if (!$log) {
        $currentDateTs = strtotime('+1 day', $currentDateTs);
        continue;
    }

    $date = $log['date'];
    $status = $log['status'];
    
    // Değişkenler
    $normalHours = 0;
    $overtimeHours = $log['overtime_hours'];
    $penaltyHours = 0;
    $holidayHours = 0;
    
    $normalPay = 0;
    $overtimePay = 0;
    $penaltyDeduction = 0;
    $holidayPay = 0;
    
    // Geç Kalma Cezası Hesabı (Dakika bazlı -> Saat'e çevir)
    // Eğer is_late ise ne kadar geç kaldığını bulmalıyız. 
    // DB'de 'late_duration' tutmuyoruz, tekrar hesaplayacağız.
    if (!empty($log['is_late']) && $log['is_late'] == 1) {
         $sStart = strtotime($log['date'] . ' ' . ($log['shift_start'] ?? '09:00:00'));
         // clock_in '-' gelirse (virtual log) -> is_late zaten 0 olur
         if ($log['clock_in'] !== '-') {
             $uIn = strtotime($log['date'] . ' ' . $log['clock_in']);
             $diff = $uIn - $sStart;
             if ($diff > 0) {
                 $penaltyHours = $diff / 3600;
                 $penaltyDeduction = $penaltyHours * $hourlyRate * $multipliers['late_penalty'];
             }
         }
    }

    // Çalışma Süresi Hesabı
    $workedHours = 0;
    if ($log['clock_in'] !== '-' && $log['clock_out'] !== '-') {
        $start = strtotime($log['clock_in']);
        $end = strtotime($log['clock_out']);
        if ($end < $start) $end += 24 * 3600;
        $workedHours = ($end - $start) / 3600;
    }

    // Statüye göre ücretlendirme
    // Vardiya Süresi (Günlük Sabit Kazanç için)
    $s1 = strtotime($log['date'] . ' ' . $log['shift_start']);
    $s2 = strtotime($log['date'] . ' ' . $log['shift_end']);
    if ($s2 < $s1) $s2 += 24*3600;
    $shiftHours = ($s2 - $s1) / 3600;

    // 1. Günlük Sabit Kazanç (Her durumda verilir - devamsız hariç)
    // present, holiday, excused_late, paid_leave, annual_leave, weekly_leave -> Tam Yevmiye
    if (in_array($status, ['present', 'holiday', 'excused_late', 'paid_leave', 'annual_leave', 'weekly_leave', 'sick_leave'])) {
        $normalHours = $shiftHours;
        $normalPay = $normalHours * $hourlyRate;
    } else {
        // unpaid_leave, absent vs.
        $normalHours = 0;
        $normalPay = 0;
    }

    // 2. Fazla Mesai (Her durumda - holiday ve weekly_leave dahil)
    if (in_array($status, ['present', 'holiday', 'excused_late', 'weekly_leave'])) {
        $overtimePay = $overtimeHours * $hourlyRate * $multipliers['overtime'];
    }

    // 3. Geç Kalma Cezası (Her durumda - holiday ve excused_late dahil)
    if (in_array($status, ['present', 'holiday', 'excused_late'])) {
         $shiftStartTs = strtotime($log['date'] . ' ' . ($log['shift_start'] ?? '09:00:00'));
         
         if ($log['clock_in'] !== '-') {
             $userInTs = strtotime($log['date'] . ' ' . $log['clock_in']);
             
             // 15 Dakika (900 saniye) Tolerans
             if ($userInTs > ($shiftStartTs + 900)) {
                 $diff = $userInTs - $shiftStartTs;
                 $pHours = $diff / 3600;
                 
                 $multiplier = $multipliers['late_penalty']; // Varsayılan (Örn: 2x)
                 if ($status == 'excused_late') {
                     $multiplier = 1.0; // Mazeretli Geç: 1x Kesinti
                 }
                 
                 $penaltyHours = $pHours;
                 $penaltyDeduction = $penaltyHours * $hourlyRate * $multiplier;
             }
         }
    }

    // 4. Resmi Tatil Farkı
    if ($status == 'holiday') {
        // Kullanıcı isteği: Tatil bedeli sabit (Vardiya süresi kadar)
        $holidayHours = $shiftHours;
        $holidayPay = $holidayHours * $hourlyRate * ($multipliers['holiday'] - 1);
    }

    $totalDaily = ($normalPay + $overtimePay + $holidayPay) - $penaltyDeduction;

    // Gün adı (Türkçe)
    $ts = strtotime($date);
    $daysMap = [
        'Monday' => 'Pazartesi',
        'Tuesday' => 'Salı',
        'Wednesday' => 'Çarşamba',
        'Thursday' => 'Perşembe',
        'Friday' => 'Cuma',
        'Saturday' => 'Cumartesi',
        'Sunday' => 'Pazar'
    ];
    $dayNameEn = date('l', $ts);
    $dayNameTr = $daysMap[$dayNameEn] ?? $dayNameEn;

    $dailyDetails[] = [
        'date' => $date,
        'day_name' => $dayNameTr,
        'day_name' => $dayNameTr,
        'status' => $status,
        'note' => $log['note'] ?? '',
        'clock_in' => $log['clock_in'],
        'clock_out' => $log['clock_out'],
        'shift_start' => $log['shift_start'],
        'shift_end' => $log['shift_end'],
        'hours' => [
            'normal' => number_format($normalHours, 2),
            'overtime' => number_format($overtimeHours, 2),
            'holiday' => number_format($holidayHours, 2),
            'penalty' => number_format($penaltyHours, 2)
        ],
        'financials' => [
            'normal_pay' => number_format($normalPay, 2),
            'overtime_pay' => number_format($overtimePay, 2),
            'holiday_pay' => number_format($holidayPay, 2),
            'penalty_deduction' => number_format($penaltyDeduction, 2),
            'total' => number_format($normalPay + $overtimePay + $holidayPay - $penaltyDeduction, 2)
        ]
    ];

    $currentDateTs = strtotime('+1 day', $currentDateTs);
}

echo json_encode([
    'user' => $user,
    'details' => $dailyDetails
]);
?>
