<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class InspectPurchaseDetails extends CI_Controller {
    public function index() {
        $json_path = FCPATH . 'stok_cari.json';
        $data = json_decode(file_get_contents($json_path), true);
        
        $found = false;
        foreach($data as $cari) {
            if (!empty($cari['kayitlar'])) {
                foreach($cari['kayitlar'] as $kayit) {
                    $adi = mb_strtolower($kayit['adi'], 'UTF-8');
                    // Alış Faturası türevlerini ara
                    if (strpos($adi, 'satınalma') !== false || strpos($adi, 'alış') !== false) {
                        echo "<h1>Alış Kaydı Bulundu: {$kayit['adi']}</h1>";
                        echo "<h3>Ürünler:</h3><pre>";
                        if (!empty($kayit['urunler'])) {
                            print_r($kayit['urunler']);
                            $found = true;
                        } else {
                            echo "ÜRÜN DİZİSİ BOŞ!";
                        }
                        echo "</pre>";
                        
                        if ($found) return; // İlk doluyu bulunca çık
                    }
                }
            }
        }
        if (!$found) echo "İçinde ürün olan hiç alış faturası bulunamadı.";
    }
}
