<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_foreign_keys extends CI_Migration {

    public function up() {
        // Foreign key constraints for data integrity
        
        // inv_entity_transactions -> inv_entities
        $this->db->query("ALTER TABLE inv_entity_transactions 
            ADD CONSTRAINT fk_transaction_entity 
            FOREIGN KEY (entity_id) REFERENCES inv_entities(id) 
            ON DELETE RESTRICT ON UPDATE CASCADE");
        
        // inv_entity_transactions -> inv_wallets
        $this->db->query("ALTER TABLE inv_entity_transactions 
            ADD CONSTRAINT fk_transaction_wallet 
            FOREIGN KEY (wallet_id) REFERENCES inv_wallets(id) 
            ON DELETE SET NULL ON UPDATE CASCADE");
        
        // inv_entity_transactions -> inv_invoices
        $this->db->query("ALTER TABLE inv_entity_transactions 
            ADD CONSTRAINT fk_transaction_invoice 
            FOREIGN KEY (invoice_id) REFERENCES inv_invoices(id) 
            ON DELETE CASCADE ON UPDATE CASCADE");
        
        // inv_invoices -> inv_entities
        $this->db->query("ALTER TABLE inv_invoices 
            ADD CONSTRAINT fk_invoice_entity 
            FOREIGN KEY (entity_id) REFERENCES inv_entities(id) 
            ON DELETE RESTRICT ON UPDATE CASCADE");
        
        // inv_invoices -> inv_wallets
        $this->db->query("ALTER TABLE inv_invoices 
            ADD CONSTRAINT fk_invoice_wallet 
            FOREIGN KEY (wallet_id) REFERENCES inv_wallets(id) 
            ON DELETE SET NULL ON UPDATE CASCADE");
        
        // inv_invoice_items -> inv_invoices
        $this->db->query("ALTER TABLE inv_invoice_items 
            ADD CONSTRAINT fk_item_invoice 
            FOREIGN KEY (invoice_id) REFERENCES inv_invoices(id) 
            ON DELETE CASCADE ON UPDATE CASCADE");
        
        // inv_invoice_items -> inv_products
        $this->db->query("ALTER TABLE inv_invoice_items 
            ADD CONSTRAINT fk_item_product 
            FOREIGN KEY (product_id) REFERENCES inv_products(id) 
            ON DELETE SET NULL ON UPDATE CASCADE");
        
        // inv_invoice_items -> inv_expense_categories
        $this->db->query("ALTER TABLE inv_invoice_items 
            ADD CONSTRAINT fk_item_expense_category 
            FOREIGN KEY (expense_category_id) REFERENCES inv_expense_categories(id) 
            ON DELETE SET NULL ON UPDATE CASCADE");
    }

    public function down() {
        // Drop foreign key constraints
        $constraints = [
            'inv_entity_transactions' => ['fk_transaction_entity', 'fk_transaction_wallet', 'fk_transaction_invoice'],
            'inv_invoices' => ['fk_invoice_entity', 'fk_invoice_wallet'],
            'inv_invoice_items' => ['fk_item_invoice', 'fk_item_product', 'fk_item_expense_category']
        ];
        
        foreach ($constraints as $table => $keys) {
            foreach ($keys as $key) {
                $this->db->query("ALTER TABLE {$table} DROP FOREIGN KEY {$key}");
            }
        }
    }
}
