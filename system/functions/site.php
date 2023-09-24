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

	$image = $core->route->get('image');
	if( $image ) $title[] = $image->get_number().'/'.$gallery->get_image_count();

	$title = array_reverse( $title );

	$title = implode( ' · ', $title );

	$title = strip_tags($title);

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

	$description = false;
	if( $gallery ) {
		$description = $gallery->get_description();
	}

	$thumbnail = false;
	if( $image ) {
		$thumbnail = $image->get_image_url( [ 'width' => 1200, 'height' => 1200, 'crop' => false, 'type' => 'jpg'] );
	}

	$sharing_tags = [];

	$site_title = strip_tags(get_config('site_title'));

	$page_url = url(implode('/',$core->route->get('request')));
	$page_title = strip_tags(get_site_title());

	$sharing_tags[] = '<meta property="og:site_name" content="'.$site_title.'">';
	$sharing_tags[] = '<meta property="og:url" content="'.$page_url.'">';
	$sharing_tags[] = '<meta property="og:type" content="website">';
	$sharing_tags[] = '<meta property="og:title" content="'.$page_title.'">';

	if( $thumbnail ) {
		$thumbnail = strip_tags($thumbnail);
		$sharing_tags[] = '<meta name="twitter:card" content="summary_large_image">';
		$sharing_tags[] = '<meta property="og:image" content="'.$thumbnail.'">';
	}

	if( $description ) {
		$description = strip_tags($description);
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
	// preload loading animation:
	?>
	<link rel="preload" href="<?= url('system/site/assets/img/loading_white.svg', false) ?>" as="image" type="image/svg+xml" crossorigin>
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
	} // get_config('system_js')


	// prerender/prefetch previous and next image
	$image = $core->route->get('image');
	if( $image ) {

		$prev_image = $image->get_adjacent_image('prev');
		$next_image = $image->get_adjacent_image('next');

		if( $next_image ) {
			echo '<link id="next-image-preload" rel="prefetch" href="'.$next_image->get_link().'" />';
		}
		if( $prev_image ) {
			echo '<link id="prev-image-preload" rel="prefetch" href="'.$prev_image->get_link().'" />';
		}

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
