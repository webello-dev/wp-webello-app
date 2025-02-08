<?php
function wp_webello_create_post($request) {
	// دریافت داده‌ها از درخواست
	$title = $request->get_param('title');
	$slug = $request->get_param('slug');
	$content = $request->get_param('content');
	$category_name = $request->get_param('category_name'); // نام دسته‌بندی
	$featured_image = $request->get_param('featured_image');

	// بررسی اینکه عنوان خالی نباشد
	if (empty($title)) {
		return new WP_Error('missing_title', 'عنوان نباید خالی باشد.', array('status' => 400));
	}

	// اگر اسلاگ ارسال نشده باشد، از عنوان ساخته شود
	if (empty($slug)) {
		$slug = sanitize_title($title);
	}

	// اگر محتوایی ارسال نشده باشد، محتوای خالی تنظیم شود
	if (empty($content)) {
		$content = '';
	}

	// بررسی اینکه آیا دسته‌بندی وجود دارد یا خیر
	$category_id = null;
	if (!empty($category_name)) {
		$term = get_term_by('name', $category_name, 'category');
		if (!$term) {
			// اگر دسته‌بندی وجود ندارد، ایجادش کن
			$new_term = wp_insert_term($category_name, 'category');
			if (is_wp_error($new_term)) {
				return new WP_Error('category_creation_failed', 'خطا در ایجاد دسته‌بندی.', array('status' => 500));
			}
			$category_id = $new_term['term_id'];
		} else {
			// اگر دسته‌بندی وجود دارد، ID آن را دریافت کن
			$category_id = $term->term_id;
		}
	}

	// ایجاد پست جدید
	$post_id = wp_insert_post(array(
		'post_title'   => $title,
		'post_name'    => $slug,
		'post_content' => $content,
		'post_status'  => 'publish',
		'post_type'    => 'post',
		'post_category' => $category_id ? array($category_id) : array(), // اگر دسته‌بندی وجود داشت
	));

	if (is_wp_error($post_id)) {
		return new WP_Error('post_creation_failed', 'خطا در ایجاد پست.', array('status' => 500));
	}

	// اگر تصویر شاخص وجود دارد، تنظیم کن
	if (!empty($featured_image)) {
		$image_id = media_sideload_image($featured_image, $post_id, null, 'id');
		if (!is_wp_error($image_id)) {
			set_post_thumbnail($post_id, $image_id);
		}
	}

	return array(
		'message' => 'پست با موفقیت ایجاد شد.',
		'post_id' => $post_id,
		'category_id' => $category_id,
	);
}
