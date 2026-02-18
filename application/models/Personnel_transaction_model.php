<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Personnel_transaction_model extends CI_Model {

    public function get_user_transactions($user_id, $filters = [], $limit = 20, $offset = 0) {
        $this->db->select('*');
        $this->db->from('transactions');
        $this->db->where('user_id', $user_id);

        if (!empty($filters['month']) && !empty($filters['year'])) {
            $startDate = sprintf('%04d-%02d-01', $filters['year'], $filters['month']);
            $endDate = date('Y-m-t', strtotime($startDate));
            $this->db->where('date >=', $startDate);
            $this->db->where('date <=', $endDate);
        } elseif (!empty($filters['year'])) {
            $this->db->where('date >=', $filters['year'] . '-01-01');
            $this->db->where('date <=', $filters['year'] . '-12-31');
        }

        $this->db->order_by('date', 'DESC');
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit, $offset);

        return $this->db->get()->result_array();
    }

    public function count_user_transactions($user_id, $filters = []) {
        $this->db->from('transactions');
        $this->db->where('user_id', $user_id);

        if (!empty($filters['month']) && !empty($filters['year'])) {
            $startDate = sprintf('%04d-%02d-01', $filters['year'], $filters['month']);
            $endDate = date('Y-m-t', strtotime($startDate));
            $this->db->where('date >=', $startDate);
            $this->db->where('date <=', $endDate);
        } elseif (!empty($filters['year'])) {
            $this->db->where('date >=', $filters['year'] . '-01-01');
            $this->db->where('date <=', $filters['year'] . '-12-31');
        }

        return $this->db->count_all_results();
    }

    public function get_totals($user_id, $filters = []) {
        $this->db->select('type, SUM(amount) as total');
        $this->db->from('transactions');
        $this->db->where('user_id', $user_id);

        if (!empty($filters['month']) && !empty($filters['year'])) {
            $startDate = sprintf('%04d-%02d-01', $filters['year'], $filters['month']);
            $endDate = date('Y-m-t', strtotime($startDate));
            $this->db->where('date >=', $startDate);
            $this->db->where('date <=', $endDate);
        }

        $this->db->group_by('type');
        $results = $this->db->get()->result_array();

        $totals = ['payment' => 0, 'advance' => 0, 'expense' => 0, 'salary_accrual' => 0];
        foreach ($results as $row) {
            $totals[$row['type']] = floatval($row['total']);
        }
        return $totals;
    }

    public function get_balance($user_id, $end_date = null) {
        $this->db->select("SUM(CASE WHEN affects_balance<>0 AND type='expense' THEN amount * affects_balance ELSE 0 END) AS bal");
        $this->db->from('transactions');
        $this->db->where('user_id', $user_id);
        if ($end_date) {
            $this->db->where('date <=', $end_date);
        }
        $row = $this->db->get()->row_array();
        return floatval($row['bal'] ?? 0);
    }
}
