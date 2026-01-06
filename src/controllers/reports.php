<?php
try {
    // 1. Genel İstatistikler (Bu Ay)
    $currentMonth = date('Y-m');
    
    // Toplam Katılım
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE DATE_FORMAT(date, '%Y-%m') = ? AND status = 'present'");
    $stmt->execute([$currentMonth]);
    $totalPresent = $stmt->fetchColumn();

    // Geç Kalanlar
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE DATE_FORMAT(date, '%Y-%m') = ? AND is_late = 1");
    $stmt->execute([$currentMonth]);
    $totalLate = $stmt->fetchColumn();
    
    // Toplam Kayıtlı Personel
    $totalEmployees = $pdo->query("SELECT COUNT(*) FROM users WHERE role != 'admin'")->fetchColumn();

    // 2. Personel Bazlı Rapor (Bu Ay)
    $stmt = $pdo->prepare("
        SELECT 
            u.id, 
            u.full_name,
            COUNT(case when a.status = 'present' then 1 end) as days_present,
            COUNT(case when a.is_late = 1 then 1 end) as days_late,
            SUM(COALESCE(a.overtime_hours, 0)) as total_overtime
        FROM users u
        LEFT JOIN attendance a ON u.id = a.user_id AND DATE_FORMAT(a.date, '%Y-%m') = ?
        WHERE u.role != 'admin'
        GROUP BY u.id
        ORDER BY days_present DESC
    ");
    $stmt->execute([$currentMonth]);
    $userStats = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Rapor verisi alınamadı: " . $e->getMessage());
}

view('reports', [
    'totalPresent' => $totalPresent,
    'totalLate' => $totalLate,
    'totalEmployees' => $totalEmployees,
    'userStats' => $userStats,
    'monthName' => date('F Y') // Türkçe ay ismi gerekirse manuel mapping yapılabilir
]);
?>
