<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class InspectData extends CI_Controller {
    public function index() {
        $this->load->database();
        
        // 1. Cüzdanları Getir
        $wallet = $this->db->get('inv_wallets')->row_array();
        echo "<h1>Varsayılan Cüzdan</h1>";
        if ($wallet) {
            echo "<pre>" . print_r($wallet, true) . "</pre>";
        } else {
            echo "Cüzdan bulunamadı! Lütfen önce bir nakit kasa oluşturun.<br>";
        }

        // 2. JSON Yapısını Oku
        $json_path = FCPATH . 'stok_cari.json';
        if (file_exists($json_path)) {
            $json_content = file_get_contents($json_path);
            $data = json_decode($json_content, true);
            
            echo "<h1>JSON Örneği (İlk Kayıt)</h1>";
            if (!empty($data) && is_array($data)) {
                // İlk cariyi al
                $first_cari = reset($data);
                // Carinin temel bilgileri
                echo "<h3>Cari Yapısı:</h3>";
                // Sadece anahtarları ve basit değerleri göster, alt dizileri kısalt
                foreach($first_cari as $key => $val) {
                    if (is_array($val)) {
                        echo "[$key] => Array (" . count($val) . " items)<br>";
                        if (count($val) > 0) {
                            echo " -- İlk Öğe:<pre>" . print_r($val[0], true) . "</pre>";
                        }
                    } else {
                        echo "[$key] => $val<br>";
                    }
                }
            } else {
                echo "JSON decode edilemedi veya boş.";
            }
        } else {
            echo "stok_cari.json bulunamadı.";
        }
    }
}
