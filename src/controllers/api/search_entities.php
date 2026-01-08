<?php
// src/controllers/api/search_entities.php
require_once __DIR__ . '/../../Models/EntityModel.php';

header('Content-Type: application/json');

$query = $_GET['q'] ?? '';

try {
    $stmt = $pdo->prepare("SELECT id, name, type FROM inv_entities WHERE name LIKE ? ORDER BY name ASC LIMIT 20");
    $stmt->execute(['%' . $query . '%']);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $results]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
