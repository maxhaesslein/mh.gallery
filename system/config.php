<?php

// This file is part of mh.gallery
// Copyright (C) 2023-2024 maxhaesslein
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
// See the file LICENSE.md for more details.


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

	// cache lifetimes
	'cache_lifetime' => 60*60*24*30, // default; 30 days in seconds
	'zip_cache_lifetime' => 60*60*24*7, // gallery zip files; 7 days in seconds
	'admin_cache_lifetime' => 60*60*24, // admin login cache; 1 day in seconds

	// search for these extensions while loading gallery images (gif images will lose their animation)
	'image_extensions' => ['jpg', 'jpeg', 'png', 'webp', 'avif', 'gif'],

	// set to false to disable download of single images
	'download_image_enabled' => true,

	// set to false to disable download of the whole gallery
	'download_gallery_enabled' => true,

	// set to false to use original filetype
	'download_filetype' => 'jpg',

	// add OpenGraph sharing tags to HTML head, for better link previews
	'site_sharing_tags' => true,

	// these image formats are enabled by default. you can disable them by setting these options to false; for webp & avif see 'modern image formats' in README.md for more details
	'png_enabled' => true,
	'gif_enabled' => true,
	'webp_enabled' => true,
	'avif_enabled' => true,

	// sort images by this option; can be 'filename', 'filedate' (file modification date), 'exifdate' (date recorderd in the exif metadata if available, or file modification date otherwise), 'bridge' (sort by a .BridgeSort file, created by Adobe Bridge) or 'random' (sort randomly on every visit); can be overwritten via gallery.txt
	'image_sort_order' => 'filename',

	// sort (sub-)galleries by this option; can be 'title', 'slug', 'foldername' (folder on disk); can be overwritten via gallery.txt for sub-galleries
	'gallery_sort_order' => 'title',

	// these HTML tags are allowed in text fields, all other tags are stripped (set to false to allow all tags)
	'allowed_tags' => [ 'p', 'ul', 'ol', 'li', 'br', 'i', 'u', 'b', 'em', 'strong' ],

	// chmod for new folders; needs to be set as an octal value, so you need to prefix a leading zero; see https://www.php.net/manual/en/function.chmod.php#refsect1-function.chmod-parameters
	'chmod_folder' => 0775,

	// the first available algorithm will be used. you probably don't need to change this option
	'hash_algorithm' => [ 'murmur3f', 'murmur3c', 'tiger128,3', 'sha256' ],

	// set a hashed password as a string to enable the admin area, under the /admin path or set to false to disable the admin area. use the /admin/create-hash path to create a hashed password. see 'admin area' in the README.md for details
	'admin_password' => false,

];
