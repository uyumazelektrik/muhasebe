<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once FCPATH . 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

class DebugExcel extends CI_Controller {
    public function index() {
        $file = FCPATH . 'Stok Detaylı Cari Hesap Ekstresi (Renk Beden).XLS';
        $reader = IOFactory::createReaderForFile($file);
        $reader->setReadDataOnly(true);
        // İlgili satır aralığını okuyalım (Excel görselindeki 1656. satır civarı)
        // Filtre kullanarak sadece o satırları çekmek performanslı olur
        $filter = new MyReadFilter(1650, 1670);
        $reader->setReadFilter($filter);
        $spreadsheet = $reader->load($file);
        $sheet = $spreadsheet->getActiveSheet();
        
        echo "<pre>";
        foreach ($sheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            
            echo "<b>Satır " . $row->getRowIndex() . ":</b>\n";
            foreach ($cellIterator as $cell) {
                $val = $cell->getValue();
                $formatted = $cell->getFormattedValue();
                if (!empty($val)) {
                    echo "  [" . $cell->getColumn() . "] Raw: " . var_export($val, true) . " | Formatted: " . $formatted . "\n";
                }
            }
            echo "-----------------------------------\n";
        }
    }
}

class MyReadFilter implements \PhpOffice\PhpSpreadsheet\Reader\IReadFilter {
    private $startRow = 0;
    private $endRow = 0;
    public function __construct($start, $end) {
        $this->startRow = $start;
        $this->endRow = $end;
    }
    public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool {
        return ($row >= $this->startRow && $row <= $this->endRow);
    }
}
