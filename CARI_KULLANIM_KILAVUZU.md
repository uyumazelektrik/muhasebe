# 🎯 Cari Takip Sistemi - Kullanım Kılavuzu

## ✅ Tamamlanan Özellikler

### 📦 FAZ 1: Veritabanı ve Finansal Altyapı
- ✓ `inv_entities` tablosu ile cari hesap yönetimi
- ✓ `inv_entity_transactions` tablosu ile cari hareketler
- ✓ Bakiye takibi (Alacak/Borç)

### 🤖 FAZ 2: Gemini API Entegrasyonu
- ✓ Faturalardan otomatik VKN/TCKN çıkarma
- ✓ Ödeme durumu tespiti (Ödenmiş/Ödenmemiş)
- ✓ Tedarikçi otomatik eşleştirme ve oluşturma

### 💰 FAZ 3: Bakiye Önizleme
- ✓ Fatura onay ekranında cari bilgi paneli
- ✓ Mevcut bakiye, fatura tutarı ve tahmini yeni bakiye gösterimi
- ✓ Ödeme durumu seçeneği (Cariye İşle / Nakit Ödendi)

### 🔄 FAZ 4: Atomik Kayıt ve Kontroller
- ✓ Stok + Cari bakiye eş zamanlı güncelleme
- ✓ Tüm işlemlerin transaction ile loglanması
- ✓ Borç limiti kontrolü ve uyarıları
- ✓ Limit aşımında görsel uyarılar

### 📊 FAZ 5: Raporlama ve Analiz
- ✓ Cari hesaplar listesi
- ✓ Detaylı cari ekstre sayfası
- ✓ Tarih bazlı filtreleme
- ✓ Borç/Alacak yaşlandırma grafiği
- ✓ Yazdırma desteği

---

## 🚀 Kullanım Senaryoları

### 1️⃣ Fatura Yükleme ve İşleme

1. **Fatura Yükle** menüsünden fatura/fiş görselini yükleyin
2. Gemini AI otomatik olarak:
   - Tedarikçi bilgilerini çıkarır
   - VKN/TCKN ile cariyi bulur/oluşturur
   - Ürünleri tanır ve eşleştirir
   - Ödeme durumunu tespit eder
3. **Onay Ekranında:**
   - Mevcut bakiyeyi görün
   - Fatura sonrası bakiyeyi önceden görün
   - Borç limiti uyarılarını kontrol edin
   - Ödeme durumunu seçin (Cariye İşle / Nakit Ödendi)
4. **Kaydet** butonuna basın
5. Sistem otomatik olarak:
   - Stokları günceller (Ağırlıklı ortalama maliyet)
   - Cari bakiyeyi günceller
   - Tüm hareketleri loglar

### 2️⃣ Cari Hesap Takibi

**Cari Hesaplar** menüsünden:
- Tüm tedarikçi/müşterileri görün
- Toplam borç/alacak özetini inceleyin
- Yaşlandırma analizini kontrol edin
- Kritik borçları tespit edin (90+ gün)

### 3️⃣ Cari Ekstre Görüntüleme

1. Cari listesinden bir cari seçin
2. **Ekstre** ikonuna tıklayın
3. Ekstre sayfasında:
   - Tüm işlemleri görün (Fatura, Tahsilat, Ödeme)
   - Tarih filtresi uygulayın
   - Güncel bakiyeyi kontrol edin
   - Yazdır butonuyla PDF çıktı alın

---

## ⚙️ Sistem Ayarları

### Borç Limiti Ayarlama

Varsayılan borç limiti: **-50,000 ₺**

Değiştirmek için:
1. `src/controllers/InvoiceController.php` dosyasını açın
2. Satır 119'daki `$debtLimit = -50000;` değerini düzenleyin
3. `views/invoice/validation.php` dosyasında da aynı değeri güncelleyin (Satır 21)

> **Not:** İleride bu ayar veritabanı ayarlarına taşınacaktır.

---

## 🎨 Görsel Uyarı Sistemi

### Bakiye Önizleme Renkleri:
- 🔵 **Mavi**: Normal durum
- 🟠 **Turuncu**: Borç limiti yaklaşıyor (%80)
- 🔴 **Kırmızı**: Borç limiti aşılıyor!

### Yaşlandırma Analizi:
- **0-30 gün**: Yeni borçlar/alacaklar
- **31-60 gün**: Takip gerektiren
- **61-90 gün**: Dikkat edilmesi gereken
- **90+ gün**: 🚨 Kritik - Acil ödeme takibi gerekli

---

## 📁 Veritabanı Yapısı

### `inv_entities` Tablosu
Cari hesapları saklar:
- `id`: Benzersiz kimlik
- `name`: Cari adı
- `type`: Tedarikçi/Müşteri/Her İkisi
- `tax_id`: VKN/TCKN
- `balance`: Güncel bakiye (+ Alacak, - Borç)
- `address`, `phone`, `email`: İletişim bilgileri

### `inv_entity_transactions` Tablosu
Cari hareketlerini saklar:
- `id`: Benzersiz kimlik
- `entity_id`: Hangi cariye ait
- `type`: İşlem tipi (fatura/tahsilat/odeme/iade)
- `amount`: Tutar (+ Alacak, - Borç)
- `description`: Açıklama
- `transaction_date`: İşlem tarihi

---

## 🔐 Güvenlik Notları

- Tüm işlemler **transaction** ile yapılır (atomik)
- Hata durumunda **rollback** otomatik çalışır
- Borç limiti aşımında uyarı verilir ama işlem engellenmez
- Tüm işlemler loglanır ve takip edilebilir

---

## 🆘 Sorun Giderme

### Fatura İşlenmiyor
1. Gemini API anahtarının geçerli olduğundan emin olun
2. Fatura görselinin net ve okunabilir olduğunu kontrol edin
3. Hata mesajlarını kontrol edin

### Bakiye Yanlış Hesaplanıyor
1. Cari ekstresini kontrol edin
2. Tüm işlemlerin doğru kaydedildiğini doğrulayın
3. Gerekirse veritabanını kontrol edin

### Yaşlandırma Grafiği Boş
1. Cari hesaplarda işlem olduğundan emin olun
2. `inv_entity_transactions` tablosunda kayıt olup olmadığını kontrol edin

---

## 📞 Destek

Sorularınız için sistem yöneticinizle iletişime geçin.

**Versiyon:** 1.0.0  
**Son Güncelleme:** 2026-01-07
