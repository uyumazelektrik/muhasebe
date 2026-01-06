<?php
// Yardımcı Fonksiyonlar
require_once __DIR__ . '/auth.php';

function view($name, $data = []) {
    extract($data);
    $path = __DIR__ . '/../views/' . $name . '.php';
    if (file_exists($path)) {
        require $path;
    } else {
        die("View dosyası bulunamadı: $name");
    }
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function public_url($path = '') {
    // Projenin kök URL'sini dinamik olarak belirlemeye çalışır
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $script = $_SERVER['SCRIPT_NAME'];
    $dir = dirname($script);
    
    // Eğer root'taysak / koyma
    $base = ($dir == '/' || $dir == '\\') ? '' : $dir;
    
    return $protocol . '://' . $host . $base . '/' . ltrim($path, '/');
}

function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function format_hours($h) {
    if (!$h) return '0 dk';
    $hours = floor($h);
    $minutes = round(($h - $hours) * 60);
    
    $parts = [];
    if ($hours > 0) $parts[] = "{$hours} sa";
    if ($minutes > 0) $parts[] = "{$minutes} dk";
    
    return empty($parts) ? '0 dk' : implode(' ', $parts);
}

function is_valid_date($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}


function calculate_total_with_tax($amount, $tax_rate) {
    return $amount * (1 + ($tax_rate / 100));
}

function redirect_with_message($url, $status, $message) {
    $q = http_build_query(['status' => $status, 'message' => $message]);
    redirect($url . (strpos($url, '?') === false ? '?' : '&') . $q);
}
?>
