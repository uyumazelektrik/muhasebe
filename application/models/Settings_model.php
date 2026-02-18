<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Settings_model extends CI_Model {

    // ==================== SETTINGS ====================
    
    public function get_all_settings() {
        return $this->db->get('settings')->result_array();
    }
    
    public function get_setting($key) {
        $row = $this->db->where('setting_key', $key)->get('settings')->row_array();
        return $row ? $row['setting_value'] : null;
    }
    
    public function update_setting($key, $value) {
        $exists = $this->db->where('setting_key', $key)->count_all_results('settings');
        
        if ($exists) {
            $this->db->where('setting_key', $key);
            return $this->db->update('settings', ['setting_value' => $value]);
        } else {
            return $this->db->insert('settings', [
                'setting_key' => $key,
                'setting_value' => $value
            ]);
        }
    }
    
    public function delete_setting($key) {
        $this->db->where('setting_key', $key);
        return $this->db->delete('settings');
    }

    // ==================== SHIFTS ====================
    
    public function get_all_shifts() {
        return $this->db->order_by('id', 'ASC')->get('shifts')->result_array();
    }
    
    public function get_shift($id) {
        return $this->db->where('id', $id)->get('shifts')->row_array();
    }
    
    public function create_shift($data) {
        $this->db->insert('shifts', $data);
        return $this->db->insert_id();
    }
    
    public function update_shift($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('shifts', $data);
    }
    
    public function delete_shift($id) {
        // Check if shift is in use
        $in_use = $this->db->where('shift_id', $id)->count_all_results('attendance');
        if ($in_use > 0) {
            return false; // Cannot delete, in use
        }
        
        $this->db->where('id', $id);
        return $this->db->delete('shifts');
    }
}
