<?php
require_once __DIR__ . '/../../src/auth.php';

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : null;

$where = [];
$params = [];

if (current_role() === 'personel') {
    $where[] = "a.user_id = :current_user_id";
    $params[':current_user_id'] = current_user_id();
}

if ($startDate) {
    $where[] = "a.date >= :start_date";
    $params[':start_date'] = $startDate;
}

if ($endDate) {
    $where[] = "a.date <= :end_date";
    $params[':end_date'] = $endDate;
}

$whereSql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Toplam kayıt sayısı
$totalSql = "SELECT COUNT(*) FROM attendance a $whereSql";
$totalStmt = $pdo->prepare($totalSql);
foreach ($params as $key => $val) {
    $totalStmt->bindValue($key, $val);
}
$totalStmt->execute();
$totalLogs = $totalStmt->fetchColumn();
$totalPages = ceil($totalLogs / $limit);

// Kayıtları çek
try {
    $sql = "
        SELECT 
            a.*, 
            u.full_name,
            s.name as shift_name
        FROM attendance a
        JOIN users u ON a.user_id = u.id
        LEFT JOIN shifts s ON a.shift_id = s.id
        $whereSql
        ORDER BY a.date DESC, a.clock_in DESC
        LIMIT :limit OFFSET :offset
    ";
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $logs = $stmt->fetchAll();
} catch (PDOException $e) { $logs = []; }

// Modal için tüm kullanıcıları çek (Sadece Admin)
$all_users = [];
if (current_role() === 'admin') {
    $all_users = $pdo->query("SELECT id, full_name FROM users ORDER BY full_name ASC")->fetchAll();
}

view('attendance_logs', [
    'logs' => $logs,
    'all_users' => $all_users,
    'page' => $page,
    'totalPages' => $totalPages,
    'totalLogs' => $totalLogs,
    'startDate' => $startDate,
    'endDate' => $endDate
]);
?>
