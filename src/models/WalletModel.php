<?php
// src/Models/WalletModel.php

class WalletModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Tüm aktif cüzdanları/kasaları getirir
     */
    public function getAllActive() {
        return $this->pdo->query("
            SELECT w.*, e.name as owner_name 
            FROM inv_wallets w
            JOIN inv_entities e ON w.owner_entity_id = e.id
            WHERE w.is_active = 1
            ORDER BY w.name ASC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Belirli bir cüzdanı getirir
     */
    public function find($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM inv_wallets WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Cüzdanı sil (soft delete)
     */
    public function delete($id) {
        $stmt = $this->pdo->prepare("UPDATE inv_wallets SET is_active = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Cüzdan bilgilerini güncelle
     */
    public function update($id, $data) {
        $stmt = $this->pdo->prepare("
            UPDATE inv_wallets 
            SET owner_entity_id = ?, name = ?, wallet_type = ?, asset_type = ?, limit_amount = ?, statement_day = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['owner_entity_id'],
            $data['name'],
            $data['wallet_type'],
            $data['asset_type'],
            $data['limit_amount'] ?? 0,
            $data['statement_day'] ?? 1,
            $id
        ]);
    }

    /**
     * Cüzdan bakiyesini günceller
     */
    public function updateBalance($id, $amount) {
        $stmt = $this->pdo->prepare("UPDATE inv_wallets SET balance = balance + ? WHERE id = ?");
        return $stmt->execute([$amount, $id]);
    }

    /**
     * Yeni cüzdan ekle
     */
    public function create($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO inv_wallets (owner_entity_id, name, wallet_type, asset_type, limit_amount, balance, statement_day)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['owner_entity_id'],
            $data['name'],
            $data['wallet_type'],
            $data['asset_type'],
            $data['limit_amount'] ?? 0,
            $data['balance'] ?? 0,
            $data['statement_day'] ?? 1
        ]);
    }
}
