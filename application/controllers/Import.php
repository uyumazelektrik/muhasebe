<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Import extends CI_Controller {

    public $active_entity_id = null;
    public $default_wallet_id = null;
    private $product_cache = [];

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->model('Entity_model'); // Re-balance için
    }

    public function run() {
        set_time_limit(3600); // 1 Saat
        echo "<h1>IMPORT V11 (JSON): Başlıyor...</h1><pre>";

        // 1. TEMİZLİK
        echo "Veritabanı temizleniyor...\n";
        $this->db->query("SET FOREIGN_KEY_CHECKS=0");
        foreach(['inv_invoice_items','inv_entity_transactions','inv_invoices','inv_products','inv_entities','inv_wallets'] as $t) $this->db->truncate($t);
        $this->db->query("SET FOREIGN_KEY_CHECKS=1");

        // 2. ÖNCE ŞİRKET OLUŞTUR
        $owner_id = $this->Entity_model->create(['name' => 'ŞİRKET MERKEZİ', 'type' => 'company', 'created_at' => date('Y-m-d H:i:s')]);

        // 3. CÜZDAN OLUŞTUR
        $this->db->insert('inv_wallets', ['name' => 'Merkez Kasa', 'owner_entity_id' => $owner_id, 'balance' => 0, 'created_at' => date('Y-m-d H:i:s')]);
        $this->default_wallet_id = $this->db->insert_id();
        echo "✅ Varsayılan Kasa ve Şirket Oluşturuldu (ID: {$this->default_wallet_id})\n";
        
        $this->product_cache = []; // Cache Reset

        // 3. JSON OKU
        $json_path = FCPATH . 'stok_cari.json';
        if (!file_exists($json_path)) {
            die("Dosya bulunamadı: $json_path");
        }
        $json_data = json_decode(file_get_contents($json_path), true);
        if (!$json_data) {
            die("JSON formatı hatalı!");
        }

        echo "JSON Yüklendi. " . count($json_data) . " cari işlenecek.\n";

        // 4. İŞLE
        foreach ($json_data as $cari_raw) {
            $this->process_cari($cari_raw);
        }
        
        // Final: Cüzdan Bakiyesi Güncelle
        $this->db->query("UPDATE inv_wallets SET balance = (SELECT SUM(amount) FROM inv_entity_transactions WHERE wallet_id = " . $this->default_wallet_id . ") WHERE id = " . $this->default_wallet_id);

        echo "\n🏁 TÜM İŞLEMLER TAMAMLANDI.</pre>";
    }

    private function process_cari($cari_data) {
        // Cari Adı Ayrıştır (120-01-001  BEKİROĞLU...)
        $full_name = trim($cari_data['cari_kod_ve_ad']);
        $parts = explode(' ', $full_name, 2);
        $name = isset($parts[1]) ? trim($parts[1]) : $full_name;
        
        // Cari Kaydet
        $entity_id = $this->Entity_model->create([
            'name' => $name,
            'type' => 'supplier', // Varsayılan tedarikçi
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $this->active_entity_id = $entity_id;
        
        echo "\n🔸 Cari: $name (ID: $entity_id)\n";

        if (empty($cari_data['kayitlar'])) return;

        foreach ($cari_data['kayitlar'] as $idx => $kayit) {
            $evrak_adi = trim($kayit['adi']);
            $tarih = $this->parse_date($kayit['tarih']);

            // Mapping
            $map = $this->get_mapping($evrak_adi);
            
            $borc = (float)$kayit['borc'];
            $alacak = (float)$kayit['alacak'];
            $amount = ($borc > 0) ? $borc : $alacak;
            
            // Tarih Kontrolü
            if ($map['inv_type'] == 'opening') {
                $tarih = '2025-01-01'; // Sabit Tarih
                
                // Devir Bakiye: Yön sadece tutarın işaretini belirler, TİP SABİT KALIR 'opening'
                $map['inv_type'] = 'opening';
                $map['trx_type'] = 'opening';
                
                if ($borc > 0) {
                    $map['lbl'] = 'Açılış Bakiyesi (Borç)'; // Cari Borçlu (+)
                } else {
                    $map['lbl'] = 'Açılış Bakiyesi (Alacak)'; // Cari Alacaklı (-)
                }
                
                $map['type_category'] = 'opening_balance'; 
            }

            // Fatura/Fiş Oluştur
            $inv_no = "INV-" . $entity_id . "-" . ($idx + 1);
            
            // Temel Fatura Verisi
            $inv_data = [
                'invoice_no' => $inv_no,
                'invoice_date' => $tarih,
                'entity_id' => $entity_id,
                'type' => $map['inv_type'], 
                'status' => 'finalized',
                'notes' => "Kaynak: $evrak_adi",
                'created_at' => date('Y-m-d H:i:s'),
                'net_amount' => 0,
                'total_amount' => $amount,
                'tax_amount' => 0,
                'payment_status' => 'unpaid'
            ];
            
            // Finansal İşlemler (Tahsilat/Ödeme)
            if ($map['type_category'] == 'finance') {
                 $inv_data['payment_status'] = 'paid';
                 $inv_data['payment_type'] = 'cash_bank';
                 $inv_data['wallet_id'] = $this->default_wallet_id;
                 $inv_data['total_amount'] = $amount;
                 $inv_data['net_amount'] = $amount;
            } 
            // Açılış Bakiyesi (Ödeme/Tahsilat gibi ama Kasasız)
            else if ($map['type_category'] == 'opening_balance') {
                 $inv_data['payment_status'] = 'paid'; // Kapalı işlem
                 $inv_data['total_amount'] = $amount; 
                 // wallet_id YOK
            }

            $this->db->insert('inv_invoices', $inv_data);
            $inv_id = $this->db->insert_id();

            // 1. CARİ HAREKETİ (Transaction)
            // Borç (+), Alacak (-)
            // Satış (Sale) -> Cari Borçlanır (+)
            // Alış (Purchase) -> Cari Alacaklanır (-)
            // Tahsilat (Biz para aldık) -> Cari Alacaklanır (Borcu düşer) (-)
            // Ödeme (Biz para verdik) -> Cari Borçlanır (Alacağı düşer) (+)
            
            $trx_amount = 0;
            if ($borc > 0) $trx_amount = $borc; // Borç her zaman artıdır (Sistemde)
            elseif ($alacak > 0) $trx_amount = -$alacak; // Alacak her zaman eksidir

            $trx_data = [
                'entity_id' => $entity_id,
                'invoice_id' => $inv_id,
                'type' => $map['trx_type'], // fatura, tahsilat, odeme
                'document_no' => $inv_no,
                'transaction_date' => $tarih,
                'amount' => $trx_amount,
                'description' => $map['lbl'],
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            // Eğer finansal ise (tahsilat/ödeme), Cüzdan bilgisini de ekleyebiliriz ama 
            // `inv_entity_transactions` tablosunda `wallet_id` varsa ekleyelim.
            if ($map['type_category'] == 'finance') {
                $trx_data['wallet_id'] = $this->default_wallet_id;
            }

            $this->db->insert('inv_entity_transactions', $trx_data);

            // 2. KASA HAREKETİ (Eğer Finansal İse) - Opsiyonel
            // Genelde `inv_entity_transactions` carinin ekstresi içindir.
            // Ayrıca kasanın da hareket görmesi lazım. `inv_wallet_transactions` tablosu var mı? 
            // Yoksa `inv_entity_transactions` içinde type='tahsilat' zaten kasayı etkiliyor mu?
            // Sistemin yapısında `Invoice_model->create_invoice` içinde ayrı bir insert yoksa manuel eklemeye gerek yok.
            // Ancak varsayılan kasanın bakiyesinin artması için bu işlemlerin kaydı şart.
            // Şimdilik sadece cariye odaklanıyoruz.
            
            // 3. KALEMLER (Ürünler)
            // Sadece Belge tipindekilerde ürün ekle (Fatura/Fiş)
            if ($map['type_category'] == 'document' && !empty($kayit['urunler'])) {
                $total_net = 0;
                $total_tax = 0;
                $total_grand = 0;

                foreach ($kayit['urunler'] as $urun) {
                    $stok_adi = $urun['stok_adi'];
                    $giren = (float)$urun['giren'];
                    $cikan = (float)$urun['cikan'];
                    $qty = max($giren, $cikan);
                    $fiyat = (float)$urun['kdv_haric_birim_fiyat'];
                    
                    // Eğer miktar 0 ise Formülden hesapla: (Tutar - KDV) / Fiyat
                    if ($qty <= 0 && $fiyat > 0) {
                        $tutar_kdvli = (float)$urun['tutar'];
                        $kdv_tutari = (float)$urun['kdv_tutari'];
                        $matrah = $tutar_kdvli - $kdv_tutari;
                        
                        // Hassasiyet için round gerekebilir ama şimdilik direkt bölüyoruz
                        $qty = $matrah / $fiyat;
                        
                        // Hesap sonucu çok küçük veya negatifse varsayılan 1 olsun (Hizmet vb.)
                        if ($qty <= 0) $qty = 1;
                    } elseif ($qty <= 0) {
                         // Fiyat da yoksa
                         $qty = 1;
                    }
                    $kdv_orani = (float)$urun['kdv'];
                    $tutar = (float)$urun['tutar']; // Bu genellikle KDV dahil veya hariç toplam olabilir, kontrol edelim
                    // JSON'da: tutar = 8769.6. Fiyat=1218, Miktar=6. 1218*6=7308. 
                    // KDV Tutarı=1461.6. 7308+1461.6 = 8769.6.
                    // Demek ki JSON'daki 'tutar' GENEL TOPLAM (KDV Dahil).

                    // Ürün Bul/Ekle
                    $prod_id = $this->get_product_id($stok_adi);

                    $item_net = ($fiyat * $qty);
                    $item_tax = ($item_net * $kdv_orani / 100);
                    
                    $this->db->insert('inv_invoice_items', [
                        'invoice_id' => $inv_id,
                        'product_id' => $prod_id,
                        'quantity' => $qty,
                        'unit_price' => $fiyat,
                        'tax_rate' => $kdv_orani,
                        'tax_amount' => $item_tax,
                        'total_amount' => ($item_net + $item_tax), // Satır toplamı (KDV Dahil)
                        'description' => $stok_adi,
                        'item_type' => 'stok'
                    ]);

                    // --- STOK & FİYAT GÜNCELLEME (V16) ---
                    $price_col = ($inv_data['type'] == 'purchase') ? 'last_buy_price' : 'satis_fiyat';
                    $qty_op = ($inv_data['type'] == 'purchase') ? '+' : '-';
                    
                    // Güvenlik: Eğer 'tahsilat/odeme/opening' gibi stoksuz tipler buraya girerse diye (gerçi map kontrolde eleniyor)
                    if (in_array($inv_data['type'], ['sale', 'purchase'])) {
                        $sql = "UPDATE inv_products SET 
                                stock_quantity = stock_quantity $qty_op $qty, 
                                $price_col = $fiyat 
                                WHERE id = $prod_id";
                        $this->db->query($sql);
                    }

                    $total_net += $item_net;
                    $total_tax += $item_tax;
                    $total_grand += ($item_net + $item_tax);
                }

                // Faturayı Güncelle (Gerçek Toplamlarla)
                $this->db->where('id', $inv_id)->update('inv_invoices', [
                    'net_amount' => $total_net,
                    'tax_amount' => $total_tax,
                    'total_amount' => $total_grand
                ]);

                // Cari Hareket Tutarını Güncelle (Eğer Excel'deki tutar ile hesaplanan tutar farklıysa hesaplananı baz alalım mı?
                // Hayır, Excel'deki BORÇ/ALACAK tutarı esastır. Bazen kuruş farkı olabilir.
                // Şimdilik Excel'den gelen ana tutarı ($trx_amount) ellemiyoruz.
                // Eğer ürünlerin toplamı ile fatura genel toplamı tutmuyorsa, aradaki farkı 'Yuvarlama Farkı' gibi bir ekstra kaleme atabiliriz
                // Ama şimdilik basit tutalım.
            }
        }
        
        // Cari Bakiye Update
        $this->db->query("UPDATE inv_entities SET balance = (SELECT SUM(amount) FROM inv_entity_transactions WHERE entity_id = $entity_id) WHERE id = $entity_id");
    }

    private function get_mapping($text) {
        $text = mb_strtolower($text, 'UTF-8');
        // KULLANICI EŞLEŞTİRMELERİ
        // Açık Normal Toptan Satış Faturası = Satış Faturası -> sale
        // Alacak Dekontu = Tahsilat Dekontu -> tahsilat
        // Açık Normal Satış Fişi = Satış Faturası -> sale
        // Açık Normal Satınalma Fişi = Alış Faturası -> purchase
        // Açık Normal Toptan Satınalma Faturası = Alış Faturası -> purchase
        // Devir Bakiye = Açılış Bakiyesi -> opening
        // Borç Dekontu = Ödeme Dekontu -> odeme
        // Virman Dekontu = Virman Dekontu -> virman
        // Tahsilat Makbuzu = Tahsilat Dekontu -> tahsilat
        
        if (strpos($text, 'satış') !== false) {
            return ['type_category'=>'document', 'inv_type'=>'sale', 'trx_type'=>'fatura', 'lbl'=>'Satış Faturası'];
        }
        if (strpos($text, 'satınalma') !== false || strpos($text, 'alış') !== false) {
             return ['type_category'=>'document', 'inv_type'=>'purchase', 'trx_type'=>'fatura', 'lbl'=>'Alış Faturası'];
        }
        if (strpos($text, 'tahsilat') !== false || strpos($text, 'alacak dekontu') !== false) {
             return ['type_category'=>'finance', 'inv_type'=>'tahsilat', 'trx_type'=>'tahsilat', 'lbl'=>'Tahsilat Dekontu'];
        }
        if (strpos($text, 'borç dekontu') !== false || strpos($text, 'ödeme') !== false) {
             return ['type_category'=>'finance', 'inv_type'=>'odeme', 'trx_type'=>'odeme', 'lbl'=>'Ödeme Dekontu'];
        }
        if (strpos($text, 'virman') !== false) {
             return ['type_category'=>'finance', 'inv_type'=>'virman', 'trx_type'=>'virman', 'lbl'=>'Virman Dekontu'];
        }
        if (strpos($text, 'devir') !== false) {
             return ['type_category'=>'info', 'inv_type'=>'opening', 'trx_type'=>'fatura', 'lbl'=>'Açılış Bakiyesi'];
        }
        
        return ['type_category'=>'other', 'inv_type'=>'sale', 'trx_type'=>'fatura', 'lbl'=>$text];
    }

    private function get_product_id($raw_name) {
        $raw_name_trim = trim($raw_name);
        if (empty($raw_name_trim)) return null;

        // 1. Cache'te Tam Eşleşme (Hız için)
        if (isset($this->product_cache[$raw_name_trim])) {
            return $this->product_cache[$raw_name_trim];
        }

        // 2. Normalize Edilmiş İsim Eşleşmesi
        // "32A KONDAKTÖR" vs "32a kondaktor"
        $norm_new = $this->normalize_name($raw_name_trim);
        
        // Cache'i tara
        foreach ($this->product_cache as $cached_name => $cached_id) {
            $norm_cached = $this->normalize_name($cached_name);
            
            // a) Normalize Eşitlik
            if ($norm_new === $norm_cached) {
                $this->product_cache[$raw_name_trim] = $cached_id; // Yeni varyasyonu da cache'e ekle
                return $cached_id;
            }

            // b) Fuzzy Match (Levenshtein) - Sayısal Bütünlük Korumalı
            if (strlen($norm_new) > 4) {
                // Performans için ilk harf kontrolü
                if ($norm_new[0] !== $norm_cached[0]) continue;

                // NUMERİK KONTROL (Kritik):
                preg_match_all('/(\d+([.,]\d+)?)/', $raw_name_trim, $m_new);
                preg_match_all('/(\d+([.,]\d+)?)/', $cached_name, $m_cached);
                $nums_new = $m_new[0] ?? [];
                $nums_cached = $m_cached[0] ?? [];
                if ($nums_new != $nums_cached) continue;

                // ZITLIK KONTROLÜ (V20): "Akülü" vs "Aküsüz", "Altı" vs "Üstü"
                if ($this->has_conflict($norm_new, $norm_cached)) continue;

                $dist = levenshtein($norm_new, $norm_cached);
                
                // Tolerans: Maksimum 2 Karakter VEYA %5 (Hangisi küçükse değil, isim uzunsa 2 iyidir)
                // "Kondaktör" (1 fark) -> OK
                // "Sıvaaltısensör" (3 fark) -> RED
                // "Akülü" vs "Aküsüz" (s-z, l-s gibi değişimler riskli, o yüzden conflict check şart)
                $tolerance = 2; // Sabit tolerans en güvenlisi
                
                if ($dist <= $tolerance) {
                    echo "    ⚠️ Benzer Ürün Bulundu ve Eşleştirildi: '$raw_name_trim' ~= '$cached_name' (Fark: $dist)\n";
                    $this->product_cache[$raw_name_trim] = $cached_id;
                    return $cached_id;
                }
            }
        }

        // 3. Bulunamadı -> Yeni Oluştur
        $this->db->insert('inv_products', [
            'name' => $raw_name_trim,
            'stock_quantity' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'match_names' => $norm_new
        ]);
        $new_id = $this->db->insert_id();

        $this->product_cache[$raw_name_trim] = $new_id;
        return $new_id;
    }

    private function has_conflict($n1, $n2) {
        $pairs = [
            ['alti', 'ustu'],
            ['sag', 'sol'],
            ['ic', 'dis'],
            ['akulu', 'akusuz'],
            ['erkek', 'disi'],
            ['giris', 'cikis']
        ];
        foreach ($pairs as $p) {
            $h1_a = (strpos($n1, $p[0]) !== false);
            $h1_b = (strpos($n1, $p[1]) !== false);
            $h2_a = (strpos($n2, $p[0]) !== false);
            $h2_b = (strpos($n2, $p[1]) !== false);

            // Biri A, Diğeri B içeriyorsa -> CONFLICT
            if (($h1_a && $h2_b) || ($h1_b && $h2_a)) return true;
        }
        return false;
    }

    private function normalize_name($str) {
        $str = mb_strtolower($str, 'UTF-8');
        $str = str_replace(
            ['ı', 'ğ', 'ü', 'ş', 'ö', 'ç', ' '], 
            ['i', 'g', 'u', 's', 'o', 'c', ''], 
            $str
        );
        $str = preg_replace('/[^a-z0-9]/', '', $str); 
        return $str;
    }
    
    private function parse_date($val) {
        // JSON'da tarih formatı boş olabilir mi?
        if (empty($val)) return date('Y-m-d');
        // DD.MM.YYYY geliyorsa convert et
        if (preg_match('/(\d{2})\.(\d{2})\.(\d{4})/', $val, $m)) {
            return "{$m[3]}-{$m[2]}-{$m[1]}";
        }
        return date('Y-m-d', strtotime($val));
    }
}
