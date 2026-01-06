<?php
require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/../../../src/helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);

    try {
        $stmt = $pdo->prepare("DELETE FROM stoklar WHERE id = ?");
        $stmt->execute([$id]);
        
        redirect_with_message(public_url('inventory'), 'success', 'Ürün başarıyla silindi.');
    } catch (PDOException $e) {
        redirect_with_message(public_url('inventory'), 'error', 'Hata: ' . $e->getMessage());
    }
}
?>
