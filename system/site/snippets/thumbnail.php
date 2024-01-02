<?php

if( ! $core ) exit;

$image = $args['image'];

if( ! $image ) return;

$width = 640;
$height = (int) round($width * 2/3);
$crop = true;

$thumbnail_html = $image->resize( $width, $height, $crop )->get_html( true, false, '(min-width: 1700px) 640px, 320px' );

echo $thumbnail_html;
