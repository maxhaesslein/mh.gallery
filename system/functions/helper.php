<?php

function get_class_attribute( $classes ) {

	if( ! is_array( $classes ) ) $classes = explode( ' ', $classes );

	$classes = array_unique( $classes ); // remove double class names
	$classes = array_filter( $classes ); // remove empty class names

	if( ! count($classes) ) return '';

	return ' class="'.implode( ' ', $classes ).'"';
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
