<?php
require __DIR__ . '/config/db.php';

try {
    // 1. Create transactions table
    $sqlTransactions = "
    CREATE TABLE IF NOT EXISTS transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        type ENUM('payment', 'advance', 'expense', 'salary_accrual') NOT NULL,
        amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        description TEXT,
        date DATE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    $pdo->exec($sqlTransactions);
    echo "Table 'transactions' created successfully.<br>";

    $idxCheck = $pdo->query("SHOW INDEX FROM transactions WHERE Key_name='idx_user_date'");
    if ($idxCheck->rowCount() == 0) {
        $pdo->exec("CREATE INDEX idx_user_date ON transactions(user_id, date)");
        echo "Index 'idx_user_date' created on 'transactions'.<br>";
    } else {
        echo "Index 'idx_user_date' already exists.<br>";
    }

    // 2. Add annual_leave_days to users
    // Check if column exists first to avoid error
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'annual_leave_days'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN annual_leave_days TINYINT DEFAULT 14 AFTER salary_day");
        echo "Column 'annual_leave_days' added to 'users'.<br>";
    } else {
        echo "Column 'annual_leave_days' already exists.<br>";
    }

} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}
?>
