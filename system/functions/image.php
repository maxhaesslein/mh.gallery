<?php

// This file is part of mh.gallery
// Copyright (C) 2023-2025 maxhaesslein
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
// See the file LICENSE.md for more details.


// NOTE: internally in our image array, the key of an image is their slug, but with a leading 'img-'; this is important to have the key exist as a string, instead of an int

function get_image_key_from_slug( $image_slug ) {
	
	$image_key = 'img-'.$image_slug;

	return $image_key;
}

function get_image_slug_from_key( $image_key ) {

	// remove leading 'img-'
	$image_key = explode('img-', $image_key);
	if( count($image_key) > 1) unset($image_key[0]);
	$image_slug = implode('img-', $image_key);
	
	return $image_slug;
}

