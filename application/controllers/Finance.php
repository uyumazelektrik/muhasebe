<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Finance extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Wallet_model', 'wallet');
        $this->load->model('Entity_model', 'entity');
        $this->load->model('Transaction_model', 'transaction');
    }

    public function transaction_detail($id) {
        $transaction = $this->db->select('t.*, e.name as entity_name, e.type as entity_type, e.phone, w.name as wallet_name')
            ->from('inv_entity_transactions t')
            ->join('inv_entities e', 't.entity_id = e.id', 'left')
            ->join('inv_wallets w', 't.wallet_id = w.id', 'left')
            ->where('t.id', $id)
            ->get()->row_array();

        if (!$transaction) {
            show_404();
            return;
        }

        // Eğer bu bir fatura veya fiş ise, Fatura detayına yönlendir
        if (in_array($transaction['type'], ['fatura', 'fis', 'invoice', 'purchase', 'sale'])) {
            redirect('invoices/detail/' . $id);
            return;
        }

        $data['transaction'] = $transaction;
        $data['entities'] = $this->entity->get_all();
        $data['wallets'] = $this->wallet->get_active();
        
        $data['linked_txn'] = null;
        if (!empty($transaction['linked_transaction_id'])) {
            $data['linked_txn'] = $this->db->select('t.*, e.name as entity_name, w.name as wallet_name')
                ->from('inv_entity_transactions t')
                ->join('inv_entities e', 't.entity_id = e.id', 'left')
                ->join('inv_wallets w', 't.wallet_id = w.id', 'left')
                ->where('t.id', $transaction['linked_transaction_id'])
                ->get()->row_array();
        } elseif ($transaction['type'] === 'virman') {
            // Fatura üzerinden veya manuel oluşturulmuş ama linklenmemiş virmanlar
             $this->db->select('t.*, e.name as entity_name, w.name as wallet_name');
             $this->db->from('inv_entity_transactions t');
             $this->db->join('inv_entities e', 't.entity_id = e.id', 'left');
             $this->db->join('inv_wallets w', 't.wallet_id = w.id', 'left');
             
             $this->db->where('t.id !=', $transaction['id']);
             $this->db->where('t.type', 'virman');
             
             if (!empty($transaction['invoice_id'])) {
                 $this->db->where('t.invoice_id', $transaction['invoice_id']);
             } elseif (!empty($transaction['document_no'])) {
                 $this->db->where('t.document_no', $transaction['document_no']);
             } else {
                 // Bağlantı bulunamadı
                 $this->db->where('1=0');
             }
             
             $data['linked_txn'] = $this->db->order_by('t.id', 'DESC')->limit(1)->get()->row_array();
        }

        $data['page_title'] = 'İşlem Detayı - ' . ($transaction['document_no'] ?: 'ID #' . $id);

        $this->load->view('layout/header', $data);
        $this->load->view('finance/transaction_detail', $data);
        $this->load->view('layout/footer');
    }

    public function wallets() {
        if (current_role() !== 'admin') {
            redirect(site_url('dashboard'));
        }

        $this->load->model('Entity_model', 'entity');
        
        // Auto-fix invalid wallet types
        $this->db->where('wallet_type IS NULL', null, false)->or_where('wallet_type', '')->update('inv_wallets', ['wallet_type' => 'CASH']);
        $this->db->where('wallet_type', 'BANK')->update('inv_wallets', ['wallet_type' => 'BANK_ACCOUNT']);
        
        // Ensure ENUM includes SAFE, GOLD_ACCOUNT, LOAN
        $this->db->query("ALTER TABLE inv_wallets MODIFY COLUMN wallet_type ENUM('CASH', 'CREDIT_CARD', 'BANK_ACCOUNT', 'GOLD_ACCOUNT', 'LOAN', 'SAFE') DEFAULT 'CASH'");
        
        // Add additional fields for Credit Card and Loan
        if (!$this->db->field_exists('statement_day', 'inv_wallets')) {
            $this->db->query("ALTER TABLE inv_wallets ADD COLUMN statement_day TINYINT NULL COMMENT 'Hesap Kesim Günü'");
        }
        if (!$this->db->field_exists('payment_day', 'inv_wallets')) {
            $this->db->query("ALTER TABLE inv_wallets ADD COLUMN payment_day TINYINT NULL COMMENT 'Son Ödeme/Taksit Günü'");
        }
        
        $data['wallets'] = $this->wallet->get_all();
        $data['stats'] = $this->wallet->get_stats();
        $data['entities'] = $this->entity->get_all(); // For owner selection
        $data['page_title'] = "Kasa & Banka Yönetimi";
        
        $this->load->view('layout/header', $data);
        $this->load->view('finance/wallets', $data);
        $this->load->view('layout/footer');
    }

    public function wallet_detail($id) {
        if (current_role() !== 'admin') {
            redirect(site_url('dashboard'));
        }
        $data['wallet'] = $this->wallet->get_by_id($id);
        if (!$data['wallet']) show_404();
        
        $data['transactions'] = $this->wallet->get_transactions($id, 100);
        $data['transaction_count'] = $this->wallet->count_transactions($id);
        $data['page_title'] = $data['wallet']['name'] . " - Kasa Detayı";
        
        $this->load->view('layout/header', $data);
        $this->load->view('finance/wallet_detail', $data);
        $this->load->view('layout/footer');
    }

    public function api_add_wallet() {
        header('Content-Type: application/json');
        
        $owner_entity_id = $this->input->post('owner_entity_id');
        $data = [
            'name' => $this->input->post('name'),
            'wallet_type' => $this->input->post('wallet_type') ?: 'CASH',
            'asset_type' => $this->input->post('asset_type') ?: 'TL',
            'balance' => floatval($this->input->post('balance') ?: 0),
            'description' => $this->input->post('description') ?: '',
            'owner_entity_id' => !empty($owner_entity_id) ? $owner_entity_id : NULL,
            'statement_day' => $this->input->post('statement_day') ?: NULL,
            'payment_day' => $this->input->post('payment_day') ?: NULL,
            'is_active' => 1
        ];

        if (empty($data['name'])) {
            echo json_encode(['success' => false, 'message' => 'Kasa adı zorunludur']);
            return;
        }

        $id = $this->wallet->create($data);
        if ($id) {
            echo json_encode(['success' => true, 'id' => $id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Kasa oluşturulamadı']);
        }
    }

    public function api_edit_wallet() {
        header('Content-Type: application/json');
        
        $id = $this->input->post('id');
        $owner_entity_id = $this->input->post('owner_entity_id');
        
        $data = [
            'name' => $this->input->post('name'),
            'wallet_type' => $this->input->post('wallet_type'),
            'asset_type' => $this->input->post('asset_type'),
            'description' => $this->input->post('description'),
            'owner_entity_id' => !empty($owner_entity_id) ? $owner_entity_id : NULL,
            'statement_day' => $this->input->post('statement_day') ?: NULL,
            'payment_day' => $this->input->post('payment_day') ?: NULL,
            'is_active' => $this->input->post('is_active') ?? 1
        ];

        if ($this->wallet->update($id, $data)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Güncelleme başarısız']);
        }
    }

    public function api_delete_wallet() {
        header('Content-Type: application/json');
        
        $id = $this->input->post('id');
        $result = $this->wallet->delete($id);
        echo json_encode($result);
    }

    public function recalculate_all_wallets() {
        $wallets = $this->wallet->get_all();
        $count = 0;
        foreach ($wallets as $w) {
            $this->wallet->recalculate_balance($w['id']);
            $count++;
        }
        echo "Tüm kasaların bakiyeleri yeniden hesaplandı. ($count adet)";
    }

    public function api_add_wallet_transaction() {
        header('Content-Type: application/json');
        
        $this->load->model('Transaction_model', 'transaction');
        
        $wallet_id = $this->input->post('wallet_id');
        $type = $this->input->post('type');
        $amount = abs(floatval($this->input->post('amount')));
        
        // For odeme (payment), amount should be negative (money going out)
        // For tahsilat (collection), amount should be positive (money coming in)
        if ($type === 'tahsilat') {
            $amount = -$amount;
        } else {
            $amount = abs($amount);
        }
        
        $data = [
            'wallet_id' => $wallet_id,
            'type' => $type,
            'amount' => $amount,
            'description' => $this->input->post('description'),
            'transaction_date' => $this->input->post('transaction_date') ?: date('Y-m-d')
        ];

        $result = $this->transaction->create($data);
        
        if ($result) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'İşlem kaydedilemedi']);
        }
    }

    public function api_edit_wallet_transaction() {
        header('Content-Type: application/json');
        
        $id = $this->input->post('id');
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'İşlem ID gerekli']);
            return;
        }
        
        $type = $this->input->post('type');
        $amount = abs(floatval($this->input->post('amount')));
        
        // For odeme (payment), amount should be negative (money going out)
        // For tahsilat (collection), amount should be positive (money coming in)
        if ($type === 'tahsilat') {
            $amount = -$amount;
        } else {
            $amount = abs($amount);
        }
        
        $data = [
            'type' => $type,
            'amount' => $amount,
            'description' => $this->input->post('description'),
            'transaction_date' => $this->input->post('transaction_date') ?: date('Y-m-d')
        ];

        // Get wallet_id before update for recalculation
        $txn = $this->db->get_where('inv_entity_transactions', ['id' => $id])->row_array();
        $wallet_id = $txn['wallet_id'] ?? $this->input->post('wallet_id');
        
        $this->db->where('id', $id);
        $result = $this->db->update('inv_entity_transactions', $data);
        
        if ($result) {
            // Recalculate wallet balance
            if ($wallet_id) {
                $this->wallet->recalculate_balance($wallet_id);
            }
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Güncelleme başarısız']);
        }
    }

    public function api_wallet_transfer() {
        header('Content-Type: application/json');
        
        $from_wallet_id = $this->input->post('from_wallet_id');
        $to_wallet_id = $this->input->post('to_wallet_id');
        $amount = abs(floatval($this->input->post('amount')));
        $description = $this->input->post('description') ?: 'Hesaplar arası transfer';
        
        if (!$from_wallet_id || !$to_wallet_id || $amount <= 0) {
            echo json_encode(['success' => false, 'message' => 'Geçersiz parametreler']);
            return;
        }
        
        if ($from_wallet_id == $to_wallet_id) {
            echo json_encode(['success' => false, 'message' => 'Kaynak ve hedef hesap aynı olamaz']);
            return;
        }
        
        $this->db->trans_start();
        
        // Ensure linked_transaction_id column exists
        if (!$this->db->field_exists('linked_transaction_id', 'inv_entity_transactions')) {
            $this->load->dbforge();
            $this->dbforge->add_column('inv_entity_transactions', [
                'linked_transaction_id' => ['type' => 'INT', 'constraint' => 11, 'null' => TRUE, 'after' => 'wallet_id']
            ]);
        }
        
        // Get wallet details including owner_entity_id
        $from_wallet = $this->wallet->get_by_id($from_wallet_id);
        $to_wallet = $this->wallet->get_by_id($to_wallet_id);
        
        // Use owner_entity_id from wallets if available
        $from_entity_id = !empty($from_wallet['owner_entity_id']) ? $from_wallet['owner_entity_id'] : null;
        $to_entity_id = !empty($to_wallet['owner_entity_id']) ? $to_wallet['owner_entity_id'] : null;
        
        // If no owner_entity_id, disable FK check temporarily
        if (!$from_entity_id || !$to_entity_id) {
            $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        }
        
        // Create OUTGOING transaction (from source wallet)
        $this->db->insert('inv_entity_transactions', [
            'type' => 'odeme',
            'entity_id' => $from_entity_id,
            'wallet_id' => $from_wallet_id,
            'amount' => -$amount,
            'description' => $description . ' -> ' . $to_wallet['name'],
            'transaction_date' => date('Y-m-d'),
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $outgoing_id = $this->db->insert_id();
        
        // Create INCOMING transaction (to target wallet)
        $this->db->insert('inv_entity_transactions', [
            'type' => 'tahsilat',
            'entity_id' => $to_entity_id,
            'wallet_id' => $to_wallet_id,
            'amount' => $amount,
            'description' => $description . ' <- ' . $from_wallet['name'],
            'transaction_date' => date('Y-m-d'),
            'linked_transaction_id' => $outgoing_id, // Link to outgoing
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $incoming_id = $this->db->insert_id();
        
        // Update outgoing transaction with incoming link
        $this->db->where('id', $outgoing_id);
        $this->db->update('inv_entity_transactions', ['linked_transaction_id' => $incoming_id]);
        
        // Re-enable FK check if it was disabled
        if (!$from_entity_id || !$to_entity_id) {
            $this->db->query('SET FOREIGN_KEY_CHECKS=1');
        }
        
        // Recalculate both wallet balances
        $this->wallet->recalculate_balance($from_wallet_id);
        $this->wallet->recalculate_balance($to_wallet_id);
        
        $this->db->trans_complete();
        
        if ($this->db->trans_status()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Transfer başarısız']);
        }
    }

    public function api_external_transaction() {
        header('Content-Type: application/json');
        
        $wallet_id = $this->input->post('wallet_id');
        $type = $this->input->post('type'); // 'giris' or 'cikis'
        $amount = abs(floatval($this->input->post('amount')));
        $description = $this->input->post('description');
        $date = $this->input->post('transaction_date') ?: date('Y-m-d');
        
        if (!$wallet_id || $amount <= 0) {
            echo json_encode(['success' => false, 'message' => 'Geçersiz parametreler']);
            return;
        }
        
        // Determine transaction type and amount sign
        if ($type === 'giris') {
            $txn_type = 'tahsilat';
            $txn_amount = $amount; // Positive - money coming in
        } else {
            $txn_type = 'odeme';
            $txn_amount = -$amount; // Negative - money going out
        }
        
        // Get wallet's owner_entity_id
        $wallet = $this->wallet->get_by_id($wallet_id);
        $entity_id = !empty($wallet['owner_entity_id']) ? $wallet['owner_entity_id'] : null;
        
        // Disable FK check if no owner_entity_id
        if (!$entity_id) {
            $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        }
        
        $this->db->insert('inv_entity_transactions', [
            'type' => $txn_type,
            'entity_id' => $entity_id,
            'wallet_id' => $wallet_id,
            'amount' => $txn_amount,
            'description' => $description,
            'transaction_date' => $date,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        if (!$entity_id) {
            $this->db->query('SET FOREIGN_KEY_CHECKS=1');
        }
        
        if ($this->db->insert_id()) {
            $this->wallet->recalculate_balance($wallet_id);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'İşlem kaydedilemedi']);
        }
    }

    public function expense_categories() {
        if (current_role() !== 'admin') {
            redirect(site_url('dashboard'));
        }
        $this->load->model('Expense_category_model', 'expense');
        $data['categories'] = $this->expense->get_all();
        $data['page_title'] = "Gider Kategorileri";
        
        $this->load->view('layout/header', $data);
        $this->load->view('finance/expense_categories', $data);
        $this->load->view('layout/footer');
    }

    public function api_add_expense_category() {
        header('Content-Type: application/json');
        if (current_role() !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim']);
            return;
        }

        $name = $this->input->post('name');
        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Kategori adı zorunludur']);
            return;
        }

        $this->load->model('Expense_category_model', 'expense');
        $result = $this->expense->create(['name' => $name]);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Kategori başarıyla eklendi']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Hata oluştu']);
        }
    }

    public function api_edit_expense_category() {
        header('Content-Type: application/json');
        if (current_role() !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim']);
            return;
        }

        $id = $this->input->post('id');
        $name = $this->input->post('name');

        if (!$id || empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Eksik bilgi']);
            return;
        }

        $this->load->model('Expense_category_model', 'expense');
        $result = $this->expense->update($id, ['name' => $name]);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Kategori güncellendi']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Hata oluştu']);
        }
    }

    public function api_delete_expense_category() {
        header('Content-Type: application/json');
        if (current_role() !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim']);
            return;
        }

        $id = $this->input->post('id');
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID gerekli']);
            return;
        }

        $this->load->model('Expense_category_model', 'expense');
        
        // Check if any transactions belong to this category
        $count = $this->db->where('expense_category_id', $id)->count_all_results('inv_entity_transactions');
        if ($count > 0) {
             echo json_encode(['success' => false, 'message' => 'Bu kategoriye ait harcamalar bulunduğu için silinemez.']);
             return;
        }

        if ($this->expense->delete($id)) {
            echo json_encode(['success' => true, 'message' => 'Kategori silindi']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Silme başarısız']);
        }
    }
    public function user_transactions() {
        $user_id = $this->input->get('user_id');
        if (!$user_id) redirect(site_url('dashboard'));

        $this->load->model('User_model', 'user_model');
        $this->load->model('Personnel_transaction_model', 'pt_model');
        $this->load->model('Attendance_model', 'attendance_model');

        $user = $this->db->get_where('users', ['id' => $user_id])->row_array();
        if (!$user) show_error('Kullanıcı bulunamadı');

        $month = $this->input->get('month') ?: 0;
        $year = $this->input->get('year') ?: 0;
        $page = max(1, $this->input->get('page') ?: 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $filters = ['month' => $month, 'year' => $year];
        $data['transactions'] = $this->pt_model->get_user_transactions($user_id, $filters, $limit, $offset);
        $data['totalTransactions'] = $this->pt_model->count_user_transactions($user_id, $filters);
        
        $data['totals'] = $this->pt_model->get_totals($user_id, $filters);
        
        $endDate = null;
        if ($month > 0 && $year > 0) {
            $endDate = date('Y-m-t', strtotime("$year-$month-01"));
        }
        $data['balance'] = $this->pt_model->get_balance($user_id, $endDate);

        // Annual leave usage
        $data['usedLeave'] = $this->db->where([
            'user_id' => $user_id,
            'status' => 'annual_leave'
        ])->where('YEAR(date)', date('Y'))->count_all_results('attendance');
        
        $data['remainingLeave'] = max(0, ($user['annual_leave_days'] ?? 0) - $data['usedLeave']);
        
        $data['user'] = $user;
        $data['month'] = $month;
        $data['year'] = $year;
        $data['page'] = $page;
        $data['limit'] = $limit;
        $data['totalPages'] = ceil($data['totalTransactions'] / $limit);
        $data['page_title'] = $user['full_name'] . " - Cari Hareketler";

        $this->load->view('layout/header', $data);
        $this->load->view('finance/user_transactions', $data);
        $this->load->view('layout/footer');
    }
    public function api_add_transaction() {
        $user_id = $this->input->post('user_id');
        $data = [
            'user_id' => $user_id,
            'type' => $this->input->post('type'),
            'amount' => $this->input->post('amount'),
            'date' => $this->input->post('date'),
            'description' => $this->input->post('description'),
            'affects_balance' => $this->input->post('affects_balance'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        if ($this->db->insert('transactions', $data)) {
            redirect(site_url('finance/user-transactions?user_id='.$user_id.'&status=success'));
        } else {
            redirect(site_url('finance/user-transactions?user_id='.$user_id.'&status=error'));
        }
    }

    public function api_edit_transaction() {
        $id = $this->input->post('id');
        $user_id = $this->input->post('user_id');
        $data = [
            'type' => $this->input->post('type'),
            'amount' => $this->input->post('amount'),
            'date' => $this->input->post('date'),
            'description' => $this->input->post('description'),
            'affects_balance' => $this->input->post('affects_balance')
        ];

        $this->db->where('id', $id);
        if ($this->db->update('transactions', $data)) {
            redirect(site_url('finance/user-transactions?user_id='.$user_id.'&status=success'));
        } else {
            redirect(site_url('finance/user-transactions?user_id='.$user_id.'&status=error'));
        }
    }

    public function api_delete_transaction() {
        $id = $this->input->post('id');
        $user_id = $this->input->post('user_id');

        $this->db->where('id', $id);
        if ($this->db->delete('transactions')) {
            redirect(site_url('finance/user-transactions?user_id='.$user_id.'&status=success'));
        } else {
            redirect(site_url('finance/user-transactions?user_id='.$user_id.'&status=error'));
        }
    }

    public function api_save_transaction() {
        header('Content-Type: application/json');
        
        $id = $this->input->post('id');
        $type = $this->input->post('type');
        $amount = abs(floatval($this->input->post('amount')));
        $date = $this->input->post('invoice_date');
        $due_date = $this->input->post('due_date');
        $notes = $this->input->post('notes');
        $entity_id = $this->input->post('entity_id');
        $wallet_id = $this->input->post('wallet_id');
        $document_no = $this->input->post('invoice_no');

        // Debug log
        log_message('debug', 'Transaction Save - ID: ' . $id . ', Type: ' . $type . ', All POST: ' . json_encode($this->input->post()));

        if (!$id) {
             echo json_encode(['status' => 'error', 'message' => 'Geçersiz parametreler - ID eksik']);
             return;
        }

        $this->db->trans_start();

        $original = $this->db->get_where('inv_entity_transactions', ['id' => $id])->row_array();
        if (!$original) {
            echo json_encode(['status' => 'error', 'message' => 'İşlem bulunamadı']);
            return;
        }

        if ($type === 'virman') {
            $linked_entity_id = $this->input->post('linked_entity_id');
            
            $source_data = [
                'entity_id' => $entity_id,
                'type' => 'virman',
                'amount' => -$amount, 
                'description' => $notes,
                'document_no' => $document_no,
                'transaction_date' => $date,
                'due_date' => $due_date
            ];
            $this->transaction->update($id, $source_data);

            if (!empty($original['linked_transaction_id'])) {
                $target_data = [
                    'entity_id' => $linked_entity_id,
                    'type' => 'virman',
                    'amount' => $amount,
                    'description' => $notes,
                    'document_no' => $document_no,
                    'transaction_date' => $date,
                    'due_date' => $due_date
                ];
                $this->transaction->update($original['linked_transaction_id'], $target_data);
            }
            
            $new_id = $id;
        } else {
            // Type boşsa veya tahsilat/ödeme ise
            // Eğer type boşsa (emanet ödemeler), mevcut amount'u koru
            if (empty($type)) {
                $final_amount = $amount; // Pozitif olarak al
                // Eğer orijinal negatifse, negatif yap
                if (!empty($original['amount']) && $original['amount'] < 0) {
                    $final_amount = -$amount;
                }
            } else {
                $final_amount = ($type === 'tahsilat') ? -$amount : $amount;
            }
            
            $data = [
                'entity_id' => !empty($entity_id) ? $entity_id : null,
                'wallet_id' => !empty($wallet_id) ? $wallet_id : null,
                'type' => $type ?: $original['type'], // Type boşsa orijinali koru
                'amount' => $final_amount,
                'description' => $notes,
                'document_no' => $document_no,
                'transaction_date' => $date,
                'due_date' => $due_date
            ];
            $this->transaction->update($id, $data);
            $new_id = $id;
        }

        $this->db->trans_complete();

        if ($this->db->trans_status()) {
            echo json_encode(['status' => 'success', 'invoice_id' => $new_id]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'İşlem güncellenemedi']);
        }
    }
}
