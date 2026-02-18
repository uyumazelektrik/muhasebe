<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Attendance_model extends CI_Model {

    public function get_logs($filters = array(), $limit = 20, $offset = 0) {
        $this->db->select('a.*, u.full_name, s.name as shift_name');
        $this->db->from('attendance a');
        $this->db->join('users u', 'a.user_id = u.id');
        $this->db->join('shifts s', 'a.shift_id = s.id', 'left');

        if (!empty($filters['user_id'])) {
            $this->db->where('a.user_id', $filters['user_id']);
        }
        if (!empty($filters['start_date'])) {
            $this->db->where('a.date >=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $this->db->where('a.date <=', $filters['end_date']);
        }

        $this->db->order_by('a.date', 'DESC');
        $this->db->order_by('a.clock_in', 'DESC');
        $this->db->limit($limit, $offset);

        return $this->db->get()->result_array();
    }

    public function count_logs($filters = array()) {
        $this->db->from('attendance a');
        if (!empty($filters['user_id'])) {
            $this->db->where('a.user_id', $filters['user_id']);
        }
        if (!empty($filters['start_date'])) {
            $this->db->where('a.date >=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $this->db->where('a.date <=', $filters['end_date']);
        }
        return $this->db->count_all_results();
    }

    public function get_all_users() {
        return $this->db->select('id, full_name')->order_by('full_name', 'ASC')->get('users')->result_array();
    }
}
