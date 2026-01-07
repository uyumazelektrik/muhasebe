<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/../../../src/helpers.php';

// API Key kontrolü
$apiKey = $_ENV['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY');

if (!$apiKey || $apiKey === 'YOUR_GEMINI_API_KEY_HERE') {
    echo json_encode(['status' => 'error', 'message' => 'Gemini API anahtarı ayarlanmamış. Lütfen .env dosyasını kontrol edin.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Geçersiz istek.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$imageData = $input['image'] ?? '';

if (empty($imageData)) {
    echo json_encode(['status' => 'error', 'message' => 'Görüntü verisi bulunamadı.']);
    exit;
}

// Base64 verisini temizle
$imageData = preg_replace('#^data:image/\w+;base64,#i', '', $imageData);

// Veritabanındaki mevcut ürünleri çek
$dbProducts = [];
try {
    $stmt = $pdo->query("SELECT id, name as urun_adi, barcode FROM inv_products");
    $dbProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Hata durumunda boş devam et
}

/**
 * 2026 Başı İtibariyle Aktif ve En Üst Seviye Modeller (Öncelik Sırasıyla)
 * Listedeki modeller API'den gelen canlı liste baz alınarak güncellenmiştir.
 */
$models = [
    'gemini-2.5-flash',           // En yeni amiral gemisi (Hızlı ve Zeki)
    'gemini-2.0-flash',           // Kararlı 2.0 sürümü
    'gemini-2.5-flash-lite',      // Çok hızlı ve verimli
    'gemini-2.0-flash-exp',       // 2.0 Experimental
    'gemini-1.5-flash-latest',    // Klasik kararlı sürüm
    'gemini-2.5-pro'              // Güçlü ama yüksek kota tüketebilir (Fallback olarak sonlara eklendi)
];

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

$responseBody = null;
$chosenModel = '';
$lastError = '';

// Model Fallback Döngüsü
foreach ($models as $model) {
    // API adresi v1beta olarak ayarlandı çünkü sistem talimatı bu sürümde daha kararlı
    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . $apiKey;
    
    $payload = [
        'system_instruction' => [
            'parts' => [['text' => $systemInstruction]]
        ],
        'contents' => [
            [
                'parts' => [
                    ['text' => $prompt],
                    [
                        'inline_data' => [
                            'mime_type' => 'image/jpeg',
                            'data' => $imageData
                        ]
                    ]
                ]
            ]
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    
    $execResult = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);

    if ($httpCode === 200 && $execResult) {
        $responseBody = $execResult;
        $chosenModel = $model;
        break;
    } else {
        $errorMsg = json_decode($execResult, true);
        $lastError = $errorMsg['error']['message'] ?? ($curlErr ?: "HTTP $httpCode");
        continue;
    }
}

if (!$responseBody) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Analiz başarısız. Tüm modeller denendi. Son hata: ' . $lastError
    ]);
    exit;
}

$result = json_decode($responseBody, true);
$aiText = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';

// AI yanıt temizleme
$aiText = preg_replace('/^```json\s*|```\s*$/i', '', trim($aiText));
$aiData = json_decode($aiText, true);

$productName = $aiData['product_name'] ?? '';
$barcode = $aiData['barcode'] ?? null;
$isMatched = $aiData['is_matched'] ?? false;
$dbId = $aiData['db_id'] ?? null;

if (empty($productName)) {
    echo json_encode(['status' => 'error', 'message' => 'Model ürünü tanımlayamadı.']);
    exit;
}

try {
    $items = [];
    
    // 1. Eğer Gemini bir veritabanı ID'si döndürmüşse öncelikle onu al
    if ($isMatched && $dbId) {
        $stmt = $pdo->prepare("SELECT *, name as urun_adi, stock_quantity as miktar, unit as birim, satis_fiyat, critical_level as kritik_esik FROM inv_products WHERE id = ? LIMIT 1");
        $stmt->execute([$dbId]);
        $directItem = $stmt->fetch();
        if ($directItem) {
            $items[] = $directItem;
        }
    }

    // 2. Eğer ID ile gelmediyse veya ek benzerleri bulmak istiyorsak Barkod ve İsim ile ara
    $searchIds = array_column($items, 'id');
    
    // Barkod ile ara
    if ($barcode) {
        $stmt = $pdo->prepare("SELECT *, name as urun_adi, stock_quantity as miktar, unit as birim, satis_fiyat, critical_level as kritik_esik FROM inv_products WHERE barcode = ? AND id NOT IN (" . (empty($searchIds) ? '0' : implode(',', $searchIds)) . ")");
        $stmt->execute([$barcode]);
        $foundByBarcode = $stmt->fetchAll();
        $items = array_merge($items, $foundByBarcode);
        $searchIds = array_column($items, 'id');
    }
    
    // İsim ile benzerleri ara (LIKE)
    if ($productName) {
        // İsimdeki kelimeleri parçalayarak daha geniş bir arama yapabiliriz
        $searchWords = explode(' ', $productName);
        $likeQuery = "%" . $productName . "%";
        $stmt = $pdo->prepare("SELECT *, name as urun_adi, stock_quantity as miktar, unit as birim, satis_fiyat, critical_level as kritik_esik FROM inv_products WHERE name LIKE ? AND id NOT IN (" . (empty($searchIds) ? '0' : implode(',', $searchIds)) . ") LIMIT 5");
        $stmt->execute([$likeQuery]);
        $foundByName = $stmt->fetchAll();
        $items = array_merge($items, $foundByName);
    }

    if (!empty($items)) {
        echo json_encode([
            'status' => 'success', 
            'items' => $items, // Artık bir dizi dönüyoruz
            'ai_data' => [
                'name' => $productName,
                'barcode' => $barcode,
                'description' => $aiData['description'] ?? '',
                'source' => $isMatched ? 'db_match' : 'manual_search',
                'model' => $chosenModel
            ]
        ]);
    } else {
        echo json_encode([
            'status' => 'not_found', 
            'identified_as' => $productName,
            'ai_barcode' => $barcode,
            'message' => "Ürün tanımlandı ('$productName') ancak benzer bir ürün stoklarda bulunamadı.",
            'model' => $chosenModel
        ]);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
