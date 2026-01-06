<?php
// src/controllers/api/add_transaction.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_admin();
    $user_id = intval($_POST['user_id'] ?? 0);
    $type = sanitize($_POST['type'] ?? '');
    $amount = floatval($_POST['amount'] ?? 0);
    $description = sanitize($_POST['description'] ?? '');
    $date = sanitize($_POST['date'] ?? date('Y-m-d'));
    $affects_balance = intval($_POST['affects_balance'] ?? 0);
    $allowedTypes = ['payment','advance','expense','salary_accrual'];
    if (!in_array($type, $allowedTypes, true)) {
        redirect_with_message(public_url('user-transactions') . '?user_id=' . $user_id, 'error', 'Geçersiz işlem türü');
    }
    if (!is_valid_date($date)) {
        redirect_with_message(public_url('user-transactions') . '?user_id=' . $user_id, 'error', 'Geçersiz tarih');
    }
    if (!in_array($affects_balance, [-1,0,1], true)) {
        redirect_with_message(public_url('user-transactions') . '?user_id=' . $user_id, 'error', 'Geçersiz bakiye etkisi');
    }
    if ($user_id <= 0 || $amount <= 0 || empty($type)) {
        redirect_with_message(public_url('user-transactions') . '?user_id=' . $user_id, 'error', 'Zorunlu alanlar eksik');
    }
    if ($type === 'advance' && $affects_balance !== -1) {
        redirect_with_message(public_url('user-transactions') . '?user_id=' . $user_id, 'error', 'Avans için bakiye etkisi - olmalıdır');
    }
    if (($type === 'payment' || $type === 'salary_accrual') && $affects_balance !== 0) {
        redirect_with_message(public_url('user-transactions') . '?user_id=' . $user_id, 'error', 'Ödeme/Tahakkuk bakiyeyi etkilememelidir');
    }
    if ($type === 'expense' && !in_array($affects_balance, [-1,0,1], true)) {
        redirect_with_message(public_url('user-transactions') . '?user_id=' . $user_id, 'error', 'Harcama için bakiye etkisi -, 0 veya + olmalıdır');
    }

    try {
        $stmt = $pdo->prepare('INSERT INTO transactions (user_id, type, affects_balance, amount, description, date) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$user_id, $type, $affects_balance, $amount, $description, $date]);
        redirect_with_message(public_url('user-transactions') . '?user_id=' . $user_id, 'success', 'İşlem eklendi');
    } catch (PDOException $e) {
        redirect_with_message(public_url('user-transactions') . '?user_id=' . $user_id, 'error', 'Veritabanı hatası');
    }
} else {
    redirect(public_url('dashboard'));
}
?>
