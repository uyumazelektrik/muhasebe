<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once FCPATH . 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

class ReadFirstCari extends CI_Controller {
    public function index() {
        $file = FCPATH . 'stok-cari.xlsx';
        try {
            $spreadsheet = IOFactory::load($file);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);
            
            $cariCount = 0;
            foreach ($rows as $r_idx => $cols) {
                foreach ($cols as $colLetter => $val) {
                    $val = trim($val ?? '');
                    if (preg_match('/^Cari\s*[:\.]\s*(.*)/iu', $val, $matches)) {
                        $cariCount++;
                        echo "--- CARİ #$cariCount BULUNDU ---\n";
                        echo "Satır: $r_idx\n";
                        echo "Tam İçerik: $val\n";
                        echo "Ayıklanan İsim: " . $matches[1] . "\n";
                        echo "--- Takip Eden 5 Satır ---\n";
                        
                        $subset = array_slice($rows, $r_idx, 6, true);
                        foreach ($subset as $idx => $rowCols) {
                            if ($idx == $r_idx) continue;
                            echo "SATIR $idx: ";
                            foreach ($rowCols as $L => $V) {
                                if ($V !== null && $V !== '') echo "[$L] $V | ";
                            }
                            echo "\n";
                        }
                        
                        if ($cariCount >= 1) return; // Sadece ilk cariyi oku
                    }
                }
            }
        } catch (Exception $e) {
            echo "Hata: " . $e->getMessage();
        }
    }
}
