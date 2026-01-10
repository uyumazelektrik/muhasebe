<?php
require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/../../../src/helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $musteri_adi = sanitize($_POST['musteri_adi']);
    $is_tanimi = sanitize($_POST['is_tanimi']);

    try {
        $stmt = $pdo->prepare("INSERT INTO isler (musteri_adi, is_tanimi, durum, toplam_tutar) VALUES (?, ?, 'Devam Ediyor', 0)");
        $stmt->execute([$musteri_adi, $is_tanimi]);
        
        $job_id = $pdo->lastInsertId();
        redirect_with_message(public_url('job-detail?id=' . $job_id), 'success', 'İş kaydı oluşturuldu.');
    } catch (PDOException $e) {
        redirect_with_message(public_url('jobs'), 'error', 'Hata: ' . $e->getMessage());
    }
}
?>
