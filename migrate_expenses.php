<?php
require_once __DIR__ . '/config/db.php';

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS inv_expense_categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        is_active TINYINT DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    
    // Check if column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM inv_movements LIKE 'expense_category_id'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE inv_movements ADD COLUMN expense_category_id INT DEFAULT NULL;");
    }
    
    // Add default categories if table is empty
    $count = $pdo->query("SELECT COUNT(*) FROM inv_expense_categories")->fetchColumn();
    if ($count == 0) {
        $categories = ['Elektrik', 'Su', 'İnternet', 'Yemek', 'Genel Gider'];
        $stmt = $pdo->prepare("INSERT INTO inv_expense_categories (name) VALUES (?)");
        foreach ($categories as $cat) {
            $stmt->execute([$cat]);
        }
    }
    
    echo "Migration successful.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
