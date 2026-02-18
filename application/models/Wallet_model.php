<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Wallet_model extends CI_Model {

    private $table_checked = false;

    private function check_table() {
        if ($this->table_checked) return;
        $this->table_checked = true;
        
        if (!$this->db->table_exists('inv_wallets')) {
            $this->load->dbforge();
            $this->dbforge->add_field([
                'id' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => TRUE],
                'name' => ['type' => 'VARCHAR', 'constraint' => 100],
                'wallet_type' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'CASH'],
                'asset_type' => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'TL'],
                'balance' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
                'description' => ['type' => 'TEXT', 'null' => TRUE],
                'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
                'created_at' => ['type' => 'DATETIME', 'null' => TRUE]
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->create_table('inv_wallets', TRUE);
        }
    }

    public function get_all() {
        $this->check_table();
        return $this->db->order_by('name', 'ASC')->get('inv_wallets')->result_array();
    }

    public function get_active($asset_type = null) {
        $this->db->where('is_active', 1);
        if ($asset_type) {
            $this->db->where('asset_type', $asset_type);
        }
        return $this->db->order_by('name', 'ASC')->get('inv_wallets')->result_array();
    }

    public function get_by_id($id) {
        return $this->db->get_where('inv_wallets', array('id' => $id))->row_array();
    }

    public function create($data) {
        $this->check_table();
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert('inv_wallets', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('inv_wallets', $data);
    }

    public function delete($id) {
        // Check if wallet has transactions
        $count = $this->db->where('wallet_id', $id)->count_all_results('inv_entity_transactions');
        if ($count > 0) {
            return ['success' => false, 'message' => 'Bu kasada işlem bulunduğu için silinemez.'];
        }
        $this->db->where('id', $id)->delete('inv_wallets');
        return ['success' => true];
    }

    public function update_balance($id, $amount) {
        $this->db->set('balance', 'balance + ' . (float)$amount, FALSE);
        $this->db->where('id', $id);
        return $this->db->update('inv_wallets');
    }

    public function recalculate_balance($id) {
        // Kasa bakiyesi işlem TÜRÜNE göre hesaplanır:
        // tahsilat/collection/income = para GİRİŞİ (+)
        // odeme/payment/expense = para ÇIKIŞI (-)
        
        $transactions = $this->db->select('type, amount')
            ->from('inv_entity_transactions')
            ->where('wallet_id', $id)
            ->get()
            ->result_array();
        
        $total = 0;
        foreach ($transactions as $t) {
            $absAmount = abs($t['amount']);
            $type = $t['type'];
            
            // Tahsilat türleri: kasaya para girer (+)
            if (in_array($type, ['tahsilat', 'collection', 'income'])) {
                $total += $absAmount;
            }
            // Ödeme türleri: kasadan para çıkar (-)
            elseif (in_array($type, ['odeme', 'payment', 'expense'])) {
                $total -= $absAmount;
            }
            // Diğer türler: tutar işaretine göre
            else {
                $total += $t['amount'];
            }
        }
        
        $this->db->where('id', $id);
        return $this->db->update('inv_wallets', ['balance' => $total]);
    }

    public function get_transactions($wallet_id, $limit = 50, $offset = 0) {
        $this->db->select('t.*, e.name as entity_name');
        $this->db->from('inv_entity_transactions t');
        $this->db->join('inv_entities e', 't.entity_id = e.id', 'left');
        $this->db->where('t.wallet_id', $wallet_id);
        $this->db->order_by('t.transaction_date', 'DESC');
        $this->db->order_by('t.id', 'DESC');
        $this->db->limit($limit, $offset);
        return $this->db->get()->result_array();
    }

    public function count_transactions($wallet_id) {
        return $this->db->where('wallet_id', $wallet_id)->count_all_results('inv_entity_transactions');
    }

    public function get_stats() {
        $stats = [];
        
        // Total balance by asset type
        $this->db->select('asset_type, SUM(balance) as total');
        $this->db->group_by('asset_type');
        $stats['by_asset'] = $this->db->get('inv_wallets')->result_array();
        
        // Total wallets
        $stats['total_wallets'] = $this->db->count_all('inv_wallets');
        
        // Total balance in TL
        $this->db->select_sum('balance');
        $this->db->where('asset_type', 'TL');
        $stats['total_tl'] = $this->db->get('inv_wallets')->row()->balance ?? 0;
        
        return $stats;
    }
}
