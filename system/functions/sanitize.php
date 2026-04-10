<?php
// This file is part of mh.gallery
// Copyright (C) 2023-2026 maxhaesslein
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
// See the file LICENSE.md for more details.

function sanitize_value( $value, $default = null, $filter = FILTER_DEFAULT ) {
	if( $filter === FILTER_VALIDATE_INT || $filter === FILTER_VALIDATE_FLOAT || $filter === FILTER_VALIDATE_BOOLEAN ) {
		$result = filter_var( $value, $filter );
		return $result === false ? $default : $result;
	}
	
	return filter_var( $value, $filter, ['options' => ['default' => $default]] );
}

function sanitize_get( $key, $default = null, $filter = FILTER_DEFAULT ) {
	if( ! isset($_GET[$key]) ) {
		return $default;
	}
	
	$value = $_GET[$key];
	return sanitize_value( $value, $default, $filter );
}

function sanitize_post( $key, $default = null, $filter = FILTER_DEFAULT ) {
	if( ! isset($_POST[$key]) ) {
		return $default;
	}
	
	$value = $_POST[$key];
	return sanitize_value( $value, $default, $filter );
}

function sanitize_request( $key, $default = null, $filter = FILTER_DEFAULT ) {
	if( ! isset($_REQUEST[$key]) ) {
		return $default;
	}
	
	$value = $_REQUEST[$key];
	return sanitize_value( $value, $default, $filter );
}
