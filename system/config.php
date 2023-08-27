<?php

// NOTE: you can overwrite these options via custom/config.php

return [
	'debug' => false, // show or hide debug messages; should be set to false on a live system
	'site_title' => 'mh.gallery', // the page title, displayed in the browser tab
	'site_lang' => 'en', // the HTML lang tag
	'system_css' => true, // set to false to disable the systems css files
	'system_js' => true, // set to false to disable the systems js files
	'jpg_quality' => 88,
	'png_to_jpg' => true,
	'image_background_color' => '#fff', // backgroundcolor for transparent images, when converting to jpg
	'cache_lifetime' => 60*60*24*30, // cache time: 30 days in seconds
	'cache_disabled' => false,
];
