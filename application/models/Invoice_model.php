<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Invoice_model extends CI_Model {
    // Model updated to filter out collection/payment records securely

    public function get_invoices($filters = [], $limit = 25, $offset = 0) {
        $this->db->select('t.*, e.name as entity_name, e.type as entity_type');
        $this->db->from('inv_entity_transactions t');
        $this->db->join('inv_entities e', 't.entity_id = e.id', 'left');
        $this->db->where_in('t.type', ['fatura', 'fis']);
        $this->db->where_not_in('t.type', ['tahsilat', 'odeme']);
        
        // Filters
        if (!empty($filters['invoice_no'])) {
            $this->db->like('t.document_no', $filters['invoice_no']);
        }
        
        if (!empty($filters['entity_id'])) {
            $this->db->where('t.entity_id', $filters['entity_id']);
        }
        
        if (!empty($filters['date_from'])) {
            $this->db->where('t.transaction_date >=', $filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $this->db->where('t.transaction_date <=', $filters['date_to']);
        }
        
        $this->db->order_by('t.transaction_date', 'DESC');
        $this->db->order_by('t.id', 'DESC');
        $this->db->limit($limit, $offset);
        
        return $this->db->get()->result_array();
    }

    public function count_invoices($filters = []) {
        $this->db->from('inv_entity_transactions t');
        $this->db->where_in('t.type', ['fatura', 'fis']);
        $this->db->where_not_in('t.type', ['tahsilat', 'odeme']);
        
        if (!empty($filters['invoice_no'])) {
            $this->db->like('t.document_no', $filters['invoice_no']);
        }
        
        if (!empty($filters['entity_id'])) {
            $this->db->where('t.entity_id', $filters['entity_id']);
        }
        
        if (!empty($filters['date_from'])) {
            $this->db->where('t.transaction_date >=', $filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $this->db->where('t.transaction_date <=', $filters['date_to']);
        }
        
        return $this->db->count_all_results();
    }

    public function get_invoice_by_id($id) {
        // 1. Önce bu ID bir transaksiyon ID'si mi bak (Liste genellikle buradaki ID'leri kullanır)
        $this->db->select('t.*, e.name as entity_name, e.type as entity_type, e.tax_id, e.phone, e.address, w.name as wallet_name');
        $this->db->from('inv_entity_transactions t');
        $this->db->join('inv_entities e', 't.entity_id = e.id', 'left');
        $this->db->join('inv_wallets w', 't.wallet_id = w.id', 'left');
        $this->db->where('t.id', $id);
        $trx = $this->db->get()->row_array();
        
        if ($trx) {
            // Eğer bu bir fatura/fiş ise, detaylı tabloya (inv_invoices) bak
            if (in_array($trx['type'], ['fatura', 'fis'])) {
                $this->db->select('i.*, e.name as entity_name, e.type as entity_type, e.tax_id, e.phone, e.address, w.name as wallet_name, i.invoice_no as document_no, i.invoice_date as transaction_date, i.net_amount as amount');
                $this->db->from('inv_invoices i');
                $this->db->join('inv_entities e', 'i.entity_id = e.id', 'left');
                $this->db->join('inv_wallets w', 'i.wallet_id = w.id', 'left');
                $this->db->where('i.invoice_no', $trx['document_no']);
                $invoice = $this->db->get()->row_array();
                
                if ($invoice) {
                    // Detaylı fatura bulundu, ID'yi orijinal transaksiyon ID'si ile tutarlı kılmak için gerekirse sakla
                    $invoice['transaction_id'] = $trx['id'];
                    return $invoice;
                }
            }
            return $trx;
        }
        
        // 2. Transaksiyon bulunamadıysa direkt inv_invoices ID'si olarak dene
        $this->db->select('i.*, e.name as entity_name, e.type as entity_type, e.tax_id, e.phone, e.address, w.name as wallet_name, i.invoice_no as document_no, i.invoice_date as transaction_date, i.net_amount as amount');
        $this->db->from('inv_invoices i');
        $this->db->join('inv_entities e', 'i.entity_id = e.id', 'left');
        $this->db->join('inv_wallets w', 'i.wallet_id = w.id', 'left');
        $this->db->where('i.id', $id);
        
        return $this->db->get()->row_array();
    }

    public function get_invoice_items($document_no, $invoice_id = null) {
        if (empty($document_no) && empty($invoice_id)) {
            return [];
        }
        
        $document_no = trim($document_no);
        
        // Try by invoice_id first if provided
        if ($invoice_id) {
            $this->db->select('ii.*, COALESCE(ii.description, p.name) as product_name, p.barcode, p.stock_quantity, p.gorsel, COALESCE(ii.unit, p.unit) as unit, ec.name as expense_category_name', FALSE);
            $this->db->from('inv_invoice_items ii');
            $this->db->join('inv_products p', 'ii.product_id = p.id', 'left');
            $this->db->join('inv_expense_categories ec', 'ii.expense_category_id = ec.id', 'left');
            $this->db->where('ii.invoice_id', $invoice_id);
            $this->db->order_by('ii.id', 'ASC');
            
            $results = $this->db->get()->result_array();
            if (count($results) > 0) return $results;
        }

        // Then try by invoice_no
        if (!empty($document_no)) {
            $invoice = $this->db->get_where('inv_invoices', ['invoice_no' => $document_no])->row_array();
            if ($invoice) {
                $this->db->select('ii.*, COALESCE(ii.description, p.name) as product_name, p.barcode, p.stock_quantity, p.gorsel, COALESCE(ii.unit, p.unit) as unit, ec.name as expense_category_name', FALSE);
                $this->db->from('inv_invoice_items ii');
                $this->db->join('inv_products p', 'ii.product_id = p.id', 'left');
                $this->db->join('inv_expense_categories ec', 'ii.expense_category_id = ec.id', 'left');
                $this->db->where('ii.invoice_id', $invoice['id']);
                $this->db->order_by('ii.id', 'ASC');
                
                return $this->db->get()->result_array();
            }
        }
        
        return [];
    }

    public function create_invoice($data) {
        $this->ensure_tables_exist();
        
        if (empty($data['entity_id'])) {
            log_message('error', 'Invoice creation failed: entity_id is empty');
            return false;
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        
        $this->db->insert('inv_invoices', $data);
        $insert_id = $this->db->insert_id();
        
        // --- 1. Transaction: Ana Hareket ---
        $amount = abs($data['net_amount']);
        $txn_type = '';
        $desc_prefix = '';
        $is_standalone_payment = in_array($data['type'], ['tahsilat', 'odeme', 'virman']);
        
        if ($data['type'] == 'purchase') {
            $amount = -$amount; // Alış = Borçlanma (-)
            $txn_type = 'fatura'; 
            $desc_prefix = 'Alış Faturası';
        } elseif ($data['type'] == 'sale') {
            // Satış = Alacaklanma (+)
            $txn_type = 'fatura';
            $desc_prefix = 'Satış Faturası';
        } elseif ($data['type'] == 'fis') {
            $txn_type = 'fis';
            $desc_prefix = 'Satış Fişi';
        } elseif ($data['type'] == 'tahsilat') {
            // Tahsilat = Müşteri ödeme yapar, bakiyesi azalır (alacaklanır) -> Negatif
            $amount = -$amount; 
            $txn_type = 'tahsilat';
            $desc_prefix = 'Tahsilat Makbuzu';
        } elseif ($data['type'] == 'odeme') {
            // Ödeme = Biz ödeme yaparız, borcumuz azalır -> Pozitif
            $amount = $amount;
            $txn_type = 'odeme';
            $desc_prefix = 'Ödeme Makbuzu';
        } elseif ($data['type'] == 'virman') {
            // Virman yönüne göre değişir ama genelde:
            // Ana entity için alacak (para çıkar) -> Negatif varsayalım veya formdan gelen işaret
            // Ancak api_transfer'de: Kaynak (Para Çıkan) = Negatif.
            // Burada tek bir fatura kaydı var. Eğer bu "Kaynak" tarafıysa negatif olmalı.
            $amount = -$amount; 
            $txn_type = 'virman';
            $desc_prefix = 'Virman İşlemi';
        } elseif ($data['type'] == 'borc_dekontu') {
            // Borç Dekontu = Cari Borçlanır (+). Satış gibi.
            $amount = $amount; // Pozitif
            $txn_type = 'borc_dekontu';
            $desc_prefix = 'Borç Dekontu';
        } elseif ($data['type'] == 'alacak_dekontu') {
            // Alacak Dekontu = Cari Alacaklanır (-). Alış gibi.
            $amount = -$amount; // Negatif
            $txn_type = 'alacak_dekontu';
            $desc_prefix = 'Alacak Dekontu';
        }
        
        $transaction = [
             'entity_id' => $data['entity_id'],
             'invoice_id' => $insert_id,
             'type' => $txn_type,
             'document_no' => $data['invoice_no'],
             'transaction_date' => $data['invoice_date'],
             'due_date' => $data['due_date'] ?? $data['invoice_date'],
             'amount' => $amount,
             'description' => isset($data['notes']) && $data['notes'] ? $data['notes'] : $desc_prefix,
             'created_at' => date('Y-m-d H:i:s')
        ];
        
        // Eğer standalone ödeme ise (Tahsilat/Ödeme) ve Cüzdan seçili ise -> Wallet ID'yi de ekle
        if ($is_standalone_payment && !empty($data['wallet_id'])) {
            $transaction['wallet_id'] = $data['wallet_id'];
        }
        
        $this->db->insert('inv_entity_transactions', $transaction);
        
        // --- 2. Transaction: Bağlı Ödeme Hareketi (Sadece Fatura/Fiş için) ---
        // Tahsilat/Ödeme zaten kendisi ödemedir, ikinci bir harekete gerek yok.
        if (!$is_standalone_payment && $data['payment_status'] == 'paid') {
            $payment_type_method = $data['payment_type'] ?? 'cash_bank';
            $entity_payment_amount = -$amount; // Cari için: Faturanın tersi (borç/alacak azalır)
            
            if ($payment_type_method == 'cash_bank' && !empty($data['wallet_id'])) {
                 // Determine payment type based on invoice/transaction type
                 // Sale/Borç Dekontu -> We collect money (Tahsilat)
                 // Purchase/Alacak Dekontu -> We pay money (Odeme)
                 if (in_array($data['type'], ['purchase', 'alacak_dekontu'])) {
                     $payment_type = 'odeme';
                 } else {
                     $payment_type = 'tahsilat';
                 }
                 
                 $payment_txn = [
                     'entity_id' => $data['entity_id'],
                     'invoice_id' => $insert_id,
                     'type' => $payment_type,
                     'document_no' => $data['invoice_no'],
                     'transaction_date' => $data['invoice_date'],
                     'amount' => $entity_payment_amount, // Cari bakiyesi için
                     'wallet_id' => $data['wallet_id'],
                     'description' => $desc_prefix . ' Ödemesi/Tahsilatı',
                     'created_at' => date('Y-m-d H:i:s')
                ];
                $this->db->insert('inv_entity_transactions', $payment_txn);
            } 
            elseif ($payment_type_method == 'virman' && !empty($data['transfer_entity_id'])) {
                 // ... Virman logic for invoice payment ...
                 $payment_amount = $entity_payment_amount; 
                 // 1. Ana Cari Hareketi
                 $virman_txn1 = [
                     'entity_id' => $data['entity_id'],
                     'invoice_id' => $insert_id,
                     'type' => 'virman',
                     'document_no' => $data['invoice_no'],
                     'transaction_date' => $data['invoice_date'],
                     'amount' => $payment_amount,
                     'description' => $desc_prefix . ' Virman (-> Cari #' . $data['transfer_entity_id'] . ')',
                     'created_at' => date('Y-m-d H:i:s')
                 ];
                 $this->db->insert('inv_entity_transactions', $virman_txn1);
                 
                 // 2. Karşı Cari Hareketi (Ters Bakiye)
                 $transfer_amount = -$payment_amount; 
                 $virman_txn2 = [
                     'entity_id' => $data['transfer_entity_id'],
                     'invoice_id' => $insert_id,
                     'type' => 'virman',
                     'document_no' => $data['invoice_no'],
                     'transaction_date' => $data['invoice_date'],
                     'amount' => $transfer_amount,
                     'description' => $desc_prefix . ' Virman (<- Cari #' . $data['entity_id'] . ')',
                     'created_at' => date('Y-m-d H:i:s')
                 ];
                 $this->db->insert('inv_entity_transactions', $virman_txn2);
            }
        }
        
        // --- 3. Bakiye Güncelleme ---
        $this->load->model('Entity_model', 'entity');
        $this->entity->recalculate_balance($data['entity_id']);
        
        // Eğer standalone ödeme ise ve cüzdan varsa
        if ($is_standalone_payment && !empty($data['wallet_id'])) {
             $this->load->model('Wallet_model', 'wallet');
             $this->wallet->recalculate_balance($data['wallet_id']);
        }
        // Eğer fatura ise ve ödenmişse
        elseif (!$is_standalone_payment && ($data['payment_status'] ?? 'unpaid') == 'paid') {
            if (($data['payment_type'] ?? 'cash_bank') == 'virman' && !empty($data['transfer_entity_id'])) {
                $this->entity->recalculate_balance($data['transfer_entity_id']);
            } elseif (!empty($data['wallet_id'])) {
                $this->load->model('Wallet_model', 'wallet');
                $this->wallet->recalculate_balance($data['wallet_id']);
            }
        }
        
        return $insert_id;
    }

    public function ensure_tables_exist() {
        $this->load->dbforge();
        
        if (!$this->db->table_exists('inv_invoices')) {
            $this->dbforge->add_field([
                'id' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => TRUE],
                'invoice_no' => ['type' => 'VARCHAR', 'constraint' => 50],
                'invoice_date' => ['type' => 'DATE'],
                'due_date' => ['type' => 'DATE', 'null' => TRUE],
                'type' => ['type' => 'VARCHAR', 'constraint' => 20], // purchase/sale
                'entity_id' => ['type' => 'INT', 'constraint' => 11],
                'total_amount' => ['type' => 'DECIMAL', 'constraint' => '15,4', 'default' => 0],
                'tax_amount' => ['type' => 'DECIMAL', 'constraint' => '15,4', 'default' => 0],
                'discount_amount' => ['type' => 'DECIMAL', 'constraint' => '15,4', 'default' => 0],
                'general_discount' => ['type' => 'DECIMAL', 'constraint' => '15,4', 'default' => 0],
                'tax_included' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'net_amount' => ['type' => 'DECIMAL', 'constraint' => '15,4', 'default' => 0],
                'payment_status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'unpaid'],
                'payment_type' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'cash_bank'],
                'wallet_id' => ['type' => 'INT', 'constraint' => 11, 'null' => TRUE],
                'transfer_entity_id' => ['type' => 'INT', 'constraint' => 11, 'null' => TRUE],
                'notes' => ['type' => 'TEXT', 'null' => TRUE],
                'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'finalized'],
                'created_at' => ['type' => 'DATETIME', 'null' => TRUE]
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->create_table('inv_invoices', TRUE);
        } else {
            // Check for missing columns in existing table
            $missing_fields = [];
            if (!$this->db->field_exists('payment_status', 'inv_invoices')) {
                $missing_fields['payment_status'] = ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'unpaid'];
            }
            if (!$this->db->field_exists('payment_type', 'inv_invoices')) {
                $missing_fields['payment_type'] = ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'cash_bank'];
            }
            if (!$this->db->field_exists('wallet_id', 'inv_invoices')) {
                $missing_fields['wallet_id'] = ['type' => 'INT', 'constraint' => 11, 'null' => TRUE];
            }
            if (!$this->db->field_exists('transfer_entity_id', 'inv_invoices')) {
                $missing_fields['transfer_entity_id'] = ['type' => 'INT', 'constraint' => 11, 'null' => TRUE];
            }
            if (!$this->db->field_exists('discount_amount', 'inv_invoices')) {
                $missing_fields['discount_amount'] = ['type' => 'DECIMAL', 'constraint' => '15,4', 'default' => 0];
            }
            if (!$this->db->field_exists('general_discount', 'inv_invoices')) {
                $missing_fields['general_discount'] = ['type' => 'DECIMAL', 'constraint' => '15,4', 'default' => 0];
            }
            if (!$this->db->field_exists('tax_included', 'inv_invoices')) {
                $missing_fields['tax_included'] = ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0];
            }
            if (!$this->db->field_exists('due_date', 'inv_invoices')) {
                $missing_fields['due_date'] = ['type' => 'DATE', 'null' => TRUE];
            }

            if (!empty($missing_fields)) {
                $this->dbforge->add_column('inv_invoices', $missing_fields);
            }
            
            // Modify existing columns to 15,4 precision
            $modify_fields = [
                'total_amount' => ['type' => 'DECIMAL', 'constraint' => '15,4', 'default' => 0],
                'tax_amount' => ['type' => 'DECIMAL', 'constraint' => '15,4', 'default' => 0],
                'discount_amount' => ['type' => 'DECIMAL', 'constraint' => '15,4', 'default' => 0],
                'general_discount' => ['type' => 'DECIMAL', 'constraint' => '15,4', 'default' => 0],
                'net_amount' => ['type' => 'DECIMAL', 'constraint' => '15,4', 'default' => 0]
            ];
            $this->dbforge->modify_column('inv_invoices', $modify_fields);
        }

        // Add invoice_id to inv_entity_transactions if missing
        if ($this->db->table_exists('inv_entity_transactions') && !$this->db->field_exists('invoice_id', 'inv_entity_transactions')) {
            $this->dbforge->add_column('inv_entity_transactions', [
                'invoice_id' => ['type' => 'INT', 'constraint' => 11, 'null' => TRUE, 'after' => 'id']
            ]);
        }

        if (!$this->db->table_exists('inv_invoice_items')) {
            $this->dbforge->add_field([
                'id' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => TRUE],
                'invoice_id' => ['type' => 'INT', 'constraint' => 11],
                'product_id' => ['type' => 'INT', 'constraint' => 11, 'null' => TRUE],
                'expense_category_id' => ['type' => 'INT', 'constraint' => 11, 'null' => TRUE],
                'item_type' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'stok'],
                'description' => ['type' => 'TEXT', 'null' => TRUE],
                'unit' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'Adet'],
                'quantity' => ['type' => 'DECIMAL', 'constraint' => '15,4', 'default' => 0],
                'unit_price' => ['type' => 'DECIMAL', 'constraint' => '15,4', 'default' => 0],
                'discount_rate' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0],
                'discount_amount' => ['type' => 'DECIMAL', 'constraint' => '15,4', 'default' => 0],
                'tax_rate' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0],
                'tax_amount' => ['type' => 'DECIMAL', 'constraint' => '15,4', 'default' => 0],
                'total_amount' => ['type' => 'DECIMAL', 'constraint' => '15,4', 'default' => 0]
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->create_table('inv_invoice_items', TRUE);
        } else {
            // Tablo varsa ama sütunlar eksikse (migration gibi)
            if (!$this->db->field_exists('description', 'inv_invoice_items')) {
                $this->dbforge->add_column('inv_invoice_items', [
                    'description' => ['type' => 'TEXT', 'null' => TRUE],
                    'expense_category_id' => ['type' => 'INT', 'constraint' => 11, 'null' => TRUE]
                ]);
            }
            
            // Modify existing columns to 15,4 precision
            $modify_item_fields = [
                'quantity' => ['type' => 'DECIMAL', 'constraint' => '15,4', 'default' => 0],
                'unit_price' => ['type' => 'DECIMAL', 'constraint' => '15,4', 'default' => 0],
                'discount_amount' => ['type' => 'DECIMAL', 'constraint' => '15,4', 'default' => 0],
                'tax_amount' => ['type' => 'DECIMAL', 'constraint' => '15,4', 'default' => 0],
                'total_amount' => ['type' => 'DECIMAL', 'constraint' => '15,4', 'default' => 0]
            ];
            $this->dbforge->modify_column('inv_invoice_items', $modify_item_fields);
        }
        
        // Update inv_entity_transactions table precision
        if ($this->db->table_exists('inv_entity_transactions')) {
             $modify_trx_fields = [
                'amount' => ['type' => 'DECIMAL', 'constraint' => '15,4', 'default' => 0]
             ];
             $this->dbforge->modify_column('inv_entity_transactions', $modify_trx_fields);
        }
        
        // Update inv_entities table precision
        if ($this->db->table_exists('inv_entities')) {
             $modify_entity_fields = [
                'balance' => ['type' => 'DECIMAL', 'constraint' => '15,4', 'default' => 0]
             ];
             $this->dbforge->modify_column('inv_entities', $modify_entity_fields);
        }
        
        // Update inv_products table precision
        if ($this->db->table_exists('inv_products')) {
             $modify_product_fields = [
                'stock_quantity' => ['type' => 'DECIMAL', 'constraint' => '15,4', 'default' => 0],
                'last_buy_price' => ['type' => 'DECIMAL', 'constraint' => '15,4', 'default' => 0], // Assuming these columns exist
                'satis_fiyat' => ['type' => 'DECIMAL', 'constraint' => '15,4', 'default' => 0]
             ];
             // Check if columns exist before modifying to avoid errors if scheme differs slightly
             $existing_cols = $this->db->list_fields('inv_products');
             $final_mod_fields = [];
             foreach ($modify_product_fields as $key => $def) {
                 if (in_array($key, $existing_cols)) {
                     $final_mod_fields[$key] = $def;
                 }
             }
             
             if (!empty($final_mod_fields)) {
                 $this->dbforge->modify_column('inv_products', $final_mod_fields);
             }
        }
    }


    public function update_invoice_full($id, $data) {
        $this->ensure_tables_exist();
        
        // 1. Orijinal transaksiyonu bul (Gelen ID transaksiyon ID'si de olabilir, fatura ID'si de)
        $trx = $this->db->get_where('inv_entity_transactions', ['id' => $id])->row_array();
        
        if (!$trx) {
            // Belki fatura ID'si gelmiştir?
            $invoice = $this->db->get_where('inv_invoices', ['id' => $id])->row_array();
            if ($invoice) {
                // Bu faturaya bağlı ana transaksiyonu bul
                $trx = $this->db->where('invoice_id', $invoice['id'])
                               ->where_in('type', ['fatura', 'fis', 'invoice', 'purchase', 'sale'])
                               ->get('inv_entity_transactions')->row_array();
            }
        }
        
        if (!$trx) {
            log_message('error', 'update_invoice_full: Transaction not found for ID ' . $id);
            return false;
        }
        
        $real_id = $trx['id']; // Gerçek transaksiyon ID'si
        $invoice_id = $trx['invoice_id'];
        $invoice = $this->db->get_where('inv_invoices', ['id' => $invoice_id])->row_array();
        
        if (!$invoice) {
             log_message('error', 'update_invoice_full: Invoice not found for ID ' . $invoice_id);
             return false;
        }

        $this->db->trans_start();

        // 2. Etkilenen cari ve kasaları takip et
        $affected_entities = [$trx['entity_id'], $data['entity_id']];
        $affected_wallets = [$trx['wallet_id'] ?? null, $data['wallet_id'] ?? null];
        
        $related = $this->db->get_where('inv_entity_transactions', ['invoice_id' => $invoice_id])->result_array();
        foreach($related as $r) {
            if($r['entity_id']) $affected_entities[] = $r['entity_id'];
            if($r['wallet_id']) $affected_wallets[] = $r['wallet_id'];
        }

        // 3. STOKLARI GERİ AL
        $old_items = $this->db->get_where('inv_invoice_items', ['invoice_id' => $invoice_id])->result_array();
        foreach ($old_items as $item) {
            if (!empty($item['product_id']) && ($item['item_type'] ?? 'stok') === 'stok') {
                $qty = (float)$item['quantity'];
                if ($invoice['type'] === 'purchase') {
                    $this->db->set('stock_quantity', 'stock_quantity - ' . $qty, FALSE);
                } else {
                    $this->db->set('stock_quantity', 'stock_quantity + ' . $qty, FALSE);
                }
                $this->db->where('id', $item['product_id'])->update('inv_products');
            }
        }
        
        
        // 3.5 Check for existing payment transaction to preserve amount (Only for Regular Invoices)
        $preserved_payment_amount = null;
        if (!in_array($data['type'], ['tahsilat', 'odeme', 'virman'])) {
             // Find any linked transaction that is NOT the main one (real_id)
             // We look for types 'tahsilat', 'odeme', 'virman' linked to this invoice
             $existing_payment = $this->db->where('invoice_id', $invoice_id)
                                          ->where('id !=', $real_id)
                                          ->where_in('type', ['tahsilat', 'odeme', 'virman'])
                                          ->order_by('id', 'DESC')
                                          ->get('inv_entity_transactions')
                                          ->row_array();
                                          
             if ($existing_payment) {
                 $preserved_payment_amount = abs($existing_payment['amount']);
             }
        }
        
        // 4. TEMİZLİK
        $this->db->where('invoice_id', $invoice_id)->delete('inv_invoice_items');
        $this->db->where('invoice_id', $invoice_id)->where('id !=', $real_id)->delete('inv_entity_transactions');

        // 5. FATURA BAŞLIĞINI GÜNCELLE
        $this->db->where('id', $invoice_id)->update('inv_invoices', $data);

        // 6. ANA TRANSAKSİYONU GÜNCELLE (ID Korunuyor)
        $amount = abs($data['net_amount']);
        $txn_type = '';
        $desc_prefix = '';
        
        if ($data['type'] == 'purchase') {
            $amount = -$amount; // Alış = Borçlanma (-)
            $txn_type = 'fatura'; 
            $desc_prefix = 'Alış Faturası';
        } elseif ($data['type'] == 'sale') {
            $txn_type = 'fatura'; // Satış = Alacaklanma (+)
            $desc_prefix = 'Satış Faturası';
        } elseif ($data['type'] == 'fis') {
            $txn_type = 'fis';
            $desc_prefix = 'Satış Fişi';
        } elseif ($data['type'] == 'tahsilat') {
            $amount = -$amount; // Tahsilat = Müşteri alacağı artar (borç azalır) (-)
            $txn_type = 'tahsilat';
            $desc_prefix = 'Tahsilat Makbuzu';
        } elseif ($data['type'] == 'odeme') {
            $amount = $amount; // Ödeme = Bizim borcumuz azalır (+)
            $txn_type = 'odeme';
            $desc_prefix = 'Ödeme Makbuzu';
            $desc_prefix = 'Virman İşlemi';
        } elseif ($data['type'] == 'borc_dekontu') {
            $amount = $amount; 
            $txn_type = 'borc_dekontu';
            $desc_prefix = 'Borç Dekontu';
        } elseif ($data['type'] == 'alacak_dekontu') {
            $amount = -$amount; 
            $txn_type = 'alacak_dekontu';
            $desc_prefix = 'Alacak Dekontu';
        } else {
            $txn_type = $data['type'];
            $desc_prefix = 'Belge';
        }
        
        $is_standalone_payment = in_array($data['type'], ['tahsilat', 'odeme', 'virman']);

        $transaction = [
             'entity_id' => $data['entity_id'],
             'type' => $txn_type,
             'document_no' => $data['invoice_no'],
             'transaction_date' => $data['invoice_date'],
             'due_date' => $data['due_date'] ?? $data['invoice_date'],
             'amount' => $amount,
             'description' => isset($data['notes']) && $data['notes'] ? $data['notes'] : $desc_prefix
        ];

        // Standalone ödemelerde cüzdan ID'sini de güncelle
        if ($is_standalone_payment && !empty($data['wallet_id'])) {
            $transaction['wallet_id'] = $data['wallet_id'];
        }

        $this->db->where('id', $real_id)->update('inv_entity_transactions', $transaction);

        // 7. YENİ ÖDEME HAREKETİ EKLE (Eğer ödendi ise)
        if (!$is_standalone_payment && $data['payment_status'] == 'paid') {
            $payment_type_method = $data['payment_type'] ?? 'cash_bank';
            
            // Use preserved payment amount if available, otherwise use new invoice total
            $pay_amt = $preserved_payment_amount !== null ? $preserved_payment_amount : $amount;
            
            $entity_payment_amount = -$pay_amt; // Cari için ters bakiye
            
            if ($payment_type_method == 'cash_bank' && !empty($data['wallet_id'])) {
                 if (in_array($data['type'], ['purchase', 'alacak_dekontu'])) {
                     $payment_type = 'odeme';
                 } else {
                     $payment_type = 'tahsilat';
                 }
                 $payment_txn = [
                     'entity_id' => $data['entity_id'],
                     'invoice_id' => $invoice_id,
                     'type' => $payment_type,
                     'document_no' => $data['invoice_no'],
                     'transaction_date' => $data['invoice_date'],
                     'amount' => $entity_payment_amount,
                     'wallet_id' => $data['wallet_id'],
                     'description' => $desc_prefix . ' Ödemesi/Tahsilatı',
                     'created_at' => date('Y-m-d H:i:s')
                ];
                $this->db->insert('inv_entity_transactions', $payment_txn);
            } 
            elseif ($payment_type_method == 'virman' && !empty($data['transfer_entity_id'])) {
                 // Virman Hareketi 1 (Ana Cari)
                 $virman_txn1 = [
                     'entity_id' => $data['entity_id'],
                     'invoice_id' => $invoice_id,
                     'type' => 'virman',
                     'document_no' => $data['invoice_no'],
                     'transaction_date' => $data['invoice_date'],
                     'amount' => $entity_payment_amount,
                     'description' => $desc_prefix . ' Virman (-> Cari #' . $data['transfer_entity_id'] . ')',
                     'created_at' => date('Y-m-d H:i:s')
                 ];
                 $this->db->insert('inv_entity_transactions', $virman_txn1);
                 
                 // Virman Hareketi 2 (Hedef Cari)
                 $virman_txn2 = [
                     'entity_id' => $data['transfer_entity_id'],
                     'invoice_id' => $invoice_id,
                     'type' => 'virman',
                     'document_no' => $data['invoice_no'],
                     'transaction_date' => $data['invoice_date'],
                     'amount' => -$entity_payment_amount,
                     'description' => $desc_prefix . ' Virman (<- Cari #' . $data['entity_id'] . ')',
                     'created_at' => date('Y-m-d H:i:s')
                 ];
                 $this->db->insert('inv_entity_transactions', $virman_txn2);
                 $affected_entities[] = $data['transfer_entity_id'];
            }
        }
        
        // 8. BAKİYELERİ YENİDEN HESAPLA
        $this->load->model('Entity_model', 'entity_model');
        $this->load->model('Wallet_model', 'wallet_model');
        
        foreach (array_unique(array_filter($affected_entities)) as $eid) {
            $this->entity_model->recalculate_balance($eid);
        }
        foreach (array_unique(array_filter($affected_wallets)) as $wid) {
            $this->wallet_model->recalculate_balance($wid);
        }

        $this->db->trans_complete();
        return $this->db->trans_status() ? $invoice_id : false;
    }

    public function update_invoice($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('inv_invoices', $data);
    }

    public function delete_invoice($id) {
        $this->ensure_tables_exist();
        
        // Find the transaction record
        $trx = $this->db->get_where('inv_entity_transactions', ['id' => $id])->row_array();
        
        // If not found, check if it's an invoice ID
        if (!$trx) {
            $invoice = $this->db->get_where('inv_invoices', ['id' => $id])->row_array();
            if ($invoice) {
                $trx = $this->db->get_where('inv_entity_transactions', ['document_no' => $invoice['invoice_no']])->row_array();
            }
        }
        
        if (!$trx) return false;

        $this->db->trans_start();

        $document_no = $trx['document_no'];
        $entity_id = $trx['entity_id'];

        // 1. Handle deep delete for INVOICES (if exists)
        $invoice = $this->db->get_where('inv_invoices', ['invoice_no' => $document_no])->row_array();
        if ($invoice) {
            // Find items for stock reversal
            $items = $this->db->get_where('inv_invoice_items', ['invoice_id' => $invoice['id']])->result_array();
            foreach ($items as $item) {
                if (!empty($item['product_id']) && $item['item_type'] === 'stok') {
                    // Reverse stock based on invoice type:
                    // - Purchase invoice added stock, so we SUBTRACT
                    // - Sale invoice subtracted stock, so we ADD
                    $qty = (float)$item['quantity'];
                    
                    if ($invoice['type'] === 'purchase') {
                        // Alış faturası silindi -> Stoğu azalt
                        $this->db->set('stock_quantity', 'stock_quantity - ' . $qty, FALSE);
                    } else {
                        // Satış faturası silindi -> Stoğu artır
                        $this->db->set('stock_quantity', 'stock_quantity + ' . $qty, FALSE);
                    }
                    $this->db->where('id', $item['product_id']);
                    $this->db->update('inv_products');
                }
            }
            // Delete invoice items
            $this->db->where('invoice_id', $invoice['id'])->delete('inv_invoice_items');
            // Delete invoice header
            $this->db->where('id', $invoice['id'])->delete('inv_invoices');
        }

        // 2. Delete all related ledger transaction(s) - including payments, virman etc.
        // We find all related transactions first to know which entities/wallets to recalculate
        
        $this->db->group_start();
        if ($invoice) {
            $this->db->where('invoice_id', $invoice['id']);
            if ($document_no) $this->db->or_where('document_no', $document_no);
        } else {
            if ($document_no) $this->db->where('document_no', $document_no);
        }
        $this->db->or_where('id', $id);
        $this->db->group_end();
        
        $related_trxs = $this->db->get('inv_entity_transactions')->result_array();
        
        $affected_entities = [];
        $affected_wallets = [];
        
        foreach ($related_trxs as $rt) {
            if ($rt['entity_id']) $affected_entities[] = $rt['entity_id'];
            if ($rt['wallet_id']) $affected_wallets[] = $rt['wallet_id'];
        }

        $this->db->group_start();
        if ($invoice) {
            $this->db->where('invoice_id', $invoice['id']);
            if ($document_no) $this->db->or_where('document_no', $document_no);
        } else {
            if ($document_no) $this->db->where('document_no', $document_no);
        }
        $this->db->or_where('id', $id);
        $this->db->group_end();
        
        $this->db->delete('inv_entity_transactions');
        $deleted_count = $this->db->affected_rows();

        if ($deleted_count === 0) {
            $this->db->trans_rollback();
            return false;
        }

        // 3. Recalculate Balances
        $this->load->model('Entity_model', 'entity_model');
        $this->load->model('Wallet_model', 'wallet_model');
        
        foreach (array_unique($affected_entities) as $eid) {
            $this->entity_model->recalculate_balance($eid);
        }
        foreach (array_unique($affected_wallets) as $wid) {
            $this->wallet_model->recalculate_balance($wid);
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function add_invoice_item($data) {
        return $this->db->insert('inv_invoice_items', $data);
    }

    public function update_invoice_item($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('inv_invoice_items', $data);
    }

    public function delete_invoice_item($id) {
        $this->db->where('id', $id);
        return $this->db->delete('inv_invoice_items');
    }

    public function get_next_invoice_no($prefix = 'INV') {
        $year = date('Y');
        $search = $prefix . '-' . $year . '-';
        
        // Check both tables for the highest number
        $this->db->select('document_no as num');
        $this->db->from('inv_entity_transactions');
        $this->db->like('document_no', $search, 'after');
        $query1 = $this->db->get_compiled_select();
        
        $this->db->select('invoice_no as num');
        $this->db->from('inv_invoices');
        $this->db->like('invoice_no', $search, 'after');
        $query2 = $this->db->get_compiled_select();
        
        $unified_query = "SELECT num FROM ($query1 UNION $query2) as unified ORDER BY num DESC LIMIT 1";
        $result = $this->db->query($unified_query)->row_array();
        
        $number = 1;
        if ($result && !empty($result['num'])) {
            $parts = explode('-', $result['num']);
            // Expecting PREFIX-YEAR-NUMBER
            $last_num = end($parts);
            if (is_numeric($last_num)) {
                $number = intval($last_num) + 1;
            }
        }
        
        return sprintf('%s-%s-%05d', $prefix, $year, $number);
    }

    public function finalize_invoice($id) {
        $invoice = $this->get_invoice_by_id($id);
        if (!$invoice || $invoice['status'] !== 'draft') return false;
        
        $items = $this->get_invoice_items($id);
        
        // Update stock for each item
        foreach ($items as $item) {
            if ($item['item_type'] === 'stock' && $item['product_id']) {
                $product = $this->db->get_where('inv_products', ['id' => $item['product_id']])->row_array();
                if ($product) {
                    if ($invoice['type'] === 'purchase') {
                        // Purchase: increase stock
                        $new_stock = $product['stock_quantity'] + $item['quantity'];
                    } else {
                        // Sale: decrease stock
                        $new_stock = $product['stock_quantity'] - $item['quantity'];
                    }
                    
                    $this->db->where('id', $item['product_id']);
                    $this->db->update('inv_products', ['stock_quantity' => $new_stock]);
                }
            }
        }
        
        // Update invoice status
        return $this->update_invoice($id, ['status' => 'finalized']);
    }
}
