<?php
function wp_webello_delete_user(WP_REST_Request $request) {
	$params = $request->get_json_params();
	$user_id = intval($params['user_id']);

	if (empty($user_id) || !get_userdata($user_id)) {
		return new WP_Error('invalid_user', 'کاربر موردنظر یافت نشد.', array('status' => 404));
	}

	if (!wp_delete_user($user_id)) {
		return new WP_Error('delete_failed', 'حذف کاربر با خطا مواجه شد.', array('status' => 500));
	}

	return array('message' => 'کاربر با موفقیت حذف شد.');
}