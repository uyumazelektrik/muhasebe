<?php
// src/Models/EntityModel.php

class EntityModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Find entity by name or tax ID, create if not exists
     */
    public function findOrCreate($name, $taxId = null, $type = 'supplier') {
        // Try to find by tax_id first if provided
        if ($taxId) {
            $stmt = $this->pdo->prepare("SELECT * FROM inv_entities WHERE tax_id = ? LIMIT 1");
            $stmt->execute([$taxId]);
            $entity = $stmt->fetch();
            if ($entity) {
                return $entity;
            }
        }

        // Try to find by name (regardless of type to avoid duplicates)
        $stmt = $this->pdo->prepare("SELECT * FROM inv_entities WHERE name = ? LIMIT 1");
        $stmt->execute([$name]);
        $entity = $stmt->fetch();
        
        if ($entity) {
            return $entity;
        }

        // Create new entity
        $stmt = $this->pdo->prepare("INSERT INTO inv_entities (name, type, tax_id, balance) VALUES (?, ?, ?, 0.0000)");
        $stmt->execute([$name, $type, $taxId]);
        
        return $this->pdo->query("SELECT * FROM inv_entities WHERE id = LAST_INSERT_ID()")->fetch();
    }

    /**
     * Cari kartın tüm varlık bakiyelerini getirir (TL, USD, Altın vb.)
     */
    public function getAssetBalances($entityId) {
        $stmt = $this->pdo->prepare("SELECT * FROM inv_entity_balances WHERE entity_id = ?");
        $stmt->execute([$entityId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Belirli bir varlık tipindeki bakiyeyi getirir
     */
    public function getAssetBalance($entityId, $assetType = 'TL') {
        $stmt = $this->pdo->prepare("SELECT amount FROM inv_entity_balances WHERE entity_id = ? AND asset_type = ?");
        $stmt->execute([$entityId, $assetType]);
        return (float)$stmt->fetchColumn() ?: 0.0;
    }

    /**
     * Ana TL bakiyesini getirir (inv_entities tablosundan)
     */
    public function getBalance($entityId) {
        $stmt = $this->pdo->prepare("SELECT balance FROM inv_entities WHERE id = ?");
        $stmt->execute([$entityId]);
        return (float)$stmt->fetchColumn() ?: 0.0;
    }

    /**
     * Varlık bazlı bakiye güncelleme ve işlem kaydı
     */
    /**
     * Varlık bazlı bakiye güncelleme ve işlem kaydı
     */
    /**
     * Varlık bazlı bakiye güncelleme ve işlem kaydı
     */
    public function updateAssetBalance($entityId, $amount, $assetType = 'TL', $type = 'fatura', $description = '', $transactionDate = null, $documentNo = null, $rate = 1.0, $walletId = null, $isOffsetProcess = false, $installmentCount = 1, $commissionFee = 0, $transferEntityId = null, $dueDate = null, $parentTransactionId = null) {
        $transactionDate = $transactionDate ?? date('Y-m-d');
        $dueDate = $dueDate ?? $transactionDate;
        $tlAmount = $amount * $rate;
        $installmentCount = intval($installmentCount) > 0 ? intval($installmentCount) : 1;
        $commissionFee = floatval($commissionFee);
        $firstId = null;

        // 0. Cüzdan Bakiyesini Güncelle (Wallet tablosu için)
        if ($walletId) {
            require_once __DIR__ . '/WalletModel.php';
            $walletModel = new WalletModel($this->pdo);
            // Ödeme (-) cüzdan bakiyesini azaltmalı, Tahsilat (+) artırmalı.
            // Entity tarafında Tahsilat (+) ise borç artar, ödeme (-) ise borç azalır.
            // Wallet tarafında ise tam tersi: Tahsilat (+) cüzdana giren paradır, Ödeme (-) cüzdandan çıkan paradır.
            // Cari işlem tipine göre karar verelim:
            $walletMovement = $amount; 
            if (!$isOffsetProcess) {
                // Eğer bu ana işlemseniz ve başka bir cüzdan kullanıyorsanız 
                // (Örn: Cari'ye ödeme yapıyorsanız), cüzdan hareketini burada işlemeyelim, 
                // offset (emanet) kısmında işleyelim ki çift kayıt olmasın.
            } else {
                $walletModel->updateBalance($walletId, $walletMovement);
            }
        }

        // 1. İşlem Kaydı
        // Önemli: Eğer bu bir ana işlemse (Cari Ekstresi) ve cüzdan kullanılıyorsa, 
        // cüzdan hareketleri (offset) ayrıca oluşacağı için ana kayda wallet_id'yi DB'de NULL geçelim.
        // Böylece cüzdan dökümünde "ana tutar + parça taksitler" şeklinde mükerrer kayıt görünmez.
        $dbWalletId = ($isOffsetProcess) ? $walletId : null; 

        if ($isOffsetProcess && $installmentCount > 1) {
            // "Hesap Hareketleri" görünsün istendiği için Wallet/Emanet tarafında taksitli döküm yapılır.
            $perAmount = $amount / $installmentCount;
            $perTlAmount = $tlAmount / $installmentCount;
            $localParentId = $parentTransactionId;

            for ($i = 1; $i <= $installmentCount; $i++) {
                $currentDate = date('Y-m-d', strtotime("+" . ($i-1) . " month", strtotime($transactionDate)));
                $currentDueDate = date('Y-m-d', strtotime("+" . ($i-1) . " month", strtotime($dueDate)));
                $instDesc = $description . " (Taksit $i/$installmentCount)";
                
                $stmt = $this->pdo->prepare(
                    "INSERT INTO inv_entity_transactions (entity_id, type, amount, asset_type, exchange_rate, asset_amount, description, transaction_date, due_date, document_no, wallet_id, installment_count, installment_no, parent_transaction_id, commission_fee) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
                );
                $stmt->execute([
                    $entityId, $type, $perTlAmount, $assetType, $rate, $perAmount, 
                    $instDesc, $currentDate, $currentDueDate, $documentNo, $dbWalletId, 
                    $installmentCount, $i, $localParentId, 0
                ]);

                if ($i === 1) {
                    $firstId = $this->pdo->lastInsertId();
                    if (!$localParentId) {
                        $localParentId = $firstId;
                        $this->pdo->prepare("UPDATE inv_entity_transactions SET parent_transaction_id = ? WHERE id = ?")
                                 ->execute([$localParentId, $localParentId]);
                    }
                }
            }
            $finalParentId = $localParentId;
        } else {
            // Cari tarafında (asıl işlem) tek bir kayıt olarak işlensin (User Request)
            $stmt = $this->pdo->prepare(
                "INSERT INTO inv_entity_transactions (entity_id, type, amount, asset_type, exchange_rate, asset_amount, description, transaction_date, due_date, document_no, wallet_id, installment_count, commission_fee, parent_transaction_id) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $entityId, $type, $tlAmount, $assetType, $rate, $amount, 
                $description, $transactionDate, $dueDate, $documentNo, $dbWalletId, 
                $installmentCount, $commissionFee, $parentTransactionId
            ]);
            $firstId = $this->pdo->lastInsertId();
            $finalParentId = $parentTransactionId ?? $firstId;
            
            if (!$parentTransactionId) {
                $this->pdo->prepare("UPDATE inv_entity_transactions SET parent_transaction_id = ? WHERE id = ?")
                         ->execute([$finalParentId, $firstId]);
            }
        }

        // 2. Bakiyeleri GÜNCELLE
        $stmt = $this->pdo->prepare("INSERT INTO inv_entity_balances (entity_id, asset_type, amount) 
                                     VALUES (?, ?, ?) 
                                     ON DUPLICATE KEY UPDATE amount = amount + ?");
        $stmt->execute([$entityId, $assetType, $amount, $amount]);

        $stmt = $this->pdo->prepare("UPDATE inv_entities SET balance = balance + ? WHERE id = ?");
        $stmt->execute([$tlAmount, $entityId]);

        // 3. FAZ 8.2: Ödeme Kaynağı (Wallet) ve Emanet Kart Mantığı
        if (!$isOffsetProcess && $walletId) {
            require_once __DIR__ . '/WalletModel.php';
            $walletModel = new WalletModel($this->pdo);
            $wallet = $walletModel->find($walletId);

            // Cüzdan sahibi ile işlem yapılan cari farklıysa veya cüzdan sadece bir takip aracıysa
            // Cari'nin bakiyesi değişti, şimdi cüzdan sahibinin (genelde işletme) borcunu/alacağını ve cüzdan hareketini işleyelim.
            $entityName = $this->pdo->query("SELECT name FROM inv_entities WHERE id = " . intval($entityId))->fetchColumn();
            $emanetDesc = "EMANET ÖDEME: $entityName için (" . $wallet['name'] . ")";
            
            // Cüzdan bakiyesi azalıyorsa (-), cüzdan sahibinin sistemdeki cari bakiyesi de aynı oranda değişmeli.
            $this->updateAssetBalance($wallet['owner_entity_id'], -$amount, $assetType, $type, $emanetDesc, $transactionDate, $documentNo, $rate, $walletId, true, $installmentCount, 0, null, $dueDate, $finalParentId);
        }

        // 4. VİRMAN: Başka Cari ile Ödeme
        if (!$isOffsetProcess && $transferEntityId) {
             $entityName = $this->pdo->query("SELECT name FROM inv_entities WHERE id = " . intval($entityId))->fetchColumn();
             $virmanDesc = "VİRMAN: $entityName işlemi karşılığı";
             
             // İşlem tipini tersine çevir (Tahsilat <-> Ödeme)
             $targetType = $type;
             if ($type === 'tahsilat') $targetType = 'odeme';
             elseif ($type === 'odeme') $targetType = 'tahsilat';

             $this->updateAssetBalance($transferEntityId, -$amount, $assetType, $targetType, $virmanDesc, $transactionDate, $documentNo, $rate, null, true, $installmentCount, 0, null, $dueDate, $finalParentId);
        }

        return $finalParentId;
    }

    /**
     * Geriye dönük uyumluluk için eski metot (TL varsayar)
     */
    public function updateBalance($entityId, $amount, $type, $description, $transactionDate, $documentNo = null, $taxTotal = 0, $discountTotal = 0) {
        return $this->updateAssetBalance($entityId, $amount, 'TL', $type, $description, $transactionDate, $documentNo, 1.0);
    }

    /**
     * Get transaction statement
     */
    public function getStatement($entityId, $startDate = null, $endDate = null) {
        $sql = "SELECT * FROM inv_entity_transactions WHERE entity_id = ?";
        $params = [$entityId];

        if ($startDate) {
            $sql .= " AND transaction_date >= ?";
            $params[] = $startDate;
        }

        if ($endDate) {
            $sql .= " AND transaction_date <= ?";
            $params[] = $endDate;
        }

        $sql .= " ORDER BY transaction_date DESC, created_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Tüm carileri ve tüm para birimlerindeki bakiyelerini getirir
     */
    /**
     * Tüm carileri ve tüm para birimlerindeki bakiyelerini getirir
     * Not: JSON_ARRAYAGG eski sistemlerde çalışmadığı için PHP tarafında birleştiriyoruz.
     */
    public function getAllWithBalances() {
        // 1. Tüm carileri al
        $entities = $this->pdo->query("SELECT * FROM inv_entities WHERE is_active = 1 ORDER BY balance DESC")->fetchAll(PDO::FETCH_ASSOC);
        
        // 2. Tüm varlık bakiyelerini al
        $balances = $this->pdo->query("SELECT * FROM inv_entity_balances")->fetchAll(PDO::FETCH_ASSOC);
        
        // 3. Bakiyeleri grupla
        $groupedBalances = [];
        foreach ($balances as $b) {
            $groupedBalances[$b['entity_id']][] = [
                'asset_type' => $b['asset_type'],
                'amount' => $b['amount']
            ];
        }
        
        // 4. Carilerle birleştir
        foreach ($entities as &$e) {
            $e['asset_balances'] = json_encode($groupedBalances[$e['id']] ?? []);
        }
        
        return $entities;
    }

    /**
     * Get entity by ID
     */
    public function find($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM inv_entities WHERE id = ? AND is_active = 1");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Delete entity (soft delete)
     */
    public function delete($id) {
        $stmt = $this->pdo->prepare("UPDATE inv_entities SET is_active = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Get all entities by type
     */
    public function getAll($type = null) {
        if ($type) {
            $stmt = $this->pdo->prepare("SELECT * FROM inv_entities WHERE type = ? AND is_active = 1 ORDER BY name ASC");
            $stmt->execute([$type]);
            return $stmt->fetchAll();
        } else {
            return $this->pdo->query("SELECT * FROM inv_entities WHERE is_active = 1 ORDER BY name ASC")->fetchAll();
        }
    }


}
?>
