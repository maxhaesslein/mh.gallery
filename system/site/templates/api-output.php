<?php

// This file is part of mh.gallery
// Copyright (C) 2023-2024 maxhaesslein
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
// See the file LICENSE.md for more details.

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
