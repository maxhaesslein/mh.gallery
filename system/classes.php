<?php

$dir = $abspath.'system/classes/';
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
