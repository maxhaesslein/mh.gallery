<?php

if( ! $core ) exit;

snippet( 'header' );

echo '<h1>Overview</h1>';

// TODO
$gallery_slug = $core->route->get('request')[0];
// TODO: gallery should be loaded in the route? we may want to use it for the image and the single route as well
$gallery = $core->galleries->get_gallery($gallery_slug);
$images = $gallery->get_images();

echo '<ul>';
foreach( $images as $image ) {
	echo '<li>';

	snippet( 'thumbnail', [ 'image' => $image ] );

	echo '</li>';
}
echo' </ul>';

snippet( 'footer' );
