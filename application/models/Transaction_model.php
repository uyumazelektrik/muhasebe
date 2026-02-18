<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Transaction_model extends CI_Model {



    public function get_all($filters = array(), $limit = 50, $offset = 0) {
        $this->db->select('t.*, e.name as entity_name, w.name as wallet_name, c.name as category_name');
        $this->db->from('inv_entity_transactions t');
        $this->db->join('inv_entities e', 't.entity_id = e.id', 'left');
        $this->db->join('inv_wallets w', 't.wallet_id = w.id', 'left');
        $this->db->join('inv_expense_categories c', 't.category_id = c.id', 'left');

        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('t.description', $filters['search']);
            $this->db->or_like('t.document_no', $filters['search']);
            $this->db->or_like('e.name', $filters['search']);
            $this->db->group_end();
        }

        if (!empty($filters['entity_id'])) $this->db->where('t.entity_id', $filters['entity_id']);
        if (!empty($filters['type'])) $this->db->where('t.type', $filters['type']);
        if (!empty($filters['start_date'])) $this->db->where('t.transaction_date >=', $filters['start_date']);
        if (!empty($filters['end_date'])) $this->db->where('t.transaction_date <=', $filters['end_date']);

        $this->db->order_by('t.transaction_date', 'DESC');
        $this->db->order_by('t.id', 'DESC');
        $this->db->limit($limit, $offset);

        return $this->db->get()->result_array();
    }

    public function create($data) {
        $this->db->trans_start();

        $insert_data = array(
            'entity_id' => !empty($data['entity_id']) ? $data['entity_id'] : NULL,
            'wallet_id' => !empty($data['wallet_id']) ? $data['wallet_id'] : NULL,
            'linked_transaction_id' => !empty($data['linked_transaction_id']) ? $data['linked_transaction_id'] : NULL,
            'category_id' => !empty($data['category_id']) ? $data['category_id'] : NULL,
            'type' => $data['type'],
            'amount' => $data['amount'],
            'description' => $data['description'],
            'document_no' => $data['document_no'] ?? NULL,
            'transaction_date' => $data['transaction_date'] ?? date('Y-m-d'),
            'due_date' => !empty($data['due_date']) ? $data['due_date'] : NULL,
            'created_at' => date('Y-m-d H:i:s')
        );

        $this->db->insert('inv_entity_transactions', $insert_data);
        $trx_id = $this->db->insert_id();

        // 1. Cari Bakiyesi Güncelleme
        if (!empty($data['entity_id'])) {
            $this->load->model('Entity_model', 'entity');
            $this->entity->recalculate_balance($data['entity_id']);
        }

        // 2. Kasa Bakiyesi Güncelleme
        if (!empty($data['wallet_id'])) {
            $this->load->model('Wallet_model', 'wallet');
            $this->wallet->recalculate_balance($data['wallet_id']);
        }

        $this->db->trans_complete();
        return $this->db->trans_status() ? $trx_id : FALSE;
    }

    public function update($id, $data) {
        $this->db->trans_start();

        // Mevcut (eski) veriyi al (bakiye hesaplaması için lazım olabilir)
        $old_trx = $this->db->get_where('inv_entity_transactions', ['id' => $id])->row_array();
        if (!$old_trx) return FALSE;

        // Güncelleme verisi hazırlığı
        $update_data = [
            'entity_id' => !empty($data['entity_id']) ? $data['entity_id'] : $old_trx['entity_id'],
            'wallet_id' => isset($data['wallet_id']) ? ($data['wallet_id'] !== '' ? $data['wallet_id'] : NULL) : $old_trx['wallet_id'],
            'category_id' => isset($data['category_id']) ? ($data['category_id'] !== '' ? $data['category_id'] : NULL) : ($old_trx['category_id'] ?? NULL),
            'type' => $data['type'] ?? $old_trx['type'],
            'amount' => isset($data['amount']) ? $data['amount'] : $old_trx['amount'],
            'description' => $data['description'] ?? $old_trx['description'],
            'document_no' => $data['document_no'] ?? $old_trx['document_no'],
            'transaction_date' => $data['transaction_date'] ?? $old_trx['transaction_date'],
            'due_date' => isset($data['due_date']) ? ($data['due_date'] !== '' ? $data['due_date'] : NULL) : ($old_trx['due_date'] ?? NULL),
            'linked_transaction_id' => !empty($data['linked_transaction_id']) ? $data['linked_transaction_id'] : ($old_trx['linked_transaction_id'] ?? NULL)
        ];

        $this->db->where('id', $id)->update('inv_entity_transactions', $update_data);

        // Bakiyeleri yeniden hesapla
        $this->load->model('Entity_model', 'entity');
        $this->load->model('Wallet_model', 'wallet');

        // Etkilenen cariler
        $entity_ids = array_filter([$old_trx['entity_id'], $update_data['entity_id']]);
        foreach (array_unique($entity_ids) as $eid) {
            $this->entity->recalculate_balance($eid);
        }

        // Etkilenen kasalar
        $wallet_ids = array_filter([$old_trx['wallet_id'], $update_data['wallet_id']]);
        foreach (array_unique($wallet_ids) as $wid) {
            $this->wallet->recalculate_balance($wid);
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function delete($id) {
        $trx = $this->db->get_where('inv_entity_transactions', array('id' => $id))->row_array();
        if (!$trx) return FALSE;

        $ids_to_process = [$id];
        $trxs_to_process = [$trx];

        // 1. Check direct linked transaction
        if (!empty($trx['linked_transaction_id'])) {
            $linked = $this->db->get_where('inv_entity_transactions', array('id' => $trx['linked_transaction_id']))->row_array();
            if ($linked) {
                $ids_to_process[] = $linked['id'];
                $trxs_to_process[] = $linked;
            }
        }

        // 2. Check reverse linked transactions (where this transaction is the 'linked_transaction_id')
        $reverse = $this->db->get_where('inv_entity_transactions', array('linked_transaction_id' => $id))->result_array();
        foreach ($reverse as $rev) {
            if (!in_array($rev['id'], $ids_to_process)) {
                 $ids_to_process[] = $rev['id'];
                 $trxs_to_process[] = $rev;
            }
        }

        $this->db->trans_start();

        // Bulk delete
        if (!empty($ids_to_process)) {
            $this->db->where_in('id', $ids_to_process);
            $this->db->delete('inv_entity_transactions');
        }

        // Recalculate balances
        $this->load->model('Entity_model', 'entity');
        $this->load->model('Wallet_model', 'wallet');

        // Use array_unique for entity_ids and wallet_ids to avoid redundant calculations
        $entity_ids = [];
        $wallet_ids = [];

        foreach ($trxs_to_process as $t) {
            if (!empty($t['entity_id'])) $entity_ids[] = $t['entity_id'];
            if (!empty($t['wallet_id'])) $wallet_ids[] = $t['wallet_id'];
        }

        foreach (array_unique($entity_ids) as $eid) {
            $this->entity->recalculate_balance($eid);
        }
        foreach (array_unique($wallet_ids) as $wid) {
            $this->wallet->recalculate_balance($wid);
        }
        
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function get_upcoming_debt($entity_id) {
        $this->db->select_sum('amount');
        $this->db->where('entity_id', $entity_id);
        $this->db->where('amount >', 0); // Only debts
        $this->db->where('due_date >', date('Y-m-d'));
        $query = $this->db->get('inv_entity_transactions');
        return (float) $query->row()->amount;
    }
}
