<?php


function get_config( $option, $gallery = false, $fallback = false ) {
	global $core;

	$config = false;

	if( $gallery ) {
		$config = $gallery->get_config( $option );

		if( $config !== NULL ) return $config;
	}

	return $core->config->get( $option, $fallback );
}


function get_abspath( $path = false ) {
	global $core;

	return $core->get_abspath( $path );
}


function get_basefolder( $path = false ) {
	global $core;

	return $core->get_basefolder( $path );
}


function get_baseurl( $path = false ) {
	global $core;

	return $core->get_baseurl( $path );
}


function get_version() {
	global $core;

	$path = $core->get_abspath('system/version.txt');

	return trim(file_get_contents($path));
}

