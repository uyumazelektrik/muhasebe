<?php
// Kullanıcıları ve Bugünkü Durumlarını Veritabanından Çek
try {
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("
        SELECT u.*, a.status as today_status, a.clock_in, a.clock_out 
        FROM users u 
        LEFT JOIN (
            SELECT a1.* FROM attendance a1
            INNER JOIN (
                SELECT user_id, MAX(id) as max_id 
                FROM attendance 
                WHERE date = ? 
                GROUP BY user_id
            ) a2 ON a1.id = a2.max_id
        ) a ON u.id = a.user_id
        ORDER BY u.full_name ASC
    ");
    $stmt->execute([$today]);
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Kullanıcı listesi alınamadı: " . $e->getMessage());
}

// Görünümü oluştur
view('users', ['users' => $users]);
?>
