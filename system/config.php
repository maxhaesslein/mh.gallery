<?php

// NOTE: you can overwrite these options via custom/config.php

return [
	'debug' => false, // show or hide debug messages; should be set to false on a live system
	'site_title' => 'mh.gallery', // the page title, displayed in the browser tab
	'site_lang' => 'en', // the HTML lang tag
	'system_css' => true, // set to false to disable the systems css files
	'system_js' => true, // set to false to disable the systems js files
	'allow_overview' => true, // set to false to disable overview page with index of public galleries
	'default_image_quality' => 85, // this is the default image quality for jpg images
	'default_modern_image_quality' => 75, // this is the default image quality for modern formats, like webp and avif
	'default_image_width' => 2000, // the default image width in the single view
	'thumbnail_aspect_ratio' => 3/2, // the aspect ratio of thumbnails; width/height
	'cache_disabled' => false, // you should not disable the cache, because then every image needs to be re-generated on every load
	'cache_lifetime' => 60*60*24*30, // cache time: 30 days in seconds
	'image_extensions' => ['jpg', 'jpeg', 'png', 'webp'], // search for these extensions while loading gallery images; TODO: add avif
	'download_image_enabled' => true, // set to false to disable download of single images
	'download_gallery_enabled' => true, // set to false to disable download of the whole gallery
	'download_filetype' => 'jpg', // set to false to use original filetype
	'site_sharing_tags' => true, // add OpenGraph sharing tags to HTML head, for better link previews
	'webp_enabled' => true,
	'avif_enabled' => false, // for now, avif is disabled by default. if you enable it, there is still an additional check to see if the hosting environment supports avif. you need to be at least on PHP 8.1 to use avif; TODO: enable avif by default
	'image_sort_order' => 'filename', // sort images by this option; can be 'filename', 'filedate' or 'exifdate'; can be overwritten via gallery.txt
	'gallery_sort_order' => 'title', // sort (sub-)galleries by this option; can be 'title', 'slug', 'foldername' (folder on disk); can be overwritten via gallery.txt for sub-galleries
	'allowed_tags' => [ 'p', 'ul', 'ol', 'li', 'br', 'i', 'u', 'b', 'em', 'strong' ], // these HTML tags are allowed in text fields, all other tags are stripped (set to false to allow all tags)
	'chmod_folder' => 0775, // chmod for new folders; needs to be set as an octal value, so you need to prefix a leading zero; see https://www.php.net/manual/en/function.chmod.php#refsect1-function.chmod-parameters
	'hash_algorithm' => [ 'murmur3f', 'murmur3c', 'tiger128,3', 'sha256' ], // the first available algorithm will be used. you probably don't need to change this option
];
