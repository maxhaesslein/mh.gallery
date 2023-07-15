<?php

function snippet( $path, $args = [], $return = false ) {
	global $core;
	
	$snippet_path = 'snippets/'.$path.'.php';

	if( file_exists(get_abspath('custom/'.$snippet_path)) ) {
		$include_path = get_abspath('custom/'.$snippet_path);
	} else {
		$include_path = get_abspath('system/site/'.$snippet_path);
	}

	if( ! file_exists($include_path) ) {
		return;
	}

	ob_start();

	include( $include_path );

	$snippet = ob_get_contents();
	ob_end_clean();

	if( $return === true ) {
		return $snippet;
	}

	echo $snippet;
}


function head() {
	global $core;

	?>
	<title><?= $core->config->get('title') ?></title>
	<?php	
}
