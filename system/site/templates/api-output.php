<?php

if( ! $core ) exit;

define( 'DOING_AJAX', true );

$template_path = 'templates/image.php';
if( file_exists(get_abspath('custom/'.$template_path)) ) {
	$include_path = get_abspath('custom/'.$template_path);
} else {
	$include_path = get_abspath('system/site/'.$template_path);
}

ob_start();
include($include_path);
$content = ob_get_contents();
ob_end_clean();

header("Content-type: application/json");
echo json_encode([
	'content' => $content,
	'title' => get_site_title(),
]);

exit;
