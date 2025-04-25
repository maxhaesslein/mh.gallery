<?php

// This file is part of mh.gallery
// Copyright (C) 2023-2025 maxhaesslein
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
// See the file LICENSE.md for more details.

function debug( ...$messages ) {

	if( ! get_config('debug') ) return;

	echo '<div class="debugmessage"><strong class="debugmessage-head">DEBUGMESSAGE</strong><pre>';
	$first = true;
	foreach( $messages as $message ) {
		if( is_array($message) || is_object($message) ) $message = var_export($message, true);
		if( ! $first ) echo '<br>';
		echo $message;
		$first = false;
	}
	echo '</pre></div>';

}


function measure_execution_time() {

	if( ! get_config('debug') ) return;

	global $global_script_execution_start_time;

	if( ! $global_script_execution_start_time ) return;

	$diff = hrtime(true)-$global_script_execution_start_time;
	$diff /= 1e+6; // nanoseconds to milliseconds

	?><!-- execution time was <?= $diff ?>ms -->
	<?php

}
