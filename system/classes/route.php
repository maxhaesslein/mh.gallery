<?php

class Route {

	private $route;
	
	function __construct() {
		global $core;

		$gallery = false;
		$image = false;
		$args = [];

		$request_string = $_SERVER['REQUEST_URI'];
		$request_string = preg_replace( '/^'.preg_quote(get_basefolder(), '/').'/', '', $request_string );
		
		$request_string = urldecode($request_string);

		$request = explode( '?', $request_string );
		$request = $request[0];
		$request = explode( '/', $request );
		$request = array_filter($request); // remove empty elements

		$template_name = '404';

		$mode = false;
		if( count($request) > 0 && in_array($request[0], ['img', 'api', 'download']) ) {
			$mode = array_shift($request);
		}


		if( $mode == 'img' ) {

			// TODO

			$image_name = array_pop($request);
			$gallery_slug = implode('/', $request);

			$gallery = $core->collection->get_gallery($gallery_slug);
			if( $gallery ) {

				$image_name = explode('.', $image_name);
				$type = array_pop($image_name);
				$image_name = explode('_', implode('.', $image_name));
				$image_args = array_pop($image_name);
				$image_name = implode('_', $image_name);

				$image = $gallery->get_image($image_name);
				if( $image ) {

					$image_args = explode('-', $image_args);

					$size = array_shift($image_args);
					$size = explode('x', $size);

					$width = (int) $size[0];
					$height = (int) $size[1];

					$crop = array_shift($image_args);
					if( $crop == 'crop' ) {
						$quality = array_pop($image_args);
						$crop = true;
					} else {
						$quality = $crop;
						$crop = false;
					}

					$quality = (int) $quality;
					if( $quality <= 0 ) $quality = false;

					$args = [
						'width' => $width,
						'height' => $height,
						'crop' => $crop,
						'quality' => $quality,
						'type' => $type,
					];

					$template_name = 'img-output';
				}
			}

		}elseif( $mode == 'download' ) {

			// TODO

			$gallery_slug = implode('/', $request);

			$gallery_slug = explode('.', $gallery_slug);
			unset($gallery_slug[count($gallery_slug)-1]);
			$gallery_slug = implode('.', $gallery_slug);

			$gallery = $core->collection->get_gallery($gallery_slug);
			if( $gallery && $gallery->is_download_gallery_enabled() ) {
				$template_name = 'download';
			}

		} elseif( $mode == 'api' ) {

			// TODO

			$gallery_slug = $request[0];

			if( $core->collection->gallery_exists($gallery_slug) ) {

				$gallery = $core->collection->get_gallery($gallery_slug);

				if( $gallery ) {
					$image_slug = $request[1];

					$image = $gallery->get_image($image_slug);

					if( $image ) {
						$template_name = 'api-output';
					}

				}

			}

		} elseif( count($request)) {

			// TODO

			$gallery_slug = $request[0];

			if( $core->collection->gallery_exists($gallery_slug) ) {

				$gallery = $core->collection->get_gallery($gallery_slug);

				if( $gallery ) {

					$template_name = 'overview';

					if( ! empty($request[1]) ) {
						$image_slug = $request[1];
						$image = $gallery->get_image($image_slug);

						if( $image ) {
							$template_name = 'image';
						} else {
							$template_name = '404';
						}

					}

				}

			}

		} elseif( get_config('allow_overview') ) {

			$template_name = 'index';

		}

		$include_path = $this->get_include_path($template_name);
		if( ! $include_path ) {
			$template_name = '404';
			$include_path = $this->get_include_path($template_name);
		}


		$this->route = array(
			'template_name' => $template_name,
			'template_include' => $include_path,
			'request' => $request,
			'query' => $_REQUEST,
			'gallery' => $gallery,
			'image' => $image,
			'args' => $args,
		);

	}


	function get_include_path( $template_name ) {

		$template_path = 'templates/'.$template_name.'.php';
		
		if( file_exists(get_abspath('custom/'.$template_path)) ) {
			return get_abspath('custom/'.$template_path);
		}

		if( file_exists(get_abspath('system/site/'.$template_path))) {
			return get_abspath('system/site/'.$template_path);
		}
		
		return false;
	}

	function get( $option = false ) {

		if( $option ) {
			if( array_key_exists($option, $this->route) ) {
				return $this->route[$option];
			}
			return false;
		}

		return $this->route;
	}

}
