<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../config/db.php';

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;

if (!$id) {
    echo json_encode(['status' => 'error', 'message' => 'ID gerekli']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE inv_expense_categories SET is_active = 0 WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
