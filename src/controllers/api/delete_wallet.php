<?php
// src/controllers/api/delete_wallet.php
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

try {
    require_once __DIR__ . '/../../Models/WalletModel.php';
    $walletModel = new WalletModel($pdo);

    $success = $walletModel->delete($id);

    if ($success) {
        echo json_encode(['status' => 'success', 'message' => 'Cüzdan başarıyla silindi.']);
    } else {
        throw new Exception('Cüzdan silinemedi.');
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
