<?php

if( ! $core ) exit;

$gallery = $core->route->get('gallery');

$gallery->output_zip_file();
