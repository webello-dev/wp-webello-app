<?php
if (!defined('ABSPATH')) {
    exit;
}
include 'login/login.php';
include 'login/auth.php';
include 'post/post.php';
include 'login/qr/qr.php';
function wp_webello_register_auth_routes() {
    // مسیر ورود
	register_rest_route('wp-webello/v1', '/login', array('methods' => 'POST', 'callback' => 'wp_webello_login_user', 'permission_callback' => '__return_true',));
	register_rest_route('wp-webello/v1', '/auth/(?P<unique_id>[a-zA-Z0-9_\-]+)', array('methods' => 'POST', 'callback' => 'handle_qr_auth', 'permission_callback' => '__return_true',));
	register_rest_route('wp-webello/v1', '/auth/status', array( 'methods' => 'GET', 'callback' => 'check_login_status', 'permission_callback' => '__return_true', ));
    register_rest_route('wp-webello/v1/get', '/info', array('methods' => 'GET', 'callback' => 'wp_webello_get_site_info', 'permission_callback' => 'wp_webello_check_token',));
    register_rest_route('wp-webello/v1/get', '/posts', array('methods' => 'GET', 'callback' => 'wp_webello_get_posts', 'permission_callback' => 'wp_webello_check_token',));
    register_rest_route('wp-webello/v1/get', '/categories', array('methods' => 'GET', 'callback' => 'wp_webello_get_categories', 'permission_callback' => 'wp_webello_check_token',));
    register_rest_route('wp-webello/v1/post', '/create', array('methods'  => 'POST', 'callback' => 'wp_webello_create_post', 'permission_callback' => 'wp_webello_check_token',));
    register_rest_route('wp-webello/v1/post', '/update', array('methods'  => 'POST', 'callback' => 'wp_webello_update_post', 'permission_callback' => 'wp_webello_check_token',));
    register_rest_route('wp-webello/v1/post', '/delete', array('methods'  => 'DELETE', 'callback' => 'wp_webello_delete_post', 'permission_callback' => 'wp_webello_check_token',));
    register_rest_route('wp-webello/v1/category', '/create', array('methods'  => 'POST', 'callback' => 'wp_webello_create_category', 'permission_callback' => 'wp_webello_check_token',));
    register_rest_route('wp-webello/v1/category', '/update', array('methods'  => 'POST', 'callback' => 'wp_webello_update_category', 'permission_callback' => 'wp_webello_check_token',));
    register_rest_route('wp-webello/v1/category', '/delete', array('methods'  => 'DELETE', 'callback' => 'wp_webello_delete_category', 'permission_callback' => 'wp_webello_check_token',));
    register_rest_route('wp-webello/v1/send', '/image', array('methods' => 'POST', 'callback' => 'wp_webello_image_upload', 'permission_callback' => 'wp_webello_check_token',));
}
add_action('rest_api_init', 'wp_webello_register_auth_routes');


function wp_webello_get_site_info($request) {
    $user_id = $request->get_param('user_id');
    $user_info = get_userdata($user_id);
    return array(
        'site_name'     => get_bloginfo('name'),              // نام سایت
        'site_url'      => get_bloginfo('url'),               // آدرس سایت
        'admin_email'   => get_bloginfo('admin_email'),       // ایمیل مدیر سایت
        'description'   => get_bloginfo('description'),       // شعار سایت
        'wp_version'    => get_bloginfo('version'),           // نسخه وردپرس
        'theme_name'    => wp_get_theme()->get('Name'),       // نام پوسته فعال
        'theme_version' => wp_get_theme()->get('Version'),    // نسخه پوسته فعال
        'site_logo'     => get_site_icon_url(),               // لوگوی سایت
        'ID'        => $user_info->ID,
        'username'  => $user_info->user_login,
        'email'     => $user_info->user_email,
        'name'      => $user_info->display_name,
        'roles'     => $user_info->roles, // نقش‌های کاربر
        'registered'=> $user_info->user_registered, // تاریخ ثبت‌نام
    );
}