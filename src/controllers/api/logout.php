<?php
require_once __DIR__ . '/../../../src/helpers.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$_SESSION = [];
session_destroy();

redirect(public_url('login'));
?>
