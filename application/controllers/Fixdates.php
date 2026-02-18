<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Fixdates extends CI_Controller {

    public function __construct() {
        parent::__construct();
        // Sadece admin yetkisi olanların çalıştırabilmesi için kontrol eklenebilir
        // Ancak talep "script yaz ve url ver" olduğu için basit tutuyoruz.
        // Güvenlik için işlem bitince bu dosyayı silmenizi öneririm.
        $this->load->database();
    }

    public function index() {
        echo "<h1>Vade Tarihi Düzeltme İşlemi Başlatılıyor...</h1>";

        // 1. inv_entity_transactions tablosunu güncelle
        $this->db->trans_start();
        
        // Boş, NULL veya 0000-00-00 olan vade tarihlerini işlem tarihi ile eşitle
        $sql_transactions = "UPDATE inv_entity_transactions 
                             SET due_date = transaction_date 
                             WHERE due_date IS NULL 
                                OR due_date = '' 
                                OR due_date = '0000-00-00'";
        
        $this->db->query($sql_transactions);
        $affected_transactions = $this->db->affected_rows();

        // 2. inv_invoices tablosunu güncelle (Eğer varsa)
        if ($this->db->table_exists('inv_invoices')) {
            $sql_invoices = "UPDATE inv_invoices 
                             SET due_date = invoice_date 
                             WHERE due_date IS NULL 
                                OR due_date = '' 
                                OR due_date = '0000-00-00'";
            
            $this->db->query($sql_invoices);
            $affected_invoices = $this->db->affected_rows();
        } else {
            $affected_invoices = 0;
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            echo "<p style='color:red'>Hata oluştu! İşlem geri alındı.</p>";
        } else {
            echo "<h3>İşlem Başarıyla Tamamlandı!</h3>";
            echo "<ul>";
            echo "<li><strong>inv_entity_transactions</strong> tablosunda güncellenen kayıt sayısı: $affected_transactions</li>";
            echo "<li><strong>inv_invoices</strong> tablosunda güncellenen kayıt sayısı: $affected_invoices</li>";
            echo "</ul>";
            echo "<p>Vade tarihi boş olan kayıtların vade tarihi, işlem/fatura tarihi ile aynı yapıldı.</p>";
            echo "<p><em>Güvenlik nedeniyle işlemlerinizi bitirdikten sonra bu dosyayı (application/controllers/FixDates.php) silmeniz önerilir.</em></p>";
        }
    }
}
