<?php
function wp_webello_update_user(WP_REST_Request $request) {
	$params = $request->get_json_params();

	$user_id = !empty($params['user_id']) ? intval($params['user_id']) : get_current_user_id();
	$current_user = wp_get_current_user();

	// بررسی مجوز ویرایش کاربر
	if (!$current_user || (!$current_user->has_cap('edit_users') && $current_user->ID != $user_id)) {
		return new WP_Error('unauthorized', 'شما اجازه ویرایش این کاربر را ندارید.', array('status' => 403));
	}

	// دریافت کاربر
	$user = get_userdata($user_id);
	if (!$user) {
		return new WP_Error('user_not_found', 'کاربر مورد نظر یافت نشد.', array('status' => 404));
	}

	// آرایه‌ای برای ذخیره اطلاعاتی که باید به‌روزرسانی شود
	$userdata = array('ID' => $user_id);

	// بروزرسانی ایمیل
	if (!empty($params['email'])) {
		$email = sanitize_email($params['email']);
		if (!is_email($email) || email_exists($email)) {
			return new WP_Error('invalid_email', 'ایمیل نامعتبر است یا قبلاً استفاده شده است.', array('status' => 400));
		}
		$userdata['user_email'] = $email;
	}

	// بروزرسانی نام کاربری (نام نمایشی)
	if (!empty($params['display_name'])) {
		$userdata['display_name'] = sanitize_text_field($params['display_name']);
	}

	// بروزرسانی رمز عبور
	if (!empty($params['password'])) {
		$userdata['user_pass'] = $params['password'];
	}

	// بروزرسانی نقش کاربر (تنها مدیران مجاز به تغییر نقش هستند)
	if (!empty($params['role']) && $current_user->has_cap('edit_users')) {
		$role = sanitize_text_field($params['role']);
		if (in_array($role, wp_roles()->roles)) {
			$user_obj = new WP_User($user_id);
			$user_obj->set_role($role);
		} else {
			return new WP_Error('invalid_role', 'نقش کاربری نامعتبر است.', array('status' => 400));
		}
	}

	// ذخیره تغییرات
	$updated_user_id = wp_update_user($userdata);
	if (is_wp_error($updated_user_id)) {
		return new WP_Error('update_failed', 'به‌روزرسانی کاربر با خطا مواجه شد.', array('status' => 500));
	}

	return array('message' => 'کاربر با موفقیت به‌روزرسانی شد.', 'user_id' => $updated_user_id);
}
