<?php

if( ! $core ) exit;

define( 'DOING_AJAX', true );

$image = $core->route->get('image');
$gallery = $core->route->get('gallery');

if( ! empty($_REQUEST['imageonly']) && $_REQUEST['imageonly'] == 'true' ) {

	$image_args = [
		'width' => get_config('default_image_width'),
	];

	$url = $image->get_link();
	$title = get_site_title();
	$number = $image->get_number();

	$prev_image = $image->get_adjacent_image('prev');
	$prev = false;
	if( $prev_image ) {
		$prev = [
			'url' => $prev_image->get_link(),
			'slug' => $prev_image->get_slug(),
			'gallerySlug' => $gallery->get_slug(),
		];
	}

	$next_image = $image->get_adjacent_image('next');
	$next = false;
	if( $next_image ) {
		$next = [
			'url' => $next_image->get_link(),
			'slug' => $next_image->get_slug(),
			'gallerySlug' => $gallery->get_slug(),
		];
	}

	$json = [
		'content' => $image->get_html( $image_args ),
		'url' => $url,
		'title' => $title,
		'number' => $number,
		'prev' => $prev,
		'next' => $next,
	];

	header("Content-type: application/json");
	echo json_encode($json);

	exit;
}

$prev_image = $image->get_adjacent_image('prev');
$next_image = $image->get_adjacent_image('next');

$template_path = 'templates/image.php';
if( file_exists(get_abspath('custom/'.$template_path)) ) {
	$include_path = get_abspath('custom/'.$template_path);
} else {
	$include_path = get_abspath('system/site/'.$template_path);
}

ob_start();
include($include_path);
$content = ob_get_contents();
ob_end_clean();

$json = [
	'content' => $content,
	'title' => get_site_title(),
];

if( $prev_image ) $json['prev_image_url'] = $prev_image->get_link();
if( $next_image ) $json['next_image_url'] = $next_image->get_link();

header("Content-type: application/json");
echo json_encode($json);

exit;
