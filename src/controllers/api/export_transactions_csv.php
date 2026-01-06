<?php
require_admin();
$userId = intval($_GET['user_id'] ?? 0);
$month = isset($_GET['month']) ? intval($_GET['month']) : 0;
$year = isset($_GET['year']) ? intval($_GET['year']) : 0;
if ($userId <= 0) {
    http_response_code(400);
    die('Geçersiz kullanıcı');
}
try {
    if ($month > 0 && $year > 0) {
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = date('Y-m-t', strtotime($startDate));
        $stmt = $pdo->prepare('SELECT date, type, affects_balance, amount, description FROM transactions WHERE user_id = ? AND date BETWEEN ? AND ? ORDER BY date ASC, created_at ASC');
        $stmt->execute([$userId, $startDate, $endDate]);
    } else {
        $stmt = $pdo->prepare('SELECT date, type, affects_balance, amount, description FROM transactions WHERE user_id = ? ORDER BY date ASC, created_at ASC');
        $stmt->execute([$userId]);
    }
    $rows = $stmt->fetchAll();
} catch (PDOException $e) {
    http_response_code(500);
    die('Veri alınamadı');
}
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="transactions_' . $userId . '_' . date('Ymd_His') . '.csv"');
$out = fopen('php://output', 'w');
fputcsv($out, ['Tarih','Tür','Bakiye Etkisi','Tutar','Açıklama']);
foreach ($rows as $r) {
    $aff = $r['affects_balance'];
    fputcsv($out, [$r['date'], $r['type'], $aff, number_format($r['amount'], 2, '.', ''), $r['description']]);
}
fclose($out);
exit;
