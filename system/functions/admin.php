<?php

// This file is part of mh.gallery
// Copyright (C) 2023-2025 maxhaesslein
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
// See the file LICENSE.md for more details.


function admin_login( $input_password ) {

	$stored_password = get_config('admin_password');

	if( password_verify($input_password, $stored_password) ) {

		$cache_id = uniqid();

		$cache_data = password_hash( get_config('admin_password'), PASSWORD_DEFAULT );

		$cache = new Cache( 'admin', $cache_id );
		$cache->add_data( $cache_data );

		if( $cache->exists() && $cache->get_data() == $cache_data ) {
			$_SESSION['admin-auth'] = $cache_id;
			return true;
		}

	}

	return false;
}


function admin_verify() {

	$cache_id = $_SESSION['admin-auth'] ?? false;

	if( ! $cache_id ) return false;

	$cache = new Cache( 'admin', $cache_id );

	if( ! $cache->exists() ) return false;

	$cache_data = $cache->get_data();

	if( ! $cache_data ) return false;

	if( ! password_verify( get_config('admin_password'), $cache_data ) ) return false;

	return true;
}


function admin_logout() {

	$cache_id = $_SESSION['admin-auth'] ?? false;
	$cache = new Cache( 'admin', $cache_id );
	$cache->remove();

	$_SESSION['admin-auth'] = '';
	unset($_SESSION['admin-auth']);

	return true;
}
