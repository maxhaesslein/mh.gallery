<?php

// NOTE: you can overwrite these options via custom/config.php

return [

	// show or hide debug messages; should be set to false on a live system
	'debug' => false,

	// the page title, displayed in the browser tab
	'site_title' => 'mh.gallery',

	// the HTML lang tag
	'site_lang' => 'en',

	// add links to external content, like this:
	// 'footer_menu' => [ ['title' => 'About', 'url' => 'https://www.maxhaesslein.de', 'target' => '_blank'], ['title' => 'Imprint', 'url' => 'https://www.maxhaesslein.de/imprint/', 'target' => '_blank'] ],
	'footer_menu' => [],

	// set to false to disable the systems css files
	'system_css' => true,

	// set to false to disable the systems js files
	'system_js' => true,

	// set to false to disable overview page with index of public galleries
	'allow_overview' => true,

	// this is the default image quality for jpg images
	'default_image_quality' => 85,

	// this is the default image quality for modern formats, like webp and avif
	'default_modern_image_quality' => 75,

	// the default image width in the single view
	'default_image_width' => 2000,

	// the aspect ratio of thumbnails; width/height
	'thumbnail_aspect_ratio' => 3/2,

	// you should not disable the cache, because then every image needs to be re-generated on every load
	'cache_disabled' => false,

	// default cache time: 30 days in seconds
	'cache_lifetime' => 60*60*24*30,

	// search for these extensions while loading gallery images
	// TODO: add avif
	'image_extensions' => ['jpg', 'jpeg', 'png', 'webp'],

	// set to false to disable download of single images
	'download_image_enabled' => true,

	// set to false to disable download of the whole gallery
	'download_gallery_enabled' => true,

	// set to false to use original filetype
	'download_filetype' => 'jpg',

	// add OpenGraph sharing tags to HTML head, for better link previews
	'site_sharing_tags' => true,

	// webp is enabled by default. you can disable it by setting this option to false; if neither webp nor avif is enabled, only jpg files will be generated
	'webp_enabled' => true,

	// for now, avif is disabled by default. if you enable it, there is still an additional check to see if the hosting environment supports avif. you need to be at least on PHP 8.1 to use avif
	// TODO: enable avif by default
	'avif_enabled' => false,

	// sort images by this option; can be 'filename', 'filedate' or 'exifdate'; can be overwritten via gallery.txt
	'image_sort_order' => 'filename',

	// sort (sub-)galleries by this option; can be 'title', 'slug', 'foldername' (folder on disk); can be overwritten via gallery.txt for sub-galleries
	'gallery_sort_order' => 'title',

	// these HTML tags are allowed in text fields, all other tags are stripped (set to false to allow all tags)
	'allowed_tags' => [ 'p', 'ul', 'ol', 'li', 'br', 'i', 'u', 'b', 'em', 'strong' ],

	// chmod for new folders; needs to be set as an octal value, so you need to prefix a leading zero; see https://www.php.net/manual/en/function.chmod.php#refsect1-function.chmod-parameters
	'chmod_folder' => 0775,

	// the first available algorithm will be used. you probably don't need to change this option
	'hash_algorithm' => [ 'murmur3f', 'murmur3c', 'tiger128,3', 'sha256' ],

];
