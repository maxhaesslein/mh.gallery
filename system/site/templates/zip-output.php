<?php

if( ! $core ) exit;

$gallery = $core->route->get('gallery');

// TODO: use cachefile with a lifetime of 7 days;
// if it exists, output it
// if no cache file exists, create a new zip with all images in original quality, then output it

var_dump($gallery->get_zip_filename());
