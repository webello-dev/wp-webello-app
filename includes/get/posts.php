<?php
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
