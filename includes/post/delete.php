<?php
function wp_webello_delete_post($request) {
	$post_id = $request->get_param('post_id');

	// بررسی وجود پست
	if (!get_post($post_id)) {
		return new WP_Error('post_not_found', 'پست یافت نشد.', array('status' => 404));
	}

	// حذف پست
	wp_delete_post($post_id, true);

	return array('success' => true);
}
