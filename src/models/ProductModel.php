<?php
// src/Models/ProductModel.php

class ProductModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function create($data) {
        $sql = "INSERT INTO inv_products (name, barcode, unit, stock_quantity, avg_cost, satis_fiyat) VALUES (:name, :barcode, :unit, :stock_quantity, :avg_cost, :satis_fiyat)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':name' => $data['name'],
            ':barcode' => $data['barcode'],
            ':unit' => $data['unit'] ?? 'Adet',
            ':stock_quantity' => $data['stock_quantity'] ?? 0,
            ':avg_cost' => $data['avg_cost'] ?? 0,
            ':satis_fiyat' => $data['satis_fiyat'] ?? 0
        ]);
        return $this->pdo->lastInsertId();
    }

    public function find($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM inv_products WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function findByBarcode($barcode) {
        $stmt = $this->pdo->prepare("SELECT * FROM inv_products WHERE barcode = :barcode");
        $stmt->execute([':barcode' => $barcode]);
        return $stmt->fetch();
    }

    public function findBestMatch($rawName) {
        // 1. Check exact mapping
        $stmt = $this->pdo->prepare("SELECT p.* FROM inv_mapping m JOIN inv_products p ON m.product_id = p.id WHERE m.raw_name = :name");
        $stmt->execute([':name' => $rawName]);
        if ($product = $stmt->fetch()) {
            return $product;
        }

        // 2. Exact Name Match
        $stmt = $this->pdo->prepare("SELECT * FROM inv_products WHERE name = :name");
        $stmt->execute([':name' => $rawName]);
        if ($product = $stmt->fetch()) {
            return $product;
        }

        // 3. Extract part code in parentheses if exists (e.g. "... (527-010200-301)")
        if (preg_match('/\((.*?)\)/', $rawName, $matches)) {
            $code = trim($matches[1]);
            // Search by barcode or name containing this code
            $stmt = $this->pdo->prepare("SELECT * FROM inv_products WHERE barcode = :code OR name LIKE :likeCode LIMIT 1");
            $stmt->execute([':code' => $code, ':likeCode' => '%' . $code . '%']);
            if ($product = $stmt->fetch()) {
                return $product;
            }
        }

        // 4. Fuzzy search (LIKE) - Database name contains the query
        $stmt = $this->pdo->prepare("SELECT * FROM inv_products WHERE name LIKE :name LIMIT 1");
        $stmt->execute([':name' => '%' . $rawName . '%']);
        if ($product = $stmt->fetch()) {
            return $product;
        }

        // 5. Reverse Fuzzy Search - Query contains the database name (AI is more specific than DB)
        $stmt = $this->pdo->prepare("SELECT * FROM inv_products WHERE :name LIKE CONCAT('%', name, '%') ORDER BY LENGTH(name) DESC LIMIT 1");
        $stmt->execute([':name' => $rawName]);
        if ($product = $stmt->fetch()) {
            return $product;
        }

        return false;
    }

    public function updateCostAndStock($id, $newStock, $newAvgCost, $lastBuyPrice) {
        $sql = "UPDATE inv_products SET stock_quantity = :stock, avg_cost = :avg_cost, last_buy_price = :last_price WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':stock' => $newStock,
            ':avg_cost' => $newAvgCost,
            ':last_price' => $lastBuyPrice,
            ':id' => $id
        ]);
    }

    public function updateSalePrice($id, $newSalePrice) {
        $sql = "UPDATE inv_products SET satis_fiyat = :price WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':price' => $newSalePrice,
            ':id' => $id
        ]);
    }

    // Faz 5.1: Analiz Sorguları
    public function getChartData($id) {
        // Fetch last 20 movements for analysis
        $stmt = $this->pdo->prepare("SELECT type, unit_price, new_stock, movement_date FROM inv_movements WHERE product_id = :id ORDER BY movement_date ASC LIMIT 40");
        $stmt->execute([':id' => $id]);
        return $stmt->fetchAll();
    }
}
?>
