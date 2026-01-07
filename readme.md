# Akıllı Ön Muhasebe & AI Destekli Stok Yönetim Sistemi

Bu proje, geleneksel personel takip sistemini; yapay zeka (Gemini AI) destekli fatura işleme, akıllı stok takibi ve dinamik cari yönetim modülleriyle birleştiren kapsamlı bir ERP çözümüdür.

## 🚀 Proje Amacı
Sistemin temel amacı, işletmelerin manuel veri girişi yükünü azaltmak, hata payını minimize etmek ve stok/cari süreçlerini yapay zeka yardımıyla otomatize etmektir. Özellikle fatura görsellerinden otomatik veri çıkarma (OCR) ve ürün eşleştirme özellikleri projenin kalbini oluşturur.

---

## 📂 Dizin Yapısı
Proje, modüler ve MVC yapısına yakın bir şekilde organize edilmiştir:

```text
proje/
├── assets/             # CSS (Tailwind), JS ve Medya dosyaları
├── config/             # Veritabanı bağlantı yapılandırması (db.php)
├── database/           # SQL şemaları ve güncelleme betikleri
├── modules/            # Özelleşmiş iş modülleri (Stok, İş Takibi, Finans)
├── public/             # Dışarıya açık sayfalar (Müşteri görüntüleme vb.)
├── src/                # Çekirdek PHP mantığı
│   ├── Controllers/    # Rotaları yöneten kontrolörler (InvoiceController vb.)
│   ├── Models/         # Veritabanı modelleri (ProductModel, EntityModel vb.)
│   ├── Services/       # Dış servis entegrasyonları (GeminiService.php)
│   └── helpers.php     # Global yardımcı fonksiyonlar
├── views/              # Arayüz dosyaları (HTML/PHP Karışık)
│   ├── invoice/        # Fatura yükleme ve doğrulama ekranları
│   ├── entities/       # Cari yönetim ekranları
│   └── layout/         # Header/Footer gibi ortak şablonlar
├── .env                # API Anahtarları ve Hassas Ayarlar
├── index.php           # Ana Router (Yönlendirici)
└── readme.md           # Sistem dökümantasyonu
```

---

## 🗄️ Veritabanı Yapısı
Sistem, ilişkisel bir MySQL veritabanı üzerinde çalışır. Temel tablolar şunlardır:

### 1. Stok ve Hareketler
*   **`inv_products`**: Ürünlerin adı, barkodu, birimi, ortalama maliyeti, son alış fiyatı ve stok miktarı bilgisini tutar.
*   **`inv_movements`**: Her türlü stok giriş-çıkış hareketini (fatura, sarfiyat, iade) `tax_rate` ve `tax_amount` detaylarıyla kaydeder.
*   **`inv_mapping`**: Yapay zekanın faturada okuduğu metinler ile stok kartları arasındaki eşleşmeleri hafızaya alır.

### 2. Cari ve Finans
*   **`inv_entities`**: Tedarikçiler, müşteriler ve personel bilgilerini tutar. `balance` (cari bakiye) bilgisini anlık günceller.
*   **`inv_entity_transactions`**: Cari ekstre kayıtlarını tutar. Fatura bazlı borç/alacak hareketlerini `tax_total` ve `discount_total` bazlı belgelerle saklar.

### 3. Personel ve Devamlılık
*   **`users`**: Kullanıcı rolleri (Admin/Personel) ve temel bilgiler.
*   **`attendance`**: Giriş-çıkış saatleri, mesai ve gecikme takibi.

---

## 🔄 İş Akışı ve Çalışma Mantığı

### 1. AI Destekli Fatura İşleme (OCR)
1.  **Yükleme:** Kullanıcı bir fatura fotoğrafı veya PDF'i sisteme yükler.
2.  **Analiz (Gemini AI):** `GeminiService`, görseli analiz ederek faturan tüm kalemlerini (Miktar, Birim, İskontolu Net Fiyat, KDV Oranı) JSON formatında çıkarır.
3.  **Akıllı Eşleştirme:** Sistem, faturadaki her bir satırı veritabanındaki mevcut stok kartlarıyla (`mapping` hafızası veya fuzzy search ile) eşleştirmeye çalışır.
4.  **Doğrulama:** Kullanıcı ekranda yapay zekanın çıkardığı verileri ve eşleşmeleri kontrol eder, gerekirse düzeltir.
5.  **Kayıt:** Onaylandığında;
    *   Stok miktarları güncellenir.
    *   Ağırlıklı ortalama maliyet (AVCO) otomatik yeniden hesaplanır.
    *   İlgili cari karta (Tedarikçi) borç olarak işlenir.
    *   Stok hareket günlüğü (Movements) oluşturulur.

### 2. Stok ve Operasyon
*   **Sarfiyat Girişi:** Personel sahada kullandığı malzemeleri iş kartına eklediğinde stoktan anlık düşüş yapılır ve işin maliyeti güncellenir.
*   **Kritik Stok Uyarıları:** Seviyenin altına düşen ürünler Dashboard üzerinde yöneticiye raporlanır.

---

## 🛠 Teknik Detaylar
*   **AI Engine:** Google Gemini 1.5 Pro / Flash (Çoklu model fallback yapısı).
*   **Frontend:** Modern ve responsive tasarım için Tailwind CSS & Material Symbols.
*   **Güvenlik:** Role-Based Access Control (RBAC). Admin tüm maliyetleri görürken, personel sadece satış fiyatlarını ve operasyonel verileri görür.
*   **Hata Yönetimi:** API kesintilerine karşı fallback modelleri ve detaylı debug paneli entegrasyonu.

---

## 🎯 Temel Özellikler
*   ✅ Görselden %99 doğrulukla fatura kalemlerini okuma.
*   ✅ İskonto ve KDV hesaplamalarının otomatik yapılması.
*   ✅ Ağırlıklı Ortalama Maliyet (AVCO) takibi.
*   ✅ Personel maaş, mesai ve finansal profil yönetimi.
*   ✅ Müşterilere özel "Cari Ekstre" paylaşım linkleri (Public Token).
*   ✅ Barkod okutarak hızlı fiyat ve stok sorgulama.

---
*Bu sistem Sürekli Geliştirme (CI) aşamasındadır ve yeni nesil AI yetenekleriyle güncellenmeye devam etmektedir.*