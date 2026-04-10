<?php

// This file is part of mh.gallery
// Copyright (C) 2023-2026 maxhaesslein
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
// See the file LICENSE.md for more details.

if( ! $core ) exit;

define( 'DOING_AJAX', true );

$image = $core->route->get('image');

$imageonly = sanitize_request('imageonly', '', FILTER_VALIDATE_BOOLEAN);
if( $imageonly === true ) {

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

$template_path = 'templates/image.php';

$include_path = false;

$custom_path = 'custom/'.$template_path;
$validated_custom = validate_include_path( $custom_path );
if( $validated_custom ) {
	$include_path = $validated_custom;
} else {
	$system_path = 'system/site/'.$template_path;
	$validated_system = validate_include_path( $system_path );
	if( $validated_system ) {
		$include_path = $validated_system;
	}
}

if( ! $include_path || ! file_exists($include_path) ) {
	http_response_code(404);
	echo json_encode(['error' => 'Template not found']);
	exit;
}

ob_start();
include($include_path);
$content = ob_get_contents();
ob_end_clean();

$json = [
	'content' => $content,
	'title' => get_site_title(),
	'url' => $image->get_link()
];

header("Content-type: application/json");
echo json_encode($json);

exit;