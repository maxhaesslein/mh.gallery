<?php

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

