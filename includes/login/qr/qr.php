<?php

add_action( 'login_form', 'generate_qr_login' );

function generate_qr_login() {
	// جلوگیری از نمایش فرم ورود پیش‌فرض
	ob_clean();

	// تولید لینک یکتا برای QR Code
	$unique_id = uniqid('qr_');
	$login_url = site_url('/wp-json/wp-webello/v1/auth/' . $unique_id);
	$logo_url = plugins_url('assets/logo.png', plugin_dir_path(dirname(__FILE__, 2))); // مسیر اصلی پلاگین

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
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود با QR Code</title>
    <style>
        @font-face {
            font-family: "IRANSans";
            src: url("https://cdn.jsdelivr.net/gh/rastikerdar/vazir-font/Vazir.woff2") format("woff2");
        }
        body {
            background-color: #000;
            color: #FFCC3B;
            font-family: "IRANSans", Arial, sans-serif;
            text-align: center;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .container {
            background: #111;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(255, 204, 59, 0.4);
            width: 90%;
            max-width: 800px;
            display: flex;
            flex-direction: row;
            justify-content: space-between;
        }
        .logo {
            margin-bottom: 20px;
        }
        .logo img {
            max-width: 150px;
        }
        .left-column, .right-column {
            flex: 1;
            padding: 20px;
        }
        .left-column {
            border-right: 2px solid #FFCC3B;
        }
        h1 {
            font-size: 20px;
            margin-bottom: 20px;
            color: #FFCC3B;
        }
        p {
            font-size: 14px;
            line-height: 1.8;
            color: #FFF;
        }
        .qr-code {
            margin: 20px auto;
        }
        .highlight {
            color: #FFCC3B;
            font-weight: bold;
        }
        footer {
            margin-top: 20px;
            font-size: 12px;
            color: #888;
        }
        footer a {
            color: #FFCC3B;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="left-column">
            <div class="logo">
                <img src="' . $logo_url . '" alt="لوگوی شرکت">
            </div>
            <h1>آموزش استفاده از QR Code</h1>
            <p>1. اپلیکیشن مخصوص شرکت را دانلود و نصب کنید.</p>
            <p>2. اپلیکیشن را باز کرده و گزینه <span class="highlight">اسکن QR Code</span> را انتخاب کنید.</p>
            <p>3. دوربین خود را روی QR Code قرار داده و منتظر ورود خودکار بمانید.</p>
            <p>برای دانلود اپلیکیشن، <a href="https://example.com/app" target="_blank">اینجا</a> کلیک کنید.</p>
        </div>
        <div class="right-column">
            <h1>ورود با QR Code</h1>
            <div class="qr-code">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($login_url) . '" alt="QR Code" width="200" height="200">
            </div>
            <p>QR Code بالا را اسکن کنید تا وارد حساب کاربری شوید.</p>
            <p>'. $login_url .'</p>
        </div>
    </div>
    <script>
        setInterval(function() {
            fetch("' . site_url('/wp-json/wp-webello/v1/auth/status?unique_id=') . $unique_id . '")
                .then(response => response.json())
                .then(data => {
                    if (data.logged_in) {
                        window.location.href = "' . site_url('/wp-admin') . '";
                    }
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
