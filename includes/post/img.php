<?php

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
