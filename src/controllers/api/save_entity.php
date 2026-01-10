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

        // STAFF Sync
        if ($type === 'staff') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'personel';
            $hourlyRate = floatval($_POST['hourly_rate'] ?? 0);

            // Check if user exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE entity_id = ?");
            $stmt->execute([$entityId]);
            $userId = $stmt->fetchColumn();

            if ($userId) {
                // Update existing user
                if (!empty($password)) {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, username = ?, password = ?, role = ?, hourly_rate = ? WHERE id = ?");
                    $stmt->execute([$name, $username, $hashedPassword, $role, $hourlyRate, $userId]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, username = ?, role = ?, hourly_rate = ? WHERE id = ?");
                    $stmt->execute([$name, $username, $role, $hourlyRate, $userId]);
                }
            } else {
                // Create new user linked to this entity
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (entity_id, full_name, username, password, role, hourly_rate) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$entityId, $name, $username, $hashedPassword, $role, $hourlyRate]);
            }
        }
        
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
            INSERT INTO inv_entities (name, type, tax_id, phone, email, address) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $type, $taxId, $phone, $email, $address]);
        
        $newEntityId = $pdo->lastInsertId();

        // STAFF Sync
        if ($type === 'staff') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'personel';
            $hourlyRate = floatval($_POST['hourly_rate'] ?? 0);

            if (empty($username) || empty($password)) {
                throw new Exception('Personel tipi için Kullanıcı Adı ve Şifre zorunludur.');
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (entity_id, full_name, username, password, role, hourly_rate) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$newEntityId, $name, $username, $hashedPassword, $role, $hourlyRate]);
        }
        
        // If there's an initial balance, use the new model method to keep all tables in sync
        if ($initialBalance != 0) {
            $entityModel->updateAssetBalance(
                $newEntityId,
                $initialBalance,
                'TL',
                'diger',
                'Başlangıç bakiyesi',
                date('Y-m-d'),
                null,
                1.0
            );
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

