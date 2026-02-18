<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Gemini_service {
    private $apiKey;
    private $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/';
    private $CI;

    private $models = [
        'gemini-2.0-flash',           // Kararlı 2.0 sürümü
        'gemini-2.5-flash',           // En yeni amiral gemisi
        'gemini-2.5-flash-lite',      // Çok hızlı ve verimli
        'gemini-1.5-flash-latest',    // Klasik kararlı sürüm
        'gemini-2.5-pro'              // Güçlü ama yüksek kota tüketebilir
    ];

    public function __construct() {
        $this->CI =& get_instance();
        // Try to get from environment or config
        $this->apiKey = getenv('GEMINI_API_KEY');
        if (!$this->apiKey) {
            $this->apiKey = $this->CI->config->item('gemini_api_key');
        }
    }

    public function analyzeImage($base64Data, $mimeType, $systemInstruction, $prompt) {
        if (empty($this->apiKey)) {
            throw new Exception("Gemini API anahtarı eksik. Lütfen ortam değişkenlerini veya config dosyasını kontrol edin.");
        }

        // Remove base64 prefix if exists
        $base64Data = preg_replace('#^data:image/\w+;base64,#i', '', $base64Data);

        return $this->getWorkingModel($base64Data, $mimeType, $systemInstruction, $prompt);
    }

    private function getWorkingModel($base64Data, $mimeType, $systemInstruction, $prompt) {
        $lastError = 'Bilinmeyen Hata';
        foreach ($this->models as $model) {
            try {
                return $this->callApiSingle($model, $base64Data, $mimeType, $systemInstruction, $prompt);
            } catch (Exception $e) {
                $lastError = $e->getMessage();
                // If it's a 404 or 429, try next model
                if (strpos($lastError, '404') !== false || strpos($lastError, 'not found') !== false || strpos($lastError, '429') !== false) {
                    continue;
                }
                // For other errors, we might want to stop or continue
                continue;
            }
        }
        throw new Exception("İşlem başarısız. Denenen modeller yanıt vermedi. Son Hata: " . $lastError);
    }

    private function callApiSingle($model, $base64Data, $mimeType, $systemInstruction, $prompt) {
        $url = $this->baseUrl . $model . ':generateContent?key=' . $this->apiKey;

        $payload = [
            'system_instruction' => [
                'parts' => [['text' => $systemInstruction]]
            ],
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $prompt],
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
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            throw new Exception("Bağlantı Hatası: " . curl_error($ch));
        }
        curl_close($ch);

        if ($httpCode !== 200) {
            $err = json_decode($response, true);
            throw new Exception("API Hatası ($httpCode): " . ($err['error']['message'] ?? $response));
        }

        $result = json_decode($response, true);
        $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
        $text = preg_replace('/^```json\s*|```\s*$/i', '', trim($text));
        
        $data = json_decode($text, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("JSON Decode Hatası: " . json_last_error_msg());
        }

        return $data;
    }
}
