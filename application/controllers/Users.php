<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('User_model', 'user_model');
        // Ensure only admin can access personnel management
        if (current_role() !== 'admin') {
            redirect(site_url('dashboard'));
        }
    }

    public function index() {
        $today = date('Y-m-d');
        $data['users'] = $this->user_model->get_users_with_attendance($today);
        $data['page_title'] = "Personel Yönetimi";

        $this->load->view('layout/header', $data);
        $this->load->view('users', $data);
        $this->load->view('layout/footer');
    }

    public function api_add() {
        $data = [
            'full_name' => $this->input->post('full_name'),
            'username' => $this->input->post('username'),
            'password' => $this->input->post('password'),
            'role' => $this->input->post('role'),
            'hourly_rate' => $this->input->post('hourly_rate')
        ];

        if ($this->user_model->create_user($data)) {
            redirect(site_url('users'));
        } else {
            show_error('Kullanıcı eklenirken bir hata oluştu.');
        }
    }

    public function api_edit() {
        $id = $this->input->post('user_id');
        $data = [
            'full_name' => $this->input->post('full_name'),
            'username' => $this->input->post('username'),
            'role' => $this->input->post('role'),
            'hourly_rate' => $this->input->post('hourly_rate')
        ];

        if ($this->input->post('password')) {
            $data['password'] = $this->input->post('password');
        }

        if ($this->user_model->update_user($id, $data)) {
            redirect(site_url('users'));
        } else {
            show_error('Kullanıcı güncellenirken bir hata oluştu.');
        }
    }

    public function api_delete() {
        $id = $this->input->post('user_id');
        if ($this->user_model->delete_user($id)) {
            redirect(site_url('users'));
        } else {
            show_error('Kullanıcı silinirken bir hata oluştu.');
        }
    }
}
