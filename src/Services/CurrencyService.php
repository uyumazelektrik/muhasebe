<?php
// src/Services/CurrencyService.php

class CurrencyService {
    private $pdo;
    private $tcmbUrl = "https://www.tcmb.gov.tr/kurlar/today.xml";

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * TCMB'den güncel kurları çeker ve veritabanına kaydeder
     */
    public function updateRates() {
        try {
            $xml = simplexml_load_file($this->tcmbUrl);
            if (!$xml) throw new Exception("TCMB verisi alınamadı.");

            $currencies = ['USD', 'EUR'];
            $rates = [];

            foreach ($xml->Currency as $currency) {
                $code = (string)$currency['CurrencyCode'];
                if (in_array($code, $currencies)) {
                    $rate = (float)$currency->ForexSelling;
                    $this->logRate($code, $rate);
                    $rates[$code] = $rate;
                }
            }

            // Altın kuru şimdilik sabit veya başka bir API'den alınabilir
            // Örnek: Gram Altın ~ USD/oz üzerinden hesaplanabilir veya manuel girilebilir.
            // Bu basitte 2500 TL varsayalım veya bir simülasyon yapalım.
            $this->logRate('GOLD', 3000.00); 
            $rates['GOLD'] = 3000.00;

            return $rates;
        } catch (Exception $e) {
            error_log("Kur güncelleme hatası: " . $e->getMessage());
            return false;
        }
    }

    private function logRate($code, $rate) {
        $stmt = $this->pdo->prepare("INSERT INTO inv_currency_logs (currency_code, rate) VALUES (?, ?)");
        $stmt->execute([$code, $rate]);
    }

    public function getLatestRate($code) {
        if ($code === 'TL') return 1.0;
        
        $stmt = $this->pdo->prepare("SELECT rate FROM inv_currency_logs WHERE currency_code = ? ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$code]);
        $rate = $stmt->fetchColumn();
        
        return $rate ? (float)$rate : 1.0;
    }
}
