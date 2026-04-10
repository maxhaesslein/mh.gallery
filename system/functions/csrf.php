<?php

// This file is part of mh.gallery
// Copyright (C) 2023-2026 maxhaesslein
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
// See the file LICENSE.md for more details.

function csrf_token(): string {
	if( session_status() === PHP_SESSION_NONE ) {
		session_start();
	}

	if( empty($_SESSION['csrf_token']) ) {
		$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
	}

	return $_SESSION['csrf_token'];
}


function csrf_validate( ?string $token = null ): bool {
	if( session_status() === PHP_SESSION_NONE ) {
		session_start();
	}

	if( $token === null ) {
		$token = sanitize_post('csrf_token');
	}

	if( ! $token ) {
		return false;
	}

	$stored_token = $_SESSION['csrf_token'] ?? '';

	return hash_equals($stored_token, $token);
}
