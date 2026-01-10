<?php
require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/../../../src/helpers.php';
require_once __DIR__ . '/../../../src/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);

    try {
        $pdo->beginTransaction();

        // 1. İş bilgilerini al
        $stmt = $pdo->prepare("SELECT * FROM isler WHERE id = ?");
        $stmt->execute([$id]);
        $job = $stmt->fetch();

        if (!$job) {
            throw new Exception("İş kaydı bulunamadı.");
        }

        // 2. Müşteriyi bul (Entity)
        $stmt = $pdo->prepare("SELECT id FROM inv_entities WHERE name = ? LIMIT 1");
        $stmt->execute([$job['musteri_adi']]);
        $entity = $stmt->fetch();

        if (!$entity) {
            throw new Exception("Hata: Müşteri carisi bulunamadı. Lütfen önce '" . $job['musteri_adi'] . "' adında bir cari oluşturun.");
        }

        $entity_id = $entity['id'];
        $tutar = floatval($job['toplam_tutar'] ?: 0);
        $kdv_orani = floatval($job['tax_rate'] ?: 0);
        $tax_included = !empty($job['tax_included']); // New Column
        
        if ($tax_included) {
            $genel_toplam = $tutar; // Already included
        } else {
            $genel_toplam = $tutar * (1 + ($kdv_orani / 100));
        }

        $transaction_date = !empty($job['job_date']) ? $job['job_date'] : date('Y-m-d');

        // 3. Benzersiz bir evrak numarası oluştur
        $document_no = 'JOB-' . $id . '-' . date('ymdHis');

        // 4. Malzemeleri inv_movements tablosuna ekle (Fatura Kalemleri Olarak Görünmesi İçin)
        $stmt = $pdo->prepare("SELECT s.*, p.name as urun_adi, p.unit as birim 
                              FROM is_sarfiyat s 
                              JOIN inv_products p ON s.stok_id = p.id 
                              WHERE s.is_id = ?");
        $stmt->execute([$id]);
        $materials = $stmt->fetchAll();

        if ($materials) {
            foreach ($materials as $m) {
                // Not: inv_movements tablosu normalde stok hareketidir ama burada fatura detayı olarak kullanıyoruz.
                // Type 'sale' (satış) diyebiliriz çünkü müşteriye fatura ediyoruz.
                // Stock değişimi yapmayacağız (zaten sarfiyat sırasında düşmüştü), sadece kayıt.
                
                // Mevcut stoğu öğrenmek için (hareket kaydı için gerekiyorsa)
                // Ancak burada amaç sadece Fatura Detayında görünmesi. 
                // get_transaction_detail.php document_no üzerinden eşleştiriyor.
                
                $stmtMov = $pdo->prepare("INSERT INTO inv_movements (product_id, entity_id, type, quantity, unit_price, document_no, description, created_at, prev_stock, new_stock) 
                                         VALUES (?, ?, 'sale', ?, ?, ?, ?, ?, 0, 0)");
                $stmtMov->execute([
                    $m['stok_id'],
                    $entity_id,
                    $m['kullanilan_miktar'],
                    $m['birim_fiyat'],
                    $document_no,
                    'İş Takibi: ' . $job['is_tanimi'],
                    $transaction_date . ' 12:00:00' // Use job date here too? Or current? Let's use job date for consistency.
                ]);
            }
        }

        // 5. Cari harekete ekle (Ana kayıt)
        // Description artık sadece başlık olacak, detaylar kalemlerde.
        // Tax Amount Calculation
        $tax_amount = $genel_toplam - $tutar;
        
        $stmt = $pdo->prepare("INSERT INTO inv_entity_transactions (entity_id, type, amount, description, transaction_date, document_no, tax_rate, tax_amount, tax_included, discount_amount) VALUES (?, 'fatura', ?, ?, ?, ?, ?, ?, ?, 0)");
        $stmt->execute([
            $entity_id,
            $genel_toplam,
            $job['is_tanimi'] . " (İş Takibi #{$id})",
            $transaction_date,
            $document_no,
            $kdv_orani,
            $tax_amount,
            $tax_included ? 1 : 0
        ]);

        // 6. Cari bakiyeyi güncelle (Fatura ise borçlanır - Bakiyeyi artırır)
        $stmt = $pdo->prepare("UPDATE inv_entities SET balance = balance + ? WHERE id = ?");
        $stmt->execute([$genel_toplam, $entity_id]);

        // 5. İşi sil (Cariye işlendikten sonra listeden kalkması istendi)
        $stmt = $pdo->prepare("DELETE FROM isler WHERE id = ?");
        $stmt->execute([$id]);

        $pdo->commit();
        redirect_with_message(public_url('jobs'), 'success', 'İş başarıyla carileştirildi ve listeden kaldırıldı.');
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        redirect_with_message(public_url('job-detail?id=' . $id), 'error', 'Hata: ' . $e->getMessage());
    }
}
