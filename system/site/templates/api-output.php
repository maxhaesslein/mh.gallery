<?php

if( ! $core ) exit;

define( 'DOING_AJAX', true );

$image = $core->route->get('image');

if( ! empty($_REQUEST['imageonly']) && $_REQUEST['imageonly'] == 'true' ) {

	$image_args = [
		'width' => get_config('default_image_width'),
	];

	$json = [
		'content' => $image->get_html( $image_args ),
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
