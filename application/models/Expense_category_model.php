<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Expense_category_model extends CI_Model {

    public function get_all() {
        return $this->db->order_by('name', 'ASC')->get('inv_expense_categories')->result_array();
    }

    public function create($data) {
        return $this->db->insert('inv_expense_categories', $data);
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('inv_expense_categories', $data);
    }

    public function delete($id) {
        $this->db->where('id', $id);
        return $this->db->delete('inv_expense_categories');
    }
}
