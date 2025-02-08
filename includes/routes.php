<?php
if (!defined('ABSPATH')) {
    exit;
}
//include 'post/post.php';

include 'get/init.php';
include 'login/init.php';
include 'post/init.php';
include 'category/init.php';


function wp_webello_register_auth_routes() {
    // مسیر ورود
	register_rest_route('wp-webello/v1',            '/login', array('methods' => 'POST', 'callback' => 'wp_webello_login_user', 'permission_callback' => '__return_true',));
	register_rest_route('wp-webello/v1',            '/auth/(?P<unique_id>[a-zA-Z0-9_\-]+)', array('methods' => 'POST', 'callback' => 'handle_qr_auth', 'permission_callback' => '__return_true',));
	register_rest_route('wp-webello/v1',            '/auth/status', array( 'methods' => 'GET', 'callback' => 'check_login_status', 'permission_callback' => '__return_true', ));

    register_rest_route('wp-webello/v1/get',        '/info', array('methods' => 'GET', 'callback' => 'wp_webello_get_site_info', 'permission_callback' => 'wp_webello_check_token',));
    register_rest_route('wp-webello/v1/get',        '/posts', array('methods' => 'GET', 'callback' => 'wp_webello_get_posts', 'permission_callback' => 'wp_webello_check_token',));
    register_rest_route('wp-webello/v1/get',        '/categorys', array('methods' => 'GET', 'callback' => 'wp_webello_get_categorys', 'permission_callback' => 'wp_webello_check_token',));
	register_rest_route('wp-webello/v1/get',        '/users', array( 'methods'  => 'GET', 'callback' => 'wp_webello_get_users', 'permission_callback' => 'wp_webello_check_token', ));

	register_rest_route('wp-webello/v1/post',       '/create', array('methods'  => 'POST', 'callback' => 'wp_webello_create_post', 'permission_callback' => 'wp_webello_check_token',));
    register_rest_route('wp-webello/v1/post',       '/update', array('methods'  => 'POST', 'callback' => 'wp_webello_update_post', 'permission_callback' => 'wp_webello_check_token',));
    register_rest_route('wp-webello/v1/post',       '/delete', array('methods'  => 'DELETE', 'callback' => 'wp_webello_delete_post', 'permission_callback' => 'wp_webello_check_token',));
	register_rest_route('wp-webello/v1/send',       '/image', array('methods' => 'POST', 'callback' => 'wp_webello_image_upload', 'permission_callback' => 'wp_webello_check_token',));

    register_rest_route('wp-webello/v1/category',   '/create', array('methods'  => 'POST', 'callback' => 'wp_webello_create_category', 'permission_callback' => 'wp_webello_check_token',));
    register_rest_route('wp-webello/v1/category',   '/update', array('methods'  => 'POST', 'callback' => 'wp_webello_update_category', 'permission_callback' => 'wp_webello_check_token',));
    register_rest_route('wp-webello/v1/category',   '/delete', array('methods'  => 'DELETE', 'callback' => 'wp_webello_delete_category', 'permission_callback' => 'wp_webello_check_token',));

	register_rest_route('wp-webello/v1/user',       '/create', array( 'methods'  => 'POST', 'callback' => 'wp_webello_create_user', 'permission_callback' => 'wp_webello_check_token', ));
	register_rest_route('wp-webello/v1/user',       '/update', array( 'methods'  => 'POST', 'callback' => 'wp_webello_update_user', 'permission_callback' => 'wp_webello_check_token', ));
	register_rest_route('wp-webello/v1/user',       '/delete', array( 'methods'  => 'DELETE', 'callback' => 'wp_webello_delete_user', 'permission_callback' => 'wp_webello_check_token', ));
}
add_action('rest_api_init', 'wp_webello_register_auth_routes');


