<?php
// src/Services/GeminiService.php

class GeminiService {
    private $apiKey;
    private $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/';
    
    private $models = [
        'gemini-3.0-flash', 
        'gemini-2.5-flash',           // En yeni amiral gemisi (Hızlı ve Zeki)
        'gemini-2.0-flash',           // Kararlı 2.0 sürümü
        'gemini-2.5-flash-lite',      // Çok hızlı ve verimli
        'gemini-2.0-flash-exp',       // 2.0 Experimental
        'gemini-1.5-flash-latest',    // Klasik kararlı sürüm
        'gemini-2.5-pro'              // Güçlü ama yüksek kota tüketebilir (Fallback olarak sonlara eklendi)
    ];

    public function __construct($apiKey) {
        $this->apiKey = $apiKey;
    }

    private function getWorkingModel($base64Data, $mimeType, $systemInstruction) {
         $lastError = 'Bilinmeyen Hata';
         foreach ($this->models as $model) {
             try {
                 error_log("Trying Gemini Model: $model");
                 return $this->callApiSingle($model, $base64Data, $mimeType, $systemInstruction);
             } catch (Exception $e) {
                 $lastError = $e->getMessage();
                 error_log("Model $model failed: " . $lastError);
                 if (strpos($lastError, '404') !== false || strpos($lastError, 'not found') !== false) continue;
                 if (strpos($lastError, '429') !== false) continue;
                 continue;
             }
         }
         throw new Exception("İşlem başarısız. Denenen modeller yanıt vermedi. Son Hata: " . $lastError);
    }

    public function processInvoice($imagePath) {
        if (empty($this->apiKey)) {
            throw new Exception("Gemini API anahtarı eksik.");
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($imagePath);
        $base64Data = base64_encode(file_get_contents($imagePath));

        /**
         * OKUMA STRATEJİSİ GÜNCELLEMESİ:
         * Yapay zekadan hesap yapmasını değil, faturadaki 'Tutar' (iskontolu net tutar) sütununu doğrudan okumasını istiyoruz.
         */
        $systemInstruction = "Sen bir profesyonel muhasebe OCR uzmanısın. Görevin faturadaki tabloyu KURUŞU KURUŞUNA dijitalleştirmektir.
        
        KESİN KURALLAR:
        1. **TABLO OKUMA (HAYATİ):** 
           - 'items' listesini oluştururken faturadaki her satır için en sağdaki 'Tutar' sütununa bak. 
           - Bu 'Tutar' sütunu zaten İSKONTO DÜŞÜLMÜŞ NET TUTARIDIR.
           - `total_price`: Doğrudan faturadaki 'Tutar' hücresindeki değeri al.
           - `unit_price`: Bu 'Tutar' değerini, faturadaki 'Adet' miktarına bölerek bul. Kendi kafandan iskonto hesaplamaya çalışma!
        2. **MATEMATİKSEL KONTROL:**
           - Tüm kalemlerin `total_price` değerlerinin toplamı, faturanın altındaki 'KDV Matrahı' (Vergi Hariç Tutar) olan 16.195,34 değerine EŞİT OLMALIDIR.
        3. **FORMAT:** Cevap SADECE saf JSON olmalıdır.

        JSON Yapısı:
        {
            \"items\": [
                {
                    \"name\": \"...\",
                    \"type\": \"STOK\", // Eğer ürün bir emtia/malzeme ise STOK, eğer hizmet/fatura (Elektrik, Su, Yemek, Nakliye vb.) ise GIDER
                    \"quantity\": 1.00,
                    \"unit\": \"Adet\",
                    \"unit_price\": 123.45, // (Tutar / Adet)
                    \"total_price\": 123.45, // Faturadaki en sağdaki 'Tutar' hücresi
                    \"tax_rate\": 20
                }
            ],
            \"total_amount\": 19434.41, // Faturadaki 'Ödenecek Tutar'
            \"total_tax\": 3239.07, // Faturadaki 'Hesaplanan KDV'
            \"total_discount\": 23641.58, // faturadaki 'Toplam İskonto'
            \"invoice_date\": \"2025-12-23\",
            \"invoice_no\": \"YE12025000029875\",
            \"supplier_name\": \"YILDIRIM ELEKTRİK\",
            \"supplier_tax_id\": \"9480042687\",
            \"payment_status\": \"unpaid\"
        }";

        return $this->getWorkingModel($base64Data, $mimeType, $systemInstruction);
    }

    private function callApiSingle($model, $base64Data, $mimeType, $systemInstruction) {
        $url = $this->baseUrl . $model . ':generateContent?key=' . $this->apiKey;

        $payload = [
            'system_instruction' => [
                'parts' => [['text' => $systemInstruction]]
            ],
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => "Faturayı analiz et. Tablodaki 'Fiyat' hücresine DEĞİL, en sağdaki 'Tutar' hücresine odaklan. Rakamlar kuruşu kuruşuna görseldekiyle AYNI olmalı."],
                        [
                            'inline_data' => [
                                'mime_type' => $mimeType,
                                'data' => $base64Data
                            ]
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.0,
                'maxOutputTokens' => 8192,
                'response_mime_type' => 'application/json'
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) throw new Exception("Bağlantı Hatası: " . curl_error($ch));
        curl_close($ch);

        if ($httpCode !== 200) {
            $err = json_decode($response, true);
            throw new Exception("API Hatası ($httpCode): " . ($err['error']['message'] ?? $response));
        }

        $result = json_decode($response, true);
        $text = $result['candidates'][0]['content']['parts'][0]['text'];
        $text = preg_replace('/^```json\s*|```\s*$/i', '', trim($text));
        
        $data = json_decode($text, true);
        if (json_last_error() !== JSON_ERROR_NONE) throw new Exception("JSON Decode Hatası: " . json_last_error_msg());

        $data['_raw_response'] = $text;
        $data['_model_used'] = $model;

        return $data;
    }
}
