<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);

    if ($id > 0) {
        try {
            $stmt = $pdo->prepare("DELETE FROM attendance WHERE id = ?");
            $stmt->execute([$id]);
        } catch (PDOException $e) {
            die("Hata: " . $e->getMessage());
        }
    }
    
    redirect(public_url('dashboard'));
} else {
    redirect(public_url('dashboard'));
}
?>
