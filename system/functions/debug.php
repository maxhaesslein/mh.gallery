<?php

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
