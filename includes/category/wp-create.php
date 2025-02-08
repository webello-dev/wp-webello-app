<?php
function wp_webello_create_category($request) {
	$name = $request->get_param('name');
	$slug = $request->get_param('slug');

	// اگر اسلاگ ارسال نشد، از اسم برای اسلاگ استفاده کن
	if (empty($slug)) {
		$slug = sanitize_title($name); // sanitize_title برای تبدیل نام به اسلاگ مناسب وردپرس
	}

	// ایجاد دسته‌بندی
	$term = wp_insert_term($name, 'category', array('slug' => $slug));

	// بررسی خطا در ایجاد دسته‌بندی
	if (is_wp_error($term)) {
		return new WP_Error('category_creation_failed', 'ایجاد دسته‌بندی شکست خورد.', array('status' => 500));
	}

	return array('success' => true, 'term_id' => $term['term_id']);
}
