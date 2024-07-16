<?php

// This file is part of mh.gallery
// Copyright (C) 2023-2024 maxhaesslein
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
// See the file LICENSE.md for more details.


session_start(); // we use this for secret links and the admin area

$global_script_execution_start_time = hrtime(true); // this is used in the measure_execution_time() function to render the execution time

$abspath = realpath(dirname(__FILE__)).'/';
$abspath = preg_replace( '/system\/$/', '', $abspath );

include_once( $abspath.'system/functions.php' );
include_once( $abspath.'system/classes.php' );

$core = new Core( $abspath );

include( $core->route->get('template_include') );
