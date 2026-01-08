<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../config/db.php';

$q = $_GET['q'] ?? '';

try {
    $stmt = $pdo->prepare("SELECT id, name FROM inv_expense_categories WHERE name LIKE ? AND is_active = 1 LIMIT 20");
    $stmt->execute(['%' . $q . '%']);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'items' => $items
    ]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
