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


function get_site_title() {

	global $core;

	$title = [ get_config('site_title') ];

	$gallery = $core->route->get('gallery');
	if( $gallery ) $title[] = $gallery->get_title();

/*
// TODO: add image number + count; this needs also be added to the AJAX navigation via js
	$image = $core->route->get('image');
	if( $image ) $title[] = $image->get_number().'/'.$gallery->get_image_count();
*/

	$title = array_reverse( $title );

	$title = implode( ' · ', $title );

	return $title;
}


function get_site_sharing_tags() {

	if( ! get_config('site_sharing_tags') ) return;

	global $core;

	$gallery = $core->route->get('gallery');
	$image = $core->route->get('image');

	if( ! $image && $gallery ) {
		$thumbnail_slug = $gallery->get_thumbnail_slug();
		$image = $gallery->get_image( $thumbnail_slug );
	}

	$description = false; // TODO: allow gallery description
	
	$thumbnail = false;
	if( $image ) {
		$thumbnail = $image->get_image_url( [ 'width' => 1200, 'height' => 1200, 'crop' => false, 'type' => 'jpg'] );
	}

	$sharing_tags = [];

	$sharing_tags[] = '<meta property="og:site_name" content="'.get_config('site_title').'">';
	$sharing_tags[] = '<meta property="og:url" content="'.url().'">';
	$sharing_tags[] = '<meta property="og:type" content="website">';
	$sharing_tags[] = '<meta property="og:title" content="'.get_site_title().'">';

	if( $thumbnail ) {
		$sharing_tags[] = '<meta name="twitter:card" content="summary_large_image">';
		$sharing_tags[] = '<meta property="og:image" content="'.$thumbnail.'">';
	}

	if( $description ) {
		$sharing_tags[] = '<meta name="description" content="'.$description.'">';
		$sharing_tags[] = '<meta property="og:description" content="'.$description.'">';
	}

	$sharing_tags = implode( "\n	", $sharing_tags );

	return $sharing_tags;
}


function head() {
	global $core;

	?>
	<title><?= get_site_title() ?></title>
	<?= get_site_sharing_tags() ?>
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
	</script><?php
	}
	
	$js_custom_path = 'custom/assets/js/';
	head_load_files( $js_custom_path, $js_filter, $js_tag );

}


function head_load_files( $path, $filter, $tag ) {
	$folder = new Folder( $path, $filter );
	$files = $folder->get();

	$version = get_version();
	if( get_config('debug') ) $version .= '.'.time(); // cache buster

	foreach( $files as $file ) {

		$url = url($file, false).'?v='.$version;

		echo str_replace( '{url}', $url, $tag );
	}
}


function doing_ajax() {
	if( ! defined('DOING_AJAX') ) return false;

	return DOING_AJAX;
}
