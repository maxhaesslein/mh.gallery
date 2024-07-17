<?php

// This file is part of mh.gallery
// Copyright (C) 2023-2024 maxhaesslein
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
// See the file LICENSE.md for more details.


// NOTE: currently, we only hash the stored password and save it directly in the session; TODO: generate a temporary key in the cache that expires after some time, don't use the password directly.

function admin_login( $input_password ) {

	$stored_password = get_config('admin_password');

	if( password_verify($input_password, $stored_password) ) {
		$_SESSION['admin-auth'] = get_hash(get_config('admin_password'));
		return true;
	}

	return false;
}


function admin_verify() {

	$auth = $_SESSION['admin-auth'] ?? false;

	if( $auth == get_hash(get_config('admin_password')) ) {
		return true;
	}

	return false;
}


function admin_logout() {

	$_SESSION['admin-auth'] = '';
	unset($_SESSION['admin-auth']);

	return true;
}
