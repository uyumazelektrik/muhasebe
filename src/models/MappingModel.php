<?php
// src/Models/MappingModel.php

class MappingModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function createOrUpdate($rawName, $productId, $confidence = 100) {
        $sql = "INSERT INTO inv_mapping (raw_name, product_id, confidence_score) 
                VALUES (:raw_name, :product_id, :confidence)
                ON DUPLICATE KEY UPDATE 
                product_id = VALUES(product_id), 
                confidence_score = VALUES(confidence_score),
                updated_at = NOW()";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':raw_name' => $rawName,
            ':product_id' => $productId,
            ':confidence' => $confidence
        ]);
    }

    public function findProduct($rawName) {
        $stmt = $this->pdo->prepare("SELECT product_id FROM inv_mapping WHERE raw_name = :raw_name");
        $stmt->execute([':raw_name' => $rawName]);
        return $stmt->fetchColumn();
    }
}
?>
