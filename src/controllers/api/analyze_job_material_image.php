<?php
require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/../../../src/helpers.php';
require_once __DIR__ . '/../../../src/auth.php';
require_once __DIR__ . '/../../../src/Services/GeminiService.php';
require_once __DIR__ . '/../../../src/Models/ProductModel.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    try {
        $apiKey = $_ENV['GEMINI_API_KEY'] ?? '';
        if (empty($apiKey)) throw new Exception("API anahtarı bulunamadı.");

        // Veritabanındaki mevcut ürünleri çek (Gemini'ye yol göstermek için)
        $dbProducts = [];
        try {
            $stmt = $pdo->query("SELECT id, name as urun_adi, barcode FROM inv_products");
            $dbProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {}

        $service = new GeminiService($apiKey);
        $tmpPath = $_FILES['image']['tmp_name'];

        $systemInstruction = "Sen bir depo ve stok yönetim asistanısın. Daima TÜRKÇE cevap ver.
        Veritabanındaki mevcut ürünlerin listesini veriyorum. Görseldeki ürünü analiz ederken ÖNCELİKLE bu listeye bak.
        1. Eğer görseldeki ürün listedeki bir isimle veya barkodla eşleşiyorsa, MUTLAK SURETLE listedeki id'yi döndür.
        2. Eğer ürün listede YOKSA, ürünü tanımla ve is_matched değerini false yap.

        Mevcut Ürün Listesi (ID, İsim, Barkod):
        " . json_encode($dbProducts);

        $prompt = "Bu bir ürün fotoğrafı. Lütfen görseldeki ürünü analiz et ve sonucunu MUTLAK SURETLE sadece aşağıdaki JSON formatında döndür. Başka hiçbir açıklama yazma.
        {
          \"is_matched\": true/false,
          \"db_id\": null (eşleştiyse veritabanındaki id),
          \"product_name\": \"Ürün adı ve markası\",
          \"barcode\": \"Eğer görünüyorsa barkod numarası, yoksa null\",
          \"description\": \"Ürün hakkında 5 kelimelik kısa açıklama\"
        }";

        $result = $service->analyzeImage($tmpPath, $systemInstruction . "\n\n" . $prompt);

        if (isset($result['product_name'])) {
            $productModel = new ProductModel($pdo);
            $match = null;

            // 1. Gemini doğrudan bir ID döndürdüyse
            if (isset($result['is_matched']) && $result['is_matched'] && !empty($result['db_id'])) {
                $match = $productModel->find($result['db_id']);
            }

            // 2. ID gelmediyse veya geçersizse findBestMatch ile ara
            if (!$match) {
                $match = $productModel->findBestMatch($result['product_name']);
            }
            
            if ($match && !empty($result['product_name'])) {
                // Mapping tablosuna kaydet (Gelecekte daha hızlı bulunması için)
                try {
                    $stmt = $pdo->prepare("INSERT IGNORE INTO inv_mapping (product_id, raw_name) VALUES (?, ?)");
                    $stmt->execute([$match['id'], $result['product_name']]);
                } catch(Exception $e) {}
            }

            echo json_encode([
                'status' => 'success', 
                'product_name' => $result['product_name'],
                'match' => $match,
                'ai_data' => $result
            ]);
        } else {
            throw new Exception("Ürün tanımlanamadı.");
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Geçersiz istek.']);
}
