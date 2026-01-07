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