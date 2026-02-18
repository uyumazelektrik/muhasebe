<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class FinanceReports extends MY_Controller {

    public function __construct() {
        parent::__construct();
        // Sadece Admin ve Muhasebe (yetki kontrolü eklenebilir)
        if (current_role() !== 'admin') {
            redirect('dashboard');
        }
        $this->load->model('Invoice_model', 'invoice');
        $this->load->model('Transaction_model', 'transaction');
        $this->load->model('Wallet_model', 'wallet');
    }

    public function index() {
        $data['page_title'] = 'Finansal Raporlar';
        
        // Tarih Aralığı (Varsayılan: Bu Ay)
        $start_date = $this->input->get('start_date') ?: date('Y-m-01');
        $end_date = $this->input->get('end_date') ?: date('Y-m-t');

        // 1. Özet Kartlar
        $data['summary'] = $this->get_summary($start_date, $end_date);
        
        // 2. KDV Raporu (Basit)
        $data['vat_report'] = $this->get_vat_report($start_date, $end_date);

        // 3. Gelir/Gider Dağılımı
        $data['income_expense'] = $this->get_income_expense($start_date, $end_date);

        // 4. Borç/Alacak Yaşlandırma (Vade Analizi)
        // Vade tarihi sütunu olmadığı için Fatura Tarihi baz alınır.
        $data['aging'] = $this->get_aging_stats();

        $data['start_date'] = $start_date;
        $data['end_date'] = $end_date;

        $this->load->view('layout/header', $data);
        $this->load->view('finance_reports/index', $data);
        $this->load->view('layout/footer');
    }

    /**
     * Finansal Raporlar - GELİŞMİŞ VADE ANALİZİ (FIFO Bakiye Dağıtımı)
     * 
     * Mantık: 
     * Sistemsel olarak faturalar "Ödendi" işaretlenmemiş olabilir ancak Cari Bakiye (Balance) esastır.
     * Bir carinin borcu varsa, bu borç EN YENİ faturalardan kaynaklanıyordur (Eskilerin ödendiği varsayılır - FIFO).
     * 
     * İşleyiş:
     * 1. Bakiyesi olan tüm carileri çek.
     * 2. Her cari için faturaları YENİDEN ESKİYE çek.
     * 3. Cari bakiyesini bu faturalara dağıt.
     * 4. Hangi faturaya ne kadar bakiye düşüyorsa, o tutarı faturanın VADE TARİHİNE göre yaşlandır.
     * 
     * Kategoriler:
     * - vadesi_gelecek: Vade tarihi bugünden sonra
     * - vadesi_gelen: Vade tarihi bugün
     * - 0-30: 0-30 gün geçmiş
     * - 31-60: 31-60 gün geçmiş
     * - 61-90: 61-90 gün geçmiş
     * - 90+: 90+ gün geçmiş
     */
    private function get_aging_stats() {
        // 1. Bakiyesi Olan Carileri Çek (Sıfır olmayanlar)
        $entities = $this->db->select('id, name, balance')
            ->where('balance !=', 0)
            ->get('inv_entities')
            ->result_array();

        $receivables = [
            'total' => 0, 
            'future_90_plus' => 0,
            'future_61_90' => 0,
            'future_31_60' => 0,
            'future_0_30' => 0,
            'vadesi_gelen' => 0, 
            '0-30' => 0, 
            '31-60' => 0, 
            '61-90' => 0, 
            '90+' => 0
        ];
        $payables = [
            'total' => 0, 
            'future_90_plus' => 0,
            'future_61_90' => 0,
            'future_31_60' => 0,
            'future_0_30' => 0,
            'vadesi_gelen' => 0, 
            '0-30' => 0, 
            '31-60' => 0, 
            '61-90' => 0, 
            '90+' => 0
        ];
        
        $now = new DateTime();

        foreach ($entities as $entity) {
            $balance = (float)$entity['balance'];
            
            if (abs($balance) < 0.01) continue; 

            $is_receivable = $balance > 0;
            $target_balance = abs($balance);
            
            // Query inv_entity_transactions instead of inv_invoices
            // Receivables (>0) -> Look for items that increased balance (>0) (Sales, Debt Notes)
            // Payables (<0) -> Look for items that decreased balance (<0) (Purchases, Credit Notes)
            $operator = $is_receivable ? '>' : '<';
            
            $trxs = $this->db->select('transaction_date, due_date, amount')
                ->where('entity_id', $entity['id'])
                ->where("amount $operator", 0)
                ->order_by('transaction_date', 'DESC')
                ->order_by('id', 'DESC')
                ->get('inv_entity_transactions')
                ->result_array();

            $remaining_balance = $target_balance;

            foreach ($trxs as $trx) {
                if ($remaining_balance <= 0) break;

                $trx_amt = abs((float)$trx['amount']);
                
                $open_amount = ($remaining_balance >= $trx_amt) ? $trx_amt : $remaining_balance;
                
                $due_date_str = !empty($trx['due_date']) ? $trx['due_date'] : date('Y-m-d', strtotime($trx['transaction_date'] . ' +30 days'));
                $due_date = new DateTime($due_date_str);
                
                $interval = $now->diff($due_date);
                $days_diff = (int)$interval->format('%R%a'); 
                
                $bucket = '0-30';
                
                if ($days_diff > 0) {
                    if ($days_diff <= 30) $bucket = 'future_0_30';
                    elseif ($days_diff <= 60) $bucket = 'future_31_60';
                    elseif ($days_diff <= 90) $bucket = 'future_61_90';
                    else $bucket = 'future_90_plus';
                } elseif ($days_diff == 0) {
                    $bucket = 'vadesi_gelen';
                } else {
                    $overdue_days = abs($days_diff);
                    if ($overdue_days > 90) $bucket = '90+';
                    elseif ($overdue_days > 60) $bucket = '61-90';
                    elseif ($overdue_days > 30) $bucket = '31-60';
                    else $bucket = '0-30';
                }

                if ($is_receivable) {
                    $receivables[$bucket] += $open_amount;
                    $receivables['total'] += $open_amount;
                } else {
                    $payables[$bucket] += $open_amount;
                    $payables['total'] += $open_amount;
                }

                $remaining_balance -= $open_amount;
            }

            // Residual balance (Opening balance or untracked debt)
            if ($remaining_balance > 0.01) {
                 if ($is_receivable) {
                    $receivables['90+'] += $remaining_balance;
                    $receivables['total'] += $remaining_balance;
                } else {
                    $payables['90+'] += $remaining_balance;
                    $payables['total'] += $remaining_balance;
                }
            }
        }

        // --- WALLETS INTEGRATION (Kredi / Kredi Kartı Borçları) ---
        // Bakiyesi negatif olan kasalar (Borçlu olduğumuz hesaplar)
        $wallets = $this->db->select('id, name, balance')
            ->where('balance <', -0.01) // Sadece borçlu olunan (negatif) kasalar
            ->get('inv_wallets')
            ->result_array();

        foreach ($wallets as $wallet) {
            $target_balance = abs((float)$wallet['balance']);
            
            // Kasa bakiyesini düşüren (borcu oluşturan) işlemleri çek: Ödeme, Gider
            // Wallet_model logic: odeme, payment, expense -> reduces balance
            $trxs = $this->db->select('transaction_date, due_date, amount')
                ->where('wallet_id', $wallet['id'])
                ->group_start()
                    ->where_in('type', ['odeme', 'payment', 'expense'])
                    ->or_where('amount <', 0) // Veya doğrudan negatif olanlar
                ->group_end()
                ->order_by('transaction_date', 'DESC')
                ->order_by('id', 'DESC')
                ->get('inv_entity_transactions')
                ->result_array();

            $remaining_balance = $target_balance;

            foreach ($trxs as $trx) {
                if ($remaining_balance <= 0) break;

                $trx_amt = abs((float)$trx['amount']);
                $open_amount = ($remaining_balance >= $trx_amt) ? $trx_amt : $remaining_balance;
                
                $due_date_str = !empty($trx['due_date']) ? $trx['due_date'] : date('Y-m-d', strtotime($trx['transaction_date'] . ' +30 days'));
                $due_date = new DateTime($due_date_str);
                
                $interval = $now->diff($due_date);
                $days_diff = (int)$interval->format('%R%a'); 
                
                $bucket = '0-30';
                
                 if ($days_diff > 0) {
                    if ($days_diff <= 30) $bucket = 'future_0_30';
                    elseif ($days_diff <= 60) $bucket = 'future_31_60';
                    elseif ($days_diff <= 90) $bucket = 'future_61_90';
                    else $bucket = 'future_90_plus';
                } elseif ($days_diff == 0) {
                    $bucket = 'vadesi_gelen';
                } else {
                    $overdue_days = abs($days_diff);
                    if ($overdue_days > 90) $bucket = '90+';
                    elseif ($overdue_days > 60) $bucket = '61-90';
                    elseif ($overdue_days > 30) $bucket = '31-60';
                    else $bucket = '0-30';
                }

                // Add to Payables
                $payables[$bucket] += $open_amount;
                $payables['total'] += $open_amount;

                $remaining_balance -= $open_amount;
            }

            // Residual Wallet Balance -> 90+
            if ($remaining_balance > 0.01) {
                $payables['90+'] += $remaining_balance;
                $payables['total'] += $remaining_balance;
            }
        }
        
        return ['receivables' => $receivables, 'payables' => $payables];
    }

    public function api_get_aging_details() {
        $type = $this->input->post('type'); // 'receivables' or 'payables'
        $bucket = $this->input->post('bucket'); 

        if (!$type || !$bucket) {
            $this->output->set_content_type('application/json')->set_output(json_encode(['error' => 'Eksik Parametre']));
            return;
        }

        $results = []; 
        
        $entities = $this->db->select('id, name, balance')
            ->where('balance !=', 0)
            ->get('inv_entities')
            ->result_array();

        $now = new DateTime();

        foreach ($entities as $entity) {
            $balance = (float)$entity['balance'];
            if (abs($balance) < 0.01) continue;

            $is_receivable = $balance > 0;
            
            // Filter by requested type
            if ($type == 'receivables' && !$is_receivable) continue;
            if ($type == 'payables' && $is_receivable) continue;

            $target_balance = abs($balance);
            $operator = $is_receivable ? '>' : '<';

            // Query transactions instead of invoices
            $trxs = $this->db->select('id, type, document_no, transaction_date, due_date, amount')
                ->where('entity_id', $entity['id'])
                ->where("amount $operator", 0)
                ->order_by('transaction_date', 'DESC')
                ->order_by('id', 'DESC')
                ->get('inv_entity_transactions')
                ->result_array();

            $remaining_balance = $target_balance;
            
            foreach ($trxs as $trx) {
                if ($remaining_balance <= 0) break;

                $trx_amt = abs((float)$trx['amount']);
                $open_amount = ($remaining_balance >= $trx_amt) ? $trx_amt : $remaining_balance;
                
                $due_date_str = !empty($trx['due_date']) ? $trx['due_date'] : date('Y-m-d', strtotime($trx['transaction_date'] . ' +30 days'));
                $due_date = new DateTime($due_date_str);
                
                $interval = $now->diff($due_date);
                $days_diff = (int)$interval->format('%R%a');
                
                $current_bucket = '0-30';
                
                if ($days_diff > 0) {
                    if ($days_diff <= 30) $current_bucket = 'future_0_30';
                    elseif ($days_diff <= 60) $current_bucket = 'future_31_60';
                    elseif ($days_diff <= 90) $current_bucket = 'future_61_90';
                    else $current_bucket = 'future_90_plus';
                } elseif ($days_diff == 0) {
                    $current_bucket = 'vadesi_gelen';
                } else {
                    $overdue_days = abs($days_diff);
                    if ($overdue_days > 90) $current_bucket = '90+';
                    elseif ($overdue_days > 60) $current_bucket = '61-90';
                    elseif ($overdue_days > 30) $current_bucket = '31-60';
                    else $current_bucket = '0-30';
                }

                if ($current_bucket == $bucket) {
                    $results[] = [
                        'invoice_no' => $trx['document_no'] ?: 'Belgesiz İşlem',
                        'entity_name' => $entity['name'],
                        'entity_id' => $entity['id'],
                        'invoice_date' => $trx['transaction_date'],
                        'due_date' => $due_date_str,
                        'days_diff' => $days_diff,
                        'total_amount' => $open_amount,
                        'id' => $trx['id'],
                        'type' => $trx['type']
                    ];
                }

                $remaining_balance -= $open_amount;
            }
            
            // Residual Balance (90+)
            if ($remaining_balance > 0.01 && $bucket == '90+') {
                 $results[] = [
                    'invoice_no' => 'DEVİR/BAKİYE',
                    'entity_name' => $entity['name'],
                    'entity_id' => $entity['id'],
                    'invoice_date' => date('Y-m-d'), 
                    'due_date' => date('Y-m-d'),
                    'days_diff' => '90+',
                    'total_amount' => $remaining_balance,
                    'id' => null,
                    'type' => 'devir'
                ];
            }
        }

        // --- WALLETS INTEGRATION FOR DETAILS (Kredi / Kredi Kartı) ---
        if ($type == 'payables') { // Wallets are treated as payables if negative
             $wallets = $this->db->select('id, name, balance')
                ->where('balance <', -0.01)
                ->get('inv_wallets')
                ->result_array();

            foreach ($wallets as $wallet) {
                $target_balance = abs((float)$wallet['balance']);
                
                $trxs = $this->db->select('id, type, document_no, transaction_date, due_date, amount, description')
                    ->where('wallet_id', $wallet['id'])
                    ->group_start()
                        ->where_in('type', ['odeme', 'payment', 'expense'])
                        ->or_where('amount <', 0)
                    ->group_end()
                    ->order_by('transaction_date', 'DESC')
                    ->order_by('id', 'DESC')
                    ->get('inv_entity_transactions')
                    ->result_array();

                $remaining_balance = $target_balance;
                
                foreach ($trxs as $trx) {
                    if ($remaining_balance <= 0) break;

                    $trx_amt = abs((float)$trx['amount']);
                    $open_amount = ($remaining_balance >= $trx_amt) ? $trx_amt : $remaining_balance;
                    
                    $due_date_str = !empty($trx['due_date']) ? $trx['due_date'] : date('Y-m-d', strtotime($trx['transaction_date'] . ' +30 days'));
                    $due_date = new DateTime($due_date_str);
                    
                    $interval = $now->diff($due_date);
                    $days_diff = (int)$interval->format('%R%a');
                    
                    $current_bucket = '0-30';
                    
                     if ($days_diff > 0) {
                        if ($days_diff <= 30) $current_bucket = 'future_0_30';
                        elseif ($days_diff <= 60) $current_bucket = 'future_31_60';
                        elseif ($days_diff <= 90) $current_bucket = 'future_61_90';
                        else $current_bucket = 'future_90_plus';
                    } elseif ($days_diff == 0) {
                        $current_bucket = 'vadesi_gelen';
                    } else {
                        $overdue_days = abs($days_diff);
                        if ($overdue_days > 90) $current_bucket = '90+';
                        elseif ($overdue_days > 60) $current_bucket = '61-90';
                        elseif ($overdue_days > 30) $current_bucket = '31-60';
                        else $current_bucket = '0-30';
                    }

                    if ($current_bucket == $bucket) {
                        $desc = !empty($trx['description']) ? $trx['description'] : 'Kredi/Kart İşlemi';
                        $results[] = [
                            'invoice_no' => $trx['document_no'] ?: $desc, // Show desc if no doc no for wallets
                            'entity_name' => $wallet['name'] . ' (KASA/KART)', // Distinguish wallets
                            'entity_id' => $wallet['id'],
                            'invoice_date' => $trx['transaction_date'],
                            'due_date' => $due_date_str,
                            'days_diff' => $days_diff,
                            'total_amount' => $open_amount,
                            'id' => $trx['id'],
                            'type' => $trx['type'],
                            'is_wallet' => true // Flag for view link
                        ];
                    }

                    $remaining_balance -= $open_amount;
                }

                // Residual Wallet Balance
                if ($remaining_balance > 0.01 && $bucket == '90+') {
                     $results[] = [
                        'invoice_no' => 'DEVİR/BAKİYE',
                        'entity_name' => $wallet['name'] . ' (KASA/KART)',
                        'entity_id' => $wallet['id'],
                        'invoice_date' => date('Y-m-d'), 
                        'due_date' => date('Y-m-d'),
                        'days_diff' => '90+',
                        'total_amount' => $remaining_balance,
                        'id' => null,
                        'type' => 'devir',
                        'is_wallet' => true
                    ];
                }
            }
        }

        // Sort by Due Date
        usort($results, function($a, $b) {
            return strtotime($a['due_date']) - strtotime($b['due_date']);
        });

        // Generate HTML
        $html = '<div class="overflow-x-auto"><table class="w-full text-sm text-left table-auto min-w-[800px]">';
        $html .= '<thead class="text-xs uppercase bg-slate-50 dark:bg-slate-900/50 text-slate-500 sticky top-0 z-10">
                    <tr>
                        <th class="px-3 py-3 whitespace-nowrap w-[20%]">Belge No / Açıklama</th>
                        <th class="px-3 py-3 w-[25%]">Cari / Hesap</th>
                        <th class="px-3 py-3 whitespace-nowrap w-[10%]">Tarih</th>
                        <th class="px-3 py-3 whitespace-nowrap w-[10%]">Vade Tarihi</th>
                        <th class="px-3 py-3 text-center whitespace-nowrap w-[15%]">Durum</th>
                        <th class="px-3 py-3 text-right whitespace-nowrap w-[10%]">Açık Tutar</th>
                        <th class="px-3 py-3 text-right w-[10%]"></th>
                    </tr>
                  </thead><tbody class="divide-y divide-slate-100 dark:divide-slate-700">';
        
        if (empty($results)) {
            $html .= '<tr><td colspan="7" class="px-4 py-8 text-center text-slate-500">Görüntülenecek kayıt yok.</td></tr>';
        }

        foreach ($results as $row) {
            $inv_date = date('d.m.Y', strtotime($row['invoice_date']));
            $due_date = date('d.m.Y', strtotime($row['due_date']));
            $amt = number_format($row['total_amount'], 2, ',', '.') . ' ₺';
            
            $link_html = '';
            if ($row['id']) {
                 // Link destination depends on type
                 $link = '#';
                 if (!empty($row['is_wallet'])) {
                     $link = base_url('finance/transaction_detail/' . $row['id']);
                 }
                 elseif (in_array($row['type'], ['fatura', 'fis', 'invoice', 'purchase', 'sale'])) {
                     $link = base_url('invoices/detail/' . $row['id']);
                 } else {
                     $link = base_url('finance/transaction_detail/' . $row['id']);
                 }

                 $link_html = "<a href='{$link}' target='_blank' class='inline-flex items-center justify-center w-8 h-8 text-blue-500 hover:text-blue-700 hover:bg-blue-50 dark:hover:bg-blue-500/10 rounded-full transition-colors' title='Görüntüle'>
                                <span class='text-lg leading-none'>&rarr;</span>
                            </a>";
            } else {
                $link_html = "<span class='text-xs text-slate-400 italic'>-</span>";
            }
            
            $status_label = '';
            $status_class = '';
            if (is_numeric($row['days_diff'])) {
                if ($row['days_diff'] > 0) {
                    $status_label = '+' . $row['days_diff'] . ' Gün';
                    $status_class = 'bg-green-100 dark:bg-green-500/20 text-green-600 dark:text-green-400';
                } elseif ($row['days_diff'] == 0) {
                    $status_label = 'BUGÜN';
                    $status_class = 'bg-yellow-100 dark:bg-yellow-500/20 text-yellow-600 dark:text-yellow-400';
                } else {
                    $status_label = abs($row['days_diff']) . ' Gün Geçmiş';
                    $status_class = 'bg-red-100 dark:bg-red-500/20 text-red-600 dark:text-red-400';
                }
            } else {
                $status_label = $row['days_diff'];
                $status_class = 'bg-red-100 dark:bg-red-500/20 text-red-600 dark:text-red-400';
            }
            
            if (!empty($row['is_wallet'])) {
                $entity_link = base_url('finance/wallet_detail/' . $row['entity_id']);
            } else {
                $entity_link = base_url('customers/detail/' . $row['entity_id']);
            }
            
            // Limit text length for display
            $display_no = mb_strlen($row['invoice_no']) > 50 ? mb_substr($row['invoice_no'], 0, 47) . '...' : $row['invoice_no'];
            $display_name = mb_strlen($row['entity_name']) > 40 ? mb_substr($row['entity_name'], 0, 37) . '...' : $row['entity_name'];

            $html .= "<tr class='hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors'>
                        <td class='px-3 py-3 font-medium text-slate-700 dark:text-slate-300 break-words' title='".htmlspecialchars($row['invoice_no'])."'>
                            <div class='line-clamp-2'>{$display_no}</div>
                        </td>
                        <td class='px-3 py-3'>
                            <a href='{$entity_link}' target='_blank' class='text-slate-800 dark:text-slate-200 hover:text-blue-600 dark:hover:text-blue-400 font-medium transition-colors border-b border-transparent hover:border-blue-500 line-clamp-2' title='".htmlspecialchars($row['entity_name'])."'>
                                {$display_name}
                            </a>
                        </td>
                        <td class='px-3 py-3 whitespace-nowrap text-slate-500 dark:text-slate-400'>{$inv_date}</td>
                        <td class='px-3 py-3 whitespace-nowrap font-medium text-slate-700 dark:text-slate-300'>{$due_date}</td>
                        <td class='px-3 py-3 text-center whitespace-nowrap'>
                            <span class='inline-block {$status_class} px-2 py-0.5 rounded text-[11px] font-bold whitespace-nowrap'>
                                {$status_label}
                            </span>
                        </td>
                        <td class='px-3 py-3 text-right font-bold whitespace-nowrap text-slate-800 dark:text-slate-200'>{$amt}</td>
                        <td class='px-3 py-3 text-right whitespace-nowrap'>
                            {$link_html}
                        </td>
                      </tr>";
        }
        $html .= '</tbody></table></div>';

        $this->output->set_content_type('application/json')->set_output(json_encode(['html' => $html]));
    }

    private function get_summary($start, $end) {
        // Satışlar (KDV Dahil/Hariç)
        $sales = $this->db->select_sum('total_amount')->select_sum('net_amount')
            ->where('type', 'sale')
            ->where('invoice_date >=', $start)
            ->where('invoice_date <=', $end)
            ->get('inv_invoices')->row();

        // Alışlar
        $purchases = $this->db->select_sum('total_amount')->select_sum('net_amount')
            ->where('type', 'purchase')
            ->where('invoice_date >=', $start)
            ->where('invoice_date <=', $end)
            ->get('inv_invoices')->row();

        // Tahsilatlar
        $collections = $this->db->select_sum('amount')
            ->where('type', 'tahsilat')
            ->where('transaction_date >=', $start)
            ->where('transaction_date <=', $end)
            ->get('inv_entity_transactions')->row();

        // Ödemeler
        $payments = $this->db->select_sum('amount')
            ->where('type', 'odeme')
            ->where('transaction_date >=', $start)
            ->where('transaction_date <=', $end)
            ->get('inv_entity_transactions')->row();

        return [
            'sales_gross' => $sales->total_amount ?? 0,
            'sales_net' => $sales->net_amount ?? 0,
            'purchases_gross' => $purchases->total_amount ?? 0,
            'purchases_net' => $purchases->net_amount ?? 0,
            'collections' => abs($collections->amount ?? 0),
            'payments' => $payments->amount ?? 0
        ];
    }

    private function get_vat_report($start, $end) {
        $sales_vat = $this->db->select_sum('tax_amount')
            ->where('type', 'sale')
            ->where('invoice_date >=', $start)->where('invoice_date <=', $end)
            ->get('inv_invoices')->row()->tax_amount ?? 0;

        $purchase_vat = $this->db->select_sum('tax_amount')
            ->where('type', 'purchase')
            ->where('invoice_date >=', $start)->where('invoice_date <=', $end)
            ->get('inv_invoices')->row()->tax_amount ?? 0;

        return [
            'collected' => $sales_vat, // Hesaplanan KDV
            'paid' => $purchase_vat,   // İndirilecek KDV
            'balance' => $sales_vat - $purchase_vat // Ödenecek KDV (+) veya Devreden (-)
        ];
    }
    
    // Basit Kar/Zarar Yaklaşımı (Mal Alışı Gider Sayılır prensibi basitleştirilmiştir)
    // Gerçek muhasebede SMM (Satılan Malın Maliyeti) hesaplanmalı ama burada basit Cashflow bakıyoruz.
    private function get_income_expense($start, $end) {
         // Gelirler: Satışlar + Diğer Gelirler
         // Giderler: Alışlar + Operasyonel Giderler (Maaş, Kira vb - Bunlar transaction'da tipi ne?)
         
         // Expense Categories ile yapılan harcamalar 'expense' tipinde olabilir
         $expenses = $this->db->select_sum('amount')
            ->where('type', 'expense')
            ->where('wallet_id >', 0)
            ->where('transaction_date >=', $start)->where('transaction_date <=', $end)
            ->get('inv_entity_transactions')->row()->amount ?? 0;
         
         return [
             'operational_expense' => $expenses
         ];
    }


}
