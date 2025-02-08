<?php
// افزودن استایل سفارشی به پنل مدیریت
function webello_admin_styles() {
	$css_path = plugin_dir_url(__FILE__) . '../../../assets/css/admin-style.css';
	wp_enqueue_style('webello-admin-style', $css_path);
}
add_action('admin_enqueue_scripts', 'webello_admin_styles');

function webello_add_logo_to_admin_bar($wp_admin_bar) {
	// تنظیمات برای اضافه کردن لوگو به نوار ابزار
	$args = array(
		'id'    => 'webello_logo',  // شناسه یکتا برای لینک
		'title' => '<img src="' . plugin_dir_url(__FILE__) . '../../../assets/logo.png" alt="Webello Logo" style="max-width: 80px; height: auto; border-radius: 5px;">',  // افزودن تصویر لوگو
		'href'  => admin_url('index.php'),  // لینک مقصد که می‌تواند صفحه پیشخان باشد
		'meta'  => array('class' => 'webello-logo-bar')  // اضافه کردن کلاس سفارشی برای استایل
	);

	// افزودن لوگو به نوار ابزار
	$wp_admin_bar->add_node($args);
}

add_action('admin_bar_menu', 'webello_add_logo_to_admin_bar', 10);

