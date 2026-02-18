<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ResetImport extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->model('Entity_model'); // Bakiye düzeltmeleri için
    }

    public function run() {
        echo "<h1>Veriler Temizleniyor (Hareketler Siliniyor)...</h1><pre>";
        
        $this->db->query("SET FOREIGN_KEY_CHECKS=0");
        
        $tables = [
            'inv_invoice_items',
            'inv_entity_transactions',
            'inv_invoices',
            'inv_entities',
            'inv_products'
        ];

        foreach ($tables as $table) {
            if ($this->db->truncate($table)) {
                echo "✅ Tablo temizlendi: $table\n";
            } else {
                echo "❌ Hata: $table temizlenemedi.\n";
            }
        }
        
        $this->db->query("SET FOREIGN_KEY_CHECKS=1");

        echo "\n🏁 İşlem başarıyla tamamlandı.\n";
    }
}
