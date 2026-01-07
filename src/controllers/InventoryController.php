<?php
// src/Controllers/InventoryController.php

require_once __DIR__ . '/../Models/ProductModel.php';

class InventoryController {
    private $pdo;
    private $productModel;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->productModel = new ProductModel($pdo);
    }

    public function detail($id) {
        $product = $this->productModel->find($id);
        if (!$product) {
            die("Ürün bulunamadı");
        }
        
        $chartData = $this->productModel->getChartData($id);
        
        // Pass to view
        // In a real router, this would be: return view('inventory/detail', [...]);
        // Here we simulate it by including the view.
        // NOTE: View variable scope needs to be handled if strictly MVC, but for this snippet:
        $pageTitle = $product['name'] . " - Detay";
        include __DIR__ . '/../../views/inventory/detail.php';
    }

    public function mappingList() {
        $stmt = $this->pdo->query("SELECT m.*, p.name as product_name FROM inv_mapping m LEFT JOIN inv_products p ON m.product_id = p.id ORDER BY m.created_at DESC");
        $mappings = $stmt->fetchAll();
        
        $pageTitle = "Eşleştirme Yönetimi";
        include __DIR__ . '/../../views/inventory/mapping_list.php';
    }
}
?>
