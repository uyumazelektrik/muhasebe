<?php
require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/../../../src/helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $durum = sanitize($_POST['durum']);

    try {
        $stmt = $pdo->prepare("UPDATE isler SET durum = ? WHERE id = ?");
        $stmt->execute([$durum, $id]);
        
        redirect_with_message(public_url('job-detail?id=' . $id), 'success', 'İş durumu güncellendi.');
    } catch (PDOException $e) {
        redirect_with_message(public_url('job-detail?id=' . $id), 'error', 'Hata: ' . $e->getMessage());
    }
}
?>
