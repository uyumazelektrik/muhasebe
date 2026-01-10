<?php
require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/../../../src/helpers.php';
require_once __DIR__ . '/../../../src/auth.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $tax_rate = floatval($_POST['tax_rate']);
    $invoice_status = sanitize($_POST['invoice_status']);

    $tax_included = intval($_POST['tax_included']);
    $job_date = !empty($_POST['job_date']) ? $_POST['job_date'] : date('Y-m-d');

    try {
        $stmt = $pdo->prepare("UPDATE isler SET tax_rate = ?, invoice_status = ?, tax_included = ?, job_date = ? WHERE id = ?");
        $stmt->execute([$tax_rate, $invoice_status, $tax_included, $job_date, $id]);
        
        redirect_with_message(public_url('job-detail?id=' . $id), 'success', 'Finansal ayarlar güncellendi.');
    } catch (PDOException $e) {
        redirect_with_message(public_url('job-detail?id=' . $id), 'error', 'Hata: ' . $e->getMessage());
    }
}
?>
