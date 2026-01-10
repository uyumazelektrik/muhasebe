<?php
require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/../../../src/helpers.php';
require_once __DIR__ . '/../../../src/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $musteri_adi = sanitize($_POST['musteri_adi']);
    $is_tanimi = sanitize($_POST['is_tanimi']);

    try {
        $stmt = $pdo->prepare("UPDATE isler SET musteri_adi = ?, is_tanimi = ? WHERE id = ?");
        $stmt->execute([$musteri_adi, $is_tanimi, $id]);

        echo json_encode(['status' => 'success', 'message' => 'İş kaydı güncellendi.']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Hata: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Geçersiz istek.']);
}
