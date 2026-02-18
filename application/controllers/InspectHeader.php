<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once FCPATH . 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

class InspectHeader extends CI_Controller {
    public function index() {
        $file = FCPATH . 'Stok Detaylı Cari Hesap Ekstresi (Renk Beden).XLS';
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        
        echo "<pre>";
        // İlk 10 satırı yazdır
        for ($i = 1; $i <= 10; $i++) {
            $vals = [];
            foreach (range('A', 'Z') as $col) {
                $cell = $sheet->getCell($col . $i);
                $val = trim($cell->getValue());
                if (!empty($val)) $vals[$col] = $val;
            }
            if (!empty($vals)) {
                echo "ROW $i: " . print_r($vals, true) . "\n";
            }
        }
    }
}
