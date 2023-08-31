<?php

if( ! $core ) exit;

$gallery = $core->route->get('gallery');

if( ! $gallery->is_zipfile_ready() ) {
	debug('zipfile not ready');
	exit;
}

$gallery->output_zipfile();
exit;
