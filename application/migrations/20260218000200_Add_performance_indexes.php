<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_performance_indexes extends CI_Migration {

    public function up() {
        // Indexes for inv_entity_transactions
        $this->db->query("CREATE INDEX IF NOT EXISTS idx_transaction_date 
            ON inv_entity_transactions(transaction_date)");
        $this->db->query("CREATE INDEX IF NOT EXISTS idx_transaction_type 
            ON inv_entity_transactions(type)");
        $this->db->query("CREATE INDEX IF NOT EXISTS idx_transaction_due_date 
            ON inv_entity_transactions(due_date)");
        $this->db->query("CREATE INDEX IF NOT EXISTS idx_transaction_entity 
            ON inv_entity_transactions(entity_id)");
        $this->db->query("CREATE INDEX IF NOT EXISTS idx_transaction_wallet 
            ON inv_entity_transactions(wallet_id)");
        $this->db->query("CREATE INDEX IF NOT EXISTS idx_transaction_document 
            ON inv_entity_transactions(document_no)");
        $this->db->query("CREATE INDEX IF NOT EXISTS idx_transaction_invoice 
            ON inv_entity_transactions(invoice_id)");
        
        // Indexes for inv_invoices
        $this->db->query("CREATE INDEX IF NOT EXISTS idx_invoice_entity 
            ON inv_invoices(entity_id)");
        $this->db->query("CREATE INDEX IF NOT EXISTS idx_invoice_date 
            ON inv_invoices(invoice_date)");
        $this->db->query("CREATE INDEX IF NOT EXISTS idx_invoice_status 
            ON inv_invoices(status)");
        $this->db->query("CREATE INDEX IF NOT EXISTS idx_invoice_type 
            ON inv_invoices(type)");
        
        // Indexes for inv_invoice_items
        $this->db->query("CREATE INDEX IF NOT EXISTS idx_item_invoice 
            ON inv_invoice_items(invoice_id)");
        $this->db->query("CREATE INDEX IF NOT EXISTS idx_item_product 
            ON inv_invoice_items(product_id)");
        
        // Indexes for inv_entities
        $this->db->query("CREATE INDEX IF NOT EXISTS idx_entity_type 
            ON inv_entities(type)");
        $this->db->query("CREATE INDEX IF NOT EXISTS idx_entity_name 
            ON inv_entities(name)");
        
        // Indexes for inv_products
        $this->db->query("CREATE INDEX IF NOT EXISTS idx_product_barcode 
            ON inv_products(barcode)");
        $this->db->query("CREATE INDEX IF NOT EXISTS idx_product_active 
            ON inv_products(is_active)");
    }

    public function down() {
        // Drop all indexes
        $tables = [
            'inv_entity_transactions' => [
                'idx_transaction_date', 'idx_transaction_type', 'idx_transaction_due_date',
                'idx_transaction_entity', 'idx_transaction_wallet', 'idx_transaction_document',
                'idx_transaction_invoice'
            ],
            'inv_invoices' => [
                'idx_invoice_entity', 'idx_invoice_date', 'idx_invoice_status', 'idx_invoice_type'
            ],
            'inv_invoice_items' => [
                'idx_item_invoice', 'idx_item_product'
            ],
            'inv_entities' => [
                'idx_entity_type', 'idx_entity_name'
            ],
            'inv_products' => [
                'idx_product_barcode', 'idx_product_active'
            ]
        ];
        
        foreach ($tables as $table => $indexes) {
            foreach ($indexes as $index) {
                $this->db->query("DROP INDEX IF EXISTS {$index} ON {$table}");
            }
        }
    }
}
