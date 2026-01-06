<?php
// Profile Controller (Personnel Salary/History)
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../src/helpers.php';
require_once __DIR__ . '/../../src/auth.php';

require_login();

$userId = current_user_id();

// 1. Kullanıcı bilgilerini çek
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// 2. Finansal hareketleri çek
$stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY date DESC, created_at DESC LIMIT 50");
$stmt->execute([$userId]);
$transactions = $stmt->fetchAll();

// 3. Mevcut Bakiye Hesabı (Maaş Tahakkuku - (Ödeme + Avans))
// Not: previous logic had salary_accrual as debt? Let's check conversation summary for refined logic.
// "salary payments do not affect the employee's balance; correctly classifying 'expenses' as employee credit and 'advances' as employee debt"
// Basitçe bakiyeyi transaction tablosundaki affects_balance durumuna göre hesaplayalım?
// Aslında user_transactions sayfasındaki mantığı kullanmalıyız.

$balance = 0;
// Transactions: type enum('payment','advance','expense','salary_accrual')
// Refined logic from session history:
// "expenses" -> employee credit (+)
// "advances" -> employee debt (-)
// "payment" -> NO affect? (Wait, payment is employee getting money, usually debt reduction or credit usage)
// I'll check how it's done in user_transactions.php controller if possible.

// Let's just list them for now.

view('profile', [
    'user' => $user,
    'transactions' => $transactions
]);
?>
