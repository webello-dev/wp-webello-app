<?php
function wp_webello_get_users(WP_REST_Request $request) {
	$users = get_users(array('fields' => array('ID', 'user_login', 'user_email', 'roles')));

	return rest_ensure_response($users);
}