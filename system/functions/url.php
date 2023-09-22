<?php


function url( $path = '', $trailing_slash = true ) {
	
	$path = get_baseurl($path);

	if( $trailing_slash ) {
		$path = trailing_slash_it($path);
	}
	
	return $path;
}


function trailing_slash_it( $string ){
	// add a slash at the end, if there isn't already one ..

	$string = preg_replace( '/\/*$/', '', $string );
	$string .= '/';

	return $string;
}


function un_trailing_slash_it( $string ) {
	// remove slash at the end

	$string = preg_replace( '/\/*$/', '', $string );

	return $string;
}


function remove_fileextension( $string ) {

	$string_exp = explode('.', $string);

	if( count($string_exp) > 1 ) {
		$file_extension = array_pop($string_exp);
		$string = implode('.', $string_exp);
	}

	return $string;
}


function sanitize_string( $string, $keep_file_extension = false ) {

	$file_extension = false;
	if( $keep_file_extension ) {
		$string_exp = explode('.', $string);

		if( count($string_exp) > 1 ) {
			$file_extension = array_pop($string_exp);
			$string = implode('.', $string_exp);
		}
	}

	// remove non-printable ASCII
	$string = preg_replace('/[\x00-\x1F\x7F]/u', '', $string);

	$string = mb_strtolower($string);

	$string = str_replace(array("ä", "ö", "ü", "ß"), array("ae", "oe", "ue", "ss"), $string);

	// replace special characters with '-'
	$string = preg_replace('/[^\p{L}\p{N}]+/u', '-', $string);

	if( $keep_file_extension && $file_extension ) {
		$string .= '.'. $file_extension;
	}

	$string = trim($string, '-');
	
	return $string;
}
