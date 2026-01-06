<?php
require __DIR__ . '/config/db.php';
$pdo->exec('DELETE FROM attendance WHERE id = 183');
echo "ID 183 deleted.\n";
