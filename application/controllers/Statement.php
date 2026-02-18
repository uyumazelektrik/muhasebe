<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Statement extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Customer_model', 'customer');
        // Note: No Auth check here as this is a public controller protected by token
    }

    public function view($token = null) {
        if (!$token) show_404();

        // Validate Token
        $entity = $this->customer->get_by_token($token);
        if (!$entity) {
            show_error('Geçersiz veya süresi dolmuş bağlantı.', 403, 'Erişim Hatası');
        }

        // Get parameters from GET (for public links, GET is better than POST for sharing filtered views)
        // Default: Current Year or Last 3 months? Let's do current month start to now as default, 
        // OR better: All unpaid open balance history? 
        // For a statement, usually "This Year" or "Last 30 Days". Let's default to "Since Beginning of Year" to show full context.
        $start_date = $this->input->get('start_date') ?: date('Y-01-01'); 
        $end_date = $this->input->get('end_date') ?: date('Y-m-d');
        $type = $this->input->get('type') ?: 'detailed';

        $id = $entity['id'];
        $data['entity'] = $entity;

        // 1. Calculate Opening Balance
        $this->db->select_sum('amount');
        $this->db->where('entity_id', $id);
        $this->db->where('transaction_date <', $start_date);
        $query = $this->db->get('inv_entity_transactions');
        $data['opening_balance'] = floatval($query->row()->amount);

        // 2. Get Transactions in Range
        $this->db->from('inv_entity_transactions');
        $this->db->where('entity_id', $id);
        $this->db->where('transaction_date >=', $start_date);
        $this->db->where('transaction_date <=', $end_date);
        $this->db->order_by('transaction_date', 'ASC');
        $this->db->order_by('id', 'ASC');
        $transactions = $this->db->get()->result_array();

        // Populate invoice items if type is detailed
        if ($type === 'detailed') {
            foreach ($transactions as &$t) {
                if (in_array($t['type'], ['fatura', 'invoice', 'sale', 'purchase']) && !empty($t['document_no'])) {
                    $invoice = $this->db->get_where('inv_invoices', ['invoice_no' => $t['document_no']])->row_array();
                    if ($invoice) {
                        $this->db->select('i.*, p.name as product_name, p.unit');
                        $this->db->from('inv_invoice_items i');
                        $this->db->join('inv_products p', 'i.product_id = p.id', 'left');
                        $this->db->where('i.invoice_id', $invoice['id']);
                        $t['items'] = $this->db->get()->result_array();
                    }
                }
            }
        }
        $data['transactions'] = $transactions;

        $data['start_date'] = $start_date;
        $data['end_date'] = $end_date;
        $data['type'] = $type;
        $data['is_public'] = true; // Flag to hide internal/admin specific UI elements if any

        // Reuse the existing view
        $this->load->view('customers/statement', $data);
    }
}
