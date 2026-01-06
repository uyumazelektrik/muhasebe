# Ön Muhasebe & Stok Takip Sistemi Entegrasyon Planı

Bu döküman, mevcut personel takip sistemine entegre edilen stok yönetimi, iş takibi ve finansal raporlama modüllerinin detaylarını içerir. Proje başarıyla finalize edilmiştir.

## 1. Kullanılan Teknolojiler
* **Backend:** PHP 8.x (PDO)
* **Frontend:** Tailwind CSS (Modern UI/UX)
* **Veritabanı:** MySQL
* **Güvenlik:** Role-Based Access Control (Admin/Personel)

---

## 2. Faz Planlaması (Tamamlandı)

### Faz 1: Stok Altyapısı (Temel) - [x]
- [x] Veritabanı tablolarının (stoklar, isler, is_sarfiyat) oluşturulması.
- [x] Stok Giriş/Listeleme ekranı (Tailwind CSS ile responsive tablo).
- [x] Birim tanımlama (Adet, Metre, KG) ve kritik stok seviyesi kontrolü.
- [x] Stok Ekleme, Düzenleme ve Silme fonksiyonları.

### Faz 2: Operasyon ve İş Yönetimi - [x]
- [x] Müşteri kayıtları ve iş (proje) oluşturma modülü.
- [x] **Sarfiyat Girişi:** Personelin bir işe malzeme eklemesi ve stoktan otomatik düşüş.
- [x] İş bazlı maliyet hesaplama algoritması (Otomatik toplam tutar güncelleme).

### Faz 3: Finans ve Yetkilendirme - [x]
- [x] **Yetki Kontrolü:** Personel için kritik mali verilerin (Alış fiyatı, kâr) gizlenmesi.
- [x] **Cari Takip:** Müşteri bazlı ciro raporları ve ödeme durumları.
- [x] **Stok Finansal Analizi:** Toplam stok maliyeti ve potansiyel satış değeri analizleri.
- [x] **Dashboard:** Kritik stok uyarıları, en çok sarf edilen ürünler ve mali özet kartları.

---

## 4. Faz: Oturum ve Rol Bazlı Erişim Kontrolü (RBAC) - [x]

Bu fazda sistemin "kim, neyi, ne kadar görebilir?" kuralları kodlanmıştır.

### 🛠 Teknik Değişiklikler
- **Oturum Yönetimi:** `session_start()` kontrolü ile `auth.php` üzerinden rol doğrulaması.
- **Data Isolation (Veri İzolasyonu):** SQL sorgularına dinamik `WHERE` koşulları eklenmesi.
- **Stok Esnekliği:** Personel için stok kontrolü "hard-stop" yerine "uyarı" seviyesine çekilmiştir.

### 📋 Tamamlananlar
- [x] **Login & Session:** `users` tablosuna göre `admin` veya `personel` yönlendirmesi.
- [x] **Personel Kısıtlı Ekranı:** Personelin sadece kendi eklediği iş kartlarını ve kendi maaş/mesai bilgilerini görmesi.
- [x] **Gizlilik Filtresi:** Personel ekranlarından `alis_fiyati` sütunu kaldırıldı.
- [x] **Yönetici Yetki Sınırı:** Stok ekleme/düzenleme yetkisi Admin'e tanımlandı.

---

## 5. Faz: Müşteri Paneli, Barkod ve Finansal Detaylar - [x]

Bu fazda dış dünyaya açılan güvenli kapılar ve personelin hızlı işlem yapabileceği araçlar kurulmuştur.

### 🛠 Teknik Değişiklikler
- **Public URL Sistemi:** Müşteriler için `access_token` (GUID) tabanlı giriş gerektirmeyen şık bir görünüm sayfası.
- **Fiyat Sorgulama API:** Sadece barkod veya isimle çalışan, maliyet verisi içermeyen hızlı sorgu ucu.
- **Gelişmiş Fatura Takibi:** Veritabanına `KDV` ve `Fatura_Durumu` sütunları işlendi.

### 📋 Tamamlananlar
- [x] **Müşteri Cari Sayfası:** Müşteriye özel bağımsız link (`public/view_account.php?token=xyz`).
- [x] **Hızlı Fiyat Gör:** Personel için barkod okutulduğunda **Sadece Satış Fiyatı** dönen modül.
- [x] **Personel Finans:** Personelin kendi `maas` ve `ödeme` dökümünü görebileceği profil sekmesi.
- [x] **KDV Entegrasyonu:** İş kartlarına %1, %10, %20 seçenekli KDV hesaplama modülü.

---

## 📂 Güncel Veritabanı Şeması

### `customers` (Dış Paylaşım Tablosu)
| Kolon | Tip | Açıklama |
| :--- | :--- | :--- |
| id | INT | PK |
| name | VARCHAR | Müşteri Adı |
| access_token| VARCHAR | Benzersiz erişim kodu |

### `isler` (Gelişmiş)
| Kolon | Tip | Açıklama |
| :--- | :--- | :--- |
| customer_id | INT | FK to customers |
| topam_tutar | DECIMAL | Vergisiz Matrah |
| tax_rate | DECIMAL | KDV Oranı (%1, %10, %20) |
| invoice_status| ENUM | Kesilmedi, Kesildi |

---

## 🔄 Final İş Akışı (Workflow)

1. **Giriş:** Kullanıcı rolüne göre Dashboard'a yönlendirilir.
2. **Operasyon:** Personel barkodla fiyat bakıp iş kartı açar, eksi stok olsa bile malzemeyi düşer.
3. **Admin Kontrolü:** Admin, iş kartını inceler, KDV oranını ve fatura durumunu günceller.
4. **Müşteri Erişimi:** Admin, müşteriye özel `view_account` linkini gönderir; müşteri kendi eksiksiz dökümünü izler.