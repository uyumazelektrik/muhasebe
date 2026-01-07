<?php
// src/Models/EntityModel.php

class EntityModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Find entity by name or tax ID, create if not exists
     */
    public function findOrCreate($name, $taxId = null, $type = 'supplier') {
        // Try to find by tax_id first if provided
        if ($taxId) {
            $stmt = $this->pdo->prepare("SELECT * FROM inv_entities WHERE tax_id = ? LIMIT 1");
            $stmt->execute([$taxId]);
            $entity = $stmt->fetch();
            if ($entity) {
                return $entity;
            }
        }

        // Try to find by name
        $stmt = $this->pdo->prepare("SELECT * FROM inv_entities WHERE name = ? AND type = ? LIMIT 1");
        $stmt->execute([$name, $type]);
        $entity = $stmt->fetch();
        
        if ($entity) {
            return $entity;
        }

        // Create new entity
        $stmt = $this->pdo->prepare("INSERT INTO inv_entities (name, type, tax_id, balance) VALUES (?, ?, ?, 0.0000)");
        $stmt->execute([$name, $type, $taxId]);
        
        return $this->pdo->query("SELECT * FROM inv_entities WHERE id = LAST_INSERT_ID()")->fetch();
    }

    /**
     * Get current balance
     */
    public function getBalance($entityId) {
        $stmt = $this->pdo->prepare("SELECT balance FROM inv_entities WHERE id = ?");
        $stmt->execute([$entityId]);
        return floatval($stmt->fetchColumn());
    }

    /**
     * Update balance and log transaction
     */
    public function updateBalance($entityId, $amount, $type, $description, $transactionDate, $documentNo = null, $taxTotal = 0, $discountTotal = 0) {
        // Update entity balance
        $stmt = $this->pdo->prepare("UPDATE inv_entities SET balance = balance + ? WHERE id = ?");
        $stmt->execute([$amount, $entityId]);

        // Log transaction
        $stmt = $this->pdo->prepare(
            "INSERT INTO inv_entity_transactions (entity_id, type, amount, description, transaction_date, document_no, tax_total, discount_total) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$entityId, $type, $amount, $description, $transactionDate, $documentNo, $taxTotal, $discountTotal]);
    }

    /**
     * Get transaction statement
     */
    public function getStatement($entityId, $startDate = null, $endDate = null) {
        $sql = "SELECT * FROM inv_entity_transactions WHERE entity_id = ?";
        $params = [$entityId];

        if ($startDate) {
            $sql .= " AND transaction_date >= ?";
            $params[] = $startDate;
        }

        if ($endDate) {
            $sql .= " AND transaction_date <= ?";
            $params[] = $endDate;
        }

        $sql .= " ORDER BY transaction_date DESC, created_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get all entities with balances
     */
    public function getAllWithBalances() {
        return $this->pdo->query("SELECT * FROM inv_entities ORDER BY balance DESC")->fetchAll();
    }

    /**
     * Get entity by ID
     */
    public function find($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM inv_entities WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Get all entities by type
     */
    public function getAll($type = null) {
        if ($type) {
            $stmt = $this->pdo->prepare("SELECT * FROM inv_entities WHERE type = ? ORDER BY name ASC");
            $stmt->execute([$type]);
            return $stmt->fetchAll();
        } else {
            return $this->pdo->query("SELECT * FROM inv_entities ORDER BY name ASC")->fetchAll();
        }
    }


}
?>
