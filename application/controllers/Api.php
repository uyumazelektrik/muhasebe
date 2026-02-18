<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Transaction_model', 'transaction');
    }

    public function delete_transaction() {
        header('Content-Type: application/json');
        
        $transaction_id = $this->input->post('transaction_id');
        
        if (empty($transaction_id)) {
            echo json_encode(['success' => false, 'error' => 'ID geçersiz']);
            return;
        }
        
        $this->load->model('Invoice_model', 'invoice');
        $this->load->model('Entity_model', 'entity');
        
        // Get transaction details
        $transaction = $this->db->get_where('inv_entity_transactions', ['id' => $transaction_id])->row_array();
        
        if (!$transaction) {
            echo json_encode(['success' => false, 'error' => 'İşlem bulunamadı']);
            return;
        }

        $entity_id = $transaction['entity_id'];
        $type = $transaction['type'];

        // If it's a 'fatura' or 'fis', use the comprehensive deletion logic
        if (in_array($type, ['fatura', 'fis', 'invoice', 'purchase', 'sale'])) {
            $success = $this->invoice->delete_invoice($transaction_id);
        } else {
            // Use Transaction_model to ensure balance and wallet reversal
            $success = $this->transaction->delete($transaction_id);
        }
        
        if ($success) {
            // Fetch updated balance for the UI
            $updated_entity = $this->db->get_where('inv_entities', ['id' => $entity_id])->row_array();
            $new_balance = $updated_entity ? $updated_entity['balance'] : 0;
            
            echo json_encode([
                'success' => true, 
                'message' => 'İşlem başarıyla silindi',
                'new_balance' => $new_balance
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'İşlem silinemedi']);
        }
    }

    public function get_invoices() {
        header('Content-Type: application/json');
        
        // Get filters
        $invoice_no = $this->input->get('invoice_no');
        $entity_id = $this->input->get('entity_id');
        $type = $this->input->get('type');
        $status = $this->input->get('status');
        $date_from = $this->input->get('date_from');
        $date_to = $this->input->get('date_to');
        $limit = $this->input->get('limit') ?: 25;
        $page = $this->input->get('page') ?: 1;
        $offset = ($page - 1) * $limit;
        
        // Build query - get transactions with multiple types
        $this->db->select('t.*, e.name as entity_name');
        $this->db->from('inv_entity_transactions t');
        $this->db->join('inv_entities e', 't.entity_id = e.id', 'left');
        $this->db->where_in('t.type', ['fatura', 'fis']); // Only invoices and receipts, no payments
        
        if ($invoice_no) {
            $this->db->like('t.document_no', $invoice_no);
        }
        if ($entity_id) {
            $this->db->where('t.entity_id', $entity_id);
        }
        // Type filter: 'purchase' = amount < 0 (we bought), 'sale' = amount > 0 (we sold)
        if ($type === 'purchase') {
            $this->db->where('t.amount <', 0);
        } elseif ($type === 'sale') {
            $this->db->where('t.amount >', 0);
        }
        // Note: status filter disabled - column may not exist in table
        // if ($status) {
        //     $this->db->where('t.status', $status);
        // }
        if ($date_from) {
            $this->db->where('t.transaction_date >=', $date_from);
        }
        if ($date_to) {
            $this->db->where('t.transaction_date <=', $date_to);
        }
        
        // Count total
        $total = $this->db->count_all_results('', false);
        
        // Get paginated results
        $this->db->order_by('t.transaction_date', 'DESC');
        $this->db->order_by('t.id', 'DESC');
        $this->db->limit($limit, $offset);
        $invoices = $this->db->get()->result_array();
        
        echo json_encode([
            'status' => 'success',
            'data' => $invoices,
            'pagination' => [
                'total' => $total,
                'current_page' => (int)$page,
                'total_pages' => ceil($total / $limit),
                'per_page' => (int)$limit
            ]
        ]);
    }
}
