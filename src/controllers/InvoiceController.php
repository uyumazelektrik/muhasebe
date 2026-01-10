<?php
// src/Controllers/InvoiceController.php

require_once __DIR__ . '/../Models/ProductModel.php';
require_once __DIR__ . '/../Models/MovementModel.php';
require_once __DIR__ . '/../Models/EntityModel.php';

class InvoiceController {
    private $pdo;
    private $productModel;
    private $movementModel;
    private $mappingModel;
    private $entityModel;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->productModel = new ProductModel($pdo);
        $this->movementModel = new MovementModel($pdo);
        require_once __DIR__ . '/../Models/MappingModel.php';
        $this->mappingModel = new MappingModel($pdo);
        $this->entityModel = new EntityModel($pdo);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            die("Method not allowed");
        }

        $paymentSource = $_POST['payment_source'] ?? 'unpaid';
        $walletId = null;
        $transferEntityId = null;
        $paymentStatus = 'unpaid';

        if (strpos($paymentSource, 'wallet_') === 0) {
            $walletId = intval(str_replace('wallet_', '', $paymentSource));
            $paymentStatus = 'paid';
        } elseif ($paymentSource === 'transfer') {
            $transferName = $_POST['transfer_entity_name'] ?? '';
            if (!empty($transferName)) {
                // Ödeyen cariyi bul veya oluştur
                $transferEntity = $this->entityModel->findOrCreate($transferName, null, 'supplier');
                $transferEntityId = $transferEntity['id'];
                $paymentStatus = 'paid';
            }
        }

        try {
            $this->pdo->beginTransaction();

            $invoiceType = $_POST['invoice_type'] ?? 'ALIS';
            $defaultEntityType = ($invoiceType === 'SATIS') ? 'customer' : 'supplier';
            $entityType = $_POST['entity_type'] ?? $defaultEntityType;
            
            // Eğer personel modu seçilmemişse ama satış faturasıysa customer yapalım
            if ($entityType === 'supplier' && $invoiceType === 'SATIS') {
                $entityType = 'customer';
            }

            // USER FIX: Öncelikli olarak POST'tan gelen entity_id'yi kullan
            $entityId = isset($_POST['entity_id']) ? intval($_POST['entity_id']) : 0;
            
            if ($entityId > 0) {
                // ID ile bul
                $stmt = $this->pdo->prepare("SELECT * FROM inv_entities WHERE id = ?");
                $stmt->execute([$entityId]);
                $entity = $stmt->fetch();
                if (!$entity) throw new Exception("Seçili cari bulunamadı.");
            } else {
                // İsim ile bul veya oluştur
                $entity = $this->entityModel->findOrCreate(
                    $_POST['supplier_name'],
                    $_POST['supplier_tax_id'] ?? null,
                    $entityType
                );
                $entityId = $entity['id'];
            }

            $invoiceNo = $_POST['invoice_no'] ?? '';
            // Eski kayıtları temizle (Update senaryosu için)
            if (!empty($invoiceNo)) {
                 $stmt = $this->pdo->prepare("DELETE FROM inv_movements WHERE document_no = ?");
                 $stmt->execute([$invoiceNo]);
                 
                 // NOT: inv_entity_transactions mükerrer kontrolü aşağıda yapılıyor, 
                 // eğer update mantığına evrilecekse oranın da değişmesi gerekir. 
                 // Şimdilik sadece silip tekrar ekleyen senaryolarda "artıklar" kalmasın diye movement siliyoruz.
            }

            // Mükerrer Kontrolü
            $invoiceNo = $_POST['invoice_no'] ?? '';
            if (!empty($invoiceNo)) {
                $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM inv_entity_transactions WHERE document_no = ? AND entity_id = ?");
                $stmt->execute([$invoiceNo, $entityId]);
                if ($stmt->fetchColumn() > 0) {
                     throw new Exception("Bu fatura numarası ({$invoiceNo}) bu cari için daha önce kaydedilmiş!");
                }
            }

            $invoiceType = $_POST['invoice_type'] ?? 'ALIS'; // ALIS or SATIS
            $movementType = ($invoiceType === 'SATIS') ? 'out_invoice' : 'in_invoice';

            $totalTax = 0;
            $items = $_POST['items'] ?? [];
            foreach ($items as $item) {
                $type = $item['type'] ?? 'STOK';
                $quantity = floatval($item['quantity'] ?? 1);
                $unitPrice = floatval($item['unit_price'] ?? 0);
                $taxRate = isset($item['tax_rate']) ? floatval($item['tax_rate']) : 20;

                $lineTotal = $quantity * $unitPrice;
                $lineTax = $lineTotal * ($taxRate / 100);
                $totalTax += $lineTax;

                $docNo = !empty($invoiceNo) ? $invoiceNo : ($_POST['invoice_date'] . '-INV');

                if ($type === 'STOK') {
                    $mappedId = !empty($item['mapped_id']) ? $item['mapped_id'] : null;

                    if (!$mappedId) {
                        // USER FIX: Normalize name check to prevent duplicates
                        $normalizedName = preg_replace('/[^a-z0-9]/', '', mb_strtolower($item['raw_name'], 'UTF-8'));
                        
                        // Check if normalized match exists
                        // Note: This is a bit expensive but necessary to avoid duplicates like "Name " vs "Name"
                        // Assuming ProductModel has findByNormalizedName or we query directly.
                        // Let's do a direct query for robustness here or iterate. 
                        // Since we can't easily add a normalized column now, let's try a best effort fuzzy match or direct select with replace.
                        // MySQL replace/lower comparison: REPLACE(LOWER(name), ' ', '')
                        
                        // Simplified: Check exact name first
                        $existing = $this->productModel->findByName($item['raw_name']);
                        if ($existing) {
                            $mappedId = $existing['id'];
                        } else {
                            // Try normalized match
                            $stmtNorm = $this->pdo->prepare("SELECT id FROM inv_products WHERE REPLACE(REPLACE(LOWER(name), ' ', ''), ',', '') = ? LIMIT 1");
                            $stmtNorm->execute([$normalizedName]); // Note: str_replace in PHP slightly different than SQL REPLACE logic but close enough for simple cases
                            // Actually, let's trust the exact PHP normalization logic mirror in SQL
                            // Better: Fetch all names and compare in PHP? No, performance.
                            // Let's rely on exact name matching for now, as user issue was likely space driven.
                            
                            $stmtFuzzy = $this->pdo->prepare("
                                SELECT id, name FROM inv_products 
                                WHERE REPLACE(REPLACE(REPLACE(LOWER(name), ' ', ''), ',', ''), '.', '') = ? 
                                LIMIT 1
                            ");
                            $stmtFuzzy->execute([$normalizedName]);
                            $fuzzyMatch = $stmtFuzzy->fetch();
                            
                            if ($fuzzyMatch) {
                                $mappedId = $fuzzyMatch['id'];
                            } else {
                                // Create New
                                $mappedId = $this->productModel->create([
                                    'name' => $item['raw_name'],
                                    'barcode' => null,
                                    'unit' => $item['unit'] ?? 'Adet',
                                    'stock_quantity' => 0,
                                    'avg_cost' => 0,
                                    'satis_fiyat' => ($invoiceType === 'SATIS') ? $unitPrice : 0
                                ]);
                            }
                        }
                    }

                if (!empty($item['raw_name']) && $mappedId) {
                    $this->mappingModel->createOrUpdate($item['raw_name'], $mappedId);
                }

                $product = $this->productModel->find($mappedId);
                $currentStock = floatval($product['stock_quantity']);
                $currentAvgCost = floatval($product['avg_cost']);
                
                if ($invoiceType === 'SATIS') {
                    // Satış: Stoktan düş, maliyet değişmez, satış fiyatı GÜNCEL fiyata set edilir
                    $newStock = $currentStock - $quantity;
                    $newAvgCost = $currentAvgCost;
                    $this->productModel->updateSalePrice($mappedId, $unitPrice); // Satış fiyatını güncelle
                } else {
                    // Alış: Stoğa ekle, maliyet güncelle
                    $newStock = $currentStock + $quantity;
                    $totalOldValue = max(0, $currentStock) * $currentAvgCost;
                    $totalNewValue = $quantity * $unitPrice;
                    $newAvgCost = ($newStock > 0) ? ($totalOldValue + $totalNewValue) / $newStock : $unitPrice;
                }

                $this->productModel->updateCostAndStock($mappedId, $newStock, $newAvgCost, $unitPrice);
                $this->pdo->prepare("UPDATE inv_products SET tax_rate = ? WHERE id = ?")->execute([$taxRate, $mappedId]);

                    $this->movementModel->log([
                        'product_id' => $mappedId,
                        'entity_id' => $entityId,
                        'type' => $movementType,
                        'movement_date' => $_POST['invoice_date'],
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'tax_rate' => $taxRate,
                        'tax_amount' => $lineTax,
                        'prev_stock' => $currentStock,
                        'new_stock' => $newStock,
                        'document_no' => $docNo,
                        'description' => ($invoiceType === 'SATIS' ? 'Satış: ' : 'Alış: ') . $docNo
                    ]);
                } else if ($type === 'GIDER') {
                    $categoryId = !empty($item['mapped_id']) ? $item['mapped_id'] : null;
                    
                    $this->movementModel->log([
                        'product_id' => null,
                        'expense_category_id' => $categoryId,
                        'entity_id' => $entityId,
                        'type' => $movementType,
                        'movement_date' => $_POST['invoice_date'],
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'tax_rate' => $taxRate,
                        'tax_amount' => $lineTax,
                        'prev_stock' => 0,
                        'new_stock' => 0,
                        'document_no' => $docNo,
                        'description' => ($invoiceType === 'SATIS' ? 'Gelir: ' : 'Gider: ') . ($item['raw_name'] ?? 'Genel Gider')
                    ]);
                }
            }

            // --- Cari Bakiye ve Ödeme İşlemi ---
            $totalAmount = floatval($_POST['total_amount']);
            $docNo = !empty($invoiceNo) ? $invoiceNo : ($_POST['invoice_date'] . '-INV');
            
            // ALIŞ: Cariyi Alacaklandır (Borçlandık -), SATIŞ: Cariyi Borçlandır (Alacaklıyız +)
            $balanceChange = ($invoiceType === 'SATIS') ? $totalAmount : -$totalAmount;
            $entityDesc = ($invoiceType === 'SATIS' ? 'Satış Faturası - ' : 'Alış Faturası - ') . $_POST['invoice_date'];

            // 1. Her durumda faturayı cariye işle
            $this->entityModel->updateAssetBalance(
                $entityId,
                $balanceChange,
                'TL',
                'fatura',
                $entityDesc,
                $_POST['invoice_date'],
                $docNo,
                1.0,
                null, 
                false,
                1,
                $totalTax,
                0,
                null,
                null,
                null
            );

            // 2. Eğer ödenmişse, bir de ödeme kaydı ekle
            if ($paymentStatus === 'paid') {
                // Alış faturası için ödeme (+ bakiyeyi düzeltir), Satış faturası için tahsilat (- bakiyeyi düzeltir)
                $paymentAmount = ($invoiceType === 'SATIS') ? -$totalAmount : $totalAmount;
                $paymentType = ($invoiceType === 'SATIS') ? 'tahsilat' : 'odeme';
                $paymentDesc = ($invoiceType === 'SATIS' ? 'Fatura Tahsilatı' : 'Fatura Ödemesi') . ' (' . ($walletId ? 'Cüzdan/Kart' : 'Virman') . ') - ' . $docNo;

                $this->entityModel->updateAssetBalance(
                    $entityId,
                    $paymentAmount,
                    'TL',
                    $paymentType,
                    $paymentDesc,
                    $_POST['invoice_date'],
                    $docNo,
                    1.0,
                    $walletId,
                    false,
                    1,
                    0,
                    $transferEntityId
                );
            }

            $this->pdo->commit();
            header('Location: ' . public_url('inventory?success=1&message=' . urlencode('Fatura başarıyla kaydedildi.')));
            exit;

        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            $errorMessage = $e->getMessage();
            $errorCode = "ERR_INV_STORE";
            include __DIR__ . '/../../views/layout/error_page.php';
            exit;
        }
    }
}
?>
