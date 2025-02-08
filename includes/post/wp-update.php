<?php
function wp_webello_update_post($request) {
	$post_id     = $request->get_param('post_id');
	$title       = $request->get_param('title');
	$slug        = $request->get_param('slug');
	$content     = $request->get_param('content');
	$categories  = $request->get_param('categorys'); // لیست اسم دسته‌بندی‌ها
	$featured_image = $request->get_param('featured_image');

	// بررسی وجود پست
	$post = get_post($post_id);
	if (!$post) {
		return new WP_Error('post_not_found', 'پست یافت نشد.', array('status' => 404));
	}

	// داده‌های به‌روزرسانی پست
	$post_data = array('ID' => $post_id);

	// فقط تغییرات ارسال‌شده را اعمال می‌کنیم
	if (!empty($title)) {
		$post_data['post_title'] = $title;
	}

	if (!empty($slug)) {
		$post_data['post_name'] = $slug;
	}

	if (!empty($content)) {
		$post_data['post_content'] = $content;
	}

	// به‌روزرسانی پست
	wp_update_post($post_data);

	// بررسی و به‌روزرسانی دسته‌بندی‌ها
	if (!empty($categories)) {
		$category_ids = array();

		// بررسی اینکه آیا دسته‌بندی‌ها وجود دارند یا خیر
		foreach ($categories as $category_name) {
			$term = get_term_by('name', $category_name, 'category');
			if (!$term) {
				// اگر دسته‌بندی وجود ندارد، ایجادش کن
				$new_term = wp_insert_term($category_name, 'category');
				if (is_wp_error($new_term)) {
					return new WP_Error('category_creation_failed', 'خطا در ایجاد دسته‌بندی.', array('status' => 500));
				}
				$category_ids[] = $new_term['term_id'];
			} else {
				// اگر دسته‌بندی وجود دارد، ID آن را اضافه کن
				$category_ids[] = $term->term_id;
			}
		}

		// به‌روزرسانی دسته‌بندی‌ها
		wp_set_post_categories($post_id, $category_ids);
	}

	// به‌روزرسانی تصویر شاخص
	if (!empty($featured_image)) {
		$attachment_id = wp_webello_upload_image($featured_image);
		if ($attachment_id) {
			set_post_thumbnail($post_id, $attachment_id);
		}
	}

	return array('success' => true, 'post_id' => $post_id);
}
