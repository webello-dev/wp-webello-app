<?php
function wp_webello_get_categorys() {
	// دریافت تمام دسته‌بندی‌ها
	$args = array(
		'taxonomy'   => 'category',
		'orderby'    => 'name',
		'hide_empty' => false, // نمایش همه دسته‌بندی‌ها حتی اگر پست ندارد
	);

	$categories = get_terms($args);

	if (is_wp_error($categories)) {
		return new WP_Error('category_fetch_failed', 'دسته‌بندی‌ها یافت نشد.', array('status' => 500));
	}

	$category_data = array();

	// پیمایش دسته‌بندی‌ها
	foreach ($categories as $category) {
		// اگر زیر دسته‌بندی‌ها داشته باشد
		$child_categories = get_terms(array(
			'taxonomy'   => 'category',
			'parent'     => $category->term_id,
			'hide_empty' => false,
		));

		$category_data[] = array(
			'category_id'    => $category->term_id,
			'category_name'  => $category->name,
			'category_slug'  => $category->slug,
			'child_categories' => !empty($child_categories) ? $child_categories : [], // زیر دسته‌بندی‌ها
		);
	}

	return $category_data;
}
