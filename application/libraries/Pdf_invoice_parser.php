<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * PDF Invoice Parser Service
 * Template-based e-Fatura/e-Arşiv parser with AI fallback
 */
class Pdf_invoice_parser {
    
    protected $CI;
    protected $templates = [];
    
    public function __construct() {
        $this->CI =& get_instance();
        $this->loadTemplates();
    }
    
    /**
     * Ana parse fonksiyonu
     * @param string $pdfPath PDF dosya yolu
     * @return array|null Başarılı olursa fatura verisi, başarısız olursa null
     */
    public function parse($pdfPath) {
        if (!file_exists($pdfPath)) {
            return null;
        }
        
        // 1. PDF'ten metin çıkar
        $text = $this->extractText($pdfPath);
        if (empty($text)) {
            return null;
        }
        
        // 2. Template eşleştir
        $template = $this->detectTemplate($text);
        if (!$template) {
            return null; // Template eşleşmedi, AI'ye devret
        }
        
        // 3. Template ile parse et
        $data = $this->parseWithTemplate($text, $template);
        
        error_log('PDF Parser: Parsed data - invoice_no: ' . ($data['invoice_no'] ?? 'null') . ', grand_total: ' . ($data['grand_total'] ?? 'null'));
        
        // 4. Temel validasyon
        if (!$this->basicValidation($data)) {
            error_log('PDF Parser: Basic validation failed');
            return null;
        }
        
        return $data;
    }
    
    /**
     * PDF'ten metin çıkarır - PrinsFrank PdfParser kullanarak
     */
    protected function extractText($pdfPath) {
        // Önce PrinsFrank PdfParser dene
        try {
            if (class_exists('PrinsFrank\PdfParser\PdfParser')) {
                $parser = new \PrinsFrank\PdfParser\PdfParser();
                $document = $parser->parseFile($pdfPath);
                $text = $document->getText();
                
                if (!empty($text)) {
                    log_message('info', 'PDF Parser: Successfully extracted text with PrinsFrank');
                    return $text;
                }
            }
        } catch (Exception $e) {
            log_message('error', 'PDF Parser: PrinsFrank extraction failed - ' . $e->getMessage());
        }
        
        // Smalot'a fallback
        try {
            if (class_exists('Smalot\PdfParser\Parser')) {
                $parser = new \Smalot\PdfParser\Parser();
                $pdf = $parser->parseFile($pdfPath);
                $text = $pdf->getText();
                
                if (!empty($text)) {
                    log_message('info', 'PDF Parser: Successfully extracted text with Smalot');
                    return $text;
                }
            }
        } catch (Exception $e) {
            log_message('error', 'PDF Parser: Smalot extraction failed - ' . $e->getMessage());
        }
        
        // Fallback: pdftotext
        $output = [];
        $returnVar = 0;
        
        $pdftotextPaths = [
            'C:/Program Files/Git/mingw64/bin/pdftotext.exe',
            'C:/xampp/poppler/Library/bin/pdftotext.exe',
            'pdftotext'
        ];
        
        foreach ($pdftotextPaths as $pdftotext) {
            $cmd = '"' . $pdftotext . '" -layout "' . $pdfPath . '" - 2>&1';
            exec($cmd, $output, $returnVar);
            
            if ($returnVar === 0 && !empty($output)) {
                log_message('info', 'PDF Parser: Successfully extracted text with ' . $pdftotext);
                return implode("\n", $output);
            }
        }
        
        log_message('error', 'PDF Parser: All extraction methods failed');
        return null;
    }
    
    /**
     * Alternatif metin çıkarım (PHP ile)
     */
    protected function extractTextAlternative($pdfPath) {
        // Smalot PDF Parser kullan
        try {
            if (!class_exists('Smalot\PdfParser\Parser')) {
                log_message('error', 'PDF Parser: Smalot PDF Parser not installed');
                return null;
            }
            
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($pdfPath);
            return $pdf->getText();
        } catch (Exception $e) {
            log_message('error', 'PDF Parser: Alternative extraction failed - ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Template tanımlarını yükle
     */
    protected function loadTemplates() {
        $this->templates = [
            'efatura_standard' => [
                'name' => 'e-Fatura Standart',
                'patterns' => [
                    'fatura_no' => '/Fatura\s*No\s*[:\s]+([A-Z0-9\-]+)/i',
                    'fatura_tarihi' => '/Fatura\s*Tarihi\s*[:\s]+(\d{2}[\-\.]\d{2}[\-\.]\d{4})/i',
                    'vkn' => '/TCKN[:\s]+(\d{11}|\d{10})/i',
                    'vergi_dairesi' => '/Vergi\s*Dairesi\s*[:\s]+([^\n]+)/i',
                    'cari_adi' => '/SAYIN\s+([A-ZĞİŞÇÖÜ][A-ZĞİŞÇÖÜ\s]{2,40})/i',
                    'subtotal' => '/Mal\s*Hizmet\s*Toplam\s*Tutari\s+([\d.,]+)/i',
                    'kdv' => '/Hesaplanan\s*KDV\s*\(%\s*20\)\s+([\d.,]+)/i',
                    'genel_toplam' => '/([\d.]{1,10},\d{2})\s*TL/i',
                    'odenecek' => '/([\d.]{1,10},\d{2})\s*TL/i',
                ],
                'item_patterns' => [
                    'row_simple' => '/(\d+)\s+([A-Z].+?)\s+(\d+(?:[\.,]\d+)?)\s+(Adet|Kg|Lt|m)\s+([\d.,]+)/i',
                ],
                'required_fields' => ['fatura_no', 'fatura_tarihi']
            ],
            
            'efatura_ozel' => [
                'name' => 'e-Fatura (Özel Format)',
                'patterns' => [
                    'fatura_no' => '/Fatura\s*No\s*:\s*([A-Z0-9\-]+)/i',
                    'fatura_tarihi' => '/Fatura\s*Tarihi\s*:\s*(\d{2}-\d{2}-\d{4})/i',
                    'tckn' => '/TCKN\s*:\s*(\d{11})/i',
                    'cari_adi' => '/SAYIN\s*\n*([A-Z\s]+)/i',
                    'vergi_dairesi' => '/Vergi\s*Dairesi\s*:\s*([^\n]+)/i',
                    'genel_toplam' => '/(\d{1,3}(?:\.\d{3})*,\d{2})\s*TL/i',
                ],
                'item_patterns' => [
                    'row_with_tl' => '/(\d+)\s+([A-Z0-9\.\-]+)\s+([^\n]+?)\s+(\d+(?:[\.,]\d+)?)\s+(?:Adet|Kg|Lt|m|Paket)\s+([\d.,]+)\s*TL/i',
                ],
                'required_fields' => ['fatura_no', 'fatura_tarihi']
            ],
            
            'efatura_detailed' => [
                'name' => 'e-Fatura Detaylı',
                'patterns' => [
                    'fatura_no' => '/Fatura\s*No\s*:\s*([A-Z0-9\-]+)/i',
                    'fatura_tarihi' => '/Fatura\s*Tarihi\s*:\s*(\d{2}-\d{2}-\d{4})/i',
                    'vkn' => '/TCKN[:\s]+(\d{11})/i',
                    'tckn' => '/T\.C\.\s*KİMLİK\s*NO[:\s]+(\d{11})/i',
                    'cari_adi' => '/SAYIN\s+([\w\s\.]+)/i',
                    'cari_adres' => '/([\w\s\.]+)\s*KARATAY\/\s*KONYA/i',
                    'vergi_dairesi' => '/Vergi\s*Dairesi\s*:\s*([^\n]+)/i',
                    'toplam' => '/Mal\s*Hizmet\s*Toplam\s+([\d.,]+)/i',
                    'kdv_orani' => '/KDV\s*\(%?(\d+)\)/i',
                    'kdv' => '/Hesaplanan\s*KDV\s*\(%?\d*%\)\s+([\d.,]+)/i',
                    'genel_toplam' => '/Vergiler\s*Dahil\s*Toplam\s+([\d.,]+)/i',
                    'odenecek' => '/Ödenecek\s*Tutar\s+([\d.,]+)/i',
                ],
                'item_patterns' => [
                    'row_detailed' => '/(\d+)\s+([A-Z0-9\.\-]+)\s+(.+?)\s+(\d+(?:[\.,]\d+)?)\s+(?:Adet|Kg|Lt|m|Paket)\s+([\d.,]+)\s+TL\s+%?\d+[\.,]?\d*\s*%?\d*[\.,]?\d*\s*%?\d+\s*%?\s+([\d.,]+)\s+TL\s+([\d.,]+)\s+TL/i',
                    'row_simple' => '/(\d+)\s+([A-Z0-9\.\-]+)\s+(.+?)\s+(\d+(?:[\.,]\d+)?)\s+(?:Adet|Kg|Lt|m)\s+([\d.,]+)\s+TL/i',
                ],
                'required_fields' => ['fatura_no', 'fatura_tarihi', 'cari_adi', 'genel_toplam']
            ],
            
            'earsiv' => [
                'name' => 'e-Arşiv',
                'patterns' => [
                    'fatura_no' => '/Fatura\s*No\s*:\s*([A-Z0-9\-]+)/i',
                    'fatura_tarihi' => '/(?:Fatura\s*Tarihi|Tarih)\s*:\s*(\d{2}[\-\/]\d{2}[\-\/]\d{4})/i',
                    'vkn' => '/Vergi\s*No\s*:\s*(\d{10})/i',
                    'cari_adi' => '/(?:Müşteri|Cari)\s*:\s*([^\n]+)/i',
                    'genel_toplam' => '/(?:Toplam|Genel\s*Toplam)\s*:\s*([\d.,]+)\s*TL/i',
                ],
                'required_fields' => ['fatura_no', 'fatura_tarihi', 'genel_toplam']
            ]
        ];
    }
    
    /**
     * Metne göre template seçer
     */
    protected function detectTemplate($text) {
        // Her template için puan hesapla
        $scores = [];
        
        foreach ($this->templates as $key => $template) {
            $score = 0;
            $matched = 0;
            
            foreach ($template['patterns'] as $field => $pattern) {
                if (preg_match($pattern, $text)) {
                    $score += 10;
                    $matched++;
                }
            }
            
            // Gerekli alanların kaçı eşleşti?
            $requiredMatched = 0;
            foreach ($template['required_fields'] as $field) {
                if (isset($template['patterns'][$field]) && 
                    preg_match($template['patterns'][$field], $text)) {
                    $requiredMatched++;
                }
            }
            
            // Tüm gerekli alanlar eşleştiyse bonus
            if ($requiredMatched === count($template['required_fields'])) {
                $score += 50;
            }
            
            $scores[$key] = [
                'score' => $score,
                'matched' => $matched,
                'required_matched' => $requiredMatched
            ];
        }
        
        // En yüksek puanlı template'i seç
        arsort($scores);
        $best = key($scores);
        
        // Minimum puan kontrolü
        if ($scores[$best]['score'] < 20) {
            log_message('info', 'PDF Parser: Template score too low - ' . json_encode($scores));
            return null;
        }
        
        return $this->templates[$best];
    }
    
    /**
     * Template ile veri çıkarımı - Satır satır parsing
     */
    protected function parseWithTemplate($text, $template) {
        $data = [
            'invoice_no' => null,
            'invoice_date' => null,
            'entity_name' => null,
            'entity_tax_id' => null,
            'entity_tax_office' => null,
            'entity_address' => null,
            'items' => [],
            'subtotal' => 0,
            'tax_total' => 0,
            'discount_total' => 0,
            'grand_total' => 0,
            'currency' => 'TRY',
            'notes' => null,
            'raw_text' => $text
        ];
        
        // Satır satır parse et
        $lines = explode("\n", $text);
        
        foreach ($lines as $i => $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Fatura No: DCE2026000000122
            if (preg_match('/Fatura\s*No/i', $line) && !$data['invoice_no']) {
                if (preg_match('/([A-Z0-9\-]{10,})/i', $line, $m)) {
                    $data['invoice_no'] = $m[1];
                }
            }
            
            // Fatura Tarihi: 09-02-2026
            if (preg_match('/Fatura\s*Tarihi/i', $line) && !$data['invoice_date']) {
                if (preg_match('/(\d{2}[-.\/]\d{2}[-.\/]\d{4})/', $line, $m)) {
                    $data['invoice_date'] = $this->parseDate($m[1]);
                }
            }
            
            // TCKN: 42256362808
            if (preg_match('/TCKN/i', $line) && !$data['entity_tax_id']) {
                if (preg_match('/(\d{10,11})/', $line, $m)) {
                    $data['entity_tax_id'] = $m[1];
                }
            }
            
            // Vergi Dairesi: SELUK
            if (preg_match('/Vergi\s*Dairesi/i', $line) && !$data['entity_tax_office']) {
                if (preg_match('/Vergi\s*Dairesi[:\s]+([A-ZĞİŞÇÖÜ]+)/i', $line, $m)) {
                    $data['entity_tax_office'] = $m[1];
                }
            }
            
            // SAYIN'dan sonraki satır cari adı
            if (preg_match('/SAYIN/i', $line) && !$data['entity_name']) {
                // Sonraki satıra bak
                if (isset($lines[$i+1])) {
                    $nextLine = trim($lines[$i+1]);
                    // Eğer sonraki satır kısa ve harflerle başlıyorsa cari adıdır
                    if (preg_match('/^([A-ZĞİŞÇÖÜ][A-ZĞİŞÇÖÜ\s]{2,45})/', $nextLine, $m)) {
                        $data['entity_name'] = trim($m[1]);
                    }
                }
            }
            
            // Mal Hizmet Toplam Tutari - ara toplam
            if (preg_match('/Mal\s*Hizmet\s*Toplam/i', $line) && $data['subtotal'] == 0) {
                if (preg_match('/([\d.,]+)\s*TL/', $line, $m)) {
                    $data['subtotal'] = $this->parseAmount($m[1]);
                }
            }
            
            // Hesaplanan KDV - vergi toplamı
            if (preg_match('/Hesaplanan\s*KDV/i', $line) && $data['tax_total'] == 0) {
                if (preg_match('/([\d.,]+)\s*TL/', $line, $m)) {
                    $data['tax_total'] = $this->parseAmount($m[1]);
                }
            }
        }
        
        // TÜM TL DEĞERLERİNİ BUL VE GRUPLA
        // Türkçe karakterler bozuldu, bu yüzden sayısal değerleri doğrudan al
        
        // Grand total - en büyük TL değeri
        preg_match_all('/([\d.,]+)\s*TL/', $text, $allMatches);
        
        $amounts = [];
        if (!empty($allMatches[1])) {
            foreach ($allMatches[1] as $amt) {
                $val = $this->parseAmount($amt);
                if ($val > 100 && $val < 500000) { // Makul aralık
                    $amounts[] = $val;
                }
            }
        }
        
        if (!empty($amounts)) {
            // En büyük değer grand total
            $data['grand_total'] = max($amounts);
            
            // İkinci büyük değer genellikle ara toplam veya KDV
            rsort($amounts);
            
            // Genellikle: grand_total > ara_toplam + kdv
            // Ara toplam: grand_total / 1.20 yaklaşık
            $expectedSubtotal = $data['grand_total'] / 1.20;
            
            // En yakın değeri ara toplam olarak kabul et
            $closest = null;
            $closestDiff = PHP_FLOAT_MAX;
            foreach ($amounts as $amt) {
                if ($amt == $data['grand_total']) continue;
                $diff = abs($amt - $expectedSubtotal);
                if ($diff < $closestDiff) {
                    $closestDiff = $diff;
                    $closest = $amt;
                }
            }
            
            if ($closest && $closestDiff < $data['grand_total'] * 0.1) { // %10 tolerans
                $data['subtotal'] = $closest;
                $data['tax_total'] = $data['grand_total'] - $closest;
            }
        }
        
        // Kalemleri çıkar
        $data['items'] = $this->parseItems($text, $template);
        
        // İskontoyu hesapla
        if ($data['subtotal'] > 0 && $data['grand_total'] > 0) {
            $calculatedTotal = $data['subtotal'] + $data['tax_total'];
            if ($calculatedTotal > $data['grand_total']) {
                $data['discount_total'] = $calculatedTotal - $data['grand_total'];
            }
        }
        
        return $data;
    }
    
    /**
     * Kalem satırlarını çıkarır - Gelişmiş
     */
    protected function parseItems($text, $template) {
        $items = [];
        
        $lines = explode("\n", $text);
        
        foreach ($lines as $lineNum => $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Satır numarası ile başla (1-99 arası)
            // Format: "1 OPAS.KUT 00 ... 1 133,092 TL %20,00 5,45 TL 27,23 TL"
            
            if (preg_match('/^(\d{1,2})\s+(.+)/', $line, $match)) {
                $rowNum = intval($match[1]);
                $rest = $match[2];
                
                // TL değerlerini bul
                preg_match_all('/([\d.,]+)\s*TL/', $rest, $tlMatches);
                
                if (count($tlMatches[1]) >= 2) {
                    // En az 2 TL değeri var - bu bir kalem satırı olabilir
                    $lastTl = end($tlMatches[1]);  // Son TL toplam
                    $secondLast = $tlMatches[1][count($tlMatches[1])-2];  // Birim fiyat veya KDV
                    
                    // Miktarı bul - genellikle ürün adından sonra tek sayı
                    preg_match_all('/\s(\d+)\s+/', $rest, $qtyMatches);
                    $qty = 1;
                    if (!empty($qtyMatches[1])) {
                        foreach ($qtyMatches[1] as $q) {
                            $qInt = intval($q);
                            if ($qInt > 0 && $qInt < 1000) {
                                $qty = $qInt;
                                break;
                            }
                        }
                    }
                    
                    // Birimi tespit et
                    $unit = 'Adet';
                    if (stripos($rest, ' m ') !== false || preg_match('/\d+\s+m\s+/', $rest)) $unit = 'm';
                    elseif (stripos($rest, 'Kg') !== false) $unit = 'Kg';
                    elseif (stripos($rest, 'Lt') !== false) $unit = 'Lt';
                    
                    $total = $this->parseAmount($lastTl);
                    $unitPrice = $this->parseAmount($secondLast);
                    
                    // Eğer unit_price > total ise, muhtemelen yer değişmiş
                    if ($unitPrice > $total && $qty > 0) {
                        $unitPrice = $total / $qty;
                    }
                    
                    // Ürün adı - TL değerlerinden önceki kısım
                    $productName = preg_replace('/[\d.,]+TL.*$/', '', $rest);
                    $productName = trim(preg_replace('/^\d+\s+/', '', $productName));
                    
                    // Aynı satır numarasını tekrar ekleme
                    $exists = false;
                    foreach ($items as $it) {
                        if ($it['line_no'] == $rowNum) {
                            $exists = true;
                            break;
                        }
                    }
                    
                    if (!$exists && $total > 0 && strlen($productName) > 2) {
                        $items[] = [
                            'line_no' => $rowNum,
                            'product_code' => '',
                            'product_name' => substr($productName, 0, 100),
                            'quantity' => $qty,
                            'unit' => $unit,
                            'unit_price' => $unitPrice,
                            'tax_rate' => 20,
                            'total' => $total
                        ];
                    }
                }
            }
        }
        
        return $items;
    }
    
    /**
     * Birim tespiti
     */
    protected function detectUnit($text) {
        if (stripos($text, 'Adet') !== false) return 'Adet';
        if (stripos($text, 'Kg') !== false) return 'Kg';
        if (stripos($text, 'Lt') !== false) return 'Litre';
        if (stripos($text, 'm') !== false && stripos($text, 'm2') === false) return 'm';
        if (stripos($text, 'Paket') !== false) return 'Paket';
        return 'Adet';
    }
    
    /**
     * Tarih parse et
     */
    protected function parseDate($dateStr) {
        // 09-02-2026 veya 09.02.2026 formatı
        $dateStr = str_replace(['.', '/'], '-', $dateStr);
        
        if (preg_match('/(\d{2})-(\d{2})-(\d{4})/', $dateStr, $matches)) {
            return $matches[3] . '-' . $matches[2] . '-' . $matches[1]; // YYYY-MM-DD
        }
        
        return date('Y-m-d'); // Varsayılan: bugün
    }
    
    /**
     * Tutar parse et
     */
    protected function parseAmount($amountStr) {
        // 1.234,56 veya 1234.56 veya 1,234.56 formatları
        $amountStr = trim($amountStr);
        
        // TL, YTL gibi para birimlerini kaldır
        $amountStr = preg_replace('/(TL|YTL|USD|EUR|₺)\s*/i', '', $amountStr);
        
        // Binlik ayracını kaldır
        if (strpos($amountStr, ',') !== false && strpos($amountStr, '.') !== false) {
            // 1,234.56 formatı
            if (strrpos($amountStr, ',') < strrpos($amountStr, '.')) {
                $amountStr = str_replace(',', '', $amountStr);
            } else {
                // 1.234,56 formatı (Türkçe)
                $amountStr = str_replace('.', '', $amountStr);
                $amountStr = str_replace(',', '.', $amountStr);
            }
        } elseif (strpos($amountStr, ',') !== false) {
            // Sadece virgül varsa (Türkçe)
            $amountStr = str_replace(',', '.', $amountStr);
        }
        
        return floatval($amountStr);
    }
    
    /**
     * Temel validasyon
     */
    protected function basicValidation($data) {
        // Zorunlu alanlar - en azından fatura no ve tarih olmalı
        if (empty($data['invoice_no'])) {
            log_message('warning', 'PDF Parser: No invoice_no found');
            return false;
        }
        if (empty($data['invoice_date'])) {
            log_message('error', 'PDF Parser: No invoice_date found');
            return false;
        }
        
        // Toplam tutar zorunlu değil - AI fallback devreye girebilir
        if (empty($data['grand_total']) || $data['grand_total'] <= 0) {
            log_message('error', 'PDF Parser: No grand_total found or is zero');
            // Bu kabul edilebilir - AI devreye girecek
        }
        
        return true;
    }
    
    /**
     * Matematiksel validasyon
     * Kalemlerin toplamı fatura toplamına eşit mi?
     */
    public function validateMath($data) {
        $errors = [];
        
        // Kalem toplamlarını hesapla
        $calculatedSubtotal = 0;
        $calculatedTax = 0;
        
        foreach ($data['items'] as $item) {
            $lineTotal = $item['quantity'] * $item['unit_price'];
            $calculatedSubtotal += $lineTotal;
            $calculatedTax += $lineTotal * ($item['tax_rate'] / 100);
        }
        
        $calculatedGrand = $calculatedSubtotal + $calculatedTax - $data['discount_total'];
        
        // Tolerans: 1 TL
        $tolerance = 1.0;
        
        if (abs($calculatedGrand - $data['grand_total']) > $tolerance) {
            $errors[] = sprintf(
                'Genel toplam uyuşmazlığı: Hesaplanan %.2f TL, Faturada %.2f TL (Fark: %.2f TL)',
                $calculatedGrand,
                $data['grand_total'],
                abs($calculatedGrand - $data['grand_total'])
            );
        }
        
        if ($data['subtotal'] > 0 && abs($calculatedSubtotal - $data['subtotal']) > $tolerance) {
            $errors[] = sprintf(
                'Ara toplam uyuşmazlığı: Hesaplanan %.2f TL, Faturada %.2f TL',
                $calculatedSubtotal,
                $data['subtotal']
            );
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'calculated' => [
                'subtotal' => $calculatedSubtotal,
                'tax' => $calculatedTax,
                'grand' => $calculatedGrand
            ]
        ];
    }
}
