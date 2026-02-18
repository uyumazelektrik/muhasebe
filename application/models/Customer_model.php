<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Customer_model extends CI_Model {



    public function get_all($filters = array(), $limit = 50, $offset = 0) {
        $this->db->select('*');
        $this->db->from('inv_entities');

        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('name', $filters['search']);
            $this->db->or_like('phone', $filters['search']);
            $this->db->or_like('email', $filters['search']);
            $this->db->group_end();
        }

        if (!empty($filters['type'])) {
            $this->db->where('type', $filters['type']);
        }

        if (isset($filters['balanceStatus'])) {
            if ($filters['balanceStatus'] == 'debtor') {
                $this->db->where('balance <', 0);
            } elseif ($filters['balanceStatus'] == 'creditor') {
                $this->db->where('balance >', 0);
            }
        }

        $this->db->order_by('name', 'ASC');
        $this->db->limit($limit, $offset);
        
        return $this->db->get()->result_array();
    }

    public function count_all($filters = array()) {
        $this->db->from('inv_entities');
        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('name', $filters['search']);
            $this->db->group_end();
        }
        if (!empty($filters['type'])) {
            $this->db->where('type', $filters['type']);
        }
        return $this->db->count_all_results();
    }

    public function get_by_id($id) {
        return $this->db->get_where('inv_entities', array('id' => $id))->row_array();
    }

    public function create($data) {
        $this->db->insert('inv_entities', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('inv_entities', $data);
    }

    public function delete($id) {
        $this->db->where('id', $id);
        return $this->db->delete('inv_entities');
    }

    public function get_stats() {
        $stats = array(
            'total_receivables' => 0,
            'total_debt' => 0,
            'net_balance' => 0
        );

        $query = $this->db->select('SUM(CASE WHEN balance > 0 THEN balance ELSE 0 END) as total_receivables, 
                                   SUM(CASE WHEN balance < 0 THEN ABS(balance) ELSE 0 END) as total_debt')
                          ->from('inv_entities')
                          ->get();
        
        $row = $query->row_array();
        $stats['total_receivables'] = (float)$row['total_receivables'];
        $stats['total_debt'] = (float)$row['total_debt'];
        $stats['net_balance'] = $stats['total_receivables'] - $stats['total_debt'];

        return $stats;
    }

    public function get_access_token($id) {
        // Auto-migrate if column missing
        if (!$this->db->field_exists('access_token', 'inv_entities')) {
            $this->load->dbforge();
            $fields = [
                'access_token' => [
                    'type' => 'VARCHAR',
                    'constraint' => 64,
                    'null' => TRUE,
                    'unique' => TRUE
                ]
            ];
            $this->dbforge->add_column('inv_entities', $fields);
        }

        $entity = $this->get_by_id($id);
        if (!$entity) return null;

        if (empty($entity['access_token'])) {
            $token = bin2hex(random_bytes(32));
            $this->update($id, ['access_token' => $token]);
            return $token;
        }

        return $entity['access_token'];
    }

    public function get_by_token($token) {
        if (!$this->db->field_exists('access_token', 'inv_entities')) return null;
        return $this->db->get_where('inv_entities', ['access_token' => $token])->row_array();
    }
}
