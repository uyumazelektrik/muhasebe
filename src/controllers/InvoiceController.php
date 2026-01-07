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

        $headers = [
            'supplier_name' => $_POST['supplier_name'],
            'supplier_tax_id' => $_POST['supplier_tax_id'] ?? null,
            'entity_type' => $_POST['entity_type'] ?? 'supplier',
            'invoice_date' => $_POST['invoice_date'],
            'invoice_no' => $_POST['invoice_no'] ?? '',
            'total_amount' => $_POST['total_amount'],
            'payment_status' => $_POST['payment_status'] ?? 'unpaid' // unpaid, paid, partial
        ];
        
        $items = $_POST['items'] ?? [];

        try {
            $this->pdo->beginTransaction();

            // --- FAZ 2.2 & FAZ 4.1: Cari Eşleme ve Bakiye Güncelleme ---
            $entity = $this->entityModel->findOrCreate(
                $headers['supplier_name'],
                $headers['supplier_tax_id'],
                $headers['entity_type']
            );
            $entityId = $entity['id'];

            // Mükerrer Kontrolü (Fatura No ve Cari bazlı)
            if (!empty($headers['invoice_no'])) {
                $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM inv_movements WHERE document_no = ? AND entity_id = ?");
                $stmt->execute([$headers['invoice_no'], $entityId]);
                if ($stmt->fetchColumn() > 0) {
                     throw new Exception("Bu fatura numarası ({$headers['invoice_no']}) bu cari için daha önce kaydedilmiş! Mükerrer kayıt yapılamaz.");
                }
            }

            // KDV ve İskonto Toplamlarını Takip Et
            $totalTax = 0;
            $totalDiscount = 0; // Şimdilik iskonto hesaplaması karmaşık olduğu için 0, ileride eklenebilir.

            // Process stock items
            foreach ($items as $item) {
                if ($item['type'] !== 'STOK') {
                    // Log expense or skip for now based on 'inv_products' structure
                    continue; 
                }

                $mappedId = !empty($item['mapped_id']) ? $item['mapped_id'] : null;
                $quantity = floatval($item['quantity']); // Alışlarda miktar pozitif
                $unitPrice = floatval($item['unit_price']); // Formdan gelen (iskontolu/net) fiyat
                $taxRate = isset($item['tax_rate']) ? floatval($item['tax_rate']) : 20;

                // Satırın KDV Tutarını Hesapla
                // Formül: Miktar * BirimFiyat * (VergiOranı / 100)
                $lineTotal = $quantity * $unitPrice;
                $lineTax = $lineTotal * ($taxRate / 100);
                $totalTax += $lineTax;

                // If not mapped, create new product
                if (!$mappedId) {
                    $mappedId = $this->productModel->create([
                        'name' => $item['raw_name'],
                        'barcode' => null,
                        'unit' => $item['unit'],
                        'stock_quantity' => 0,
                        'avg_cost' => 0
                    ]);
                }

                // --- 1.2 Mapping (Learning) ---
                if (!empty($item['raw_name']) && $mappedId) {
                    $this->mappingModel->createOrUpdate($item['raw_name'], $mappedId);
                }

                $product = $this->productModel->find($mappedId);
                
                // --- 4.2. Ağırlıklı Ortalama Algoritması ---
                $currentStock = floatval($product['stock_quantity']);
                $currentAvgCost = floatval($product['avg_cost']);
                
                // Stok miktarı artıyor
                $newStock = $currentStock + $quantity;
                $newAvgCost = 0;

                if ($currentStock < 0) $currentStock = 0; // Negatif stok varsa düzeltme (isteğe bağlı)

                // Yeni Maliyet Hesabı
                $totalOldValue = $currentStock * $currentAvgCost;
                $totalNewValue = $quantity * $unitPrice;
                
                if ($newStock > 0) {
                    $newAvgCost = ($totalOldValue + $totalNewValue) / $newStock;
                } else {
                    $newAvgCost = $unitPrice;
                }

                // Update Product
                // Ayrıca ürünün varsayılan KDV oranını da güncelle (en son faturadaki KDV oranı geçerli olsun)
                $this->productModel->updateCostAndStock($mappedId, $newStock, $newAvgCost, $unitPrice);
                
                // KDV Oranını güncellemek için ayrı bir sorgu yapabiliriz veya ProductModel'e ekleyebiliriz.
                // Şimdilik hızlı çözüm:
                $this->pdo->prepare("UPDATE inv_products SET tax_rate = ? WHERE id = ?")->execute([$taxRate, $mappedId]);

                // --- 4.3. Hareket Loglama ---
                $docNo = !empty($headers['invoice_no']) ? $headers['invoice_no'] : ($headers['invoice_date'] . '-INV');

                $this->movementModel->log([
                    'product_id' => $mappedId,
                    'entity_id' => $entityId,
                    'type' => 'in_invoice',
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'tax_rate' => $taxRate,     // Eklendi
                    'tax_amount' => $lineTax,   // Eklendi
                    'prev_stock' => $currentStock,
                    'new_stock' => $newStock,
                    'document_no' => $docNo,
                    'description' => 'Fatura: ' . $docNo . ', Tedarikçi: ' . $headers['supplier_name']
                ]);
            }

            // --- FAZ 4.2: Negatif Bakiye Kontrolü ve Borç Limiti Uyarısı ---
            $currentBalance = $this->entityModel->getBalance($entityId);
            $warnings = [];
            
            // If invoice is unpaid, update supplier balance (negative = we owe them)
            // KDV Dahil Toplam Tutar Bakiyeden Düşülür (Borç Artar)
            
            $docNo = !empty($headers['invoice_no']) ? $headers['invoice_no'] : ($headers['invoice_date'] . '-INV');

            if ($headers['payment_status'] === 'unpaid') {
                $amount = -floatval($headers['total_amount']); // Negative because we owe
                $projectedBalance = $currentBalance + $amount;
                
                // Check if debt limit is exceeded (configurable, default -50000)
                $debtLimit = -50000; 
                if ($projectedBalance < $debtLimit) {
                    $warnings[] = "UYARI: Borç limiti aşılıyor! Mevcut: " . number_format($currentBalance, 2) . " ₺, Yeni: " . number_format($projectedBalance, 2) . " ₺";
                }
                
                // Check if debt is significantly increasing
                if ($currentBalance < 0 && $projectedBalance < ($currentBalance * 1.5)) {
                    $warnings[] = "DİKKAT: Tedarikçi borcu %50'den fazla artıyor!";
                }
                
                $this->entityModel->updateBalance(
                    $entityId,
                    $amount,
                    'fatura',
                    'Alış Faturası - Tarih: ' . $headers['invoice_date'],
                    $headers['invoice_date'],
                    $docNo,
                    $totalTax,      // Eklendi
                    $totalDiscount  // Eklendi
                );
            } elseif ($headers['payment_status'] === 'paid') {
                // Nakit ödenmiş olsa bile kaydı tutulsun ama bakiye 0 değişsin.
                // Veya 'fatura' olarak borçlandırılıp, ardından 'odeme' olarak kapatılabilir.
                // Ancak basitleştirmek için 0 bakiye değişimi ile işlem logu atıyoruz.
                $this->entityModel->updateBalance(
                    $entityId,
                    0, // No balance change
                    'fatura',
                    'Alış Faturası (Ödenmiş) - Tarih: ' . $headers['invoice_date'],
                    $headers['invoice_date'],
                    $docNo,
                    $totalTax,      // Eklendi
                    $totalDiscount  // Eklendi
                );
            }

            $this->pdo->commit();
            
            // Redirect with warnings if any
            $successMessage = 'Fatura başarıyla kaydedildi.';
            if (!empty($warnings)) {
                $successMessage .= ' ' . implode(' ', $warnings);
            }
            
            if (function_exists('public_url')) {
                header('Location: ' . public_url('inventory?success=1&message=' . urlencode($successMessage)));
            } else {
                header('Location: /proje/inventory?success=1&message=' . urlencode($successMessage)); 
            }
            exit;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            die("Hata oluştu: " . $e->getMessage());
        }
    }
}
?>
