<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('public_url')) {
    function public_url($uri = '') {
        return site_url($uri);
    }
}

if (!function_exists('current_role')) {
    function current_role() {
        return $_SESSION['role'] ?? 'guest';
    }
}
if (!function_exists('current_user_id')) {
    function current_user_id() {
        return $_SESSION['user_id'] ?? 0;
    }
}

if (!function_exists('format_hours')) {
    function format_hours($decimal_hours) {
        $hours = floor($decimal_hours);
        $minutes = round(($decimal_hours - $hours) * 60);
        if ($hours == 0) return $minutes . ' dk';
        return $hours . ' sa ' . ($minutes > 0 ? $minutes . ' dk' : '');
    }
}
