<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reports extends MY_Controller {

    public function __construct() {
        parent::__construct();
        if (current_role() !== 'admin') {
            redirect('dashboard');
        }
    }

    public function index() {
        $data['page_title'] = 'Verim Analizi';
        
        $this->load->view('layout/header', $data);
        $this->load->view('reports/index', $data);
        $this->load->view('layout/footer');
    }
}
