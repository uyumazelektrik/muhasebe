<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_admin();
    $id = intval($_POST['id'] ?? 0);
    $user_id = intval($_POST['user_id'] ?? 0);

    if ($id <= 0) {
        redirect_with_message(public_url('user-transactions') . '?user_id=' . $user_id, 'error', 'Geçersiz işlem ID');
    }

    try {
        $stmt = $pdo->prepare('DELETE FROM transactions WHERE id = ?');
        $stmt->execute([$id]);
        redirect_with_message(public_url('user-transactions') . '?user_id=' . $user_id, 'success', 'İşlem silindi');
    } catch (PDOException $e) {
        redirect_with_message(public_url('user-transactions') . '?user_id=' . $user_id, 'error', 'Veritabanı hatası');
    }
} else {
    redirect(public_url('dashboard'));
}
?>
