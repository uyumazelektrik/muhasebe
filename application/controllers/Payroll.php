<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Payroll extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Payroll_model', 'payroll');
    }

    public function index() {
        if (current_role() === 'personel') {
            redirect(site_url('dashboard'));
        }
        $month = $this->input->get('month') ?: date('m');
        $year = $this->input->get('year') ?: date('Y');

        $data['payrollData'] = $this->payroll->get_payroll_data($month, $year);
        $data['month'] = $month;
        $data['year'] = $year;
        $data['years'] = range(date('Y') - 1, date('Y') + 1);
        $data['page_title'] = "Maaş Bordrosu";

        $this->load->view('layout/header', $data);
        $this->load->view('payroll', $data);
        $this->load->view('layout/footer');
    }

    public function my_payroll() {
        if (!current_user_id()) {
            redirect(site_url('login'));
        }

        $userId = current_user_id();
        $month = $this->input->get('month') ?: date('m');
        $year = $this->input->get('year') ?: date('Y');

        $data = $this->payroll->get_user_payroll_details($userId, $month, $year);
        if (!$data) {
           show_error('Bordro bilgisi alınamadı.');
        }

        $data['month'] = $month;
        $data['year'] = $year;
        $data['years'] = range(date('Y') - 1, date('Y') + 1);
        $data['page_title'] = "Maaş Hak Edişim";

        $this->load->view('layout/header', $data);
        $this->load->view('my_payroll', $data);
        $this->load->view('layout/footer');
    }

    public function api_get_user_payroll() {
        if (current_role() === 'personel') {
             // Personnel can only see their own
             if ($this->input->get('user_id') != current_user_id()) {
                 echo json_encode(['error' => 'Yetkisiz işlem.']);
                 return;
             }
        }

        $userId = $this->input->get('user_id');
        $month = $this->input->get('month') ?: date('m');
        $year = $this->input->get('year') ?: date('Y');

        if (!$userId) {
            echo json_encode(['error' => 'Geçersiz kullanıcı.']);
            return;
        }

        $result = $this->payroll->get_user_payroll_details($userId, $month, $year);
        
        if (!$result) {
            echo json_encode(['error' => 'Bordro bilgisi bulunamadı.']);
            return;
        }

        echo json_encode($result);
    }
}
