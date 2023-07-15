<?php

function debug( ...$messages ) {
	global $core;

	if( ! $core->config->get('debug') ) return;

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
