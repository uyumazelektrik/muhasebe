<?php
// src/controllers/user_transactions.<?php
$userId = intval($_GET['user_id'] ?? 0);
if ($userId <= 0) {
    // No valid user ID – redirect to dashboard or show a selection list
    redirect(public_url('dashboard'));
    exit;
}

// Kullanıcı bilgisi
$stmt = $pdo->prepare('SELECT id, full_name, annual_leave_days FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();
if (!$user) {
    die('Kullanıcı bulunamadı');
}

// İşlemler (transactions)
$month = isset($_GET['month']) ? intval($_GET['month']) : 0;
$year = isset($_GET['year']) ? intval($_GET['year']) : 0;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

if ($month > 0 && $year > 0) {
    $startDate = sprintf('%04d-%02d-01', $year, $month);
    $endDate = date('Y-m-t', strtotime($startDate));
    $countStmt = $pdo->prepare('SELECT COUNT(*) FROM transactions WHERE user_id = ? AND date BETWEEN ? AND ?');
    $countStmt->execute([$userId, $startDate, $endDate]);
    $totalTransactions = (int)$countStmt->fetchColumn();
    $stmt = $pdo->prepare('SELECT * FROM transactions WHERE user_id = :uid AND date BETWEEN :start AND :end ORDER BY date DESC, created_at DESC LIMIT :limit OFFSET :offset');
    $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':start', $startDate);
    $stmt->bindValue(':end', $endDate);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $transactions = $stmt->fetchAll();
} elseif ($month == 0 && $year > 0) {
    $startDate = sprintf('%04d-01-01', $year);
    $endDate = sprintf('%04d-12-31', $year);
    $countStmt = $pdo->prepare('SELECT COUNT(*) FROM transactions WHERE user_id = ? AND date BETWEEN ? AND ?');
    $countStmt->execute([$userId, $startDate, $endDate]);
    $totalTransactions = (int)$countStmt->fetchColumn();
    $stmt = $pdo->prepare('SELECT * FROM transactions WHERE user_id = :uid AND date BETWEEN :start AND :end ORDER BY date DESC, created_at DESC LIMIT :limit OFFSET :offset');
    $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':start', $startDate);
    $stmt->bindValue(':end', $endDate);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $transactions = $stmt->fetchAll();
} else {
    $countStmt = $pdo->prepare('SELECT COUNT(*) FROM transactions WHERE user_id = ?');
    $countStmt->execute([$userId]);
    $totalTransactions = (int)$countStmt->fetchColumn();
    $stmt = $pdo->prepare('SELECT * FROM transactions WHERE user_id = :uid ORDER BY date DESC, created_at DESC LIMIT :limit OFFSET :offset');
    $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $transactions = $stmt->fetchAll();
}

// Toplamlar ve bakiye (filtre aralığına göre tam kapsam)
$where = 'user_id = ?';
$params = [$userId];
if (isset($startDate, $endDate)) {
    $where .= ' AND date BETWEEN ? AND ?';
    $params[] = $startDate;
    $params[] = $endDate;
}

$totals = ['payment'=>0,'advance'=>0,'expense'=>0,'salary_accrual'=>0];
$sumStmt = $pdo->prepare("SELECT type, SUM(amount) as total FROM transactions WHERE $where GROUP BY type");
$sumStmt->execute($params);
foreach ($sumStmt->fetchAll() as $row) {
    $totals[$row['type']] = floatval($row['total']);
}

$balWhere = 'user_id = ?';
$balParams = [$userId];
if (isset($endDate)) {
    $balWhere .= ' AND date <= ?';
    $balParams[] = $endDate;
}
$balStmt = $pdo->prepare("SELECT SUM(CASE WHEN affects_balance<>0 AND type='expense' THEN amount * affects_balance ELSE 0 END) AS bal FROM transactions WHERE $balWhere");
$balStmt->execute($balParams);
$balance = floatval($balStmt->fetchColumn() ?: 0);

// Yıllık izin kullanımı (attendance tablosundan)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE user_id = ? AND status = 'annual_leave' AND YEAR(date) = YEAR(CURDATE())");
$stmt->execute([$userId]);
$usedLeave = $stmt->fetchColumn();
$remainingLeave = max(0, $user['annual_leave_days'] - $usedLeave);

view('user_transactions', [
    'user' => $user,
    'transactions' => $transactions,
    'totals' => $totals,
    'balance' => $balance,
    'usedLeave' => $usedLeave,
    'remainingLeave' => $remainingLeave,
    'month' => $month,
    'year' => $year,
    'page' => $page,
    'limit' => $limit,
    'totalTransactions' => $totalTransactions,
]);
?>
