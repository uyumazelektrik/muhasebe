<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Personnel extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Attendance_model', 'attendance');
        
        // Prevent non-admin users from accessing this controller
        if (current_role() !== 'admin') {
            redirect(site_url('dashboard'));
        }
    }

    public function index() {
        $data['page_title'] = 'Personel İşlemleri';
        
        // Get last 20 attendance logs
        $filters = [];
        if (current_role() !== 'admin') {
            $filters['user_id'] = $_SESSION['user_id'];
        }
        $data['logs'] = $this->attendance->get_logs($filters, 10, 0);
        
        // Get all users for the form dropdown
        $data['all_users'] = $this->attendance->get_all_users();
        
        // Get default shift for form defaults
        $default_shift = $this->db->order_by('id', 'ASC')->limit(1)->get('shifts')->row_array();
        $data['default_clock_in'] = $default_shift ? substr($default_shift['start_time'], 0, 5) : '09:00';
        $data['default_clock_out'] = $default_shift ? substr($default_shift['end_time'], 0, 5) : '18:00';
        
        $this->load->view('layout/header', $data);
        $this->load->view('personnel/index', $data);
        $this->load->view('layout/footer');
    }
    public function api_get_logs() {
        $filters = [];
        if (current_role() !== 'admin') {
            $filters['user_id'] = $_SESSION['user_id'];
        }
        $data['logs'] = $this->attendance->get_logs($filters, 10, 0);
        $this->load->view('personnel/_logs_list', $data);
    }
}
