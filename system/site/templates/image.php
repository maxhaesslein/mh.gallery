<?php

if( ! $core ) exit;

$request = $core->route->get('request');

unset($request[0]); // remove img/ route from request
$image_name = array_pop($request);
$gallery_slug = implode('/', $request);

$gallery = $core->galleries->get_gallery($gallery_slug);

$image_name = explode('.', $image_name);
$type = array_pop($image_name);
$image_name = explode('_', implode('.', $image_name));
$args = array_pop($image_name);
$image_name = implode('_', $image_name);

$args = explode('-', $args);

$size = array_shift($args);
$size = explode('x', $size);

$width = (int) $size[0];
$height = (int) $size[1];

$crop = array_shift($args);
if( $crop == 'crop' ) {
	$quality = array_pop($args);
	$crop = true;
} else {
	$quality = $crop;
	$crop = false;
}

$quality = (int) $quality;
if( $quality <= 0 ) $quality = false;

$image = $gallery->get_image($image_name);

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
