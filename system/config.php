<?php

// NOTE: you can overwrite these options via custom/config.php

return [
	'debug' => false, // show or hide debug messages; should be set to false on a live system
	'site_title' => 'mh.gallery', // the page title, displayed in the browser tab
	'site_lang' => 'en', // the HTML lang tag
	'system_css' => true, // set to false to disable the systems css files
	'system_js' => true, // set to false to disable the systems js files
	'allow_overview' => true, // set to false to disable overview page with index of public galleries
	'default_image_quality' => 88,
	'cache_lifetime' => 60*60*24*30, // cache time: 30 days in seconds
	'cache_disabled' => false,
	'image_extensions' => ['jpg', 'jpeg', 'png', 'webp'] // search for these extensions while loading gallery images; TODO: add avif
];
