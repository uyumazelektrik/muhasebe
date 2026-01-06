<?php
// Dashboard Controller
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../auth.php';

$userId = current_user_id();
$today = date('Y-m-d');

// 1. Kullanıcı Listesi
try {
    if (current_role() === 'admin') {
        $stmt = $pdo->query("SELECT id, full_name, role FROM users ORDER BY full_name ASC");
        $users = $stmt->fetchAll();
    } else {
        $stmt = $pdo->prepare("SELECT id, full_name, role, annual_leave_days FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $users = $stmt->fetchAll();
    }
} catch (PDOException $e) { $users = []; }

// 2. Bugünün Durumu (Personel için)
$todayLog = null;
if (current_role() === 'personel') {
    $stmt = $pdo->prepare("SELECT * FROM attendance WHERE user_id = ? AND date = ?");
    $stmt->execute([$userId, $today]);
    $todayLog = $stmt->fetch();
}

// 3. Son Katılım Kayıtları
try {
    $sql = "SELECT a.*, u.full_name, s.name as shift_name FROM attendance a JOIN users u ON a.user_id = u.id LEFT JOIN shifts s ON a.shift_id = s.id";
    $params = [];
    if (current_role() === 'personel') {
        $sql .= " WHERE a.user_id = ?";
        $params[] = $userId;
    }
    $sql .= " ORDER BY a.date DESC, a.clock_in DESC LIMIT 10";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $logs = $stmt->fetchAll();
} catch (PDOException $e) { $logs = []; }

// 4. İstatistikler
try {
    $jobSql = "SELECT 
        SUM(CASE WHEN durum = 'Tamamlandı' THEN toplam_tutar ELSE 0 END) as tamamlanan,
        SUM(CASE WHEN durum != 'Tamamlandı' AND durum != 'İptal' THEN toplam_tutar ELSE 0 END) as bekleyen
    FROM isler";
    $jobParams = [];
    if (current_role() === 'personel') {
        $jobSql .= " WHERE personel_id = ?";
        $jobParams[] = $userId;
    }
    $stmt = $pdo->prepare($jobSql);
    $stmt->execute($jobParams);
    $jobStats = $stmt->fetch();
    
    $kritikStoklar = $pdo->query("SELECT * FROM stoklar WHERE miktar <= kritik_esik ORDER BY miktar ASC")->fetchAll();
    
    $sarfSql = "SELECT st.urun_adi, SUM(s.kullanilan_miktar) as toplam, st.birim FROM is_sarfiyat s JOIN stoklar st ON s.stok_id = st.id";
    $sarfParams = [];
    if (current_role() === 'personel') {
        $sarfSql .= " JOIN isler i ON s.is_id = i.id WHERE i.personel_id = ?";
        $sarfParams[] = $userId;
    }
    $sarfSql .= " GROUP BY s.stok_id ORDER BY toplam DESC LIMIT 5";
    $stmt = $pdo->prepare($sarfSql);
    $stmt->execute($sarfParams);
    $topSarfiyat = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $jobStats = ['tamamlanan' => 0, 'bekleyen' => 0];
    $kritikStoklar = [];
    $topSarfiyat = [];
}

// 5. Admin için Bugünün Özeti
$presentToday = 0;
if (current_role() === 'admin') {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE date = ? AND (status = 'present' OR status = 'late' OR status = 'excused_late')");
    $stmt->execute([$today]);
    $presentToday = $stmt->fetchColumn();
}

view('dashboard', [
    'users' => $users,
    'logs' => $logs,
    'jobStats' => $jobStats,
    'kritikStoklar' => $kritikStoklar,
    'topSarfiyat' => $topSarfiyat,
    'todayLog' => $todayLog,
    'presentToday' => $presentToday
]);
?>
