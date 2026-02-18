<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Clean extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function run() {
        echo "<h1>Veri Tabanı Temizliği Başlatılıyor...</h1>";

        // 1. Transaction Tablosu Temizliği (Invoice ID'si olmayan yetimler)
        // (Opsiyonel, import aracımız hep invoice_id veriyor ama yine de iyi olur)
        
        // 2. Mükerrer Fatura Kontrolü
        // Aynı entity_id ve invoice_no'ya sahip, tutarı 0 olan faturaları bul ve sil (Eğer dolu bir versiyonu varsa)
        
        $query = $this->db->query("
            SELECT entity_id, invoice_no, COUNT(*) as c 
            FROM inv_invoices 
            GROUP BY entity_id, invoice_no 
            HAVING c > 1
        ");
        $duplicates = $query->result_array();

        $deleted_count = 0;

        foreach ($duplicates as $dup) {
            $invoices = $this->db->where('entity_id', $dup['entity_id'])
                                 ->where('invoice_no', $dup['invoice_no'])
                                 ->order_by('net_amount', 'DESC') // Dolu olanlar üstte
                                 ->order_by('id', 'DESC')
                                 ->get('inv_invoices')
                                 ->result_array();
            
            // İlk kayıt (en yüksek tutarlı veya en son eklenen) kalsın, diğerlerini sil.
            // Ancak, eğer "dolu" bir kayıt varsa, 0 olanların hepsini sil.
            // Eğer hepsi 0 ise, sadece bir tane bırak.

            $keep_id = $invoices[0]['id']; 
            
            foreach ($invoices as $inv) {
                if ($inv['id'] == $keep_id) continue;

                // Sil
                $this->delete_invoice_completely($inv['id']);
                $deleted_count++;
                echo "Silindi: ID {$inv['id']} - {$inv['invoice_no']} (Tutar: {$inv['net_amount']})<br>";
            }
        }
        
        echo "<br>Toplam $deleted_count mükerrer kayıt silindi.<br>";
        
        // 3. Sıfır Tutarlı Dekontları (Stoksuz Fişleri) Temizle (Opsiyonel: Eğer hiç kalemi yoksa sil)
        // Şimdilik sadece mükerrerleri sildik.
    }

    private function delete_invoice_completely($id) {
        $this->db->where('invoice_id', $id)->delete('inv_invoice_items');
        $this->db->where('invoice_id', $id)->delete('inv_entity_transactions');
        $this->db->where('id', $id)->delete('inv_invoices');
    }
}
