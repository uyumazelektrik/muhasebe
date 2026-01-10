<?php
require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/../../../src/helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $durum = sanitize($_POST['durum']);

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("UPDATE isler SET durum = ? WHERE id = ?");
        $stmt->execute([$durum, $id]);
        $pdo->commit();
        
        redirect_with_message(public_url('job-detail?id=' . $id), 'success', 'İş durumu güncellendi.');
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        redirect_with_message(public_url('job-detail?id=' . $id), 'error', 'Hata: ' . $e->getMessage());
    }
}
?>
