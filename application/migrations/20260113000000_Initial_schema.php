<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Initial_schema extends CI_Migration {

    public function up() {
        // Since we are moving to a new remote database, we should ensure all necessary tables exist.
        // We can use the SQL files logic or $this->dbforge.
        
        // 1. Entities
        $this->db->query("CREATE TABLE IF NOT EXISTS `inv_entities` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `type` enum('customer','supplier','both') DEFAULT 'customer',
            `tax_id` varchar(50) DEFAULT NULL,
            `tax_office` varchar(100) DEFAULT NULL,
            `phone` varchar(20) DEFAULT NULL,
            `email` varchar(100) DEFAULT NULL,
            `address` text DEFAULT NULL,
            `balance` decimal(15,4) DEFAULT 0.0000,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        // 2. Products
        $this->db->query("CREATE TABLE IF NOT EXISTS `inv_products` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `barcode` varchar(50) DEFAULT NULL,
            `unit` varchar(20) DEFAULT 'Adet',
            `buying_price` decimal(15,4) DEFAULT 0.0000,
            `selling_price` decimal(15,4) DEFAULT 0.0000,
            `stock_quantity` decimal(15,4) DEFAULT 0.0000,
            `is_active` tinyint(1) DEFAULT 1,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        // 3. Wallets
        $this->db->query("CREATE TABLE IF NOT EXISTS `inv_wallets` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(100) NOT NULL,
            `balance` decimal(15,4) DEFAULT 0.0000,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        // 4. Expense Categories
        $this->db->query("CREATE TABLE IF NOT EXISTS `inv_expense_categories` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(100) NOT NULL,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        // 5. Transactions
        $this->db->query("CREATE TABLE IF NOT EXISTS `inv_entity_transactions` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `entity_id` int(11) NOT NULL,
            `wallet_id` int(11) DEFAULT NULL,
            `type` varchar(50) NOT NULL,
            `document_no` varchar(50) DEFAULT NULL,
            `transaction_date` date NOT NULL,
            `amount` decimal(15,4) NOT NULL,
            `description` text DEFAULT NULL,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `entity_id` (`entity_id`),
            KEY `wallet_id` (`wallet_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        // 6. Invoices
        $this->db->query("CREATE TABLE IF NOT EXISTS `inv_invoices` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `invoice_no` varchar(50) NOT NULL,
            `entity_id` int(11) NOT NULL,
            `type` varchar(20) NOT NULL,
            `invoice_date` date NOT NULL,
            `total_amount` decimal(15,2) DEFAULT 0.00,
            `tax_amount` decimal(15,2) DEFAULT 0.00,
            `discount_amount` decimal(15,2) DEFAULT 0.00,
            `general_discount` decimal(15,2) DEFAULT 0.00,
            `tax_included` tinyint(1) DEFAULT 0,
            `net_amount` decimal(15,2) DEFAULT 0.00,
            `payment_status` varchar(20) DEFAULT 'unpaid',
            `payment_type` varchar(20) DEFAULT 'cash_bank',
            `wallet_id` int(11) DEFAULT NULL,
            `transfer_entity_id` int(11) DEFAULT NULL,
            `notes` text DEFAULT NULL,
            `status` varchar(20) DEFAULT 'finalized',
            `created_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `invoice_no` (`invoice_no`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        // 7. Invoice Items
        $this->db->query("CREATE TABLE IF NOT EXISTS `inv_invoice_items` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `invoice_id` int(11) NOT NULL,
            `product_id` int(11) DEFAULT NULL,
            `expense_category_id` int(11) DEFAULT NULL,
            `item_type` varchar(20) DEFAULT 'stok',
            `description` text DEFAULT NULL,
            `unit` varchar(20) DEFAULT 'Adet',
            `quantity` decimal(15,2) DEFAULT 0.00,
            `unit_price` decimal(15,2) DEFAULT 0.00,
            `discount_rate` decimal(5,2) DEFAULT 0.00,
            `discount_amount` decimal(15,2) DEFAULT 0.00,
            `tax_rate` decimal(5,2) DEFAULT 0.00,
            `tax_amount` decimal(15,2) DEFAULT 0.00,
            `total_amount` decimal(15,2) DEFAULT 0.00,
            PRIMARY KEY (`id`),
            KEY `invoice_id` (`invoice_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        
        // Users Table (needed for login/personnel)
        $this->db->query("CREATE TABLE IF NOT EXISTS `users` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `username` varchar(100) NOT NULL,
            `password` varchar(255) NOT NULL,
            `full_name` varchar(255) DEFAULT NULL,
            `role` varchar(50) DEFAULT 'staff',
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    }

    public function down() {
        // Drop tables if needed, but for initial migration we usually don't drop everything
    }
}
