<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {

    public function get_users_with_attendance($date) {
        $this->db->select('u.*, a.status as today_status, a.clock_in, a.clock_out');
        $this->db->from('users u');
        
        // Join with attendance for the specific date
        // Using a subquery to get the latest attendance record for each user today if multiple exist
        $subquery = "SELECT a1.* FROM attendance a1
                     INNER JOIN (
                         SELECT user_id, MAX(id) as max_id 
                         FROM attendance 
                         WHERE date = " . $this->db->escape($date) . " 
                         GROUP BY user_id
                     ) a2 ON a1.id = a2.max_id";
        
        $this->db->join("($subquery) a", 'u.id = a.user_id', 'left');
        $this->db->order_by('u.full_name', 'ASC');
        
        return $this->db->get()->result_array();
    }

    public function create_user($data) {
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        return $this->db->insert('users', $data);
    }

    public function update_user($id, $data) {
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['password']);
        }
        $this->db->where('id', $id);
        return $this->db->update('users', $data);
    }

    public function delete_user($id) {
        $this->db->where('id', $id);
        return $this->db->delete('users');
    }

    public function get_all_users() {
        $this->db->order_by('full_name', 'ASC');
        return $this->db->get('users')->result_array();
    }
}
