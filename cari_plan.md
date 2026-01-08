# Personel Takip, Muhasebe ve Akıllı Stok Yönetimi - Kapsamlı Uygulama Rehberi

Bu döküman, Stok yönetimine ek olarak **Cari Borç/Alacak (Bakiye)** takibini de sisteme entegre eden genişletilmiş planıdır.

---

## 🚩 FAZ 1: Veritabanı ve Finansal Altyapı
Stok tablolarına ek olarak, carilerin finansal durumunu takip edecek tablolar sisteme dahil edilir.

- [x] **1.1. Finansal Cari Tablosu:** `inv_entities` tablosuna `balance` (bakiye) alanı eklenmesi.
    * *Teknik Detay:* `balance DECIMAL(15,4) DEFAULT 0.0000` (Artı bakiye alacak, eksi bakiye borç).
- [x] **1.2. Cari Hareketler Tablosu (Cari Ekstresi):** `inv_entity_transactions` tablosunun oluşturulması.
    * *Alanlar:* `id, entity_id, type (fatura/tahsilat/odeme), amount, description, date`.
- [x] **1.3. Antigravity Model Genişletme:** `EntityModel.php` içine `get_balance()` ve `get_statement()` (ekstre) fonksiyonlarının yazılması.

---

## 🚩 FAZ 2: Gemini API ve Veri Kategorizasyonu
Faturadan sadece stok değil, finansal yükümlülük verilerinin de çıkarılması.

- [x] **2.1. Ödeme Tipi Tespiti:** Gemini promptuna "Bu fatura Kapalı (Ödenmiş) mı yoksa Açık (Borç) mı?" sorusunun eklenmesi.
- [x] **2.2. VKN/TCKN ile Cari Eşleme:** Cari bulunamazsa otomatik oluşturma ve finansal başlangıç bakiyesi tanımlama.

---

## 🚩 FAZ 3: Frontend Onay ve Bakiye Önizleme
Kullanıcı faturayı onaylarken cari bakiyesinin nasıl etkileneceğini görmelidir.

- [x] **3.1. Cari Bilgi Paneli:** Onay ekranının üst kısmına "Mevcut Bakiye" ve "Fatura Sonrası Bakiye" widget'larının eklenmesi.
- [x] **3.2. Tahsilat/Ödeme Girişi:** Fatura onaylanırken "Nakit Ödendi" veya "Cariye İşle" seçeneğinin sunulması.



---

## 🚩 FAZ 4: Kayıt, Maliyet ve Cari Entegrasyonu
Fatura onaylandığında stoklar artarken cari bakiye de güncellenir.

- [x] **4.1. Atomik Kayıt (Transaction):** Fatura kaydedilirken;
    1. Stoklar güncellenir (Ağırlıklı Ortalama).
    2. Cari bakiyesi fatura tutarı kadar güncellenir.
    3. `inv_entity_transactions` tablosuna "Alış Faturası No: X" açıklamasıyla kayıt atılır.
- [x] **4.2. Negatif Stok & Negatif Bakiye Kontrolü:** Borç limiti aşım uyarılarının tetiklenmesi.

---

## 🚩 FAZ 5: Analiz, Grafik ve Cari Raporlama
Ürün kartı detayına ek olarak **Cari Kart Detay** ekranının yapılması.

- [x] **5.1. Cari Ekstresi:** Tarih bazlı borç/alacak dökümü (PDF döküm desteğiyle).
- [x] **5.2. Borç/Alacak Yaşlandırma Grafiği:** Hangi tedarikçiye ne zamandır borçlu olduğumuzun görsel analizi.

# 🚩 FAZ 6: Personel Cari ve Zimmet Yönetimi
Personelleri birer "İç Cari" olarak sisteme dahil ederek, hem finansal hem de stok bazlı hareketlerini takip ediyoruz.

- [x] **6.1. Personel Cari Tanımlama:** `inv_entities` tablosuna `entity_type` olarak `staff` (personel) değerinin eklenmesi.
- [ ] **6.2. Personel Zimmet Mantığı:** [İPTAL EDİLDİ] Kullanıcı isteği üzerine zimmet takip özelliği sistemden kaldırıldı.
- [x] **6.3. Harcırah ve Masraf Takibi:** Personelin fatura işleme ekranı üzerinden "Personel Masrafı" olarak girdiği (yemek, yakıt) faturaların, personelin cari alacağına işlenmesi.
- [x] **6.4. Personel Performans Analizi:** Cari listesi üzerinde personelin finansal durumunun (Alacak/Borç) takibi. (Zimmet analizi kaldırıldı)

---

## 📂 Güncellenmiş Teknik Detaylar

### Veritabanı Değişikliği
* **`inv_entities`:** `entity_type` alanı `ENUM('vendor', 'customer', 'staff', 'both')` olarak güncellenmelidir.
* **`inv_movements`:** Personel zimmet hareketleri için `type` alanına `transfer` veya `staff_issue` eklenmelidir.

### Personel Cari Mantığı (Şeytanın Detayı)
1. **Borçlu Durum:** Personel şirketten avans aldıysa veya zimmetindeki ürünü kaybettiysen şirkete borçlu görünür.
2. **Alacaklı Durum:** Personel kendi cebinden bir iş harcaması yaptıysa ve faturasını Gemini ile sisteme işlediyse, şirket personele borçlanır.


# 🚩 FAZ 7: Çoklu Para Birimi ve Varlık (Döviz/Altın) Takibi - [x]
Bu faz, işletmenin sadece TL değil; döviz, altın ve kredi kartı üzerinden yaptığı borçlanmaları "parite" bazlı takip etmesini sağlar.

- [x] **7.1. Cüzdan ve Kasa Tanımları:** `inv_entity_balances` tablosunun oluşturulması.
    * *Durum:* Tamamlandı. Her cari için TL, USD, EUR, GOLD bakiyeleri ayrıldı.
- [x] **7.2. Kur ve Parite Entegrasyonu:** Günlük TCMB kurlarının sisteme çekilmesi.
    * *Durum:* `CurrencyService` ile TCMB entegrasyonu sağlandı.
- [x] **7.3. Kredi Kartı ve Taksit Takibi:** Kredi kartı varlığı ENUM olarak eklendi.
- [x] **7.4. Çapraz Ödeme Mantığı:** İşlem anındaki kur ile TL bakiye eşlemesi sağlandı.

---

## 🛠 Teknik Veritabanı Genişletmesi

### Yeni Tablo: `inv_entity_balances` (Varlık Bazlı Bakiye)
| Alan | Tip | Açıklama |
| :--- | :--- | :--- |
| `entity_id` | INT | Cari veya Personel ID |
| `asset_type` | ENUM | TL, USD, EUR, GOLD, CREDIT_CARD |
| `amount` | DECIMAL(15,4) | Mevcut Varlık/Borç miktarı |

### Yeni Tablo: `inv_currency_logs` (Kur Geçmişi)
| Alan | Tip | Açıklama |
| :--- | :--- | :--- |
| `currency_code` | VARCHAR(5) | USD, GOLD (Gr), vb. |
| `rate` | DECIMAL(15,4) | İşlem anındaki TL karşılığı |
| `date` | DATETIME | Kayıt tarihi |

---

## 📂 Dosya Dizin Değişikliği
* `app/Services/CurrencyService.php`: Kur verilerini çeken yeni servis.
* `app/Controllers/FinanceController.php`: Borç alma/verme ve kur farkı işlemlerini yöneten kontrolcü.

---

## ⚠️ Şeytanın Avukatı: "Fiktif Kâr/Zarar" Riski
Borcu altınla alıp TL ile ödediğinizde, arada oluşan fark sizin operasyonel kârınız değil, "Finansman Gideri" veya "Geliri"dir. 
* **Öneri:** Sistem, ödeme anındaki kur ile borcun alındığı gündeki kur arasındaki farkı otomatik hesaplayıp "Kur Farkı Zararı" olarak raporlamalıdır. Aksi halde işletmenizin neden para kaybettiğini bulamazsınız.

🚩 FAZ 8: Karmaşık Finansman ve 3. Taraf Kart Kullanımı Takibi
Bu faz, işletmenin kendi finansal araçları ile başkalarına ait araçların (emanet kartlar) senkronize takibini sağlar.

[ ] 8.1. Finansal Araç (Cüzdan) Havuzu Tanımlama: inv_wallets tablosunun oluşturulması.

Kapsam: "İşletme Kredi Kartı X", "Ahmet'in Kredi Kartı", "Banka Kredisi Y", "Nakit Kasa".

[ ] 8.2. Çapraz Ödeme Motoru: Bir borcun, o borca ait olmayan bir araçla ödenmesi mantığı.

Senaryo: Tedarikçi faturası (10.000 TL) -> Ahmet'in Kredi Kartı (Ödeme Aracı) -> Tedarikçi Bakiyesi: 0 / Ahmet'in Cari Bakiyesi: -10.000 TL (Borçlandık).

[ ] 8.3. Taksit ve Faiz Takibi: Kredi kartı taksitlerinin veya banka kredisi faizlerinin "Finansman Gideri" olarak stok maliyetinden bağımsız izlenmesi.

[ ] 8.4. Geri Ödeme Planlayıcı: Arkadaşınızın kartına veya banka kredisine yapılacak ödemelerin takvim bazlı hatırlatılması.

🛠 Teknik Veritabanı ve Akış Güncellemesi
Yeni Tablo: inv_wallets (Ödeme Kaynakları)
Bu tablo, paranın fiziksel olarak nereden çıktığını tutar. | Alan | Tip | Açıklama | | :--- | :--- | :--- | | id | INT | Otomatik ID | | owner_entity_id | INT | Kart/Kredi sahibi (İşletme veya Personel/Arkadaş) | | wallet_type | ENUM | CC (Kredi Kartı), CASH, LOAN (Kredi), GOLD_ACC | | limit | DECIMAL | Varsa limit (Kredi kartı/Kredi için) |

İşlem Akış Mantığı (PHP / Antigravity)
Başkasının kartıyla ödeme yapıldığında InvoiceController şu üçlü kaydı aynı anda (Atomic Transaction) atmalıdır:

Tedarikçi Kaydı: Borç kapanır.

Emanet Sahibi Kaydı: Arkadaşınızın cari kartına "Ödeme Yapıldı" açıklamasıyla borç yazılır.

Varlık Kaydı: Eğer ödeme taksitliyse, her ay personelin/arkadaşın ekstresine yansıyacak şekilde inv_entity_transactions tablosuna taksitli döküm oluşturulur.

📂 Güncellenmiş .md Kontrol Listesi
[ ] [ ] Faz 8.1: inv_wallets tablosunu oluştur ve inv_entities (Personel/Arkadaş) ile ilişkilendir.

[ ] [ ] Faz 8.2: Ödeme ekranına "Ödeme Aracı Seç" dropdown listesi ekle (Kendi kartım, Ahmet'in kartı vb.).

[ ] [ ] Faz 8.3: Taksitli işlemler için otomatik takvim (Reoccurrence) fonksiyonu yaz.

[ ] [ ] Faz 8.4: Döviz/Altın bazlı borçlanmalarda "Ödeme Anındaki Kur" ile "Borç Anındaki Kur" farkını hesaplayacak FinanceService metodunu geliştir.

⚠️ Şeytanın Avukatı: "Gizli Faiz ve Sadakat" Riski
Arkadaşınızın kartını kullandığınızda, banka ona puan/mil kazandırıyor olabilir veya taksit faizini arkadaşınız üstleniyor olabilir.

Soru: Arkadaşınıza olan borcunuzu öderken bankanın kestiği komisyonu da ekleyecek misiniz?

Öneri: "Dost işi" borçlanmalarda, inv_entity_transactions tablosuna bir "Masraf/Komisyon" alanı ekleyelim ki arkadaşınıza ana parayı öderken banka kesintilerini de sistemde görebilin.

Bir sonraki adım: Bu karmaşık "Ödeme Kaynakları" (Wallets) sistemini veritabanında tanımlayıp, InvoiceController içinde "Başkasının Kartıyla Öde" butonunu mu kurgulayalım?