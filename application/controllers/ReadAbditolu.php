<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once FCPATH . 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

class ReadAbditolu extends CI_Controller {
    public function index() {
        $file = FCPATH . 'stok-cari.xlsx';
        try {
            $spreadsheet = IOFactory::load($file);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);
            
            echo "<pre>";
            $foundTarget = false;
            foreach ($rows as $r_idx => $cols) {
                // Her satırı kontrol et "Cari :" ifadesi için
                foreach ($cols as $colLetter => $val) {
                    $val = trim($val ?? '');
                    if (preg_match('/^Cari\s*[:\.]\s*(.*Abditolu.*)/iu', $val, $matches)) {
                        echo "HEDEF CARİ BULUNDU -> SATIR $r_idx\n";
                        echo "Cari Bilgisi: $val\n";
                        echo "----------------------------------------------------------\n";
                        $foundTarget = true;
                        continue 2; // Bu satırı geç, altındakileri oku
                    }
                }

                if ($foundTarget) {
                    // Eğer yeni bir cari satırı geldiyse dur
                    foreach ($cols as $val) {
                        if (preg_match('/^Cari\s*[:\.]/iu', trim($val ?? ''))) {
                             echo "\n--- YENİ CARİ BAŞLADI, OKUMA BİTTİ ---\n";
                             return;
                        }
                    }

                    // Bu carinin hareketlerini yazdır
                    echo "SATIR $r_idx: ";
                    foreach ($cols as $letter => $v) {
                        if ($v !== null && $v !== '') {
                            echo "[$letter] $v | ";
                        }
                    }
                    echo "\n";
                }
            }
            
            if (!$foundTarget) {
                echo "Abditolu Ömer Çamlı bulunamadı.";
            }
            
        } catch (Exception $e) {
            echo "Hata: " . $e->getMessage();
        }
    }
}
