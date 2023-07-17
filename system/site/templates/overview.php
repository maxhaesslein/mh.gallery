<?php

if( ! $core ) exit;

snippet( 'header' );

echo '<h1>Overview</h1>';

// TODO
$gallery_slug = $core->route->get_route('request')[0];
$gallery = $core->galleries->get_gallery($gallery_slug);
$images = $gallery->get_images();
echo' <pre>';
var_dump($images);
echo' </pre>';

snippet( 'footer' );
