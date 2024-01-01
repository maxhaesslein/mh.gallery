<?php

class Route {

	private $route;
	
	function __construct() {
		global $core;

		$request_string = $_SERVER['REQUEST_URI'];
		$request_string = preg_replace( '/^'.preg_quote(get_basefolder(), '/').'/', '', $request_string );
		
		$request_string = urldecode($request_string);

		$request = explode( '?', $request_string );
		$request = $request[0];
		$request = explode( '/', $request );
		$request = array_filter($request); // remove empty elements


		$mode = false;
		if( count($request) > 0 && in_array($request[0], ['img', 'api', 'download']) ) {
			$mode = array_shift($request);
		}

		if( $mode == 'download' ) {
			$request[count($request)-1] = str_replace('.zip', '', $request[count($request)-1]);
		}


		$template_name = '404';
		$collection = false;
		$gallery = false;
		$image = false;
		$args = [];

		if( count($request) ) {

			$request_object = $core->collection;
			foreach( $request as $request_part ) {
				$new_request_object = $request_object->get($request_part);
				if( $new_request_object ) {
					$request_object = $new_request_object;

					if( $request_object->is('gallery') ) {
						$gallery = $request_object;
						$template_name = 'overview';
					} elseif( $request_object->is('collection') ) {
						$collection = $request_object;
						$template_name = 'index';
					} elseif( $request_object->is('image') ) {
						$image = $request_object;
						$template_name = 'image';
					}

				} else {
					$template_name = '404';
				}
			}
		
		} else {

			if( get_config('allow_overview') ) {
				$collection = $core->collection;
				$template_name = 'index';
			}

		}


		if( $mode == 'img' ) {

			$template_name = '404';
			
			if( $gallery ) {

				$image_name = array_pop($request);

				$image_name = explode('.', $image_name);
				$type = array_pop($image_name);
				$image_name = implode('.', $image_name);
				$image_name = explode('_', $image_name);
				$cache_fragment = array_pop($image_name);
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

		} elseif( $mode == 'download' ) {

			$template_name = '404';
			
			if( $gallery && $gallery->is_download_gallery_enabled() ) {
				$template_name = 'download';
			}

		} elseif( $mode == 'api' ) {
			
			$template_name = '404';

			if( $image ) {
				$template_name = 'api-output';
			}

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
			'collection' => $collection,
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
