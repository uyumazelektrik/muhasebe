<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Invoices extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Invoice_model', 'invoice');
        $this->load->model('Entity_model', 'entity');
        $this->load->model('Product_model', 'product');
    }

    public function index() {
        $data['page_title'] = 'Fatura/Fiş Yönetimi';
        $data['entities'] = $this->entity->get_all();
        
        $this->load->view('layout/header', $data);
        $this->load->view('invoices/index', $data);
        $this->load->view('layout/footer');
    }







    public function api_get_invoices() {
        $filters = [
            'invoice_no' => $this->input->get('invoice_no'),
            'entity_id' => $this->input->get('entity_id'),
            'type' => $this->input->get('type'),
            'status' => $this->input->get('status'),
            'date_from' => $this->input->get('date_from'),
            'date_to' => $this->input->get('date_to'),
        ];
        
        $limit = $this->input->get('limit') ?: 25;
        $page = $this->input->get('page') ?: 1;
        $offset = ($page - 1) * $limit;
        
        $invoices = $this->invoice->get_invoices($filters, $limit, $offset);
        $total = $this->invoice->count_invoices($filters);
        
        // Prevent caching
        header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
        header("Pragma: no-cache"); // HTTP 1.0.
        header("Expires: 0"); // Proxies.
        
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'data' => $invoices,
            'pagination' => [
                'total' => $total,
                'per_page' => (int)$limit,
                'current_page' => (int)$page,
                'total_pages' => ceil($total / $limit)
            ],
            'debug_query' => $this->db->last_query()
        ]);
    }

    public function detail($id) {
        log_message('error', 'DEBUG: Invoices::detail called with ID: ' . $id);
        $invoice = $this->invoice->get_invoice_by_id($id);
        
        if (!$invoice) {
            log_message('error', 'DEBUG: Invoice not found for ID: ' . $id);
            show_404();
            return;
        }

        // Tahsilat, Ödeme veya Virman ise Finans detayına yönlendir
        if (in_array($invoice['type'], ['tahsilat', 'odeme', 'virman'])) {
            redirect('finance/transaction_detail/' . $id);
            return;
        }
        
        $document_no = $invoice['document_no'] ?: null;
        $invoice_id = isset($invoice['id']) ? $invoice['id'] : null;
        $data['invoice'] = $invoice;
        $data['items'] = $this->invoice->get_invoice_items($document_no, $invoice_id);

        // If this is a payment/collection, try to find the linked invoice/document info
        $data['linked_invoice'] = null;
        $data['linked_txn'] = null;
        
        if (in_array($invoice['type'], ['tahsilat', 'odeme']) && $document_no) {
            $this->db->select('id, type, document_no, amount');
            $this->db->from('inv_entity_transactions');
            $this->db->where('document_no', $document_no);
            $this->db->where_in('type', ['fatura', 'fis']);
            $data['linked_invoice'] = $this->db->get()->row_array();
        } elseif ($invoice['type'] === 'virman' && !empty($invoice['linked_transaction_id'])) {
            $this->db->select('t.*, e.name as entity_name, w.name as wallet_name');
            $this->db->from('inv_entity_transactions t');
            $this->db->join('inv_entities e', 't.entity_id = e.id', 'left');
            $this->db->join('inv_wallets w', 't.wallet_id = w.id', 'left');
            $this->db->where('t.id', $invoice['linked_transaction_id']);
            $data['linked_txn'] = $this->db->get()->row_array();
        }
        
        $data['page_title'] = 'Belge Detayı - ' . ($document_no ?: 'ID #' . $id);
        
        // Data for editing
        $data['entities'] = $this->entity->get_all();
        $data['products'] = $this->product->get_all('', 1000);
        $data['expense_categories'] = $this->db->order_by('name', 'ASC')->get('inv_expense_categories')->result_array();
        $data['wallets'] = $this->db->get('inv_wallets')->result_array();

        $this->load->view('layout/header', $data);
        $this->load->view('invoices/detail', $data);
        $this->load->view('layout/footer');
    }

    public function create() {
        // Ensure db tables exist because product query needs them
        $this->invoice->ensure_tables_exist();
        
        $data['page_title'] = 'Yeni Fatura Oluştur';
        $data['entities'] = $this->entity->get_all();
        $data['products'] = $this->product->get_all('', 1000);
        $data['expense_categories'] = $this->db->order_by('name', 'ASC')->get('inv_expense_categories')->result_array();
        $data['wallets'] = $this->db->get('inv_wallets')->result_array();
        $data['next_invoice_no'] = $this->invoice->get_next_invoice_no();
        
        $this->load->view('layout/header', $data);
        $this->load->view('invoices/create', $data);
        $this->load->view('layout/footer');
    }

    public function upload() {
        $this->invoice->ensure_tables_exist();
        
        $data['page_title'] = 'AI Fatura Yükle';
        $data['entities'] = $this->entity->get_all();
        $data['products'] = $this->product->get_all('', 1000);
        $data['expense_categories'] = $this->db->order_by('name', 'ASC')->get('inv_expense_categories')->result_array();
        $data['wallets'] = $this->db->get('inv_wallets')->result_array();
        $data['next_invoice_no'] = $this->invoice->get_next_invoice_no();
        
        $this->load->view('layout/header', $data);
        $this->load->view('invoices/upload', $data);
        $this->load->view('layout/footer');
    }

    public function api_analyze_invoice() {
        header('Content-Type: application/json');
        
        try {
            $imageData = $this->input->post('image_data');
            $mimeType = $this->input->post('mime_type') ?: 'image/jpeg';
            
            if (empty($imageData)) {
                echo json_encode(['status' => 'error', 'message' => 'Görsel verisi bulunamadı']);
                return;
            }
            
            $this->load->library('Gemini_service');
            
            $systemInstruction = "Sen bir fatura analiz uzmanısın. Türkçe faturaları analiz ediyorsun. 
Verilen fatura görselini analiz et ve aşağıdaki JSON formatında yanıt ver.
Sadece JSON döndür, başka açıklama ekleme.

{
    \"type\": \"Alış Faturası\" veya \"Satış Faturası\" (Alış = Biz bir şey aldık, Satış = Biz bir şey sattık. Eğer faturayı kesen taraf Mustafa Uyumaz ise bu bir Satış Faturasıdır),
    \"invoice_no\": \"Fatura numarası (varsa)\",
    \"invoice_date\": \"YYYY-MM-DD formatında tarih\",
    \"seller\": {
        \"name\": \"Faturayı KESEN/DÜZENLEYEN firma - üst tarafta logolu olan\",
        \"tax_no\": \"Vergi numarası\",
        \"tax_office\": \"Vergi dairesi\",
        \"phone\": \"Telefon numarası\",
        \"email\": \"E-posta adresi\",
        \"address\": \"Adres\"
    },
    \"buyer\": {
        \"name\": \"SAYIN veya MÜŞTERİ bölümündeki firma/kişi\",
        \"tax_no\": \"Vergi numarası\",
        \"tax_office\": \"Vergi dairesi\",
        \"phone\": \"Telefon numarası\",
        \"email\": \"E-posta adresi\",
        \"address\": \"Adres\"
    },
    \"subtotal\": \"KDV hariç ara toplam (sayısal)\",
    \"tax_amount\": \"KDV tutarı (sayısal)\",
    \"total_amount\": \"Genel toplam (sayısal)\",
    \"tax_included\": true veya false,
    \"items\": [
        {
            \"name\": \"Ürün/Hizmet adı\",
            \"quantity\": 1,
            \"unit\": \"Adet\",
            \"unit_price\": 100.00,
            \"tax_rate\": 20,
            \"total\": 120.00
        }
    ]
}

ÖNEMLİ:
1. SELLER = Faturanın üstünde logosu/başlığı olan firma (faturayı kesen)
2. BUYER = 'SAYIN', 'MÜŞTERİ', 'ALICI' yazan bölümdeki firma/kişi
3. KDV DURUMU (tax_included):
   - Eğer 'Genel Toplam' = 'Ara Toplam' + 'KDV' ise, KDV Hariçtir (tax_included: false).
   - Eğer 'Ara Toplam' içerisinde KDV zaten varsa veya 'Genel Toplam' ile 'Ara Toplam' aynı ise KDV Dahildir (tax_included: true).
4. Tüm iletişim bilgilerini (telefon, email, adres) mutlaka çıkar
5. Tutarları sayısal ver, tarih YYYY-MM-DD formatında
6. Okunamayan alanlar için null döndür";

            $prompt = "Bu fatura görselini analiz et ve JSON formatında bilgileri çıkar.";
            
            $result = $this->gemini_service->analyzeImage($imageData, $mimeType, $systemInstruction, $prompt);
            
            echo json_encode([
                'status' => 'success',
                'data' => $result
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function api_save_invoice() {
        header('Content-Type: application/json');
        
        // PHP hatalarını yakala (deprecation uyarılarını atla)
        set_error_handler(function($errno, $errstr, $errfile, $errline) {
            // Deprecation uyarılarını atla (PHP 8.2+ uyumluluk)
            if ($errno === E_DEPRECATED || $errno === E_USER_DEPRECATED) {
                return true;
            }
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        });
        
        try {
            $id = $this->input->post('id'); // Existing transaction ID if updating
            
            // Debug için gelen veriyi logla
            log_message('debug', 'api_save_invoice called. POST data: ' . json_encode($this->input->post()));
            
            // entity_id kontrolü
            $entity_id = $this->input->post('entity_id');
            if (empty($entity_id)) {
                echo json_encode(['status' => 'error', 'message' => 'Lütfen bir cari seçin. entity_id boş geldi.']);
                return;
            }
            
            // Fatura numarası duplicate kontrolü
            $invoice_no = trim($this->input->post('invoice_no'));
            $id = $this->input->post('id'); // Gelen ID (trx id veya invoice id olabilir)
            
            // --- GELİŞMİŞ ID ÇÖZÜMLEME VE DUPLICATE KONTROLÜ ---
            
            $my_inv_id = null;
            $my_trx_id = null;
            
            // 1. Düzenlenen kaydın kimliklerini (Invoice ID ve Transaction ID) tespit et
            if ($id) {
                // A. ID direkt transaction tablosunda var mı?
                $t_row = $this->db->get_where('inv_entity_transactions', ['id' => $id])->row();
                if ($t_row) {
                    $my_trx_id = $t_row->id;
                    $my_inv_id = $t_row->invoice_id; // Varsa al
                    
                    // Transaction var ama invoice_id boş ise, belki document_no ile invoice tablosunda eşleşme vardır
                    if (!$my_inv_id && !empty($t_row->document_no)) {
                         $i_link = $this->db->get_where('inv_invoices', ['invoice_no' => $t_row->document_no])->row();
                         if ($i_link) $my_inv_id = $i_link->id;
                    }
                } 
                
                // B. ID transaction değilse, invoice olabilir
                if (!$my_trx_id) {
                    $i_row = $this->db->get_where('inv_invoices', ['id' => $id])->row();
                    if ($i_row) {
                        $my_inv_id = $i_row->id;
                        // Buna bağlı transaction'ı bulmaya çalış
                        // Önce invoice_id sütunundan
                        $t_link = $this->db->get_where('inv_entity_transactions', ['invoice_id' => $i_row->id])->row();
                        if ($t_link) {
                            $my_trx_id = $t_link->id;
                        } else {
                            // Bulamazsa document_no ile (Sadece fatura tipleri)
                             $this->db->where('document_no', $i_row->invoice_no);
                             $this->db->where_in('type', ['fatura', 'fis', 'invoice', 'purchase', 'sale', 'borc_dekontu', 'alacak_dekontu']);
                             $t_link2 = $this->db->get('inv_entity_transactions')->row();
                             if ($t_link2) $my_trx_id = $t_link2->id;
                        }
                    }
                }
            }
            
            // 2. Duplicate Kontrolü (Her durumda çalışır, self-collision kontrolü içerir)
            if (!empty($invoice_no)) {
                $type = $this->input->post('type');
                $is_formal_invoice = in_array($type, ['purchase', 'sale', 'fatura', 'fis', 'invoice', 'borc_dekontu', 'alacak_dekontu']);
                
                if ($is_formal_invoice) {
                    // A. Inv_invoices tablosunda ara
                    $conflict_msg = null;
                    
                    $dup_inv = $this->db->get_where('inv_invoices', ['invoice_no' => $invoice_no])->row();
                    if ($dup_inv) {
                        // Bulunan kayıt BEN MİYİM?
                        $is_me = false;
                        if ($my_inv_id && $dup_inv->id == $my_inv_id) $is_me = true;
                        
                        // Eğer invoice ID eşleşmediyse, belki transaction üzerinden bana bağlıdır?
                        if (!$is_me && $my_trx_id) {
                            // Bulunan faturanın invoice_id'si ile benim transaction'ım eşleşiyor mu?
                            // (Ters kontrol: benim transaction'ımdaki invoice_id bu mu?)
                            $my_trx_chk = $this->db->get_where('inv_entity_transactions', ['id' => $my_trx_id])->row();
                            if ($my_trx_chk && $my_trx_chk->invoice_id == $dup_inv->id) $is_me = true;
                        }
                        
                        if (!$is_me) {
                            $conflict_msg = 'Bu fatura numarası zaten başka bir faturada kullanılmış: ' . $invoice_no;
                        }
                    }
                    
                    // B. Inv_entity_transactions tablosunda ara (Eğer inv_invoices'ta sorun çıkmadıysa)
                    if (!$conflict_msg) {
                        $this->db->where('document_no', $invoice_no);
                        $this->db->where_in('type', ['fatura', 'fis', 'invoice', 'purchase', 'sale', 'borc_dekontu', 'alacak_dekontu']);
                        $dup_trx = $this->db->get('inv_entity_transactions')->row();
                        
                        if ($dup_trx) {
                            $is_me = false;
                            if ($my_trx_id && $dup_trx->id == $my_trx_id) $is_me = true;
                            
                            // Bulunan transaction benim faturama mı ait?
                            if (!$is_me && $my_inv_id) {
                                if ($dup_trx->invoice_id == $my_inv_id) $is_me = true;
                            }
                            
                            if (!$is_me) {
                                $conflict_msg = 'Bu fatura numarası işlem geçmişinde başka bir kayıtta kullanılmış: ' . $invoice_no;
                            }
                        }
                    }
                    
                    if ($conflict_msg) {
                         echo json_encode(['status' => 'error', 'message' => $conflict_msg]);
                         return;
                    }

                } else {
                    // Stand-alone transactions (tahsilat, odeme, virman vb.)
                    // Bu durumda sadece kendi ID'sini hariç tutması yeterli
                    $this->db->where('document_no', $invoice_no);
                    $this->db->where('type', $type);
                    if ($my_trx_id) $this->db->where('id !=', $my_trx_id);
                    $exists = $this->db->get('inv_entity_transactions')->num_rows() > 0;
                    
                    if ($exists) {
                         echo json_encode(['status' => 'error', 'message' => 'Bu numara başka bir ' . $type . ' işleminde kullanılmış: ' . $invoice_no]);
                         return;
                    }
                }
            }
            
            $invoice_no = trim($this->input->post('invoice_no'));
            $net_amount = $this->input->post('net_amount');
            
            // Auto-generate document number if empty
            if (empty($invoice_no)) {
                $type_prefix = 'INV';
                if ($this->input->post('type') === 'tahsilat') $type_prefix = 'THS';
                elseif ($this->input->post('type') === 'odeme') $type_prefix = 'ODM';
                elseif ($this->input->post('type') === 'virman') $type_prefix = 'VRM';
                
                $invoice_no = $this->invoice->get_next_invoice_no($type_prefix);
            }

            $invoice_data = [
                'invoice_no' => $invoice_no,
                'entity_id' => $entity_id,
                'type' => $this->input->post('type'),
                'invoice_date' => $this->input->post('invoice_date'),
                'due_date' => $this->input->post('due_date'), // Vade tarihi eklendi
                'total_amount' => $this->input->post('total_amount'),
                'tax_amount' => $this->input->post('tax_amount') ?: 0,
                'discount_amount' => $this->input->post('discount_amount') ?: 0,
                'general_discount' => $this->input->post('general_discount') ?: 0,
                'tax_included' => $this->input->post('tax_included') ?: 0,
                
                'payment_status' => $this->input->post('payment_status') ?: 'unpaid',
                'payment_type' => $this->input->post('payment_type') ?: 'cash_bank',
                'wallet_id' => $this->input->post('wallet_id') ?: null,
                'transfer_entity_id' => $this->input->post('transfer_entity_id') ?: null,
                
                'net_amount' => $net_amount,
                'notes' => $this->input->post('notes'),
                'status' => $this->input->post('status') ?: 'finalized'
            ];
            
            // If updating, call the dedicated update method to preserve original transaction ID
            if ($id) {
                // Bu metot stok geri almayı ve eski kalemleri silmeyi içerir
                $invoice_id = $this->invoice->update_invoice_full($id, $invoice_data);
            } else {
                $invoice_id = $this->invoice->create_invoice($invoice_data);
            }
        
            if ($invoice_id) {
            $items = json_decode($this->input->post('items'), true);
            if ($items) {
                foreach ($items as $item) {
                    $product_id = (isset($item['product_id']) && is_numeric($item['product_id'])) ? $item['product_id'] : null;
                    $product_name = $item['product_name'] ?? $item['name'] ?? '';
                    
                    // If product_id was a name string (from Select2 tags), update product_id to null and set name
                    if (!$product_id && empty($product_name) && !empty($item['product_id'])) {
                        $product_name = $item['product_id'];
                    }
                    
                    // --- 1. OTOMATİK ÜRÜN OLUŞTURMA VEYA FİYAT GÜNCELLEME (ALIŞ VE SATIŞ FATURASI İÇİN) ---
                    if (!empty($product_name) && in_array($invoice_data['type'], ['purchase', 'sale']) && ($item['item_type'] ?? 'stok') === 'stok') {
                        // Önce isimle ürünü bul veya varsa product_id'yi kullan
                        $product = null;
                        if ($product_id) {
                            $product = $this->db->get_where('inv_products', ['id' => $product_id])->row_array();
                        } else {
                            // --- AKILLI ÜRÜN EŞLEŞTİRME ---
                            $product_name_clean = trim($product_name);
                            $product_name_normalized = str_replace(' ', '', mb_strtolower($product_name_clean, 'UTF-8'));
                            
                            // 1. Önce birebir isimle ara
                            $product = $this->db->get_where('inv_products', ['name' => $product_name_clean])->row_array();
                            
                            // 2. Eğer bulunamadıysa, tüm boşlukları temizleyip normalize edilmiş haliyle ara (Karakter bazlı tam eşleşme)
                            if (!$product) {
                                $this->db->where("LOWER(REPLACE(name, ' ', '')) =", $product_name_normalized);
                                $product = $this->db->get('inv_products')->row_array();
                            }
                            
                            // 3. Hala bulunamadıysa, match_names (Alias) sütununda ara
                            if (!$product) {
                                $this->db->group_start();
                                $this->db->like('match_names', $product_name_clean);
                                // Ayrıca alias listesindeki her bir öğeyi normalize ederek kontrol etmeyiz ama LIKE yeterli olabilir
                                $this->db->group_end();
                                $product = $this->db->get('inv_products')->row_array();
                                
                                // Doğrula: Alias'lardan biri gerçekten uyuşuyor mu? (Hatalı LIKE eşleşmesini önle)
                                if ($product && !empty($product['match_names'])) {
                                    $aliases = array_map('trim', explode(',', $product['match_names']));
                                    $match_found = false;
                                    foreach ($aliases as $alias) {
                                        if (mb_strtolower($alias, 'UTF-8') === mb_strtolower($product_name_clean, 'UTF-8') ||
                                            str_replace(' ', '', mb_strtolower($alias, 'UTF-8')) === $product_name_normalized) {
                                            $match_found = true;
                                            break;
                                        }
                                    }
                                    if (!$match_found) $product = null; // Geri al, hatalı eşleşme
                                }
                            }
                        }

                        if (!$product) {
                            // Yeni ürün oluştur - sadece tabloda var olan sütunları ekle
                            $productData = ['name' => $product_name];
                            
                            if ($this->db->field_exists('unit', 'inv_products')) {
                                $productData['unit'] = $item['unit'] ?? 'Adet';
                            }
                            if ($this->db->field_exists('stock_quantity', 'inv_products')) {
                                $productData['stock_quantity'] = 0;
                            }
                            
                            // Yeni üründe mevcut fiyatı başlangıç değeri olarak ata
                            if ($invoice_data['type'] === 'purchase' && $this->db->field_exists('last_buy_price', 'inv_products')) {
                                $productData['last_buy_price'] = $item['price'];
                            } else if ($invoice_data['type'] === 'sale' && $this->db->field_exists('satis_fiyat', 'inv_products')) {
                                $productData['satis_fiyat'] = $item['price'];
                            }

                            if ($this->db->field_exists('created_at', 'inv_products')) {
                                $productData['created_at'] = date('Y-m-d H:i:s');
                            }
                            
                            $this->db->insert('inv_products', $productData);
                            $product_id = $this->db->insert_id();
                        } else {
                            $product_id = $product['id'];
                            // Var olan üründe fiyat güncelleme (En yüksek fiyatı koru)
                            $current_price = (float)$item['price'];
                            if ($invoice_data['type'] === 'purchase' && $this->db->field_exists('last_buy_price', 'inv_products')) {
                                if ($current_price > (float)$product['last_buy_price']) {
                                    $this->db->where('id', $product_id)->update('inv_products', ['last_buy_price' => $current_price]);
                                }
                            } else if ($invoice_data['type'] === 'sale' && $this->db->field_exists('satis_fiyat', 'inv_products')) {
                                if ($current_price > (float)$product['satis_fiyat']) {
                                    $this->db->where('id', $product_id)->update('inv_products', ['satis_fiyat' => $current_price]);
                                }
                            }
                        }
                    }

                    $item_data = [
                        'invoice_id' => $invoice_id,
                        'product_id' => $product_id,
                        'expense_category_id' => ($item['item_type'] ?? 'stok') === 'gider' ? $item['product_id'] : null,
                        'item_type' => $item['item_type'] ?? 'stok',
                        'description' => $product_name,
                        'unit' => $item['unit'] ?? 'Adet',
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['price'],
                        'discount_rate' => $item['discount_rate'] ?? 0,
                        'discount_amount' => $item['discount_amount'] ?? 0,
                        'tax_rate' => $item['tax_rate'],
                        'tax_amount' => $item['tax_amount'],
                        'total_amount' => $item['total']
                    ];
                    $this->invoice->add_invoice_item($item_data);

                    // --- 2. STOK GÜNCELLEME ---
                    if ($product_id && ($item['item_type'] ?? 'stok') === 'stok' && $invoice_data['status'] === 'finalized') {
                        // Stok miktarını güncelle
                        $qty_change = $invoice_data['type'] === 'purchase' ? (float)$item['quantity'] : -(float)$item['quantity'];
                        $this->db->set('stock_quantity', 'stock_quantity + ' . $qty_change, FALSE);
                        $this->db->where('id', $product_id)->update('inv_products');
                    }
                }
            }
            
            // --- ID Fix for Redirection ---
            // Frontend should receive the Transaction ID to avoid collision with other tables
            $redirect_id = $invoice_id;
            
            $search_type = $invoice_data['type'];
            // Map types to transaction table values
            if ($search_type == 'purchase' || $search_type == 'sale') $search_type = 'fatura';
            // Dekontlar kendi tiplerini korur (borc_dekontu, alacak_dekontu)
            
            $this->db->where('invoice_id', $invoice_id);
            $this->db->where('type', $search_type);
            $this->db->order_by('id', 'DESC');
            $main_trx = $this->db->get('inv_entity_transactions')->row();
            
            if ($main_trx) {
                $redirect_id = $main_trx->id;
            }
            
            echo json_encode(['status' => 'success', 'invoice_id' => $redirect_id]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Fatura kaydedilemedi: ' . $this->db->error()['message']]);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Hata: ' . $e->getMessage()]);
        }
    }

    public function api_delete_invoice() {
        header('Content-Type: application/json');
        try {
            $id = $this->input->post('id');
            if ($this->invoice->delete_invoice($id)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Fatura silinemedi: Kayıt bulunamadı.']);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Sistem Hatası: ' . $e->getMessage()]);
        }
    }

    public function api_update_item() {
        $id = $this->input->post('id');
        $quantity = floatval($this->input->post('quantity'));
        $unit_price = floatval($this->input->post('unit_price'));
        $tax_rate = floatval($this->input->post('tax_rate'));
        
        // Calculate tax amount
        $line_total = $quantity * $unit_price;
        $tax_amount = $line_total * ($tax_rate / 100);
        
        $data = [
            'quantity' => $quantity,
            'unit_price' => $unit_price,
            'tax_rate' => $tax_rate,
            'tax_amount' => $tax_amount,
            'total_amount' => $line_total + $tax_amount
        ];
        
        $this->db->where('id', $id);
        if ($this->db->update('inv_invoice_items', $data)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Güncelleme başarısız']);
        }
    }

    public function api_delete_item() {
        $id = $this->input->post('id');
        
        $this->db->where('id', $id);
        if ($this->db->delete('inv_invoice_items')) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Silme başarısız']);
        }
    }
    public function debug_model() {
        if (!is_cli()) {
            echo '<pre>';
        }
        
        $this->load->model('Invoice_model', 'invoice');
        
        echo "Testing Invoice_model::get_invoices...\n";
        
        // Force reset query builder just in case
        $this->db->reset_query();
        
        $filters = [];
        $result = $this->invoice->get_invoices($filters, 25, 0);
        
        echo "Last Query:\n" . $this->db->last_query() . "\n\n";
        
        echo "Result Count: " . count($result) . "\n";
        echo "Row Types:\n";
        foreach ($result as $row) {
             echo "ID: " . $row['id'] . " - Type: " . $row['type'] . "\n";
        }
        
        if (!is_cli()) {
            echo '</pre>';
        }
    }
}
