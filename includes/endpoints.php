<?php
if (!defined('ABSPATH')) {
    exit;
}

// // ثبت مسیرهای REST API
// function wp_webello_register_routes() {
//     // مسیر برای ایجاد پست
//     register_rest_route('wp-webello/v1', '/create-post', array(
//         'methods' => 'POST',
//         'callback' => 'wp_webello_create_post',
//         'permission_callback' => 'wp_webello_permission_check',
//     ));

//     // مسیر برای ایجاد دسته‌بندی
//     register_rest_route('wp-webello/v1', '/create-category', array(
//         'methods' => 'POST',
//         'callback' => 'wp_webello_create_category',
//         'permission_callback' => 'wp_webello_permission_check',
//     ));

//     // مسیر برای دریافت تمام پست‌ها
//     register_rest_route('wp-webello/v1', '/get-posts', array(
//         'methods' => 'GET',
//         'callback' => 'wp_webello_get_posts',
//         'permission_callback' => '__return_true',
//     ));
// }
// add_action('rest_api_init', 'wp_webello_register_routes');

// // تابع برای ایجاد پست
// function wp_webello_create_post($request) {
//     $params = $request->get_json_params();
//     $new_post = array(
//         'post_title'   => sanitize_text_field($params['title']),
//         'post_content' => sanitize_textarea_field($params['content']),
//         'post_status'  => 'publish',
//         'post_author'  => get_current_user_id(),
//     );

//     $post_id = wp_insert_post($new_post);
//     if ($post_id) {
//         return new WP_REST_Response(array('message' => 'پست ایجاد شد!', 'post_id' => $post_id), 200);
//     }
//     return new WP_Error('post_creation_failed', 'ایجاد پست با مشکل مواجه شد.', array('status' => 500));
// }

// // تابع برای ایجاد دسته‌بندی
// function wp_webello_create_category($request) {
//     $params = $request->get_json_params();
//     $new_cat = wp_insert_term($params['name'], 'category');
//     if (is_wp_error($new_cat)) {
//         return $new_cat;
//     }
//     return array('message' => 'دسته‌بندی ایجاد شد!', 'category_id' => $new_cat['term_id']);
// }

// // تابع برای دریافت تمام پست‌ها
// function wp_webello_get_posts($request) {
//     $args = array(
//         'post_type' => 'post',
//         'post_status' => 'publish',
//         'numberposts' => -1,
//     );
//     $posts = get_posts($args);

//     $data = array();
//     foreach ($posts as $post) {
//         $data[] = array(
//             'ID' => $post->ID,
//             'title' => $post->post_title,
//             'content' => $post->post_content,
//         );
//     }
//     return $data;
// }



function wp_webello_register_auth_routes() {
    // مسیر ورود
    register_rest_route('wp-webello/v1', '/login', array(
        'methods' => 'POST',
        'callback' => 'wp_webello_login_user',
        'permission_callback' => '__return_true',
    ));

    // مثال از مسیر حفاظت‌شده
    register_rest_route('wp-webello/v1', '/protected', array(
        'methods' => 'GET',
        'callback' => 'wp_webello_protected_route',
        'permission_callback' => 'wp_webello_check_token',
    ));
}
add_action('rest_api_init', 'wp_webello_register_auth_routes');

// ورود کاربر و ایجاد توکن
function wp_webello_login_user($request) {
    $params = $request->get_json_params();
    $username = sanitize_text_field($params['username']);
    $password = sanitize_text_field($params['password']);

    $user = wp_authenticate($username, $password);
    if (is_wp_error($user)) {
        return new WP_Error('invalid_credentials', 'نام کاربری یا رمز عبور اشتباه است.', array('status' => 401));
    }

    $token = wp_webello_generate_token($user);

    return array(
        'token' => $token,
        'user' => array(
            'id' => $user->ID,
            'username' => $user->user_login,
        ),
    );
}

// مسیر حفاظت‌شده
function wp_webello_protected_route($request) {
    $user_id = $request->get_attribute('user_id');
    return array('message' => 'دسترسی مجاز است.', 'user_id' => $user_id);
}

// بررسی توکن در درخواست
function wp_webello_check_token($request) {
    $auth_header = $request->get_header('Authorization');

    if (!$auth_header || strpos($auth_header, 'Bearer ') !== 0) {
        return new WP_Error('missing_token', 'توکن یافت نشد.', array('status' => 401));
    }

    $token = str_replace('Bearer ', '', $auth_header);
    $user_id = wp_webello_validate_token($token);

    if (!$user_id) {
        return new WP_Error('invalid_token', 'توکن نامعتبر است.', array('status' => 401));
    }

    $request->set_attribute('user_id', $user_id);
    return true;
}