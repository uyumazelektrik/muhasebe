<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Inventory extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Product_model', 'product');
    }

    public function index() {
        $search = $this->input->get('search') ?? '';
        $limit = $this->input->get('limit') ?? 20;
        $page = $this->input->get('page') ?? 1;
        $status = $this->input->get('status') ?? 'active';
        $offset = ($page - 1) * $limit;

        $data['products'] = $this->product->get_all($search, $limit, $offset, $status);
        $data['total_count'] = $this->product->count_all($search, $status);
        $data['page'] = $page;
        $data['limit'] = $limit;
        $data['search'] = $search;
        $data['status'] = $status;
        $data['page_title'] = $status == 'passive' ? "Pasif Stoklar" : "Stok Takibi";

        if ($this->input->get('ajax')) {
            $rows_html = $this->load->view('inventory/product_rows', $data, TRUE);
            $pagination_html = $this->load->view('inventory/pagination', $data, TRUE);
            
            echo json_encode([
                'rows' => $rows_html,
                'pagination' => $pagination_html,
                'total_count' => $data['total_count']
            ]);
            return;
        }

        $this->load->view('layout/header', $data);
        $this->load->view('inventory/list', $data);
        $this->load->view('layout/footer');
    }

    public function detail($id = NULL) {
        if (!$id) show_404();
        $data['product'] = $this->product->get_by_id($id);
        if (!$data['product']) show_404();

        $data['page_title'] = "Ürün Detayı: " . $data['product']['name'];
        $data['stock_history'] = $this->product->get_stock_history($id);
        
        $this->load->view('layout/header', $data);
        $this->load->view('inventory/detail', $data);
        $this->load->view('layout/footer');
    }

    public function edit($id) {
        $data['product'] = $this->product->get_by_id($id);
        if (!$data['product']) show_404();

        $data['page_title'] = "Stok Düzenle: " . $data['product']['name'];
        
        $this->load->view('layout/header', $data);
        $this->load->view('inventory/edit', $data);
        $this->load->view('layout/footer');
    }

    public function create() {
        $data['product'] = [
            'id' => 0,
            'name' => $this->input->get('new_search') ?: '',
            'barcode' => $this->input->get('barcode') ?: '',
            'match_names' => '',
            'unit' => 'Adet',
            'last_buy_price' => 0,
            'satis_fiyat' => 0,
            'tax_rate' => 20,
            'critical_level' => 5,
            'stock_quantity' => 0,
            'gorsel' => ''
        ];

        $data['page_title'] = "Yeni Ürün Ekle";
        
        $this->load->view('layout/header', $data);
        $this->load->view('inventory/edit', $data);
        $this->load->view('layout/footer');
    }

    public function api_update() {
        // Clear any previous output
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        
        try {
            $id = $this->input->post('id');
            $is_new = ($id == 0 || empty($id));

            $data = [
                'name' => $this->input->post('name'),
                'match_names' => $this->input->post('match_names'),
                'barcode' => !empty($this->input->post('barcode')) ? $this->input->post('barcode') : null,
                'unit' => $this->input->post('unit'),
                'last_buy_price' => $this->input->post('buying_price'),
                'satis_fiyat' => $this->input->post('satis_fiyat'),
                'tax_rate' => $this->input->post('tax_rate'),
                'critical_level' => $this->input->post('critical_level'),
                'stock_quantity' => $this->input->post('stock_quantity'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Handle Image Upload (File or AI Base64)
            $ai_image_base64 = $this->input->post('ai_image_base64');
            
            if (isset($_FILES['gorsel']) && !empty($_FILES['gorsel']['name'])) {
                $uploadPath = './uploads/products/';
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0777, true);
                }
                
                $config['upload_path']   = $uploadPath;
                $config['allowed_types'] = 'gif|jpg|jpeg|png|webp';
                $config['max_size']      = 5120; // 5MB
                $config['encrypt_name']  = TRUE;

                $this->load->library('upload', $config);
                $this->upload->initialize($config);

                if (!$this->upload->do_upload('gorsel')) {
                    throw new Exception('Dosya yükleme hatası: ' . strip_tags($this->upload->display_errors('', '')));
                } else {
                    $uploadData = $this->upload->data();
                    $data['gorsel'] = 'uploads/products/' . $uploadData['file_name'];
                }
            } elseif (!empty($ai_image_base64)) {
                // Save AI base64 to file
                if (strpos($ai_image_base64, ',') !== false) {
                    $ai_image_base64 = explode(',', $ai_image_base64)[1];
                }
                $imageData = base64_decode($ai_image_base64);
                $fileName = 'ai_' . uniqid() . '.jpg';
                $filePath = 'uploads/products/' . $fileName;
                if (!is_dir('./uploads/products/')) mkdir('./uploads/products/', 0777, true);
                file_put_contents('./' . $filePath, $imageData);
                $data['gorsel'] = $filePath;
            }

            if ($is_new) {
                if ($this->product->create($data)) {
                    $new_id = $this->db->insert_id();
                    echo json_encode(['status' => 'success', 'message' => 'Yeni stok kartı oluşturuldu', 'id' => $new_id]);
                } else {
                    throw new Exception('Veritabanı kayıt hatası');
                }
            } else {
                if ($this->product->update($id, $data)) {
                    echo json_encode(['status' => 'success', 'message' => 'Stok kartı güncellendi', 'id' => $id]);
                } else {
                    throw new Exception('Veritabanı güncelleme hatası');
                }
            }
        } catch (Throwable $e) {
            log_message('error', 'Product Update Error: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function api_delete() {
        header('Content-Type: application/json');
        try {
            $id = $this->input->post('id');
            if (!$id) throw new Exception('ID gerekli');

            if ($this->product->soft_delete($id)) {
                echo json_encode(['status' => 'success', 'message' => 'Ürün pasife alındı (Silindi)']);
            } else {
                throw new Exception('Silme işlemi başarısız');
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function api_restore() {
        header('Content-Type: application/json');
        try {
            $id = $this->input->post('id');
            if (!$id) throw new Exception('ID gerekli');

            if ($this->product->restore($id)) {
                echo json_encode(['status' => 'success', 'message' => 'Ürün tekrar aktif edildi']);
            } else {
                throw new Exception('Aktifleştirme başarısız');
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function cleanup_orphans() {
        // CLI or Admin check (simple GET for now as requested)
        // Find orphan invoice items (items without parent invoice)
        $this->db->select('ii.id, ii.product_id, ii.quantity');
        $this->db->from('inv_invoice_items ii');
        $this->db->join('inv_invoices i', 'ii.invoice_id = i.id', 'left');
        $this->db->where('i.id IS NULL');
        $orphans = $this->db->get()->result_array();

        $count = 0;

        if (!empty($orphans)) {
            $orphan_ids = array_column($orphans, 'id');
            $this->db->where_in('id', $orphan_ids);
            $this->db->delete('inv_invoice_items');
            $count = $this->db->affected_rows();
        }
        echo "İşlem Tamamlandı. Silinen yetim kayıt: $count<br>";
    }
    
    public function api_price_history($product_id) {
        header('Content-Type: application/json');
        $range = $this->input->get('range') ?: '1y';
        
        $days = 365;
        if ($range === '1m') $days = 30;
        elseif ($range === '3m') $days = 90;
        elseif ($range === '6m') $days = 180;

        $startDate = date('Y-m-d', strtotime("-$days days"));

        // Purchase history from inv_invoice_items
        $this->db->select('i.invoice_date as date, ii.unit_price as price');
        $this->db->from('inv_invoice_items ii');
        $this->db->join('inv_invoices i', 'ii.invoice_id = i.id');
        $this->db->where('ii.product_id', $product_id);
        $this->db->where('i.type', 'purchase');
        $this->db->where('i.invoice_date >=', $startDate);
        $this->db->order_by('i.invoice_date', 'ASC');
        $purchases = $this->db->get()->result_array();

        // Sale history from inv_invoice_items
        $this->db->select('i.invoice_date as date, ii.unit_price as price');
        $this->db->from('inv_invoice_items ii');
        $this->db->join('inv_invoices i', 'ii.invoice_id = i.id');
        $this->db->where('ii.product_id', $product_id);
        $this->db->where('i.type', 'sale');
        $this->db->where('i.invoice_date >=', $startDate);
        $this->db->order_by('i.invoice_date', 'ASC');
        $sales = $this->db->get()->result_array();

        echo json_encode([
            'status' => 'success',
            'data' => [
                'purchases' => $purchases,
                'sales' => $sales
            ]
        ]);
    }
    public function api_analyze_image() {
        header('Content-Type: application/json');
        
        try {
            $imageData = $this->input->post('image_data');
            $mimeType = $this->input->post('mime_type') ?: 'image/jpeg';
            
            if (empty($imageData)) {
                echo json_encode(['status' => 'error', 'message' => 'Görsel verisi bulunamadı']);
                return;
            }
            
            $this->load->library('Gemini_service');
            
            $systemInstruction = "Sen bir ürün analiz uzmanısın. Ürün fotoğrafını analiz ederek temel bilgilerini çıkarıyorsun.
Sadece aşağıdaki JSON formatında yanıt ver, başka açıklama yapma:
{
    \"product_name\": \"Ürünün adı ve markası\",
    \"barcode\": \"Görselde okunabilir bir barkod (EAN/UPC/ISBN vb.) varsa sadece rakamları yaz, yoksa null\",
    \"description\": \"Ürün hakkında kısa açıklama\"
}";

            $prompt = "Bu ürün görselini analiz et. Varsa barkodunu kesinlikle oku.";
            
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
}
