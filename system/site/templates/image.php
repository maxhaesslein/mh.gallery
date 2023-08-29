<?php

if( ! $core ) exit;

snippet( 'header' );


// TODO
$gallery_slug = $core->route->get('request')[0];
// TODO: gallery should be loaded in the route? we may want to use it for the image and the single route as well
$gallery = $core->galleries->get_gallery($gallery_slug);

echo '<h1>'.$gallery->get_title().'</h1>';

$image_slug = $core->route->get('request')[1];
$image = $gallery->get_image($image_slug);

$image->resize(1200);
echo $image->get_html();


snippet( 'footer' );
