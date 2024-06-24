<?php

// This file is part of mh.gallery
// Copyright (C) 2023-2024 maxhaesslein
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
// See the file LICENSE.md for more details.


function get_config( $option, $fallback = false ) {
	global $core;

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


function get_template() {
	global $core;

	$template = $core->route->get('template_name');

	return $template;
}


function is_template( $test_template ) {
	return ( get_template() == $test_template );
}

