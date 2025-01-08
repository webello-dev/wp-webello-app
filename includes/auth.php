<?php
if (!defined('ABSPATH')) {
    exit;
}

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require_once WP_WEBELLO_PLUGIN_DIR . 'vendor/autoload.php';

define('WP_WEBELLO_JWT_SECRET', '666'); // کلید امنیتی شما
define('WP_WEBELLO_JWT_EXPIRY', 86400); // زمان انقضا (1 روز)

// ایجاد توکن برای کاربر
function wp_webello_generate_token($user) {
    $issuedAt = time();
    $expiry = $issuedAt + WP_WEBELLO_JWT_EXPIRY;

    $payload = [
        'iss' => get_site_url(),
        'iat' => $issuedAt,
        'exp' => $expiry,
        'user_id' => $user->ID,
    ];

    return JWT::encode($payload, WP_WEBELLO_JWT_SECRET, 'HS256');
}

// بررسی توکن
function wp_webello_validate_token($token) {
    try {
        $decoded = JWT::decode($token, new Key(WP_WEBELLO_JWT_SECRET, 'HS256'));
        return $decoded->user_id;
    } catch (Exception $e) {
        return false;
    }
}
