<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('User_model', 'user_model');
    }

    public function index() {
        // Auto-seed if no users exist
        if ($this->db->count_all('users') == 0) {
            $this->user_model->create_user([
                'username' => 'admin',
                'password' => 'admin123',
                'full_name' => 'Sistem Yöneticisi',
                'role' => 'admin',
                'hourly_rate' => 0
            ]);
            $this->session->set_flashdata('error', 'İlk kurulum yapıldı. Kullanıcı: admin, Şifre: admin123');
        }

        if (current_role() !== 'guest' && current_role() !== null) {
             // Already logged in
             redirect('dashboard');
        }
        $this->load->view('auth/login');
    }

    public function login() {
        $username = $this->input->post('username');
        $password = $this->input->post('password');

        $user = $this->db->get_where('users', ['username' => $username])->row_array();

        if ($user && password_verify($password, $user['password'])) {
            $session_data = [
                'user_id' => $user['id'],
                'username' => $user['username'],
                'full_name' => $user['full_name'],
                'role' => $user['role'],
                'logged_in' => TRUE
            ];
            $this->session->set_userdata($session_data);
            redirect('dashboard');
        } else {
            $this->session->set_flashdata('error', 'Kullanıcı adı veya şifre hatalı.');
            redirect('login');
        }
    }

    public function logout() {
        $this->session->sess_destroy();
        redirect('login');
    }
}
