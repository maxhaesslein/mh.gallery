<?php

function read_settings_file( $file ) {

	if( ! file_exists(get_abspath($file)) ) return [];

	$file_contents = file_get_contents(get_abspath($file));

	$file_contents = str_replace( ["\r\n", "\r"], "\n", $file_contents );

	$file_contents = explode( "\n", $file_contents );

	$settings = [];

	foreach( $file_contents as $line ) {

		$line = explode( ':', $line );

		if( count($line) < 2 ) continue;

		$key = trim(array_shift($line));
		$value = trim(implode(':', $line));

		$settings[$key] = $value;

	}

	return $settings;
}
