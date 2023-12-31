<?php

if( ! $core ) exit;

$image = $core->route->get('image');
$args = $core->route->get('args');

if( $args['width'] || $args['height'] ) {
	$image->resize($args['width'], $args['height'], $args['crop']);
}

if( $args['type'] ) {
	$image->set_image_type($args['type']);
}

if( $args['quality'] ) {
	$image->set_quality($args['quality']);
}

$image->output();
