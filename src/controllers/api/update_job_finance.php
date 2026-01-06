<?php
require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/../../../src/helpers.php';
require_once __DIR__ . '/../../../src/auth.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $tax_rate = floatval($_POST['tax_rate']);
    $invoice_status = sanitize($_POST['invoice_status']);

    try {
        $stmt = $pdo->prepare("UPDATE isler SET tax_rate = ?, invoice_status = ? WHERE id = ?");
        $stmt->execute([$tax_rate, $invoice_status, $id]);
        
        redirect_with_message(public_url('job-detail?id=' . $id), 'success', 'Finansal ayarlar güncellendi.');
    } catch (PDOException $e) {
        redirect_with_message(public_url('job-detail?id=' . $id), 'error', 'Hata: ' . $e->getMessage());
    }
}
?>
