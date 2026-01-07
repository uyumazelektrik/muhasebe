<?php
// list_gemini_models.php
require_once __DIR__ . '/src/helpers.php'; // For env load

$apiKey = $_ENV['GEMINI_API_KEY'] ?? '';
if (!$apiKey) die("API Key missing");

$url = "https://generativelanguage.googleapis.com/v1beta/models?key=$apiKey";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

if (isset($data['models'])) {
    echo "<h1>Available Models</h1><ul>";
    foreach ($data['models'] as $model) {
        if (in_array('generateContent', $model['supportedGenerationMethods'])) {
            echo "<li><strong>" . $model['name'] . "</strong> (" . $model['version'] . ")</li>";
        }
    }
    echo "</ul>";
} else {
    echo "Error: <pre>" . print_r($data, true) . "</pre>";
}
?>
