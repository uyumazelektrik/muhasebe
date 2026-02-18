<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class InspectJsonDetails extends CI_Controller {
    public function index() {
        $json_path = FCPATH . 'stok_cari.json';
        $data = json_decode(file_get_contents($json_path), true);
        
        foreach($data as $cari) {
            if (!empty($cari['kayitlar'])) {
                foreach($cari['kayitlar'] as $kayit) {
                    if (!empty($kayit['urunler'])) {
                        echo "<h1>Ürünlü Kayıt Bulundu</h1>";
                        echo "Evrak: " . $kayit['adi'] . "<br>";
                        echo "<h5>Ürün Detayı:</h5><pre>";
                        print_r($kayit['urunler'][0]);
                        echo "</pre>";
                        return; // İlk bulduğunda çık
                    }
                }
            }
        }
        echo "hiç ürün bulunamadı.";
    }
}
