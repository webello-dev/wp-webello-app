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

// 2. ویرایش پست
function wp_webello_update_post($request) {
    $post_id     = $request->get_param('post_id');
    $title       = $request->get_param('title');
    $slug        = $request->get_param('slug');
    $content     = $request->get_param('content');
    $categories  = $request->get_param('categories'); // لیست اسم دسته‌بندی‌ها
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

// 3. حذف پست
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

// 4. ایجاد دسته‌بندی
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

// 5. ویرایش دسته‌بندی
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

// 6. حذف دسته‌بندی
function wp_webello_delete_category($request) {
    $term_id = $request->get_param('term_id');

    $deleted = wp_delete_term($term_id, 'category');

    if (is_wp_error($deleted)) {
        return new WP_Error('category_delete_failed', 'حذف دسته‌بندی شکست خورد.', array('status' => 500));
    }

    return array('success' => true);
}

// آپلود تصویر شاخص
function wp_webello_upload_image($image_url) {
    $image_data = file_get_contents($image_url);
    $filename = basename($image_url);
    $upload_file = wp_upload_bits($filename, null, $image_data);

    if (!$upload_file['error']) {
        $wp_filetype = wp_check_filetype($filename, null);
        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title'     => sanitize_file_name($filename),
            'post_content'   => '',
            'post_status'    => 'inherit',
        );
        $attachment_id = wp_insert_attachment($attachment, $upload_file['file']);
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attachment_data = wp_generate_attachment_metadata($attachment_id, $upload_file['file']);
        wp_update_attachment_metadata($attachment_id, $attachment_data);
        return $attachment_id;
    }
    return false;
}
//نمایش همه پست ها
function wp_webello_get_posts($request) {
    $args = array(
        'post_type' => 'post',
        'post_status' => 'publish',
        'numberposts' => -1,
    );
    $posts = get_posts($args);

    $data = array();
    foreach ($posts as $post) {
        $data[] = array(
            'ID' => $post->ID,
            'title' => $post->post_title,
            'content' => $post->post_content,
        );
    }
    return $data;
}

function wp_webello_get_categories() {
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

// تابع برای پردازش آپلود تصویر و بازگشت لینک
function wp_webello_image_upload($request) {
    if (empty($_FILES['image'])) {
        return new WP_Error('no_image_uploaded', 'هیچ تصویری آپلود نشده است.', array('status' => 400));
    }

    // دریافت فایل تصویر از درخواست
    $uploaded_file = $_FILES['image'];

    // بررسی نوع فایل (فقط تصاویر)
    $file_type = wp_check_filetype($uploaded_file['name']);
    if (strpos($file_type['type'], 'image') === false) {
        return new WP_Error('invalid_file_type', 'لطفا فقط تصاویر را آپلود کنید.', array('status' => 400));
    }

    // بارگذاری تصویر به پوشه آپلودهای وردپرس
    $upload_dir = wp_upload_dir();
    $target_dir = $upload_dir['path'] . '/';
    $target_file = $target_dir . basename($uploaded_file['name']);

    // انتقال فایل به پوشه آپلودها
    if (move_uploaded_file($uploaded_file['tmp_name'], $target_file)) {
        // ایجاد پیوست تصویر در وردپرس
        $wp_file_type = wp_check_filetype($uploaded_file['name']);
        $attachment = array(
            'post_mime_type' => $wp_file_type['type'],
            'post_title'     => sanitize_file_name($uploaded_file['name']),
            'post_content'   => '',
            'post_status'    => 'inherit'
        );

        // وارد کردن فایل به رسانه وردپرس
        $attachment_id = wp_insert_attachment($attachment, $target_file);

        // تولید متادیتا برای فایل
        $attachment_data = wp_generate_attachment_metadata($attachment_id, $target_file);
        wp_update_attachment_metadata($attachment_id, $attachment_data);

        // بازگشت لینک تصویر
        $image_url = wp_get_attachment_url($attachment_id);
        return new WP_REST_Response(array('image_url' => $image_url), 200);
    } else {
        return new WP_Error('upload_failed', 'آپلود تصویر با خطا مواجه شد.', array('status' => 500));
    }
}
