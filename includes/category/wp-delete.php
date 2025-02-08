<?php
function wp_webello_delete_category($request) {
	$term_id = $request->get_param('term_id');

	$deleted = wp_delete_term($term_id, 'category');

	if (is_wp_error($deleted)) {
		return new WP_Error('category_delete_failed', 'حذف دسته‌بندی شکست خورد.', array('status' => 500));
	}

	return array('success' => true);
}
