<?php
require __DIR__ . '/config/db.php';
echo "--- USERS ---\n";
$stmt = $pdo->prepare('SELECT id, full_name, username, role FROM users WHERE full_name LIKE ?');
$stmt->execute(['%Doğukan Ayter%']);
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "\n--- ATTENDANCE TODAY ---\n";
$today = date('Y-m-d');
$stmt = $pdo->prepare('SELECT a.*, u.full_name FROM attendance a JOIN users u ON a.user_id = u.id WHERE u.full_name LIKE ? AND a.date = ?');
$stmt->execute(['%Doğukan Ayter%', $today]);
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
