<?php
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
function wp_webello_protected_route($request) {
    $user_id = $request->get_param('user_id');
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

    $request->set_param('user_id', $user_id);
    return true;
}
