<?php

if( ! $core ) exit;

$gallery = $core->route->get('gallery');
$images = $gallery->get_images();

snippet( 'header' );

echo '<h1>'.$gallery->get_title().'</h1>';

echo '<ul>';
foreach( $images as $image ) {
	echo '<li>';

	snippet( 'thumbnail', [ 'image' => $image ] );

	echo '</li>';
}
echo' </ul>';

snippet( 'footer' );
