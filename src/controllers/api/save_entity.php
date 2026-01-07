<?php
// src/controllers/api/save_entity.php

require_once __DIR__ . '/../../Models/EntityModel.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Method not allowed');
}

try {
    $entityModel = new EntityModel($pdo);
    
    $entityId = intval($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $type = $_POST['type'] ?? 'supplier';
    $taxId = trim($_POST['tax_id'] ?? '') ?: null;
    $phone = trim($_POST['phone'] ?? '') ?: null;
    $email = trim($_POST['email'] ?? '') ?: null;
    $address = trim($_POST['address'] ?? '') ?: null;
    $initialBalance = floatval($_POST['initial_balance'] ?? 0);
    
    // Validation
    if (empty($name)) {
        throw new Exception('Cari adı zorunludur.');
    }
    
    if (!in_array($type, ['supplier', 'customer', 'both', 'staff'])) {
        throw new Exception('Geçersiz cari tipi.');
    }
    
    $pdo->beginTransaction();
    
    if ($entityId > 0) {
        // UPDATE existing entity
        
        // Check if entity exists
        $entity = $entityModel->find($entityId);
        if (!$entity) {
            throw new Exception('Cari bulunamadı.');
        }
        
        // Check if tax_id is being changed and if it conflicts
        if ($taxId && $taxId !== $entity['tax_id']) {
            $stmt = $pdo->prepare("SELECT id FROM inv_entities WHERE tax_id = ? AND id != ?");
            $stmt->execute([$taxId, $entityId]);
            if ($stmt->fetch()) {
                throw new Exception('Bu VKN/TCKN ile kayıtlı başka bir cari mevcut.');
            }
        }
        
        // Update entity (balance is NOT updated here)
        $stmt = $pdo->prepare("
            UPDATE inv_entities 
            SET name = ?, type = ?, tax_id = ?, phone = ?, email = ?, address = ?
            WHERE id = ?
        ");
        $stmt->execute([$name, $type, $taxId, $phone, $email, $address, $entityId]);
        
        $pdo->commit();
        
        header('Location: ' . public_url('entities?success=1&message=' . urlencode('Cari başarıyla güncellendi.')));
        exit;
        
    } else {
        // INSERT new entity
        
        // Check if entity already exists
        if ($taxId) {
            $stmt = $pdo->prepare("SELECT id FROM inv_entities WHERE tax_id = ?");
            $stmt->execute([$taxId]);
            if ($stmt->fetch()) {
                throw new Exception('Bu VKN/TCKN ile kayıtlı bir cari zaten mevcut.');
            }
        }
        
        // Insert entity
        $stmt = $pdo->prepare("
            INSERT INTO inv_entities (name, type, tax_id, phone, email, address, balance) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $type, $taxId, $phone, $email, $address, $initialBalance]);
        
        $newEntityId = $pdo->lastInsertId();
        
        // If there's an initial balance, log it as a transaction
        if ($initialBalance != 0) {
            $stmt = $pdo->prepare("
                INSERT INTO inv_entity_transactions (entity_id, type, amount, description, transaction_date) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $newEntityId,
                'diger',
                $initialBalance,
                'Başlangıç bakiyesi',
                date('Y-m-d')
            ]);
        }
        
        $pdo->commit();
        
        header('Location: ' . public_url('entities?success=1&message=' . urlencode('Cari başarıyla eklendi.')));
        exit;
    }
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Redirect back with error
    $redirectUrl = $entityId > 0 ? "entity/edit?id=$entityId" : 'entity/add';
    header('Location: ' . public_url($redirectUrl . '&error=1&message=' . urlencode($e->getMessage())));
    exit;
}

