<?php
// src/controllers/api/edit_entity_transaction.php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/../../../src/helpers.php';
require_once __DIR__ . '/../../../src/Models/ProductModel.php';

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'ID gerekli']);
    exit;
}

$id = $data['id'];
$desc = $data['description'] ?? '';
$docNo = $data['document_no'] ?? '';
$date = $data['transaction_date'] ?? date('Y-m-d');
$items = $data['items'] ?? null;

$productModel = new ProductModel($pdo);

try {
    $pdo->beginTransaction();

    // 1. Mevcut işlemi çek
    $stmt = $pdo->prepare("SELECT * FROM inv_entity_transactions WHERE id = ?");
    $stmt->execute([$id]);
    $oldTrans = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$oldTrans) {
        throw new Exception("İşlem bulunamadı");
    }

    // DEBUG LOGSTART
    $logMsg = "--------------------------------\n";
    $logMsg .= "Edit Trans ID: " . $id . " | Date: " . date('Y-m-d H:i:s') . "\n";
    $logMsg .= "Old Trans: " . print_r($oldTrans, true) . "\n";
    file_put_contents(__DIR__ . '/../../../debug_stock.txt', $logMsg, FILE_APPEND);


    $newTotalAmount = $oldTrans['amount']; // Varsayılan eski tutar

    // 2. Eğer kalemler (items) gönderildiyse ve işlem tipi FATURA ise stokları güncelle
    if ($items !== null && $oldTrans['type'] === 'fatura') {
        
        // A. Eski Hareketleri Bul (inv_movements)
        // Document No ile eşleşen hareketleri bulmaya çalış, yoksa date ve type ile.
        // En güvenli yol document_no'dur ama o da değişebiliyor. 
        // Ancak bu API'ye gelmeden önceki document_no ile inv_movements'da kayıtlı olan aynıdır umarız.
        // Fakat inv_movements ile inv_entity_transactions arasında doğrudan ID bağlantısı yok.
        // Bağlantı document_no üzerinden.
        
        $oldDocNo = $oldTrans['document_no'];
        
        if (empty($oldDocNo)) {
             // Eski kayıtta document_no yoksa güncelleme yapamayız (riskli)
             // Ancak document_no güncellemesi yeni yapıldıysa, belki document_no boştur.
             // Bu durumda transaction_date ve entity_id ile deneyebiliriz ama çok riskli.
             // Şimdilik sadece document_no varsa işlem yapalım.
        } else {
            // A.1. Eski stokları geri al (Revert)
            // NOT: Sadece document_no ile siliyoruz çünkü entity_id bazen NULL kalmış olabilir (eski hatalardan).
            // Document No zaten unique olmalı (en azından o fatura için).
            $stmtMoves = $pdo->prepare("SELECT * FROM inv_movements WHERE document_no = ?");
            $stmtMoves->execute([$oldDocNo]);
            $oldMoves = $stmtMoves->fetchAll(PDO::FETCH_ASSOC);

            // DEBUG LOG
            file_put_contents(__DIR__ . '/../../../debug_stock.txt', "Old Moves Found (" . count($oldMoves) . "): " . print_r($oldMoves, true) . "\n", FILE_APPEND);


            foreach ($oldMoves as $move) {
                // Hareketi tersine çevir
                $mQty = floatval($move['quantity']);
                $moveType = $move['type'];

                // Yönü algıla
                $isOriginalSale = false;

                if (in_array($moveType, ['sale', 'out_invoice', 'production_out', 'return_to_supplier'])) {
                    $isOriginalSale = true;
                } elseif (in_array($moveType, ['purchase', 'in_invoice', 'stock_in'])) {
                    $isOriginalSale = false;
                } else {
                    // Type boş veya bilinmiyor ise, İşlem Tutarının yönüne bak.
                    // Tutar pozitif ise SATIŞ (Stoktan düşmüştür), Negatif ise ALIŞ (Stoka girmiştir).
                    if ($oldTrans['amount'] >= 0) {
                        $isOriginalSale = true;
                    } else {
                        $isOriginalSale = false;
                    }
                }
                
                if ($isOriginalSale) {
                    // Stoktan çıkmıştı, geri ekle (+)
                    $pdo->prepare("UPDATE inv_products SET stock_quantity = stock_quantity + ? WHERE id = ?")
                        ->execute([$mQty, $move['product_id']]);
                } else {
                    // Stoka girmişti (alış), geri al (düş, -)
                    $pdo->prepare("UPDATE inv_products SET stock_quantity = stock_quantity - ? WHERE id = ?")
                        ->execute([$mQty, $move['product_id']]);
                }
            }

            // A.2. Eski hareketleri sil
            $pdo->prepare("DELETE FROM inv_movements WHERE document_no = ?")
                ->execute([$oldDocNo]);
        }

        // B. Yeni Kalemleri İşle
        $calculatedTotal = 0;
        
        // İşlem Yönünü Belirle
        // Eğer amount pozitif ise SATIŞ (out_invoice), negatif ise ALIŞ (in_invoice) varsayıyoruz.
        // inv_entity_transactions tablosunda genellikle Satış=+ (Borç), Alış=- (Alacak) veya tam tersi kurgu olsa da
        // edit logic'in aşağısında (Line 138) negatif amount kontrolü var.
        
        $isSale = ($oldTrans['amount'] >= 0); 
        $newKdvIncluded = isset($data['tax_included']) ? (bool)$data['tax_included'] : (bool)($oldTrans['tax_included'] ?? 0);

        foreach ($items as $item) {
            $productId = $item['product_id'];
            $qty = floatval($item['quantity']);
            $price = floatval($item['unit_price']);
            $total = $qty * $price;
            $calculatedTotal += $total;

            // Stok Güncelleme
            if ($isSale) {
                // Satış: Stok Düş
                 $pdo->prepare("UPDATE inv_products SET stock_quantity = stock_quantity - ? WHERE id = ?")
                ->execute([$qty, $productId]);
                $moveType = 'out_invoice';
            } else {
                // Alış: Stok Ekle
                 $pdo->prepare("UPDATE inv_products SET stock_quantity = stock_quantity + ? WHERE id = ?")
                ->execute([$qty, $productId]);
                $moveType = 'in_invoice';
            }
            
            // Yeni hareket kaydı
            $moveStmt = $pdo->prepare("
                INSERT INTO inv_movements (product_id, entity_id, type, quantity, unit_price, prev_stock, new_stock, document_no, description, created_at)
                VALUES (?, ?, ?, ?, ?, 0, 0, ?, ?, ?)
            ");
            
            $moveStmt->execute([
                $productId,
                $oldTrans['entity_id'],
                $moveType,
                $qty,
                $price,
                $docNo, // Yeni Document No
                'Fatura Düzenleme: ' . $desc,
                $date . ' 12:00:00'
            ]);
        }

        // C. Yeni Toplam Tutar Hesaplama
        // Items'dan gelen toplam: calculatedTotal (Bu ürünlerin toplam fiyatıdır)
        
        $taxIncluded = isset($data['tax_included']) ? (bool)$data['tax_included'] : (bool)($oldTrans['tax_included'] ?? 0);
        $taxRate = isset($data['tax_rate']) ? floatval($data['tax_rate']) : floatval($oldTrans['tax_rate'] ?? 0);
        $discountAmount = isset($data['discount_amount']) ? floatval($data['discount_amount']) : floatval($oldTrans['discount_amount'] ?? 0);

        // Matrah (Veya Brüt Toplam) = Kalemlerin Toplamı
        $subTotal = $calculatedTotal; 
        
        // İndirim düşülür (Genellikle matrahtan düşülür)
        $taxBase = $subTotal - $discountAmount;

        if ($taxIncluded) {
            // KDV Dahil ise, hesaplanan toplam zaten Son Toplamdır.
            // Vergi içinden ayrıştırılır.
            $newTotalAmount = $taxBase;
            $taxAmount = $newTotalAmount - ($newTotalAmount / (1 + ($taxRate / 100)));
        } else {
            // KDV Hariç ise, üzerine eklenir.
            $taxAmount = $taxBase * ($taxRate / 100);
            $newTotalAmount = $taxBase + $taxAmount;
        }

        // Alış faturası tutarı negatiftir, Satış faturası pozitiftir.
        // Yönü koru
        if ($oldTrans['amount'] < 0) {
            $newTotalAmount = -1 * abs($newTotalAmount);
        } else {
            $newTotalAmount = abs($newTotalAmount);
        }
    }
    
    // YENİ EK: Eğer sadece başlık verileri (KDV, İndirim) güncelleniyorsa ve kalemler gelmediyse?
    // Bu senaryoda eski amount üzerinden geri hesaplama yapmak zor olabilir.
    // Ancak edit modalında genellikle kalemler de gönderilir veya sadece başlık editleniyorsa amount manuel gönderilir.
    // Şimdilik sadece items varsa hesaplama yaptık. Items yoksa ve amount geldiyse:
    
    if ($items === null && isset($data['amount'])) {
         // Manuel tutar güncellemesi (Hızlı İşlem vs)
         $newTotalAmount = floatval($data['amount']);
         
         // Tip kontrolü yerine mevcut yönü koru
         if ($oldTrans['amount'] < 0) {
             $newTotalAmount = -1 * abs($newTotalAmount);
         } else {
             $newTotalAmount = abs($newTotalAmount); 
         }
         
         // Eğer yön değiştirmek isteniyorsa (örn: yanlışlıkla borç yazıldı, alacak olmalıydı)
         // Bu API şu an buna izin vermiyor, sadece büyüklüğü değiştiriyor.
         // Kullanıcı silip tekrar eklemeli veya UI'da +/- seçimi olmalı. 
         // Şimdilik sadece tutar düzeltmesi varsayıyoruz.

         // Metadata güncelle
         $taxIncluded = isset($data['tax_included']) ? (bool)$data['tax_included'] : (bool)($oldTrans['tax_included'] ?? 0);
         $taxRate = isset($data['tax_rate']) ? floatval($data['tax_rate']) : floatval($oldTrans['tax_rate'] ?? 0);
         $discountAmount = isset($data['discount_amount']) ? floatval($data['discount_amount']) : floatval($oldTrans['discount_amount'] ?? 0);
         $taxAmount = isset($data['tax_amount']) ? floatval($data['tax_amount']) : floatval($oldTrans['tax_amount'] ?? 0);
    }

    // 3. Cari İşlemi Güncelle
    // Hızlı işlem ise ve yeni tutar geldiyse
    if ($oldTrans['type'] !== 'fatura' && isset($data['amount'])) {
        // ... (Mevcut kod)
        // Burayı olduğu gibi bırakıyoruz, sadece tax alanlarını ekliyoruz update sorgusuna
        $assetAmount = floatval($data['amount']);
        $newRate = floatval($data['exchange_rate'] ?? 1.0);
        
        // Yönü belirle (Eski kod bloğu)
        if ($oldTrans['type'] === 'tahsilat') {
            $assetAmount = -abs($assetAmount);
        } elseif ($oldTrans['type'] === 'odeme') {
            $assetAmount = abs($assetAmount);
        } else {
            $assetAmount = $data['amount']; 
        }
        
        $newTotalAmount = $assetAmount * $newRate;
        
        // Varlık bakiyesini güncelle
        if ($oldTrans['asset_type']) {
            $pdo->prepare("UPDATE inv_entity_balances SET amount = amount - ? WHERE entity_id = ? AND asset_type = ?")
                ->execute([$oldTrans['asset_amount'], $oldTrans['entity_id'], $oldTrans['asset_type']]);
            
            $pdo->prepare("INSERT INTO inv_entity_balances (entity_id, asset_type, amount) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE amount = amount + ?")
                ->execute([$oldTrans['entity_id'], $oldTrans['asset_type'], $assetAmount, $assetAmount]);
        }
        
        $updateStmt = $pdo->prepare("UPDATE inv_entity_transactions SET description = ?, document_no = ?, transaction_date = ?, amount = ?, asset_amount = ?, exchange_rate = ?, tax_rate = ?, tax_included = ?, discount_amount = ?, tax_amount = ? WHERE id = ?");
        $updateStmt->execute([$desc, $docNo, $date, $newTotalAmount, $assetAmount, $newRate, $taxRate, $taxIncluded ? 1 : 0, $discountAmount, $taxAmount ?? 0, $id]);

    } else {
        // Fatura güncellemesi
        $stmt = $pdo->prepare("UPDATE inv_entity_transactions SET description = ?, document_no = ?, transaction_date = ?, amount = ?, tax_rate = ?, tax_included = ?, discount_amount = ?, tax_amount = ? WHERE id = ?");
        $stmt->execute([$desc, $docNo, $date, $newTotalAmount, $taxRate, $taxIncluded ? 1 : 0, $discountAmount, $taxAmount ?? 0, $id]);
    }

    // 4. Ana (TL) Bakiye Düzeltmesi
    $diff = $newTotalAmount - $oldTrans['amount'];
    if (abs($diff) > 0.0001) {
        $pdo->prepare("UPDATE inv_entities SET balance = balance + ? WHERE id = ?")
            ->execute([$diff, $oldTrans['entity_id']]);
    }

    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'İşlem başarıyla güncellendi']);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
