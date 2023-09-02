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
	<title><?= get_config('site_title') ?></title>
<?php

	// CSS
	$css_filter = 'extension=css';
	$css_tag = '
	<link rel="stylesheet" href="{url}" type="text/css" media="all">';
	if( get_config('system_css') ) {
		$css_system_path = 'system/site/assets/css/';
		head_load_files( $css_system_path, $css_filter, $css_tag );
	}

	$css_custom_path = 'custom/assets/css/';
	head_load_files( $css_custom_path, $css_filter, $css_tag );

	// JS
	$js_filter = 'extension=js';
	$js_tag = '
	<script async src="{url}"></script>';
	if( get_config('system_js') ) {
		$js_system_path = 'system/site/assets/js/';
		head_load_files( $js_system_path, $js_filter, $js_tag );

		?>
		<script type="text/javascript">
			GALLERY = {
				'apiUrl': '<?= url('api') ?>',
			};
		</script>
		<?php
	}
	
	$js_custom_path = 'custom/assets/js/';
	head_load_files( $js_custom_path, $js_filter, $js_tag );

}


function head_load_files( $path, $filter, $tag ) {
	$folder = new Folder( $path, $filter );
	$files = $folder->get();
	foreach( $files as $file ) {
		$url = url($file, false).'?v='.get_version();

		echo str_replace( '{url}', $url, $tag );
	}
}


function doing_ajax() {
	if( ! defined('DOING_AJAX') ) return false;

	return DOING_AJAX;
}
