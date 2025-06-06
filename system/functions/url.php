<?php

// This file is part of mh.gallery
// Copyright (C) 2023-2025 maxhaesslein
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
// See the file LICENSE.md for more details.


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

	// replace space with '_'
	$string = str_replace( ' ', '_', $string );

	// remove non-printable ASCII
	$string = preg_replace('/[\x00-\x1F\x7F]/u', '', $string);

	// change to lowercase
	$string = mb_strtolower($string);

	// replace german umlauts
	$string = str_replace(array("ä", "ö", "ü", "ß"), array("ae", "oe", "ue", "ss"), $string);

	// replace special characters with '-'
	$string = preg_replace('/[^\p{L}\p{N}_~]+/u', '-', $string);

	// change '-_' and '_-' to only '_'
	$string = str_replace( ['-_', '_-'], '_', $string );

	// replace multipe '_' with one '_'
	$string = preg_replace( '/_+/', '_', $string );

	// replace multipe '-' with one '-'
	$string = preg_replace( '/-+/', '-', $string );

	if( $keep_file_extension && $file_extension ) {
		$string .= '.'. $file_extension;
	}

	$string = trim($string, '-');
	
	return $string;
}


function get_current_url() {
	return (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
}

function explode_url( $url ) {

	$url_parts = parse_url($url);

	$query = [];
	parse_str($url_parts['query'], $query);

	$url_parts['query'] = $query;

	return $url_parts;
}

function implode_url( $url_parts ) {

	// NOTE: $url_parts['query'] is an associative array instead of a string!

	$scheme = ! empty($url_parts['scheme']) ? $url_parts['scheme'] . '://' : '';
	$host = ! empty($url_parts['host']) ? $url_parts['host'] : '';
	$port = ! empty($url_parts['port']) ? ':' . $url_parts['port'] : '';
	$user = ! empty($url_parts['user']) ? $url_parts['user'] : '';
	$pass = ! empty($url_parts['pass']) ? ':' . $url_parts['pass']  : '';
	$pass = ($user || $pass) ? "$pass@" : '';
	$path = ! empty($url_parts['path']) ? $url_parts['path'] : '';
	$query = ! empty($url_parts['query']) ? '?' . http_build_query($url_parts['query']) : '';
	$fragment = ! empty($url_parts['fragment']) ? '#' . $url_parts['fragment'] : '';

	return "$scheme$user$pass$host$port$path$query$fragment";
}

function redirect( $target_path = false ) {

	$target = url($target_path);

	header('Location: '.$target);
	exit;

}
