<?php

if( ! $core ) exit;

$image = $args['image'];

if( ! $image ) return;

$width = 640;
$height = (int) round($width * (1/get_config('thumbnail_aspect_ratio')));

$image_args = [
	'width' => $width,
	'height' => $height,
	'crop' => true
];
$thumbnail_html = $image->get_html( $image_args, '(min-width: 1700px) 640px, 320px' );

echo $thumbnail_html;
