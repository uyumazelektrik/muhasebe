<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Customer_model', 'customer');
        $this->load->model('Product_model', 'product');
        $this->load->model('Transaction_model', 'transaction');
        $this->load->model('Wallet_model', 'wallet');
    }

    public function index() {
        // Load common data
        $data['product_count'] = $this->product->count_all();
        $data['page_title'] = "Yönetim Paneli";

        // Admin-only data
        if (current_role() === 'admin') {
            $data['stats'] = $this->customer->get_stats();
            $data['total_wallets'] = array_sum(array_column($this->wallet->get_all(), 'balance'));
            $data['recent_transactions'] = $this->transaction->get_all([], 5);
        } else {
            // Nullify or empty for non-admins to be safe
            $data['stats'] = ['total_receivables' => 0, 'total_debt' => 0];
            $data['total_wallets'] = 0;
            $data['recent_transactions'] = [];
            
            // For staff: Load last 10 attendance records
            $this->load->model('Attendance_model', 'attendance');
            $data['attendance_logs'] = $this->attendance->get_logs(['user_id' => current_user_id()], 10);
        }
        
        $this->load->view('layout/header', $data);
        $this->load->view('dashboard', $data);
        $this->load->view('layout/footer');
    }
}
