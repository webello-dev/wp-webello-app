<?php
function wp_webello_get_site_info($request) {
	$user_id = $request->get_param('user_id');
	$user_info = get_userdata($user_id);
	return array(
		'site_name'     => get_bloginfo('name'),              // نام سایت
		'site_url'      => get_bloginfo('url'),               // آدرس سایت
		'admin_email'   => get_bloginfo('admin_email'),       // ایمیل مدیر سایت
		'description'   => get_bloginfo('description'),       // شعار سایت
		'wp_version'    => get_bloginfo('version'),           // نسخه وردپرس
		'theme_name'    => wp_get_theme()->get('Name'),       // نام پوسته فعال
		'theme_version' => wp_get_theme()->get('Version'),    // نسخه پوسته فعال
		'site_logo'     => get_site_icon_url(),               // لوگوی سایت
		'ID'        => $user_info->ID,
		'username'  => $user_info->user_login,
		'email'     => $user_info->user_email,
		'name'      => $user_info->display_name,
		'roles'     => $user_info->roles, // نقش‌های کاربر
		'registered'=> $user_info->user_registered, // تاریخ ثبت‌نام
	);
}