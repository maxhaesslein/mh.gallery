<?php

if( ! $core ) exit;

$image = $core->route->get('image');
$args = $core->route->get('args');

if( ! $image->output($args) ) { // output the image and check, if this fails; if it does, show error:
	echo 'could not load image';
}
