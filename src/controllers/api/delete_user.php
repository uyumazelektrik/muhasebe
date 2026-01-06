<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['user_id'] ?? 0);

    if ($id <= 0) {
        die("Geçersiz ID.");
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        
        redirect(public_url('users'));
    } catch (PDOException $e) {
        die("Silme Hatası: " . $e->getMessage());
    }
} else {
    redirect(public_url('users'));
}
?>
