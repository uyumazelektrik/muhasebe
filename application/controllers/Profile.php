<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Profile extends MY_Controller {

    public function __construct() {
        parent::__construct();
        if (!current_user_id()) {
            redirect(site_url('login'));
        }
    }

    public function index() {
        $userId = current_user_id();
        $data['user'] = $this->db->where('id', $userId)->get('users')->row_array();
        
        // Get some basic stats
        $data['page_title'] = "Profilim & Maaş";

        // Attendance Summary (Last 5)
        $this->load->model('Attendance_model');
        $data['recent_attendance'] = $this->Attendance_model->get_logs(['user_id' => $userId], 5);

        $this->load->view('layout/header', $data);
        $this->load->view('profile', $data);
        $this->load->view('layout/footer');
    }

    public function update_password() {
        $userId = current_user_id();
        $currentPass = $this->input->post('current_password');
        $newPass = $this->input->post('new_password');
        $confirmPass = $this->input->post('confirm_password');

        if ($newPass !== $confirmPass) {
            echo json_encode(['status' => 'error', 'message' => 'Yeni şifreler uyuşmuyor.']);
            return;
        }

        $user = $this->db->where('id', $userId)->get('users')->row_array();
        
        // In a real app verify hash, for now straightforward comparison if plain, or hash verify
        // Assuming simple storage for this legacy/demo setup or verify against stored
        if ($user['password'] !== $currentPass) { // ideally password_verify if hashed
             echo json_encode(['status' => 'error', 'message' => 'Mevcut şifre yanlış.']);
             return;
        }

        $this->db->where('id', $userId)->update('users', ['password' => $newPass]);
        echo json_encode(['status' => 'success', 'message' => 'Şifreniz başarıyla güncellendi.']);
    }
}
