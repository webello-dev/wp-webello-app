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
require_once WP_WEBELLO_PLUGIN_DIR . 'includes/endpoints.php';
require_once WP_WEBELLO_PLUGIN_DIR . 'includes/auth.php';