<?php

if( ! $core ) exit;

define( 'DOING_AJAX', true );

$template_path = 'templates/image.php';
if( file_exists(get_abspath('custom/'.$template_path)) ) {
	$include_path = get_abspath('custom/'.$template_path);
} else {
	$include_path = get_abspath('system/site/'.$template_path);
}
include($include_path);

exit;
