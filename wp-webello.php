<?php
/**
 * Plugin Name: webello app
 * Plugin URI: https://webello.ir/
 * Description: webello plugin app
 * Version: 1.0
 * Author: amirzzz
 * Author URI: https://webello.ir
 * License: GPL2
 */

// اگر کسی مستقیماً فایل را اجرا کرد، جلوی آن را بگیرید
if (!defined('ABSPATH')) {
    exit;
}

// تعریف مسیر افزونه
define('WP_WEBELLO_PLUGIN_DIR', plugin_dir_path(__FILE__));

// بارگذاری فایل‌های افزونه
require_once WP_WEBELLO_PLUGIN_DIR . 'includes/routes.php';

// غیر فعال کردن تمامی API های پیش‌فرض وردپرس
add_filter('rest_endpoints', function($endpoints) {
    // آرایه‌ای از روت‌های مجاز (فقط API‌های افزونه شما)
    $allowed_routes = [
        '/wp-webello/v1',  // مسیر روت API افزونه شما
    ];

    // حذف تمام روت‌ها که در آرایه $allowed_routes نیستند
    foreach ($endpoints as $route => $callback) {
        // اگر روت در لیست مجاز نیست، آن را حذف کن
        $is_allowed = false;
        foreach ($allowed_routes as $allowed_route) {
            if (strpos($route, $allowed_route) === 0) {
                $is_allowed = true;
                break;
            }
        }

        // اگر روت مجاز نیست، حذف کن
        if (!$is_allowed) {
            unset($endpoints[$route]);
        }
    }

    // سپس روت‌های خودتان را ثبت کنید
    wp_webello_register_auth_routes();
    return $endpoints;
},1);
