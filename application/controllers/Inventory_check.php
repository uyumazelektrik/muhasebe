<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Inventory_check extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Product_model', 'product');
    }

    public function index() {
        $data['page_title'] = "Fiyat Sorgulama";
        
        $this->load->view('layout/header', $data);
        $this->load->view('inventory/check_price', $data);
        $this->load->view('layout/footer');
    }

    public function api_search_stock() {
        try {
            $query = $this->input->get('q');
            if ($query === 'test') {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'success', 'items' => [], 'debug' => 'Testing API connectivity']);
                return;
            }

            if (!$query) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Sorgu boş olamaz.']);
                return;
            }

            // Use the model which we know works for other parts of the system
            $raw_items = $this->product->get_all($query, 10);
            
            $items = [];
            foreach ($raw_items as $item) {
                // Map fields for frontend expectations in check_price page
                $items[] = [
                    'id'            => $item['id'],
                    'urun_adi'      => $item['name'] ?? ($item['urun_adi'] ?? 'Adsız Ürün'),
                    'barcode'       => $item['barcode'] ?? '',
                    'miktar'        => $item['stock_quantity'] ?? ($item['miktar'] ?? 0),
                    'satis_fiyat'   => $item['satis_fiyat'] ?? 0,
                    'birim'         => $item['unit'] ?? ($item['birim'] ?? 'Adet'),
                    'kritik_esik'   => $item['critical_level'] ?? ($item['kritik_seviye'] ?? 0),
                    'gorsel'        => $item['image'] ?? ($item['gorsel'] ?? null),
                ];
            }

            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'success',
                'items' => $items
            ]);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Hata: ' . $e->getMessage()]);
        }
    }

    public function api_gemini_search() {
        $this->load->library('gemini_service');
        
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        $imageData = $data['image'] ?? null;

        if (!$imageData) {
            echo json_encode(['status' => 'error', 'message' => 'Görsel bulunamadı.']);
            return;
        }

        // Get existing products for context
        $dbProducts = $this->db->select('id, name as urun_adi, barcode')
                               ->from('inv_products')
                               ->get()
                               ->result_array();

        $systemInstruction = "Sen bir depo ve stok yönetim asistanısın. Daima TÜRKÇE cevap ver.
        Veritabanındaki mevcut ürünlerin listesini veriyorum. Görseldeki ürünü analiz ederken ÖNCELİKLE bu listeye bak.
        1. Eğer görseldeki ürün listedeki bir isimle veya barkodla eşleşiyorsa, MUTLAK SURETLE listedeki id'yi döndür.
        2. Eğer ürün listede YOKSA, ürünü tanımla ve is_matched değerini false yap.

        Mevcut Ürün Listesi (ID, İsim, Barkod):
        " . json_encode($dbProducts);

        $prompt = "Bu bir ürün fotoğrafı. Lütfen görseldeki ürünü analiz et ve sonucunu MUTLAK SURETLE sadece aşağıdaki JSON formatında döndür. Başka hiçbir açıklama yazma.
        {
          \"is_matched\": true/false,
          \"db_id\": null,
          \"product_name\": \"Ürün adı ve markası\",
          \"barcode\": \"Eğer görünüyorsa barkod numarası, yoksa null\",
          \"description\": \"Ürün hakkında 5 kelimelik kısa açıklama\"
        }";

        try {
            $aiData = $this->gemini_service->analyzeImage($imageData, 'image/jpeg', $systemInstruction, $prompt);
            
            $productName = $aiData['product_name'] ?? '';
            $barcode = $aiData['barcode'] ?? null;
            $isMatched = $aiData['is_matched'] ?? false;
            $dbId = $aiData['db_id'] ?? null;

            if (empty($productName)) {
                throw new Exception("Model ürünü tanımlayamadı.");
            }

            $items = [];
            
            // 1. Direct match by ID
            if ($isMatched && $dbId) {
                $directItem = $this->db->select('*, name as urun_adi, stock_quantity as miktar, unit as birim, satis_fiyat, critical_level as kritik_esik')
                                       ->where('id', $dbId)
                                       ->get('inv_products')
                                       ->row_array();
                if ($directItem) $items[] = $directItem;
            }

            // 2. Match by Barcode
            $searchIds = array_column($items, 'id');
            if ($barcode) {
                $this->db->select('*, name as urun_adi, stock_quantity as miktar, unit as birim, satis_fiyat, critical_level as kritik_esik')
                         ->where('barcode', $barcode);
                if (!empty($searchIds)) $this->db->where_not_in('id', $searchIds);
                $foundByBarcode = $this->db->get('inv_products')->result_array();
                $items = array_merge($items, $foundByBarcode);
                $searchIds = array_column($items, 'id');
            }

            // 3. Match by Name (Like)
            if ($productName) {
                $this->db->select('*, name as urun_adi, stock_quantity as miktar, unit as birim, satis_fiyat, critical_level as kritik_esik')
                         ->like('name', $productName);
                if (!empty($searchIds)) $this->db->where_not_in('id', $searchIds);
                $foundByName = $this->db->limit(5)->get('inv_products')->result_array();
                $items = array_merge($items, $foundByName);
            }

            echo json_encode([
                'status' => 'success',
                'items' => $items,
                'ai_data' => [
                    'name' => $productName,
                    'barcode' => $barcode,
                    'description' => $aiData['description'] ?? '',
                    'source' => $isMatched ? 'db_match' : 'manual_search'
                ]
            ]);

        } catch (Throwable $e) {
            log_message('error', 'AI Search Error: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
