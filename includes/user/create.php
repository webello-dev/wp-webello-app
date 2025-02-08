<?php
function wp_webello_create_user(WP_REST_Request $request) {
	$params = $request->get_json_params();
	$username = sanitize_text_field($params['username']);
	$email    = sanitize_email($params['email']);
	$password = $params['password'];
	$role     = !empty($params['role']) ? sanitize_text_field($params['role']) : 'subscriber';

	if (empty($username) || empty($email) || empty($password)) {
		return new WP_Error('missing_fields', 'لطفا تمام فیلدها را پر کنید.', array('status' => 400));
	}

	if (username_exists($username) || email_exists($email)) {
		return new WP_Error('user_exists', 'این نام کاربری یا ایمیل قبلاً استفاده شده است.', array('status' => 400));
	}

	$user_id = wp_create_user($username, $password, $email);

	if (is_wp_error($user_id)) {
		return new WP_Error('user_creation_failed', 'ایجاد کاربر با خطا مواجه شد.', array('status' => 500));
	}

	// تنظیم نقش کاربر
	$user = new WP_User($user_id);
	$user->set_role($role);

	return array('message' => 'کاربر با موفقیت ایجاد شد.', 'user_id' => $user_id);
}