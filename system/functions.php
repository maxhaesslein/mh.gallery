<?php

// This file is part of mh.gallery
// Copyright (C) 2023-2025 maxhaesslein
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
// See the file LICENSE.md for more details.


$dir = $abspath.'system/functions/';
// CLEANUP: include all the relevant files by name instead of including all files in the $dir to make the code a bit safer
if( $handle = opendir($dir) ){
	while( false !== ($file = readdir($handle)) ){
		if( '.' === $file ) continue;
		if( '..' === $file ) continue;

		$file_extension = pathinfo( $dir.$file, PATHINFO_EXTENSION );
		if( strtolower($file_extension) != 'php' ) continue;

		include_once( $dir.$file );
	}
	closedir($handle);
}
