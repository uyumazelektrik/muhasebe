<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../config/db.php';

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;
$name = $data['name'] ?? '';

if (empty($name)) {
    echo json_encode(['status' => 'error', 'message' => 'Kategori adı boş olamaz']);
    exit;
}

try {
    if ($id) {
        $stmt = $pdo->prepare("UPDATE inv_expense_categories SET name = ? WHERE id = ?");
        $stmt->execute([$name, $id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO inv_expense_categories (name) VALUES (?)");
        $stmt->execute([$name]);
    }

    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
