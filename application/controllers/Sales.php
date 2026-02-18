<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sales extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Product_model', 'product');
        $this->load->model('Wallet_model', 'wallet');
        $this->load->model('Customer_model', 'customer');
        $this->load->model('Transaction_model', 'transaction');
    }

    public function pos() {
        $data['products'] = $this->product->get_active_for_pos();
        $data['wallets'] = $this->wallet->get_active('TL');
        $data['entities'] = $this->customer->get_all(array(), 1000);
        $data['page_title'] = "Hızlı Peşin Satış (POS)";
        
        $this->load->view('layout/header', $data);
        $this->load->view('sales/pos', $data);
        $this->load->view('layout/footer');
    }

    public function api_save_sale() {
        if ($this->input->method() !== 'post') {
            return $this->output->set_content_type('application/json')->set_output(json_encode(['status'=>'error', 'message'=>'Method not allowed']));
        }

        $input = json_decode($this->input->raw_input_stream, true);
        $wallet_id = $input['wallet_id'] ?? null;
        $customer_id = $input['customer_id'] ?? null;
        $payment_type_input = $input['payment_type'] ?? 'CASH'; // CASH, CREDIT_CARD, CREDIT
        $items = $input['items'] ?? [];

        if ($customer_id === '0' || $customer_id === 0) $customer_id = null;

        $this->load->model('Invoice_model', 'invoice');

        try {
            if (empty($items)) throw new Exception('Eksik bilgi: Sepet boş.');

            // 1. Müşteri (Cari) Kontrolü
            if (!$customer_id) {
                // Veresiye satışta cari zorunludur
                if ($payment_type_input === 'CREDIT') {
                    throw new Exception('Veresiye satış işlemi için lütfen bir cari seçiniz.');
                }

                $default = $this->db->get_where('inv_entities', ['name' => 'Peşin Müşteri'])->row_array();
                if ($default) {
                    $customer_id = $default['id'];
                } else {
                    $this->db->insert('inv_entities', ['name' => 'Peşin Müşteri', 'type' => 'customer', 'balance' => 0]);
                    $customer_id = $this->db->insert_id();
                }
            }

            // Wallet Kontrolü (Veresiye değilse zorunlu)
            if ($payment_type_input !== 'CREDIT' && !$wallet_id) {
                throw new Exception('Ödeme işlemi için kasa/banka seçilmelidir.');
            }

            $grandTotal = 0;
            foreach ($items as $item) $grandTotal += ($item['price'] * $item['qty']);

            $document_no = 'POS-' . date('YmdHis');

            // Determine payment status and type
            $payment_status = 'paid';
            $db_payment_type = 'cash_bank'; // Default for paid
            
            if ($payment_type_input === 'CREDIT') {
                $payment_status = 'unpaid';
                $db_payment_type = 'veresiye'; // Just a label, logic uses status
                $wallet_id = null; // No wallet involvement
            } elseif ($payment_type_input === 'CREDIT_CARD') {
                // Still considered cash_bank in system currently, or explicitly card if system supports it
                // For now keeping logic as cash_bank but can store description details if needed
                $db_payment_type = 'cash_bank';
            }

            // 2. Yeni Sisteme Kayıt (Header & Finance)
            $invoice_data = [
                'invoice_no' => $document_no,
                'entity_id' => $customer_id,
                'type' => 'fis',
                'invoice_date' => date('Y-m-d'),
                'total_amount' => $grandTotal,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'net_amount' => $grandTotal,
                'payment_status' => $payment_status,
                'payment_type' => $db_payment_type,
                'wallet_id' => $wallet_id,
                'status' => 'finalized'
            ];

            $invoice_id = $this->invoice->create_invoice($invoice_data);

            if (!$invoice_id) throw new Exception('Fatura başlığı oluşturulamadı.');

            // 3. Kalemleri Kaydet ve Stok Düş
            foreach ($items as $item) {
                $product = $this->product->get_by_id($item['id']);
                
                $item_data = [
                    'invoice_id' => $invoice_id,
                    'product_id' => $item['id'],
                    'item_type' => 'stok',
                    'description' => $product['name'],
                    'unit' => $product['unit'] ?? 'Adet',
                    'quantity' => $item['qty'],
                    'unit_price' => $item['price'],
                    'discount_rate' => 0,
                    'discount_amount' => 0,
                    'tax_rate' => 0,
                    'tax_amount' => 0,
                    'total_amount' => ($item['price'] * $item['qty'])
                ];
                $this->invoice->add_invoice_item($item_data);

                // Stok Güncelleme
                $qty_change = -$item['qty'];
                $this->db->set('stock_quantity', 'stock_quantity + ' . (float)$qty_change, FALSE);
                $this->db->where('id', $item['id']);
                $this->db->update('inv_products');
            }

            echo json_encode(['status' => 'success', 'document_no' => $document_no, 'invoice_id' => $invoice_id]);

        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function api_get_products() {
        header('Content-Type: application/json');
        try {
            $products = $this->product->get_active_for_pos();
            echo json_encode(['status' => 'success', 'products' => $products]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function api_delete_transaction() {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        $id = $data['id'] ?? 0;

        if ($id) {
            $this->load->model('Transaction_model', 'transaction');
            if ($this->transaction->delete($id)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Silme başarısız.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'ID geçersiz.']);
        }
    }
}
