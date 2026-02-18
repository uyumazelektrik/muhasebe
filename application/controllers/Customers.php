<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Customers extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Customer_model', 'customer');
        $this->load->model('Transaction_model', 'transaction');
    }

    public function index() {
        $filters = array();

        $allEntities = $this->customer->get_all($filters, 2000);
        
        $data['generalEntities'] = array_filter($allEntities, function($e) {
            return $e['type'] !== 'staff';
        });
        
        $data['allEntities'] = $allEntities; // Pass all for selects
        
        $data['staffEntities'] = array_filter($allEntities, function($e) {
            return $e['type'] === 'staff';
        });

        $this->load->model('Wallet_model', 'wallet');
        $data['wallets'] = $this->wallet->get_active();

        $data['stats'] = $this->customer->get_stats();
        $data['page_title'] = "Cari Hesaplar";

        $this->load->view('layout/header', $data);
        $this->load->view('customers/list', $data);
        $this->load->view('layout/footer');
    }

    public function detail($id) {
        $data['entity'] = $this->customer->get_by_id($id);
        if (!$data['entity']) {
            show_404();
        }

        $this->load->model('Wallet_model', 'wallet');
        
        $data['transactions'] = $this->transaction->get_all(['entity_id' => $id], 100);

        // Debt Breakdown Calculation
        $balance = floatval($data['entity']['balance']);
        $upcoming_debt = $this->transaction->get_upcoming_debt($id);
        
        $data['debt_breakdown'] = [
            'upcoming' => 0,
            'overdue' => 0
        ];

        if ($balance > 0) {
            $overdue = $balance - $upcoming_debt;
            
            if ($overdue < 0) {
                $data['debt_breakdown']['upcoming'] = $balance;
                $data['debt_breakdown']['overdue'] = 0;
            } else {
                $data['debt_breakdown']['upcoming'] = $upcoming_debt;
                $data['debt_breakdown']['overdue'] = $overdue;
            }
        }

        // --- Calculate Open Amount for Transactions (FIFO Logic) ---
        // This ensures we only show "Overdue" on transactions that are actually unpaid
        // based on the current running balance.
        
        $target_balance = abs((float)$data['entity']['balance']);
        $is_receivable = (float)$data['entity']['balance'] > 0;
        
        // We need to iterate transactions to assign the balance
        // The transactions array is already sorted DESC (Newest first) from get_all usually? 
        // Let's verify sort in model or here. Transaction_model->get_all typically sorts by ID/Date DESC.
        // We will loop through $data['transactions'] (which are the displayed ones).
        
        // However, to be perfectly accurate, we should strictly check only transactions contributing to the balance.
        // For visual simplicity in the list, we will just walk down and decrement the balance.
        
        // Note: $data['transactions'] might be limited to 100. If balance is older than 100 txs, this might mismatch,
        // but for the UI context "Is THIS transaction paid?", it's a good approximation.
        
        $remaining_balance = $target_balance;

        // We need to loop by reference to modify the array
        foreach ($data['transactions'] as &$t) {
            $t['open_amount'] = 0; // Default closed
            
            // Determine if this transaction 'increments' the specific balance type
            // If Balance > 0 (Receivable), we look for Debts (amount > 0)
            // If Balance < 0 (Payable), we look for Credits (amount < 0)
            // If Balance == 0, nothing is open.
            
            if ($target_balance < 0.01) continue; // Balance cleared
            
            $affects_balance = false;
            $trx_amt = 0;
            
            if ($is_receivable) { // Balance > 0
                if ($t['amount'] > 0) { // Debt
                    $affects_balance = true;
                    $trx_amt = $t['amount'];
                }
            } else { // Balance < 0 (Payable)
                if ($t['amount'] < 0) { // Credit
                    $affects_balance = true;
                    $trx_amt = abs($t['amount']);
                }
            }
            
            if ($affects_balance) {
                if ($remaining_balance >= $trx_amt) {
                    // Fully open
                    $t['open_amount'] = $trx_amt;
                    $remaining_balance -= $trx_amt;
                } else {
                    // Partially open
                    $t['open_amount'] = $remaining_balance;
                    $remaining_balance = 0;
                }
            }
        }
        unset($t); // Break reference
        $data['wallets'] = $this->wallet->get_active();
        $data['allEntities'] = $this->customer->get_all([], 2000); // For Virman modal
        $data['page_title'] = "Cari Detayı: " . $data['entity']['name'];

        $this->load->view('layout/header', $data);
        $this->load->view('customers/detail', $data);
        $this->load->view('layout/footer');
    }
    public function api_create() {
        header('Content-Type: application/json');
        
        try {
            $data = [
                'name' => $this->input->post('name'),
                'type' => $this->input->post('type') ?: 'customer',
                'phone' => $this->input->post('phone'),
                'email' => $this->input->post('email'),
                'tax_id' => $this->input->post('tax_no') ?: $this->input->post('tax_id'),
                'address' => $this->input->post('address'),
                'balance' => floatval($this->input->post('balance') ?: 0),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            // tax_office alanı varsa ekle (tablo yapısına göre)
            $taxOffice = $this->input->post('tax_office');
            if ($taxOffice && $this->db->field_exists('tax_office', 'inv_entities')) {
                $data['tax_office'] = $taxOffice;
            }
            
            // Validate
            if (empty($data['name'])) {
                echo json_encode(['status' => 'error', 'message' => 'Cari adı zorunludur']);
                return;
            }
            
            $insert_id = $this->customer->create($data);
            
            if ($insert_id) {
                echo json_encode([
                    'status' => 'success',
                    'entity_id' => $insert_id,
                    'message' => 'Cari başarıyla oluşturuldu'
                ]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Cari oluşturulamadı: ' . $this->db->error()['message']]);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Hata: ' . $e->getMessage()]);
        }
    }

    public function api_update() {
        $id = $this->input->post('id');
        if(!$id) show_error('ID Required');
        
        $data = [
            'name' => $this->input->post('name'),
            'type' => $this->input->post('type'),
            'phone' => $this->input->post('phone'),
            'email' => $this->input->post('email'),
            'tax_id' => $this->input->post('tax_id'),
            'address' => $this->input->post('address')
        ];
        
        $this->customer->update($id, $data);
        redirect('customers/detail/'.$id);
    }

    public function api_add_transaction() {
        $entity_id = $this->input->post('entity_id');
        $type = $this->input->post('type');
        $amount = floatval($this->input->post('amount'));
        $wallet_id = $this->input->post('wallet_id');
        
        $final_amount = abs($amount);
        if ($type == 'tahsilat' || $type == 'alacak_dekontu') {
             $final_amount = -1 * $final_amount;
        }
        
        $this->load->model('Invoice_model', 'invoice');
        $type_prefix = 'TRX';
        if ($type == 'tahsilat') $type_prefix = 'THS';
        elseif ($type == 'odeme') $type_prefix = 'ODM';
        elseif ($type == 'borc_dekontu') $type_prefix = 'BRC';
        elseif ($type == 'alacak_dekontu') $type_prefix = 'ALC';
        
        $document_no = $this->invoice->get_next_invoice_no($type_prefix);

        $data = [
            'entity_id' => $entity_id,
            'wallet_id' => !empty($wallet_id) ? $wallet_id : NULL,
            'type' => $type,
            'amount' => $final_amount,
            'document_no' => $document_no,
            'description' => $this->input->post('description'),
            'transaction_date' => $this->input->post('transaction_date'),
            'due_date' => $this->input->post('due_date'),
        ];
        
        $this->transaction->create($data);
        redirect($_SERVER['HTTP_REFERER'] ?? 'entities');
    }
    public function api_transfer() {
        $source_id = $this->input->post('source_entity_id');
        $target_id = $this->input->post('target_entity_id');
        $amount = abs(floatval($this->input->post('amount')));
        $date = $this->input->post('date');
        $description = $this->input->post('description');

        if (!$source_id || !$target_id || !$amount) {
            redirect('entities'); 
            return;
        }

        $source_entity = $this->customer->get_by_id($source_id);
        $target_entity = $this->customer->get_by_id($target_id);



        // Kaynak Cari (Gönderen): Alacak tarafına işlenir (Borcu azalır / Alacağı artar) -> Negatif
        $source_data = [
            'entity_id' => $source_id,
            'type' => 'virman',
            'amount' => -1 * $amount, 
            'description' => $description,
            'document_no' => 'Transfer -> ' . $target_entity['name'],
            'transaction_date' => $date,
            'due_date' => $this->input->post('due_date')
        ];
        $source_trx_id = $this->transaction->create($source_data);

        // Hedef Cari (Alan): Borç tarafına işlenir (Borcu artar / Alacağı azalır) -> Pozitif
        $target_data = [
            'entity_id' => $target_id,
            'type' => 'virman',
            'amount' => $amount,
            'description' => $description,
            'document_no' => 'Transfer <- ' . $source_entity['name'],
            'transaction_date' => $date,
            'due_date' => $this->input->post('due_date'),
            'linked_transaction_id' => $source_trx_id
        ];
        $target_trx_id = $this->transaction->create($target_data);
        
        // Linkleme (Double link: source -> target)
        if ($source_trx_id && $target_trx_id) {
            $this->db->where('id', $source_trx_id)->update('inv_entity_transactions', ['linked_transaction_id' => $target_trx_id]);
        }

        redirect('entities');
    }

    public function api_delete() {
        $id = $this->input->post('id');
        
        if(!$id) {
            echo json_encode(['success' => false, 'message' => 'ID gerekli']);
            return;
        }

        // Check for transactions

        $transactions = $this->transaction->get_all(['entity_id' => $id], 1);
        
        if (!empty($transactions)) {
            echo json_encode(['success' => false, 'message' => 'Bu cariye ait finansal işlemler bulunduğu için silinemez. Önce işlemleri silmelisiniz.']);
            return;
        }

        if ($this->customer->delete($id)) {
             echo json_encode(['success' => true]);
        } else {
             echo json_encode(['success' => false, 'message' => 'Silme işlemi sırasında bir hata oluştu.']);
        }
    }

    public function api_get_share_link() {
        header('Content-Type: application/json');
        $id = $this->input->post('id');
        
        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'ID gerekli']);
            return;
        }
        
        $token = $this->customer->get_access_token($id);
        
        if ($token) {
            echo json_encode([
                'status' => 'success', 
                'link' => site_url('statement/view/' . $token),
                'message' => 'Bağlantı oluşturuldu.'
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Token oluşturulamadı.']);
        }
    }
    public function statement($id) {
        if (!$id) show_404();
        
        $start_date = $this->input->post('start_date') ?: date('Y-m-01');
        $end_date = $this->input->post('end_date') ?: date('Y-m-d');
        $type = $this->input->post('type') ?: 'detailed';
        $format = $this->input->post('format') ?: 'pdf';

        $data['entity'] = $this->customer->get_by_id($id);
        if (!$data['entity']) show_404();

        // 1. Calculate Opening Balance
        $this->db->select_sum('amount');
        $this->db->where('entity_id', $id);
        $this->db->where('transaction_date <', $start_date);
        $query = $this->db->get('inv_entity_transactions');
        $opening_balance = floatval($query->row()->amount);

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

        $data['opening_balance'] = $opening_balance;
        $data['start_date'] = $start_date;
        $data['end_date'] = $end_date;
        $data['type'] = $type; // detailed or summary
        $data['today'] = date('Y-m-d');

        if ($format === 'excel') {
            $this->_export_excel($data);
        } else {
            $this->load->view('customers/statement', $data);
        }
    }

    private function _export_excel($data) {
        $name_clean = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $data['entity']['name']));
        $filename = "Ekstre_" . $name_clean . "_" . date('Ymd') . ".xls"; 
        
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
        echo '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head>';
        echo '<body>';
        echo '<table border="1">';
        
        // Header Info
        echo '<tr><td colspan="6" style="font-size:16px; font-weight:bold; text-align:center;">CARI HESAP EKSTRESI</td></tr>';
        echo '<tr><td colspan="6" style="text-align:center;">' . $data['entity']['name'] . '</td></tr>';
        echo '<tr><td colspan="6" style="text-align:center;">' . date('d.m.Y', strtotime($data['start_date'])) . ' - ' . date('d.m.Y', strtotime($data['end_date'])) . '</td></tr>';
        echo '<tr><td colspan="6"></td></tr>';
        
        // Table Headers
        echo '<tr style="background-color:#f0f0f0; font-weight:bold;">';
        echo '<th>Tarih</th>';
        echo '<th>Islem Turu</th>';
        echo '<th width="300">Aciklama</th>';
        echo '<th>Borc</th>';
        echo '<th>Alacak</th>';
        echo '<th>Bakiye</th>';
        echo '</tr>';
        
        $balance = $data['opening_balance'];
        
        // Opening Balance
        echo '<tr>';
        echo '<td>' . date('d.m.Y', strtotime($data['start_date'])) . '</td>';
        echo '<td>DEVIR</td>';
        echo '<td>Donem Basi Devir Bakiyesi</td>';
        echo '<td>' . ($balance > 0 ? number_format($balance, 2, ',', '.') : '') . '</td>';
        echo '<td>' . ($balance < 0 ? number_format(abs($balance), 2, ',', '.') : '') . '</td>';
        echo '<td style="font-weight:bold;">' . number_format($balance, 2, ',', '.') . '</td>';
        echo '</tr>';
        
        $total_debt = 0;
        $total_credit = 0;

        foreach ($data['transactions'] as $t) {
            $balance += $t['amount'];
            if($t['amount'] > 0) $total_debt += $t['amount'];
            else $total_credit += abs($t['amount']);
            
            $debt = $t['amount'] > 0 ? $t['amount'] : 0;
            $credit = $t['amount'] < 0 ? abs($t['amount']) : 0;
            
            echo '<tr>';
            echo '<td>' . date('d.m.Y', strtotime($t['transaction_date'])) . '</td>';
            echo '<td>' . strtoupper($t['type']) . '</td>';
            echo '<td>' . htmlspecialchars($t['description']) . '</td>';
            echo '<td>' . ($debt > 0 ? number_format($debt, 2, ',', '.') : '') . '</td>';
            echo '<td>' . ($credit > 0 ? number_format($credit, 2, ',', '.') : '') . '</td>';
            echo '<td style="font-weight:bold;">' . number_format(abs($balance), 2, ',', '.') . ' ' . ($balance <= 0 ? '(A)' : '(B)') . '</td>';
            echo '</tr>';

            // Show items if detailed
            if (!empty($t['items'])) {
                echo '<tr><td colspan="6"><table border="1" style="background-color:#fafafa; color:#555;">';
                echo '<tr style="font-size:11px; font-weight:bold;"><th>Urun/Hizmet</th><th>Miktar</th><th>Birim Fiyat</th><th>Tutar</th></tr>';
                foreach($t['items'] as $item) {
                     echo '<tr style="font-size:11px;">';
                     echo '<td>' . htmlspecialchars($item['product_name'] ?? 'Urun') . '</td>';
                     echo '<td>' . number_format($item['quantity'], 2) . ' ' . ($item['unit'] ?? '') . '</td>';
                     echo '<td>' . number_format($item['unit_price'], 2) . '</td>';
                     echo '<td>' . number_format($item['total_amount'], 2) . '</td>';
                     echo '</tr>';
                }
                echo '</table></td></tr>';
            }
        }
        
        // Final Totals
        echo '<tr><td colspan="6"></td></tr>';
        echo '<tr style="font-weight:bold; background-color:#f0f0f0;">';
        echo '<td colspan="3" style="text-align:right;">GENEL BAKIYE</td>';
        echo '<td>' . ($total_debt > 0 ? number_format($total_debt + ($data['opening_balance'] > 0 ? $data['opening_balance'] : 0), 2, ',', '.') : '') . '</td>';
        echo '<td>' . ($total_credit > 0 ? number_format($total_credit + ($data['opening_balance'] < 0 ? abs($data['opening_balance']) : 0), 2, ',', '.') : '') . '</td>';
        echo '<td style="color:' . ($balance <= 0 ? 'green' : 'red') . ';">' . number_format(abs($balance), 2, ',', '.') . ' ' . ($balance <= 0 ? '(A)' : '(B)') . '</td>';
        echo '</tr>';

        echo '</table>';
        echo '</body></html>';
    }
}
