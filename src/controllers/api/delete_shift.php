<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['shift_id'] ?? 0);

    if ($id <= 0) {
        die("Geçersiz ID.");
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM shifts WHERE id = ?");
        $stmt->execute([$id]);
        
        redirect(public_url('settings'));
    } catch (PDOException $e) {
        die("Vardiya silinirken hata: " . $e->getMessage());
    }
} else {
    redirect(public_url('settings'));
}
?>
