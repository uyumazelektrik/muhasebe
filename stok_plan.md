# Personel Takip, Muhasebe ve Akıllı Stok Yönetimi - Teknik Uygulama Rehberi

Bu döküman, mevcut Antigravity (PHP) mimarisi üzerine inşa edilecek modülün adım adım (Fazlar halinde) uygulama planıdır.

---

## 🚩 FAZ 1: Veritabanı ve Altyapı Hazırlığı
Bu aşamada sistemin hafızası oluşturulur. Mevcut tablolar bozulmadan yeni ilişkisel tablolar eklenir.

- [x] **1.1. Core Tabloların Oluşturulması:** `inv_products`, `inv_entities` ve `inv_movements` tablolarının SQL üzerinden tanımlanması.
    * *Teknik Detay:* `stock_quantity` alanı `DECIMAL(15,4) SIGNED` olmalıdır (Negatif stok için).
- [x] **1.2. Mapping Tablosunun Oluşturulması:** `inv_mapping` tablosu ile tedarikçi bazlı ürün eşleştirme hafızasının kurulması.
- [x] **1.3. Antigravity Model Entegrasyonu:** `app/Models` dizinine `ProductModel.php` ve `MovementModel.php` dosyalarının eklenmesi ve temel CRUD (Create, Read, Update, Delete) fonksiyonlarının yazılması.

**Teknik Kritik Nokta:** Ağırlıklı ortalama maliyet (`avg_cost`) alanı, her alış faturasında güncellenecek şekilde default `0.0000` atanmalıdır.

---

## 🚩 FAZ 2: Gemini API ve Görüntü İşleme Katmanı
Yapay zekanın sisteme entegre edildiği, verinin ham görüntüden dijital JSON'a dönüştüğü aşama.

- [x] **2.1. API Bağlantı Servisi:** `app/Services/GeminiService.php` oluşturulması.
    * *Teknik Detay:* Görüntüler sunucuya yüklenmeden önce PHP `GD` kütüphanesi ile 800px genişliğe resize edilmeli (Token tasarrufu ve hız için).
- [x] **2.2. Gider/Stok Ayrım Mantığı:** Gemini System Prompt'un hazırlanması.
    * *Prompt Kuralı:* "Eğer kalemler arasında elektrik, su, nakliye, işçilik varsa `type: GIDER`, fiziksel nesneler varsa `type: STOK` döndür."
- [x] **2.3. Barkod Fallback Mekanizması:** Kamera açıldığında önce barkod kütüphanesi (Zxing veya QuaggaJS) çalıştırılmalı, başarısız olursa görüntü Gemini'a gönderilmeli.

---

## 🚩 FAZ 3: Frontend Onay ve Mapping Arayüzü
Kullanıcının yapay zekayı denetlediği ve eşleştirmeleri manuel yaptığı "Human-in-the-Loop" aşaması.

- [x] **3.1. Validation Table:** `views/invoice/validation.php` ekranının kodlanması.
    * *Özellik:* Gemini'dan gelen JSON verisi `contenteditable` HTML tablolarına dökülmeli.
- [x] **3.2. Mapping Modalı:** Eşleşmeyen ürünler için `Select2` veya benzeri bir "Live Search" arama kutusu içeren modal tasarımı.
- [x] **3.3. Uyarı Mekanizmaları (Alerts):**
    - [x] **Birim Uyarısı:** Fatura birimi != Stok birimi ise kırmızı border.
    - [x] **Fiyat Artış Uyarısı:** `Yeni_Fiyat > (Mevcut_Ortalama * 1.20)` ise "Fiyat %20 arttı" rozeti.

---

## 🚩 FAZ 4: Negatif Stok ve Ağırlıklı Ortalama Kayıt Mantığı
Sistemin matematiksel olarak en hassas olduğu, veritabanına nihai kaydın atıldığı aşama.

- [x] **4.1. Kayıt Controller'ı:** `InvoiceController::store()` metodunun yazılması.
- [x] **4.2. Ağırlıklı Ortalama Algoritması:**
    * *Senaryo 1 (Stok > 0):* `((Mevcut_Stok * Mevcut_Maliyet) + (Yeni_Miktar * Yeni_Fiyat)) / (Mevcut_Stok + Yeni_Miktar)`
    * *Senaryo 2 (Stok <= 0):* `Yeni_Maliyet = Yeni_Fiyat` (Geçmişteki belirsiz maliyetli çıkışları yeni faturayla fixler).
- [x] **4.3. Hareket Loglama:** Her kalem için `inv_movements` tablosuna işlem türüyle kayıt atılması.

---

## 🚩 FAZ 5: Analiz, Grafik ve Raporlama
Verinin iş zekasına (BI) dönüştüğü, ürün bazlı detay ekranı.

- [x] **5.1. Analiz Sorguları:** `ProductModel` içinde zaman serisi verisi (Time-series) çeken SQL sorgularının yazılması.
- [x] **5.2. Chart.js Entegrasyonu:** Ürün kartı tıklandığında açılan grafikte:
    - [x] Alış fiyat trendi (Line Chart).
    - [x] Satış adetleri (Bar Chart).
    - [x] Stok seviye değişimi.
- [x] **5.3. Mapping Yönetim Paneli:** Yanlış yapılan eşleştirmelerin silinebileceği veya düzenlenebileceği basit bir liste ekranı.

---

## 📂 Dosya Dizin Planı (Antigravity Standart)

```text
/app
├── /Controllers
│   ├── InventoryController.php     # Faz 2 & 5: Barkod/Görüntü/Grafik yönetimi
│   └── InvoiceController.php       # Faz 3 & 4: Fatura işleme ve Kayıt
├── /Models
│   ├── ProductModel.php            # Faz 1 & 4: Ağırlıklı ortalama logic
│   └── MappingModel.php            # Faz 1 & 3: Eşleştirme logic
└── /Services
    └── GeminiService.php           # Faz 2: Vision API Entegrasyonu

/views
├── /inventory
│   └── detail.php                  # Faz 5: Grafiklerin olduğu ürün kartı
└── /invoice
    ├── upload.php                  # Faz 2: Dosya/Görüntü yükleme
    └── validation.php              # Faz 3: Onay ve Düzenleme tablosu