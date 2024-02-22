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

	$gallery = false;
	$request_object = $core->gallery;
	foreach( $core->route->get('request') as $request_part ) {
		$new_request_object = $request_object->get($request_part);
		if( $new_request_object ) {
			$request_object = $new_request_object;

			if( $request_object->is('gallery') ) {
				$gallery = $request_object;
				$title[] = $gallery->get_title();
			} elseif( $request_object->is('image') ) {
				$image = $request_object;
				if( $gallery ) {
					$title[] = $image->get_number().'/'.$gallery->get_image_count();
				}
			}

		} else {
			$template_name = '404';
		}
	}

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
		$image = $gallery->get_thumbnail();
	}

	$description = false;
	if( $gallery ) {
		$description = $gallery->get_description();
	}

	$thumbnail = false;
	if( $image ) {
		$thumbnail = $image->get_image_url( [ 'width' => 1600, 'height' => 900, 'crop' => true, 'type' => 'jpg'] );
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
		$sharing_tags[] = '<meta property="og:image" content="'.$thumbnail.'">';
		$sharing_tags[] = '<meta name="twitter:card" content="summary_large_image">';
		$sharing_tags[] = '<meta name="twitter:image" content="'.$thumbnail.'">';
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

	favicon();

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

		$next_link = '';
		if( $next_image ) $next_link = $next_image->get_link();
		?>
	
	<link id="next-image-preload" rel="prefetch next" href="<?= $next_link ?>"><?php

		$prev_link = '';
		if( $prev_image ) $prev_link = $prev_image->get_link();
		?>

	<link id="prev-image-preload" rel="prefetch prev" href="<?= $prev_link ?>"><?php

	} // $image

	
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


function menu( $type ) {

	// NOTE: currently, we only support $type = 'footer'; may be expanded later
	if( $type != 'footer' ) return;

	$menu = get_config($type.'_menu');
	if( ! $menu ) return;

	?>
	<menu class="footer-menu">
		<?php
		foreach( $menu as $item ) {
			if( empty($item['title']) ) continue;
		?>
		<li>
			<?php

			if( ! empty($item['url']) ) {
				echo '<a href="'.$item['url'].'"';
				if( ! empty($item['target']) ) echo ' target="'.$item['target'].'"';
				echo ' rel="noopener">';
			}

			echo $item['title'];

			if( ! empty($item['url']) ) {
				echo '</a>';
			}

			?>
		</li>
		<?php
		}
		?>
	</menu>
	<?php

}


function favicon() {

	$favicon_path = 'system/site/assets/img/favicon/';

	?>

	<link rel="icon" href="<?= url($favicon_path.'favicon.ico', false) ?>">
	<link rel="icon" href="<?= url($favicon_path.'favicon.svg', false) ?>" type="image/svg+xml">
	<link rel="apple-touch-icon" href="<?= url($favicon_path.'favicon-180x180.png', false) ?>">
	<link rel="manifest" href="<?= url($favicon_path.'favicon-webmanifest.json', false) ?>">

<?php
}
