<?php

add_action( 'login_form', 'generate_qr_login' );

function generate_qr_login() {
	// جلوگیری از نمایش فرم ورود پیش‌فرض
	ob_clean();

	// تولید لینک یکتا برای QR Code
	$unique_id = uniqid('qr_');
	$login_url = site_url('/wp-json/wp-webello/v1/auth/' . $unique_id);

	// ذخیره لینک یکتا در دیتابیس یا حافظه موقت
	global $wpdb;
	$wpdb->insert(
		$wpdb->prefix . 'qr_login_links',
		array(
			'unique_id'  => $unique_id,
			'created_at' => current_time('mysql'),
			'is_used'    => 0
		)
	);

	// نمایش استایل صفحه
	echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <title>QR Login</title>
        <style>
            body {
                background-color: #000;
                color: #fff;
                font-family: Arial, sans-serif;
                text-align: center;
                margin: 0;
                padding: 0;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
            }
            .qr-container {
                background: rgba(255, 255, 255, 0.1);
                padding: 20px;
                border-radius: 10px;
                box-shadow: 0 0 20px rgba(255, 255, 255, 0.2);
            }
            h1 {
                font-size: 24px;
                margin-bottom: 20px;
                color: #FFCC3B;
            }
            canvas {
                margin: 20px auto;
            }
        </style>
        <script src="https://cdn.jsdelivr.net/npm/qrcode/build/qrcode.min.js"></script>
    </head>
    <body>
        <div class="qr-container">
            <h1>Scan the QR Code to Log In</h1>
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($login_url) . '&bgcolor=FFCC3B&color=000" alt="QR Code" width="300" height="300">

            <p>Generated Link: <strong>' . $login_url . '</strong></p>
        </div>
        <script>
            // بررسی وضعیت لاگین
            setInterval(function() {
                fetch("' . site_url('/wp-json/wp-webello/v1/auth/status?unique_id=') . $unique_id . '")
                    .then(response => response.json())
                    .then(data => {
                        console.log("Response from status API:", data); // لاگ وضعیت
                        if (data.logged_in) {
                            window.location.href = "' . site_url('/wp-admin') . '";
                        }
                    })
                    .catch(error => {
                        console.error("Error fetching login status:", error); // خطا در درخواست
                    });
            }, 5000);
        </script>
    </body>
    </html>';

	exit;
}



// ایجاد جدول برای ذخیره لینک‌های یکتا


function handle_qr_auth($data) {
	global $wpdb;
	$unique_id = sanitize_text_field($data['unique_id']);
	$jwt_token = sanitize_text_field($data->get_param('token'));

	// بررسی یکتا بودن لینک
	$table_name = $wpdb->prefix . 'qr_login_links';
	$result = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE unique_id = %s AND is_used = 0", $unique_id));

	if (!$result) {
		return new WP_Error('invalid_link', 'QR link is invalid or already used', array('status' => 400));
	}
	$wpdb->update($table_name, array('is_used' => 1), array('unique_id' => $unique_id));

	// اعتبارسنجی JWT
	$user_id = wp_webello_validate_token($jwt_token);
	if (!$user_id) {
		return new WP_Error('invalid_token', 'Invalid JWT token', array('status' => 403));
	}
	$wpdb->update($table_name, array('is_login' => $user_id), array('unique_id' => $unique_id));
	return array('success' => true, 'message' => 'Logged in successfully');
}
function check_login_status(WP_REST_Request $request) {
	global $wpdb;

	// دریافت unique_id از درخواست
	$unique_id = sanitize_text_field($request->get_param('unique_id'));

	// جستجوی unique_id در دیتابیس
	$table_name = $wpdb->prefix . 'qr_login_links';
	$result = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE unique_id = %s AND is_used = 1", $unique_id));

	// اگر لینک معتبر و هنوز استفاده نشده باشد
	if ($result) {
		$user_id = $result->is_login;
		if ($user_id) {
			wp_set_current_user($user_id);
			wp_set_auth_cookie($user_id);
			return array('logged_in' => true); // کاربر وارد سیستم شده است
		}
	}

	return array('logged_in' => false); // اگر لینک پیدا نشد یا کاربر وارد نشده باشد
}
