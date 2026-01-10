# ⚡ Uyumaz Elektrik - Muhasebe & Yönetim Sistemi

Bu proje, **Uyumaz Elektrik** için özel olarak geliştirilmiş; **Personel Takibi**, **Cari Hesap Yönetimi**, **Akıllı Stok Takibi** ve **İş Yönetimi (CRM)** süreçlerini tek bir çatı altında toplayan, **Yapay Zeka destekli** web tabanlı bir ERP sistemidir.

Proje, geleneksel muhasebe yazılımlarının aksine, **fatura görsellerinden otomatik veri işleme (OCR)** ve **akıllı gider kategorizasyonu** gibi modern özelliklerle donatılmıştır.

---

## 🚀 Öne Çıkan Özellikler

### 1. 🧠 Yapay Zeka (AI) Destekli Fatura İşleme
*   **Görsel Analiz:** Fatura fotoğrafları veya PDF'leri sisteme yüklendiğinde, Google **Gemini 1.5 Flash/Pro** modelleri kullanılarak analiz edilir.
*   **Otomatik Veri Çıkarma:** Fatura kalemleri, miktarlar, birim fiyatlar, KDV oranları ve toplam tutarlar %99 doğrulukla dijitalleştirilir.
*   **Akıllı Stok Eşleştirme:** Çıkarılan ürün isimleri, veritabanındaki stok kartlarıyla (Fuzzy Search & Mapping hafızası) otomatik eşleştirilir.
*   **Cari Bakiye Entegrasyonu:** Fatura onaylandığında tedarikçi bakiyesi ve stok miktarları anlık güncellenir.

### 2. 📦 Stok & Depo Yönetimi
*   **Kritik Stok Takibi:** Minimum seviyenin altına düşen ürünler için dashboard uyarıları.
*   **AVCO Maliyetleme:** "Ağırlıklı Ortalama Maliyet" yöntemi ile stok maliyetlerinin dinamik hesaplanması.
*   **Hızlı Fiyat Sorgula:** Barkod okutarak veya ürün adı girerek anlık fiyat ve stok durumu sorgulama ekranı.
*   **Tarihçeli Hareketler:** Ürün bazında tüm giriş-çıkışların (Fatura, Sarfiyat, İade) detaylı loglanması.

### 3. 💰 Cari & Finans Yönetimi (Ön Muhasebe)
*   **Cari Hesaplar:** Müşteri ve Tedarikçi bakiyelerinin (Borç/Alacak) takibi.
*   **Kasa & Banka:** İşletme kasalarının ve banka hesaplarının yönetimi, hesaplar arası transfer (Virman).
*   **Gider Yönetimi:** İşletme giderlerinin (Yemek, Yakıt, Kira vb.) kategorize edilerek takibi.
*   **Detaylı Ekstre:** Müşterilerle paylaşılabilecek, işlem bazlı detaylı cari ekstreler.

### 4. 👷 Personel & İK Yönetimi
*   **Puantaj Takibi:** QR kod veya manuel giriş ile personelin işe giriş-çıkış saatlerinin kaydı.
*   **Otomatik Maaş Hesaplama:** Mesai saatleri, gecikme cezaları ve hafta tatili hakedişlerine göre net maaşın otomatik hesaplanması.
*   **Avans & Ödeme Takibi:** Personele yapılan ödemelerin cari hesap mantığıyla işlenmesi.

### 5. 🛠 Saha & İş Takibi
*   **Proje Yönetimi:** Müşterilere yapılan işlerin (Montaj, Arıza vb.) proje bazlı takibi.
*   **Malzeme Sarfiyatı:** Projelerde kullanılan malzemelerin stoktan düşülmesi ve proje maliyetine yansıtılması.

---

## 📂 Teknik Altyapı ve Klasör Yapısı

Proje, **Native PHP** kullanılarak, hafif siklet bir **MVC (Model-View-Controller)** mimarisiyle geliştirilmiştir. Frontend tarafında modern bir görünüm için **Tailwind CSS** kullanılmıştır.

### Temel Dizinler
```text
/proje
├── .env                # Veritabanı ve API anahtarları yapılandırması
├── index.php           # Merkezi Router (Tüm istekler buradan dağıtılır)
├── config/             # Konfigürasyon dosyaları (Veritabanı bağlantısı vb.)
├── src/                # Backend Mantığı
│   ├── controllers/    # İstekleri karşılayan ve işleyen sınıflar (API & Sayfalar)
│   ├── models/         # Veritabanı işlemleri (ORM benzeri yapılar)
│   └── Services/       # Harici servis entegrasyonları (GeminiService.php vb.)
├── views/              # Kullanıcı Arayüzü (HTML/PHP)
│   ├── layout/         # Ortak şablonlar (Header, Sidebar, Footer)
│   ├── entities/       # Cari modülü görünümleri
│   ├── invoice/        # Fatura yükleme ekranları
│   └── ...
├── modules/            # Büyük özellik paketleri
│   ├── finance/        # Finansal raporlar ve cüzdan araçları
│   ├── jobs/           # İş takibi modülü dosyaları
│   └── inventory/      # Stok listeleme ve detay sayfaları
├── assets/             # Statik dosyalar (Boş - CDN kullanılıyor)
└── database/           # Veritabanı şemaları ve SQL dosyaları
```

### Kullanılan Teknolojiler
*   **Backend:** PHP 8.x
*   **Veritabanı:** MySQL / MariaDB
*   **Frontend:** HTML5, JavaScript (Vanilla), Tailwind CSS (CDN)
*   **Ikon Seti:** Google Material Symbols
*   **AI Engine:** Google Gemini API (1.5 Flash & Pro)

---

## ⚙️ Kurulum ve Yapılandırma

Bu proje **XAMPP**, **WAMP** veya herhangi bir LAMP yığını üzerinde çalıştırılabilir.

### 1. Dosya Kurulumu
Proje dosyalarını web sunucunuzun kök dizinine (örn: `C:\xampp\htdocs\proje`) kopyalayın.

### 2. Veritabanı Ayarları
1.  MySQL sunucunuzda yeni bir veritabanı oluşturun (örn: `muhasebe_db`).
2.  `database/` klasöründeki SQL dosyalarını bu veritabanına içe aktarın (Eğer mevcutsa).
3.  Ana dizindeki `.env` dosyasını düzenleyin (Yoksa oluşturun):

```ini
DB_HOST=localhost
DB_NAME=muhasebe_db
DB_USER=root
DB_PASS=
GEMINI_API_KEY=AIzaSy... (Google AI Studio API Key)
```

### 3. Çalıştırma
Tarayıcınızdan `http://localhost/proje` adresine giderek sistemi başlatın.
*   **Varsayılan Admin:** (Sistem yöneticisinden temin ediniz veya veritabanından `users` tablosunu kontrol ediniz).

---

## 📖 Kullanım Senaryoları

### Fatura Yükleme (AI OCR)
1.  Menüden **"Fatura İşle"** sayfasına gidin.
2.  Faturanın fotoğrafını yükleyin.
3.  Sistem 5-10 saniye içinde faturayı okuyacak ve onay ekranını açacaktır.
4.  Eşleşmeyen ürünleri listeden seçin veya yeni stok kartı olarak tanımlayın.
5.  **"Onayla"** butonuna bastığınızda stoklar artar ve cari borçlanır.

### Personel Maaş Hesaplama
1.  Personeller QR kod ile veya **"Giriş-Çıkış Kayıtları"** sayfasından günlük girişlerini yapar.
2.  Ay sonunda **"Maaş & Hakediş"** sayfasına gidildiğinde sistem otomatik olarak;
    *   (Toplam Çalışma Günü x Günlük Ücret) + (Mesai Saati x Mesai Ücreti) - (Avanslar)
    hesabını yaparak ödenecek tutarı çıkarır.

---

## 🔄 Geliştirici Notları

*   **Routing:** Sistem `index.php` üzerinden custom bir routing mekanizması kullanır. Yeni bir sayfa eklemek için `index.php` içerisindeki `switch-case` yapısına yeni bir `case` eklemeniz yeterlidir.
*   **Tailwind:** CSS framework'ü CDN üzerinden `script` etiketi ile dahil edilmiştir (`views/layout/header.php`). `tailwind.config` yine bu dosyanın içindedir.
*   **API:** `src/controllers/api/` altında AJAX isteklerini karşılayan JSON yanıtlı endpoint'ler bulunur.

---

*Son Güncelleme: 10 Ocak 2026*