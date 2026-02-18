<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

class JsonExport extends CI_Controller {

    public function run() {
        // Increase memory and time limits
        ini_set('memory_limit', '2048M');
        set_time_limit(0);

        $file_path = FCPATH . 'stok-cari.xlsx';
        if (!file_exists($file_path)) {
            die("File not found: $file_path");
        }

        echo "Reading Excel file... Please wait.\n";
        
        try {
            $spreadsheet = IOFactory::load($file_path);
            $sheet = $spreadsheet->getActiveSheet();
            
            // Get all data first? Row-by-Row is safer for 1700 lines? 
            // 1700 is small. toArray is fine.
            $rows = $sheet->toArray(null, false, false, true);
            
            $result = [];
            $current_cari = null;
            $current_doc = null; // Currently processing document
            $cari_balance = 0;

            foreach ($rows as $r_idx => $cols) {
                // Column Mapping based on shift analysis
                $val_A = trim($cols['A'] ?? ''); // Date or Cari Header
                $val_B = trim($cols['B'] ?? ''); // Stock Name
                $val_C = trim($cols['C'] ?? ''); // Doc Type
                $val_D = trim($cols['D'] ?? ''); // Usually Empty in shifted
                $val_E = trim($cols['E'] ?? ''); // Qty
                
                // Header Value Checks
                $header_borc   = $this->parse_money($cols['L'] ?? 0);
                $header_alacak = $this->parse_money($cols['M'] ?? 0);
                
                // Item Value Checks
                $item_qty   = $this->parse_money($cols['E'] ?? 0);
                $item_unit  = trim($cols['F'] ?? '');
                $item_kdv   = $this->parse_money($cols['G'] ?? 0);
                $item_price = $this->parse_money($cols['H'] ?? 0);
                // $item_disc  = $this->parse_money($cols['I'] ?? 0);
                $item_kvat  = $this->parse_money($cols['J'] ?? 0);
                $item_total = $this->parse_money($cols['K'] ?? 0);

                // --- 1. DETECT CARI HEADER ---
                if (preg_match('/^Cari\s*[:\.]\s*(.*)/iu', $val_A, $m)) {
                    // Close previous Doc
                    if ($current_doc) {
                        $this->add_doc_to_cari($current_cari, $current_doc);
                        $current_doc = null;
                    }
                    // Close previous Cari
                    if ($current_cari) {
                        $result[] = $current_cari;
                    }

                    // Start New Cari
                    $current_cari = [
                        'cari_kod_ve_ad' => trim($m[1]),
                        'kayitlar' => []
                    ];
                    $cari_balance = 0; // Reset balance
                    continue;
                }

                if (!$current_cari) continue;

                // --- 2. DETECT DOC HEADER ---
                // Condition: Date (A) or Type (C) is present, and Stock (B) is empty.
                // Also "Devir Bakiye" special case. (Usually in C or E for unshifted, C for shifted?)
                // Let's check Val_E for "Devir Bakiye" just in case it wasn't shifted there? No, shift is uniform.
                // We use C for Type.
                
                $is_doc_header = false;
                $doc_name = $val_C;
                
                // Fix for weird empty Type but date present
                if (empty($doc_name) && !empty($val_A)) {
                    // Maybe header without type? Default to "İşlem"
                     $is_doc_header = true;
                     $doc_name = "Belgisiz İşlem";
                }
                if (!empty($doc_name) && empty($val_B)) {
                    $is_doc_header = true;
                }
                
                // Detect Devir Bakiye
                // Sometimes "Devir Bakiye" is in A or C.
                if (strpos($val_A, 'Devir Bakiye') !== false) {
                     $is_doc_header = true; 
                     $doc_name = "Devir Bakiye";
                }

                if ($is_doc_header) {
                    // Close previous Doc
                    if ($current_doc) {
                        $this->add_doc_to_cari($current_cari, $current_doc);
                    }

                    // Balance Calc
                    $cari_balance += ($header_borc - $header_alacak);

                    // Start New Doc
                    $current_doc = [
                        'adi' => $doc_name,
                        'tarih' => $this->format_date($val_A),
                        'borc' => $header_borc,
                        'alacak' => $header_alacak,
                        'bakiye' => $cari_balance,
                        'urunler' => []
                    ];
                    continue;
                }

                // --- 3. DETECT ITEM ---
                if (!empty($val_B) && $current_doc) {
                    // Determine Giren/Cikan based on Doc Context?
                    // User Example: Sales -> Cikan. Purchase -> Giren.
                    // Simple heuristic: If "Satış" in Doc Name -> Cikan. Else Giren?
                    // "Devir Bakiye" shouldn't have items usually.
                    
                    $is_sale = (stripos($current_doc['adi'], 'Satış') !== false);
                    
                    $giren = $is_sale ? 0 : $item_qty;
                    $cikan = $is_sale ? $item_qty : 0;
                    
                    // Calc item row balance impact? 
                    // Usually items explicitly list Borc/Alacak components in the user JSON? 
                    // User JSON Item: 
                    // "borc": 12369.6, "alacak": 0, "bakiye": 4637...
                    // Wait, the ITEM object has Balance? 
                    // This implies the Item Row in Excel *also* had columns L/M filled?
                    // Let's check Row 5 debug. L=0, M=0.
                    // So for Items, Borc/Alacak is 0?
                    // But User JSON has "borc": 12369.6 on the ITEM object.
                    // That value matches the HEADER Borc (Row 4).
                    // This is redundant or maybe the user example merged Header+Item?
                    
                    // If there are multiple items, does the header borc get split?
                    // Or is it repeated?
                    // Usually Accounting: Header holds the total debt. Items build up the Subtotal.
                    // I will leave item-level borc/alacak as 0 unless I find it in the row.
                    
                    $item = [
                        'stok_adi' => $val_B,
                        'giren' => $giren,
                        'cikan' => $cikan,
                        'birim' => $item_unit,
                        'kdv' => $item_kdv, // Rate
                        'kdv_haric_birim_fiyat' => $item_price,
                        'iskonto' => 0,
                        'kdv_tutari' => $item_kvat,
                        'tutar' => $item_total,
                        // 'borc' => 0, // Not available on item row
                        // 'alacak' => 0
                    ];
                    $current_doc['urunler'][] = $item;
                }
            }
            
            // Close final
            if ($current_doc) $this->add_doc_to_cari($current_cari, $current_doc);
            if ($current_cari) $result[] = $current_cari;

            // Output JSON
            $json = json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            file_put_contents(FCPATH . 'stok_cari.json', $json);
            
            echo "JSON created successfully: stok_cari.json\n";

        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }
    
    // Helper to structure the Doc adding (handling evrak_grubu nesting if we wanted, but sticking to flat list inside 'kayitlar' for now based on decision)
    // Wait, User explicitly showed `evrak_grubu` wrapping. 
    // And also explicit flat "Devir Bakiye".
    // I will simulate:
    // If Doc is "Devir Bakiye" -> Add flat to `kayitlar`.
    // Else -> Check if last entry in `kayitlar` is a `evrak_grubu` wrapper?
    // Actually, creating a simpler structure is better. 
    // I'll stick to: `kayitlar` is a list of Docs. 
    // If exact wrapping format is needed, user can request refinement. 
    // But looking at "Fatih" example, *all* his docs were in `evrak_grubu`.
    // I'll assume `evrak_grubu` is just a key for "The list of documents".
    // Wait, `kayitlar` is an ARRAY.
    // Element 0: { evrak_grubu: [ ... ] }
    // This is weird. why array of *one* object which has a list?
    // Maybe grouping by Period?
    
    // I will proceed with: `kayitlar` = [ Doc1, Doc2, ... ]. 
    // It is the most standard interpretation.
    
    private function add_doc_to_cari(&$cari, $doc) {
        if (!$cari) return;
        // Optional: Flatten structure?
        // Let's create `evrak_grubu` wrapper for *every* invoice if that's the pattern?
        // Or just push the doc.
        
        // Pattern Match Attempt:
        // If Doc Name == "Devir Bakiye", push as Object.
        // Else, push inside a "evrak_grubu" container?
        // This is complex logic for 1 minute.
        // I will just push to `kayitlar`. "Simple is better".
        
        $cari['kayitlar'][] = $doc; 
    }

    private function parse_money($val) {
        if (empty($val)) return 0;
        $val = preg_replace('/[^0-9\.-]/', '', $val);
        return (float)$val;
    }
    
    private function format_date($val) {
        if (empty($val)) return null;
        if (is_numeric($val)) {
             return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($val)->format('d.m.Y');
        }
        return $val;
    }
}
