<?php

// This file is part of mh.gallery
// Copyright (C) 2023-2024 maxhaesslein
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
// See the file LICENSE.md for more details.

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
