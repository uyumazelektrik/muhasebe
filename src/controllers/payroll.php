<?php
// Ay ve Yıl Seçimi
$month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Ayın başlangıç ve bitiş tarihleri
$startDate = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
$endDate = date("Y-m-t", strtotime($startDate));

// Ayarlar (Çarpanlar)
try {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
    $settingsRaw = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    $multipliers = [
        'late_penalty' => floatval($settingsRaw['late_penalty_multiplier'] ?? 2.0),
        'overtime' => floatval($settingsRaw['overtime_multiplier'] ?? 1.5),
        'holiday' => floatval($settingsRaw['holiday_multiplier'] ?? 2.0)
    ];
    
    // Varsayılan Vardiya (Shift ID 1) Bilgisi
    $defaultShiftStmt = $pdo->query("SELECT start_time, end_time FROM shifts WHERE id = 1");
    $defaultShift = $defaultShiftStmt->fetch(PDO::FETCH_ASSOC) ?: ['start_time' => '09:00:00', 'end_time' => '17:00:00'];
} catch (PDOException $e) {
    die("Ayarlar hatası: " . $e->getMessage());
}

// Personelleri Çek
try {
    $usersStmt = $pdo->query("SELECT id, full_name, hourly_rate, salary_day FROM users WHERE role != 'admin' ORDER BY full_name ASC");
    $users = $usersStmt->fetchAll();
} catch (PDOException $e) {
    die("Personel hatası: " . $e->getMessage());
}

$payrollData = [];

foreach ($users as $user) {
    $salaryDay = $user['salary_day'] ?? 1;
    
    // Maaş Tarih Aralığı Hesaplama
    // Kullanıcı için seçilen "Ay" ve "Yıl" maaşın ÖDENDİĞİ zamandır.
    // Eğer maaş günü 1 ise: Ararlık 1 - Aralık 31 (Normal takvim ayı)
    // Eğer maaş günü 15 ise: Kasım 15 - Aralık 14 (Aralık 15'te ödenen maaş için)
    
    if ($salaryDay == 1) {
        $startDate = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
        $endDate = date("Y-m-t", strtotime($startDate));
    } else {
        // Önceki ayın maaş günü
        // Örnek: Seçilen: Aralık (12). Maaş Günü: 15.
        // Başlangıç: Kasım (11) 15.
        // Bitiş: Aralık (12) 14.
        
        $prevMonth = $month - 1;
        $prevYear = $year;
        if ($prevMonth == 0) {
            $prevMonth = 12;
            $prevYear--;
        }
        
        $prevSalaryDate = "$prevYear-" . str_pad($prevMonth, 2, '0', STR_PAD_LEFT) . "-" . str_pad($salaryDay, 2, '0', STR_PAD_LEFT);
        // Başlangıç günü: Önceki ayın maaş gününden sonraki gün
        $startDate = date("Y-m-d", strtotime($prevSalaryDate . " +1 day"));
        
        // Bitiş günü = seçilen ayın maaş günü (Dahil)
        $endDate = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-" . str_pad($salaryDay, 2, '0', STR_PAD_LEFT);
    }

    // Personelin bu aralıktaki kayıtlarını çek (Vardiya bilgisiyle join)
    $logsStmt = $pdo->prepare("
        SELECT a.*, s.start_time as shift_start, s.end_time as shift_end 
        FROM attendance a 
        LEFT JOIN shifts s ON a.shift_id = s.id 
        WHERE a.user_id = ? AND a.date BETWEEN ? AND ?
    ");
    $logsStmt->execute([$user['id'], $startDate, $endDate]);
    $logs = $logsStmt->fetchAll();

    $totalNormalHours = 0; // Normal Mesai Saati
    $totalOvertimeHours = 0; // Fazla Mesai Saati
    $totalHolidayHours = 0; // Resmi Tatil Çalışma Saati
    $totalPenaltyHours = 0; // Ceza Saati (Geç Kalma - Fiziksel Süre)
    $totalWeightedPenaltyHours = 0; // Cezai Kesinti Karşılığı Saat (Puan)
    
    $daysWorked = 0;
    $daysLeave = 0; // Ücretli izin günleri

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

        // Pazar günü ve kayıt yoksa -> weekly_leave
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

        // Kalan günler için kayıt yoksa atla
        if (!$log) {
            $currentDateTs = strtotime('+1 day', $currentDateTs);
            continue;
        }

        $status = $log['status'];
        
        // Çalışma süresi
        if ($log['clock_in'] !== '-' && $log['clock_out'] !== '-') {
            $start = strtotime($log['clock_in']);
            $end = strtotime($log['clock_out']);
            if ($end < $start) $end += 24 * 3600;
            $workedHours = ($end - $start) / 3600;
        } else {
            $workedHours = 0;
        }

        // Vardiya Süresi Hesapla
        $s1 = strtotime($log['date'] . ' ' . ($log['shift_start'] ?? '09:00:00'));
        $s2 = strtotime($log['date'] . ' ' . ($log['shift_end'] ?? '18:00:00'));
        if ($s2 < $s1) $s2 += 24*3600;
        $shiftHours = ($s2 - $s1) / 3600;

        // 1. Günlük Sabit Kazanç
        if (in_array($status, ['present', 'holiday', 'excused_late', 'paid_leave', 'annual_leave', 'weekly_leave'])) {
            $totalNormalHours += $shiftHours;
            // Gün sayımı - Weekly Leave'i de izin/çalışma gününe ekleyelim mi? 
            // Kullanıcı "Haftalık İzin" diyor, yani 'days_leave' mantıklı olabilir veya 'days_worked' değil.
            if (in_array($status, ['present', 'holiday', 'excused_late', 'weekly_leave'])) {
                 // Weekly leave teknik olarak 'günlük kazanç' aldığı için buraya dahil edilebilir ama 'çalışılan gün' sayısında görünmemesi mi gerekir?
                 // Kullanıcı "24 gün çalışma, 2 gün izin" diyor. 2 Pazar + 2 Cumartesi = 4 gün hafta sonu.
                 // "Weekly leave" genelde 'İzin' sayılır.
                 if ($status == 'weekly_leave') {
                     $daysLeave++;
                 } else {
                     $daysWorked++;
                 }
            } else {
                 $daysLeave++;
            }
        }

        // 2. Fazla Mesai
        if (in_array($status, ['present', 'holiday', 'excused_late', 'weekly_leave'])) {
            $totalOvertimeHours += $log['overtime_hours'];
        }

        // 3. Geç Kalma Cezası
        if (in_array($status, ['present', 'holiday', 'excused_late'])) {
             // clock_in '-' kontrolü
             if ($log['clock_in'] !== '-') {
                 $shiftStartTs = strtotime($log['date'] . ' ' . ($log['shift_start'] ?? '09:00:00'));
                 $userInTs = strtotime($log['date'] . ' ' . $log['clock_in']);
                 // 15 Dakika (900 saniye) Tolerans
                 if ($userInTs > ($shiftStartTs + 900)) {
                     $diff = $userInTs - $shiftStartTs;
                     $pHours = $diff / 3600;
                     $totalPenaltyHours += $pHours; 
                     
                     $multiplier = $multipliers['late_penalty'];
                     if ($status == 'excused_late') {
                         $multiplier = 1.0;
                     }
                     
                     $totalWeightedPenaltyHours += ($pHours * $multiplier);
                 }
             }
        }

        // 4. Resmi Tatil Farkı
        if ($status == 'holiday') {
            $totalHolidayHours += $shiftHours * ($multipliers['holiday'] - 1);
        }

        $currentDateTs = strtotime('+1 day', $currentDateTs);
    }

    // Maaş Hesabı
    $hourlyRate = floatval($user['hourly_rate']);
    
    $normalPay = $totalNormalHours * $hourlyRate;
    $overtimePay = $totalOvertimeHours * $hourlyRate * $multipliers['overtime'];
    
    // Tatil saati zaten (Çarpan-1) ile ağırlıklandırılmış saat olarak toplandı. (ama yukarıda shiftHours eklendi)
    // $totalHolidayHours değişkeninde (ShiftHours * 1) birikiyor.
    // Çarpmamıza gerek yok, direkt HourlyRate ile çarpınca para çıkar.
    $holidayPay = $totalHolidayHours * $hourlyRate; 
    
    // Ceza Hesabı (Ağırlıklı Saat * Saatlik Ücret)
    $penaltyDeduction = $totalWeightedPenaltyHours * $hourlyRate;
    
    $grossSalary = $normalPay + $overtimePay + $holidayPay;
    $netSalary = $grossSalary - $penaltyDeduction;

    $payrollData[] = [
        'user' => $user,
        'stats' => [
            'normal_hours' => $totalNormalHours,
            'overtime_hours' => $totalOvertimeHours,
            'holiday_hours' => $totalHolidayHours,
            'penalty_hours' => $totalPenaltyHours,
            'days_worked' => $daysWorked,
            'days_leave' => $daysLeave
        ],
        'financials' => [
            'normal_pay' => $normalPay,
            'overtime_pay' => $overtimePay,
            'holiday_pay' => $holidayPay,
            'penalty_deduction' => $penaltyDeduction,
            'gross_salary' => $grossSalary,
            'net_salary' => $netSalary
        ]
    ];
}

view('payroll', [
    'payrollData' => $payrollData,
    'month' => $month,
    'year' => $year,
    'years' => range(date('Y') - 1, date('Y') + 1)
]);
?>
