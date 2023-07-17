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
	<title><?= get_config('title') ?></title>
<?php

	// CSS
	$css_path = 'system/site/assets/css/';
	$css_folder = new Folder( $css_path, 'css' );
	$css_files = $css_folder->order()->get();
	foreach( $css_files as $css_file ) {
		$file_url = url($css_path.$css_file, false);
		?>
	<link rel="stylesheet" href="<?= $file_url ?>?v=<?= get_version() ?>" type="text/css" media="all">
<?php
	}

	// JS
	$js_path = 'system/site/assets/js/';
	$js_folder = new Folder( $js_path, 'js' );
	$js_files = $js_folder->order()->get();
	foreach( $js_files as $js_file ) {
		$file_url = url($js_path.$js_file, false);
		?>
	<script async src="<?= $file_url ?>?v=<?= get_version() ?>"></script>
<?php
	}

}
