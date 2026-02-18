<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Invoice_parser extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('pdf_invoice_parser');
        $this->load->model('Invoice_model', 'invoice');
        $this->load->model('Entity_model', 'entity');
    }

    public function index() {
        $data['next_invoice_no'] = $this->generateInvoiceNo();
        $data['entities'] = $this->entity->get_all();
        $data['pageTitle'] = 'PDF Fatura Oku';
        
        $this->load->view('layout/header', $data);
        $this->load->view('invoices/parser', $data);
        $this->load->view('layout/footer');
    }

    public function api_parse() {
        // Force no buffering - CRITICAL for API responses
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json');
        
        try {
            if (!isset($_FILES['pdf_file']) || $_FILES['pdf_file']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('Sadece PDF dosyaları kabul edilir');
            }
            
            $tempPath = sys_get_temp_dir() . '/invoice_' . uniqid() . '.pdf';
            file_put_contents('C:/xampp/htdocs/muhasebe/api_debug.txt', 'Moving file to: ' . $tempPath . "\n", FILE_APPEND);
            
            if (!move_uploaded_file($_FILES['pdf_file']['tmp_name'], $tempPath)) {
                throw new Exception('Dosya kaydedilemedi');
            }
            
            // ÖNCE AI'YI DENE
            file_put_contents('C:/xampp/htdocs/muhasebe/api_debug.txt', 'Trying AI parser first...' . "\n", FILE_APPEND);
            $result = $this->parseWithAI($tempPath);
            $parserUsed = 'ai';
            
            // AI başarısız olursa template parser'a geri dön
            if ($result === null) {
                file_put_contents('C:/xampp/htdocs/muhasebe/api_debug.txt', 'AI failed, trying template parser...' . "\n", FILE_APPEND);
                $result = $this->pdf_invoice_parser->parse($tempPath);
                $parserUsed = 'template';
            }
            
            // Kalem sayısı azsa veya validation hatalıysa AI'yı tekrar dene
            if ($result !== null && (empty($result['items']) || count($result['items']) < 5)) {
                file_put_contents('C:/xampp/htdocs/muhasebe/api_debug.txt', 'Few items, trying AI again...' . "\n", FILE_APPEND);
                $aiResult = $this->parseWithAI($tempPath);
                if ($aiResult !== null && !empty($aiResult['items'])) {
                    $result = $aiResult;
                    $parserUsed = 'ai';
                }
            }
            
            if ($result === null) {
                throw new Exception('Fatura okunamadı. Lütfen PDF formatını kontrol edin.');
            }
            
            $mathValidation = $this->pdf_invoice_parser->validateMath($result);
            file_put_contents('C:/xampp/htdocs/muhasebe/api_debug.txt', 'Validation done' . "\n", FILE_APPEND);
            
            @unlink($tempPath);
            
            $response = [
                'status' => 'success',
                'parser_used' => $parserUsed,
                'validation' => $mathValidation,
                'data' => $result,
                'requires_confirmation' => !$mathValidation['valid'] || empty($result['items'])
            ];
            
            file_put_contents('C:/xampp/htdocs/muhasebe/api_debug.txt', 'Response prepared' . "\n", FILE_APPEND);
            
            if (!$mathValidation['valid']) {
                $response['warnings'] = $mathValidation['errors'];
                $response['message'] = 'Fatura okundu ancak matematiksel tutarlar uyuşmuyor.';
            }
            
            $json = json_encode($response, JSON_INVALID_UTF8_IGNORE | JSON_PARTIAL_OUTPUT_ON_ERROR);
            file_put_contents('C:/xampp/htdocs/muhasebe/api_debug.txt', 'JSON length: ' . strlen($json) . "\n", FILE_APPEND);
            file_put_contents('C:/xampp/htdocs/muhasebe/api_output.txt', $json);
            
            print($json);
            @ob_flush();
            flush();
            file_put_contents('C:/xampp/htdocs/muhasebe/api_debug.txt', 'JSON printed' . "\n", FILE_APPEND);
            
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    protected function parseWithAI($pdfPath) {
        $this->load->library('Gemini_service');
        
        $systemInstruction = "Sen bir fatura analiz uzmanısın. Gönderilen e-fatura PDF dosyasından çıkarılan metni analiz ederek JSON formatında yapılandırılmış veri çıkar.";
        
        $prompt = $this->getAIPrompt();
        
        // Önce pdftotext ile metin çıkar
        $text = $this->extractTextFromPdf($pdfPath);
        
        if (!$text) {
            log_message('error', 'PDF Parser: Could not extract text from PDF');
            file_put_contents('C:/xampp/htdocs/muhasebe/api_debug.txt', 'Text extraction failed' . "\n", FILE_APPEND);
            return null;
        }
        
        file_put_contents('C:/xampp/htdocs/muhasebe/api_debug.txt', 'Text extracted, length: ' . strlen($text) . "\n", FILE_APPEND);
        log_message('info', 'PDF Parser: Extracted text length: ' . strlen($text));
        
        try {
            // Metni Gemini'ye gönder
            $result = $this->gemini_service->analyzeText($text, $systemInstruction, $prompt);
            file_put_contents('C:/xampp/htdocs/muhasebe/api_debug.txt', 'AI result received' . "\n", FILE_APPEND);
        } catch (Exception $e) {
            file_put_contents('C:/xampp/htdocs/muhasebe/api_debug.txt', 'AI Error: ' . $e->getMessage() . "\n", FILE_APPEND);
            throw $e;
        }
        
        if (!$result || !isset($result['json'])) {
            file_put_contents('C:/xampp/htdocs/muhasebe/api_debug.txt', 'AI returned no result' . "\n", FILE_APPEND);
            log_message('error', 'PDF Parser: AI returned no result');
            return null;
        }
        
        return $this->normalizeAIResult($result['json']);
    }
    
    protected function extractTextFromPdf($pdfPath) {
        $pdftotextPaths = [
            'C:/Program Files/Git/mingw64/bin/pdftotext.exe',
            'C:/xampp/poppler/Library/bin/pdftotext.exe',
            'pdftotext'
        ];
        
        foreach ($pdftotextPaths as $pdftotext) {
            $cmd = '"' . $pdftotext . '" -layout "' . $pdfPath . '" - 2>&1';
            exec($cmd, $output, $returnVar);
            
            if ($returnVar === 0 && !empty($output)) {
                return implode("\n", $output);
            }
        }
        
        return null;
    }
    
    protected function getAIPrompt() {
        return <<<'PROMPT'
Bu bir Türk e-fatura PDF belgesidir. Lütfen TÜM fatura bilgilerini JSON formatında çıkar.

ÖNEMLİ:
1. TÜM kalemleri (ürünleri) eksiksiz çıkar - 45 kalem varsa 45 kalem çıkar
2. Türkçe karakterleri doğru kullan
3. Para değerlerini TL olarak ve tam sayı olarak ver (örn: 18757.04 yerine 18757.04)

JSON formatı:
{
    "invoice_no": "Fatura numarası (örn: DCE2026000000122)",
    "invoice_date": "Tarih YYYY-MM-DD formatında (örn: 2026-02-09)",
    "entity_name": "Müşteri cari adı (örn: UYUMAZ ELEKTRİK MUSTAFA UYUMAZ)",
    "entity_tax_id": "Vergi kimlik numarası (10 veya 11 haneli)",
    "entity_tax_office": "Vergi dairesi",
    "items": [
        {
            "line_no": 1,
            "product_name": "Ürün açıklaması",
            "quantity": 1.0,
            "unit": "Adet veya m veya Kg veya Lt",
            "unit_price": "Birim fiyat (örn: 133.09)",
            "tax_rate": 20,
            "total": "Toplam tutar (örn: 133.09)"
        }
    ],
    "subtotal": "Ara toplam (KDV hariç)",
    "tax_total": "Toplam KDV tutarı",
    "grand_total": "Genel toplam (KDV dahil)"
}

Sadece geçerli JSON çıktısı ver, başka metin yazma.
PROMPT;
    }
    
    protected function normalizeAIResult($aiResult) {
        if (is_string($aiResult)) {
            $aiResult = json_decode($aiResult, true);
        }
        
        if (!$aiResult || !is_array($aiResult)) {
            return null;
        }
        
        return [
            'invoice_no' => $aiResult['invoice_no'] ?? null,
            'invoice_date' => $aiResult['invoice_date'] ?? date('Y-m-d'),
            'entity_name' => $aiResult['entity_name'] ?? null,
            'entity_tax_id' => $aiResult['entity_tax_id'] ?? null,
            'entity_tax_office' => $aiResult['entity_tax_office'] ?? null,
            'items' => $aiResult['items'] ?? [],
            'subtotal' => floatval($aiResult['subtotal'] ?? 0),
            'tax_total' => floatval($aiResult['tax_total'] ?? 0),
            'discount_total' => floatval($aiResult['discount_total'] ?? 0),
            'grand_total' => floatval($aiResult['grand_total'] ?? 0),
            'currency' => 'TRY',
            'raw_text' => json_encode($aiResult)
        ];
    }

    public function api_save() {
        header('Content-Type: application/json');
        
        try {
            $data = json_decode($this->input->post('invoice_data'), true);
            
            if (!$data) {
                throw new Exception('Fatura verisi alınamadı');
            }
            
            if (empty($data['invoice_no']) || empty($data['grand_total'])) {
                throw new Exception('Fatura no ve toplam tutar zorunludur');
            }
            
            $entityId = $this->findOrCreateEntity($data);
            
            $invoiceData = [
                'invoice_no' => $data['invoice_no'],
                'invoice_date' => $data['invoice_date'],
                'type' => 'purchase',
                'entity_id' => $entityId,
                'total_amount' => $data['subtotal'],
                'tax_amount' => $data['tax_total'],
                'discount_amount' => $data['discount_total'],
                'net_amount' => $data['grand_total'],
                'status' => 'finalized',
                'payment_status' => 'unpaid'
            ];
            
            $invoiceId = $this->invoice->create($invoiceData);
            
            foreach ($data['items'] as $item) {
                $this->invoice->add_item([
                    'invoice_id' => $invoiceId,
                    'product_name' => $item['product_name'],
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'unit_price' => $item['unit_price'],
                    'tax_rate' => $item['tax_rate'],
                    'total_amount' => $item['quantity'] * $item['unit_price'],
                    'tax_amount' => $item['quantity'] * $item['unit_price'] * ($item['tax_rate'] / 100)
                ]);
            }
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Fatura başarıyla kaydedildi',
                'invoice_id' => $invoiceId
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    protected function findOrCreateEntity($data) {
        if (!empty($data['entity_tax_id'])) {
            $entity = $this->entity->get_by_tax_id($data['entity_tax_id']);
            if ($entity) {
                return $entity['id'];
            }
        }
        
        if (!empty($data['entity_name'])) {
            $entity = $this->entity->get_by_name($data['entity_name']);
            if ($entity) {
                return $entity['id'];
            }
        }
        
        return $this->entity->create([
            'name' => $data['entity_name'] ?? 'Bilinmeyen Cari',
            'type' => 'supplier',
            'tax_id' => $data['entity_tax_id'],
            'tax_office' => $data['entity_tax_office'],
            'address' => $data['entity_address']
        ]);
    }

    protected function generateInvoiceNo() {
        $prefix = 'INV-' . date('Y') . '-';
        $last = $this->db->query("SELECT MAX(CAST(SUBSTRING(invoice_no, LENGTH('$prefix') + 1) AS UNSIGNED)) as max_no FROM inv_invoices WHERE invoice_no LIKE '$prefix%'");
        $next = ($last->row()->max_no ?? 0) + 1;
        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
