<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Jobs extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Job_model', 'job');
        $this->load->model('Customer_model', 'customer');
        $this->load->model('Product_model', 'product');
    }

    public function index() {
        $filters = [
            'status' => $this->input->get('status'),
            'search' => $this->input->get('search')
        ];
        
        $data['jobs'] = $this->job->get_all($filters);
        $data['page_title'] = "İş Takibi";
        
        $this->load->view('layout/header', $data);
        $this->load->view('jobs/list', $data);
        $this->load->view('layout/footer');
    }

    public function detail($id) {
        $data['job'] = $this->job->get_by_id($id);
        if (!$data['job']) show_404();
        
        $data['materials'] = $this->job->get_materials($id);
        $data['page_title'] = "İş Detayı: #" . $id;
        
        $this->load->view('layout/header', $data);
        $this->load->view('jobs/detail', $data);
        $this->load->view('layout/footer');
    }

    public function create() {
        $this->load->model('Customer_model', 'customer');
        $data['customers'] = $this->customer->get_all([], 2000);
        $data['page_title'] = "Yeni İş Formu";
        
        $this->load->view('layout/header', $data);
        $this->load->view('jobs/create', $data);
        $this->load->view('layout/footer');
    }

    public function api_create() {
        header('Content-Type: application/json');
        try {
            $customer_id = $this->input->post('customer_id');
            $description = $this->input->post('description');
            $job_date = $this->input->post('job_date') ?: date('Y-m-d');

            if (!$customer_id || !$description) {
                throw new Exception("Müşteri ve İş Tanımı zorunludur.");
            }

            $customer = $this->customer->get_by_id($customer_id);
            if (!$customer) throw new Exception("Müşteri bulunamadı.");

            $data = [
                'customer_id' => $customer_id,
                'customer_name_text' => $customer['name'],
                'description' => $description,
                'job_date' => $job_date,
                'status' => 'Pending',
                'user_id' => $this->session->userdata('user_id') ?: 1
            ];

            $job_id = $this->job->create($data);
            echo json_encode(['status' => 'success', 'job_id' => $job_id, 'message' => 'İş başarıyla oluşturuldu.']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function api_add_material() {
        header('Content-Type: application/json');
        try {
            $job_id = $this->input->post('job_id');
            $product_id = $this->input->post('product_id');
            $quantity = $this->input->post('quantity');
            
            if (!$job_id || !$product_id || !$quantity) {
                throw new Exception("Eksik parametre.");
            }

            $product = $this->product->get_by_id($product_id);
            if (!$product) throw new Exception("Ürün bulunamadı.");

            $data = [
                'job_id' => $job_id,
                'product_id' => $product_id,
                'quantity' => $quantity,
                'unit_price' => $product['satis_fiyat'],
                'item_date' => date('Y-m-d')
            ];

            $this->job->add_material($data);
            $this->job->update_total($job_id);

            echo json_encode(['status' => 'success', 'message' => 'Malzeme eklendi.']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function api_remove_material() {
        header('Content-Type: application/json');
        try {
            $id = $this->input->post('id');
            $job_id = $this->input->post('job_id');
            
            if (!$id || !$job_id) throw new Exception("Geçersiz işlem.");

            $this->job->remove_material($id);
            $this->job->update_total($job_id);

            echo json_encode(['status' => 'success', 'message' => 'Malzeme kaldırıldı.']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function api_update_status() {
        header('Content-Type: application/json');
        try {
            $id = $this->input->post('id');
            $status = $this->input->post('status');

            if (!in_array($status, ['Pending', 'In Progress', 'Completed', 'Cancelled'])) {
                // Also accept Turkish for legacy/convenience if needed, but let's stick to English UI
                throw new Exception("Geçersiz durum.");
            }

            $this->job->update($id, ['status' => $status]);
            echo json_encode(['status' => 'success', 'message' => 'Durum güncellendi.']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function api_delete_job() {
        header('Content-Type: application/json');
        try {
            if ($this->session->userdata('role') !== 'admin') {
                throw new Exception("Yetkisiz erişim.");
            }

            $id = $this->input->post('id');
            if (!$id) throw new Exception("ID gerekli.");

            $job = $this->job->get_by_id($id);
            if (!$job) throw new Exception("İş bulunamadı.");

/*
            if ($job['status'] !== 'Cancelled') {
                throw new Exception("Sadece iptal edilen işler silinebilir.");
            }
*/

            // Delete job materials first
            $this->db->delete('job_items', ['job_id' => $id]);
            // Delete job
            $this->db->delete('jobs', ['id' => $id]);

            echo json_encode(['status' => 'success', 'message' => 'İş kaydı ve malzemeleri silindi.']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function api_invoice_job() {
        header('Content-Type: application/json');
        try {
            $id = $this->input->post('id');
            if (!$id) throw new Exception("İş ID belirtilmedi.");
            
            $result = $this->job->bill_job($id);
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function api_parse_text_items() {
        // Output buffering to catch any PHP warnings/notices that would break JSON validity
        ob_start();
        
        // Define simple response helper
        $send_json = function($data) {
            // Clean any previous output (including errors/warnings)
            ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode($data);
            exit;
        };

        try {
            $text = $this->input->post('text');
            if (empty($text)) {
                $send_json(['status' => 'error', 'message' => "Metin boş olamaz."]);
            }

            $lines = explode("\n", $text);
            $results = [];

            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line) || strlen($line) < 2) continue;
                
                // Skip separator lines
                if (preg_match('/^[-_=*]+$/', $line)) continue;

                // Basic parsing defaults
                $qty = 1;
                $search_term = $line;

                // regex checks
                // 1. "4 adet xxxx" or "4x xxxx"
                // Remove 'u' modifier to be safe against non-utf8 sequences, add 'i' for case-insensitive
                if (preg_match('/(?:^|\s)(\d+([.,]\d+)?)\s*(?:adet|aad|ad\.|tane|x)\s+(.*)$/i', $line, $matches)) {
                    $qty = (float)str_replace(',', '.', $matches[1]);
                    $search_term = trim($matches[3]);
                } 
                // 2. "4 xxxx" (number at start, then space)
                elseif (preg_match('/^(\d+([.,]\d+)?)\s+(.*)$/', $line, $matches)) {
                    $qty = (float)str_replace(',', '.', $matches[1]);
                    $search_term = trim($matches[3]);
                }

                // Cleanup search term
                // Remove common Turkish noise words at the END of the sentence (verbs)
                $noise_end = [
                    'takıldı', 'yapıldı', 'edildi', 'verildi', 'değişti', 'değiştirildi',
                    'adet', 'tane', 'kullanıldı', 'gitti', 'geldi'
                ];
                // Remove noise words at START
                $noise_start = ['garantiden', 'toplamda', 'ekstra', 'ilave'];
                
                // Remove patterns
                $search_term = preg_replace('/\b(' . implode('|', $noise_end) . ')\b/iu', '', $search_term);
                $search_term = preg_replace('/\b(' . implode('|', $noise_start) . ')\b/iu', '', $search_term);
                
                // Trim again
                $search_term = trim(preg_replace('/\s+/', ' ', $search_term));

                if (strlen($search_term) < 2) {
                     $results[] = [
                        'original' => $line,
                        'status' => 'ignored',
                        'message' => 'Çok kısa'
                    ];
                    continue;
                }

                // Function to perform search
                $perform_search = function($term_or_words, $type = 'phrase') {
                    $this->db->select('id, name, barcode, satis_fiyat as price');
                    $this->db->from('inv_products');
                    
                    if ($type === 'phrase') {
                        $term = $term_or_words;
                        $this->db->group_start();
                            $this->db->like('name', $term);
                            $this->db->or_like('match_names', $term); 
                            $this->db->or_like('barcode', $term);
                        $this->db->group_end();
                    } elseif ($type === 'and_logic') {
                        $words = $term_or_words;
                        $this->db->group_start(); 
                        foreach ($words as $word) {
                            $this->db->group_start();
                                $this->db->like('name', $word);
                                $this->db->or_like('match_names', $word);
                            $this->db->group_end();
                        }
                        $this->db->group_end();
                    }

                    $this->db->where('is_active', 1);
                    $this->db->limit(10); 
                    return $this->db->get()->result_array();
                };

                // STRATEGY 1: Exact Phrase Search
                // Finds "bant armatür" exactly as typed
                $matches_db = $perform_search($search_term, 'phrase');

                // STRATEGY 2: All Words Present (AND Logic)
                // Finds "Armatür Bant 30W" for "bant armatür"
                if (empty($matches_db) && strpos($search_term, ' ') !== false) {
                     $words = array_filter(explode(' ', $search_term), function($w) {
                         return strlen($w) > 2; // only significant words
                     });
                     
                     if (count($words) > 1) {
                         $matches_db = $perform_search($words, 'and_logic');
                     }
                }

                // STRATEGY 3: Last Word Fallback (Heuristic for Turkish)
                // In Turkish, the object is often at the end: "Balat halı" -> "halı", "Mutfak musluk" -> "musluk"
                // Finds "Halı" for "Balat halı" if strategy 1 & 2 fail.
                if (empty($matches_db) && strpos($search_term, ' ') !== false) {
                    $words = explode(' ', $search_term);
                    $last_word = end($words);
                    if (strlen($last_word) > 2) {
                        $matches_db = $perform_search($last_word, 'phrase');
                    }
                }

                // Add default unit
                foreach($matches_db as &$m) {
                    $m['unit'] = 'ADET'; 
                }
                unset($m); 

                if (!empty($matches_db)) {
                    $status = 'found';
                    if (count($matches_db) === 1) {
                        $selected_product = $matches_db[0];
                    }
                }

                $results[] = [
                    'original' => $line,
                    'parsed_qty' => $qty,
                    'search_term' => $search_term,
                    'matches' => $matches_db,
                    'status' => $status,
                    'selected_product' => $selected_product
                ];
            }

            $send_json(['status' => 'success', 'items' => $results]);

        } catch (\Throwable $e) { 
            log_message('error', 'Smart Add Error: ' . $e->getMessage());
            $send_json(['status' => 'error', 'message' => 'Bir hata oluştu: ' . $e->getMessage()]);
        }
    }
}
