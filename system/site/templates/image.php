<?php

if( ! $core ) exit;

$request = $core->route->get('request');

unset($request[0]); // remove img/ route from request
$image_name = array_pop($request);
$gallery_slug = implode('/', $request);

$gallery = $core->galleries->get_gallery($gallery_slug);

$image = new Image($image_name, $gallery);

$query = $_REQUEST;

$width = false;
$height = false;
$quality = false;
$type = false;
$crop = false;

if( ! empty($query['width']) ) {
	$width = (int) $query['width'];
	if( $width <= 0 ) $width = false;
}

if( ! empty($query['height']) ) {
	$height = (int) $query['height'];
	if( $height <= 0 ) $height = false;
}

if( ! empty($query['quality']) ) {
	$quality = (int) $query['quality'];
	if( $quality <= 0 ) $quality = false;
}

if( ! empty($query['type']) ) {
	$type = $query['type'];
}

if( ! empty($query['crop']) ) {
	$crop = $query['crop'];
	if( $crop == 'false' ) $crop = false;
	$crop = !! $crop;
}


if( $width || $height ) {
	$image->resize($width, $height, $crop);
}

if( $type ) {
	$image->set_image_type($type);
}

if( $quality ) {
	$image->set_quality($quality);
}

$image->output();
