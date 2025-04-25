<?php

// This file is part of mh.gallery
// Copyright (C) 2023-2025 maxhaesslein
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
// See the file LICENSE.md for more details.

spl_autoload_register( function($class_name) {
	global $abspath;
	include $abspath.'system/classes/'.strtolower($class_name).'.php';
} );
