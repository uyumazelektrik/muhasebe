<?php
// src/Models/MovementModel.php

class MovementModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function log($data) {
        $sql = "INSERT INTO inv_movements (product_id, entity_id, type, quantity, unit_price, tax_rate, tax_amount, prev_stock, new_stock, document_no, description) 
                VALUES (:product_id, :entity_id, :type, :quantity, :unit_price, :tax_rate, :tax_amount, :prev_stock, :new_stock, :document_no, :description)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':product_id' => $data['product_id'],
            ':entity_id' => $data['entity_id'] ?? null,
            ':type' => $data['type'],
            ':quantity' => $data['quantity'],
            ':unit_price' => $data['unit_price'] ?? 0,
            ':tax_rate' => $data['tax_rate'] ?? 0,
            ':tax_amount' => $data['tax_amount'] ?? 0,
            ':prev_stock' => $data['prev_stock'],
            ':new_stock' => $data['new_stock'],
            ':document_no' => $data['document_no'] ?? null,
            ':description' => $data['description'] ?? null
        ]);
    }

    public function getHistory($productId, $limit = 50) {
        $stmt = $this->pdo->prepare("SELECT * FROM inv_movements WHERE product_id = :id ORDER BY created_at DESC LIMIT :limit");
        $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>
