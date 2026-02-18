<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Entity_model extends CI_Model {

    public function get_all() {
        $this->db->order_by('name', 'ASC');
        return $this->db->get('inv_entities')->result_array();
    }

    public function get_by_id($id) {
        return $this->db->get_where('inv_entities', ['id' => $id])->row_array();
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

    public function recalculate_balance($entity_id) {
        $this->db->select_sum('amount');
        $this->db->from('inv_entity_transactions');
        $this->db->where('entity_id', $entity_id);
        $total = $this->db->get()->row()->amount ?? 0;
        
        $this->db->where('id', $entity_id);
        return $this->db->update('inv_entities', ['balance' => $total]);
    }
}
