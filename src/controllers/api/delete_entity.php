<?php
// src/controllers/api/delete_entity.php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id = intval($data['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Geçersiz ID.']);
    exit;
}

// Güvenlik: ID 1 (Ana İşletme) silinemez
if ($id === 1) {
    echo json_encode(['status' => 'error', 'message' => 'Ana işletme kaydı silinemez.']);
    exit;
}

try {
    require_once __DIR__ . '/../../Models/EntityModel.php';
    $entityModel = new EntityModel($pdo);

    $success = $entityModel->delete($id);

    if ($success) {
        echo json_encode(['status' => 'success', 'message' => 'Cari kaydı başarıyla silindi.']);
    } else {
        throw new Exception('Cari kaydı silinemedi.');
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
