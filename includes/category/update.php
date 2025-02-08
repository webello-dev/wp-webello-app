<?php
function wp_webello_update_category($request) {
	$term_id = $request->get_param('term_id');
	$name    = $request->get_param('name');
	$slug    = $request->get_param('slug');

	// بررسی وجود دسته‌بندی با این ID
	$term = get_term($term_id, 'category');
	if (is_wp_error($term) || !$term) {
		return new WP_Error('category_not_found', 'دسته‌بندی یافت نشد.', array('status' => 404));
	}

	// داده‌های به‌روزرسانی فقط مقادیری که ارسال شده‌اند
	$term_data = array();

	if (!empty($name)) {
		$term_data['name'] = $name;
	}

	if (!empty($slug)) {
		$term_data['slug'] = $slug;
	}

	// اگر هیچ داده‌ای ارسال نشده باشد، از به‌روزرسانی جلوگیری می‌کنیم
	if (empty($term_data)) {
		return new WP_Error('no_changes', 'هیچ تغییری ارسال نشده است.', array('status' => 400));
	}

	// به‌روزرسانی دسته‌بندی
	$updated_term = wp_update_term($term_id, 'category', $term_data);

	if (is_wp_error($updated_term)) {
		return new WP_Error('category_update_failed', 'ویرایش دسته‌بندی شکست خورد.', array('status' => 500));
	}

	return array('success' => true, 'term_id' => $updated_term['term_id']);
}
