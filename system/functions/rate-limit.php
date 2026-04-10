<?php

// This file is part of mh.gallery
// Copyright (C) 2023-2026 maxhaesslein
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
// See the file LICENSE.md for more details.

function check_rate_limit( string $type ): bool {

	$max_attempts = get_config('rate-limit_max_attempts');

	$ip = get_client_ip();

	if( ! $ip ) {
		return true;
	}

	$cache = new Cache( 'rate-limit', $ip.'_'.$type );

	if( ! $cache->exists() ) {
		return true;
	}

	$failure_count = (int) $cache->get_data();

	if( $failure_count >= $max_attempts ) {
		return false;
	}

	return true;
}


function record_failed_attempt( string $type ): bool {

	$ip = get_client_ip();

	if( ! $ip ) {
		return false;
	}

	$cache = new Cache( 'rate-limit', $ip.'_'.$type );

	$failure_count = 1;

	if( $cache->exists() ) {
		$existing_count = (int) $cache->get_data();
		$failure_count = $existing_count + 1;
	}

	$cache->add_data( $failure_count );
	$cache->refresh_lifetime();

	return true;
}


function reset_rate_limit( string $type ): bool {

	$ip = get_client_ip();

	if( ! $ip ) {
		return false;
	}

	$cache = new Cache( 'rate-limit', $ip.'_'.$type );

	$cache->remove();

	return true;
}


function get_client_ip(): string|false {

	if( ! empty( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
		$ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
	} elseif( ! empty( $_SERVER['HTTP_X_REAL_IP'] ) ) {
		$ip = $_SERVER['HTTP_X_REAL_IP'];
	} elseif( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		$ip = explode( ',', $ip );
		$ip = trim( $ip[0] );
	} elseif( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
		$ip = $_SERVER['REMOTE_ADDR'];
	} else {
		return false;
	}

	$ip = filter_var( $ip, FILTER_VALIDATE_IP );

	if( ! $ip ) {
		return false;
	}

	return $ip;
}
