<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/**
 * Kullanıcı giriş yapmış mı kontrol eder.
 * Değilse login sayfasına yönlendirir.
 */
function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . public_url('login'));
        exit;
    }
}

/**
 * Mevcut kullanıcının rolünü döner.
 */
function current_role() {
    return isset($_SESSION['role']) ? $_SESSION['role'] : null;
}

/**
 * Sadece admin erişimi sağlar.
 */
function require_admin() {
    require_login();
    if (current_role() !== 'admin') {
        http_response_code(403);
        die('Bu sayfaya erişim yetkiniz yok.');
    }
}

/**
 * Mevcut kullanıcı ID'sini döner.
 */
function current_user_id() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}
?>
