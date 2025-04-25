<?php

// This file is part of mh.gallery
// Copyright (C) 2023-2025 maxhaesslein
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
// See the file LICENSE.md for more details.

if( ! $core ) exit;

$image = $core->route->get('image');
$args = $core->route->get('args');

if( ! $image->output($args) ) { // output the image and check, if this fails; if it does, show error:
	_e('could not load image');
}
