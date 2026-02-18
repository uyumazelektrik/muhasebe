<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Update_wallet_schema extends CI_Migration {

    public function up() {
        // Update wallet_type ENUM to include new types
        $this->db->query("ALTER TABLE inv_wallets MODIFY COLUMN wallet_type 
            ENUM('CASH', 'CREDIT_CARD', 'BANK_ACCOUNT', 'GOLD_ACCOUNT', 'LOAN', 'SAFE') 
            DEFAULT 'CASH'");
        
        // Add statement_day column for credit cards
        if (!$this->db->field_exists('statement_day', 'inv_wallets')) {
            $this->dbforge->add_column('inv_wallets', [
                'statement_day' => [
                    'type' => 'TINYINT',
                    'null' => TRUE,
                    'comment' => 'Hesap Kesim Günü'
                ]
            ]);
        }
        
        // Add payment_day column for credit cards and loans
        if (!$this->db->field_exists('payment_day', 'inv_wallets')) {
            $this->dbforge->add_column('inv_wallets', [
                'payment_day' => [
                    'type' => 'TINYINT',
                    'null' => TRUE,
                    'comment' => 'Son Ödeme/Taksit Günü'
                ]
            ]);
        }
        
        // Fix invalid wallet types
        $this->db->where('wallet_type IS NULL', null, false)
                 ->or_where('wallet_type', '')
                 ->update('inv_wallets', ['wallet_type' => 'CASH']);
        
        $this->db->where('wallet_type', 'BANK')
                 ->update('inv_wallets', ['wallet_type' => 'BANK_ACCOUNT']);
    }

    public function down() {
        // Remove columns
        if ($this->db->field_exists('statement_day', 'inv_wallets')) {
            $this->dbforge->drop_column('inv_wallets', 'statement_day');
        }
        if ($this->db->field_exists('payment_day', 'inv_wallets')) {
            $this->dbforge->drop_column('inv_wallets', 'payment_day');
        }
        
        // Revert ENUM (simplified version)
        $this->db->query("ALTER TABLE inv_wallets MODIFY COLUMN wallet_type 
            ENUM('CASH', 'CREDIT_CARD', 'BANK_ACCOUNT') 
            DEFAULT 'CASH'");
    }
}
