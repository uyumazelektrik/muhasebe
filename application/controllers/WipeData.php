<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class WipeData extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function run() {
        echo "<h1>Tam Veri Temizliği</h1>";
        
        // Tabloları boşalt (TRUNCATE yerine DELETE kullanıyoruz ki foreign key constraint hatası almayalım, 
        // CodeIgniter ilişkileri yönetiyorsa güvenli olsun)
        
        // 1. Hareketler ve Detaylar
        $this->db->empty_table('inv_invoice_items');
        $this->db->empty_table('inv_entity_transactions');
        
        // 2. Faturalar
        $this->db->empty_table('inv_invoices');
        
        // 3. Ürünler ve Cariler (Kullanıcı 'mevcut kayıtların tamamını sil' dediği için)
        // Ancak admin kullanıcısını silmemeliyiz (Entity tablosunda user yok ama yine de dikkatli olalım)
        $this->db->empty_table('inv_products');
        $this->db->empty_table('inv_entities');
        
        // Auto Increment Sıfırlama (MySQL)
        $this->db->query("ALTER TABLE inv_invoice_items AUTO_INCREMENT = 1");
        $this->db->query("ALTER TABLE inv_entity_transactions AUTO_INCREMENT = 1");
        $this->db->query("ALTER TABLE inv_invoices AUTO_INCREMENT = 1");
        $this->db->query("ALTER TABLE inv_products AUTO_INCREMENT = 1");
        $this->db->query("ALTER TABLE inv_entities AUTO_INCREMENT = 1");

        echo "Veritabanı (Faturalar, Hareketler, Ürünler, Cariler) tamamen temizlendi ve sıfırlandı.<br>";
    }
}
