<?php

if( ! $core ) exit;

$request = $core->route->get('request');
$request[0] = 'content';
$filepath = implode('/', $request);

$image = new Image($filepath);

$query = $_REQUEST;

$width = false;
$height = false;
$crop = false;

if( ! empty($query['width']) ) {
	$width = (int) $query['width'];
	if( $width <= 0 ) $width = false;
}

if( ! empty($query['height']) ) {
	$height = (int) $query['height'];
	if( $height <= 0 ) $height = false;
}

if( ! empty($query['crop']) ) {
	$crop = $query['crop'];
	if( $crop == 'false' ) $crop = false;
	$crop = !! $crop;
}


if( $width || $height ) {
	$image->resize($width, $height, $crop);
}


$image->output();
