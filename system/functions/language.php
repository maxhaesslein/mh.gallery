<?php

// This file is part of mh.gallery
// Copyright (C) 2023-2025 maxhaesslein
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
// See the file LICENSE.md for more details.


function _e($string) {
	echo __($string);
}

function __( $string, $fallback = null ) {
	global $core;

	$text = $core->language->get($string, $fallback);

	return $text;
}

function get_language_code() {
	global $core;

	$language_code = $core->language->get_language_code();

	return $language_code;
}
