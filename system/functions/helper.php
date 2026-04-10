<?php

// This file is part of mh.gallery
// Copyright (C) 2023-2026 maxhaesslein
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
// See the file LICENSE.md for more details.

function get_class_attribute( $classes ) {

	if( ! is_array( $classes ) ) $classes = explode( ' ', $classes );

	$classes = array_unique( $classes ); // remove double class names
	$classes = array_filter( $classes ); // remove empty class names

	if( ! count($classes) ) return '';

	return ' class="'.escape_html(implode( ' ', $classes )).'"';
}

function get_hash( $input ) {
	// NOTE: this hash is for data validation, NOT cryptography!
	// DO NOT USE FOR CRYPTOGRAPHIC PURPOSES

	$algorithm = get_config('hash_algorithm');
	$hash = hash( $algorithm, $input );

	return $hash;
}


function format_filesize( $raw_size ) {

	$units = [ 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB' ];

	$power = $raw_size > 0 ? floor(log($raw_size, 1024)) : 0;

	return number_format($raw_size / pow(1024, $power), 2, '.', ',' ).$units[$power];
}


function string_cleanup( $string ) {

	$string = trim($string);
	$string = strip_tags($string);

	return $string;
}


function hex_to_rgb($hex) {
	$hex = ltrim($hex, '#');

	if (strlen($hex) === 3) {
		$hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
	}

	[$r, $g, $b] = sscanf($hex, '%02x%02x%02x');

	return [$r, $g, $b];
}


function escape_html( $string ) {
	return htmlspecialchars( (string) $string, ENT_QUOTES, 'UTF-8' );
}


function validate_include_path( string $relative_path ): string|false {
	
	$allowed_dirs = [
		'custom/templates',
		'custom/snippets', 
		'system/site/templates',
		'system/site/snippets'
	];

	$relative_path = trim($relative_path);
	if( $relative_path === '' ) return false;

	$is_allowed = false;
	foreach( $allowed_dirs as $allowed ) {
		if( str_starts_with( $relative_path, $allowed . '/' ) ) {
			$is_allowed = true;
			break;
		}
	}
	if( ! $is_allowed ) return false;

	$abspath = dirname( __DIR__, 2 );
	$full_path = $abspath . '/' . $relative_path;

	$real_path = realpath( $full_path );
	if( $real_path === false ) return false;

	$real_abspath = realpath( $abspath );
	$is_allowed = false;
	foreach( $allowed_dirs as $dir ) {
		$allowed_path = realpath( $abspath . '/' . $dir );
		if( $allowed_path && strpos( $real_path, $allowed_path ) === 0 ) {
			$is_allowed = true;
			break;
		}
	}

	if( ! $is_allowed ) return false;

	return $full_path;
}
