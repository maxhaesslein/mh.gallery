<?php

// This file is part of mh.gallery
// Copyright (C) 2023-2026 maxhaesslein
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
// See the file LICENSE.md for more details.

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


		$query_parameters = [];
		if( isset($_GET['lock']) ) {
			$query_parameters['lock'] = sanitize_get('lock', '', FILTER_VALIDATE_BOOLEAN);
		}
		if( isset($_GET['end-session']) ) {
			$query_parameters['end-session'] = sanitize_get('end-session', '', FILTER_VALIDATE_BOOLEAN);
		}
		if( isset($_GET['create']) ) {
			$query_parameters['create'] = sanitize_get('create', '', FILTER_VALIDATE_BOOLEAN);
		}
		if( isset($_GET['lock']) ) {
			$query_parameters['lock'] = sanitize_get('lock', '', FILTER_VALIDATE_BOOLEAN);
		}
		if( isset($_GET['imageonly']) ) {
			$query_parameters['imageonly'] = sanitize_get('imageonly', '', FILTER_VALIDATE_BOOLEAN);
		}
		if( isset($_GET['secret']) ) {
			$query_parameters['secret'] = sanitize_get('secret', '');
		}


		$mode = false;
		if( count($request) > 0 && in_array($request[0], [ 'img', 'api', 'download', 'admin' ]) ) {
			$mode = array_shift($request);
		}

		if( $mode == 'download' ) {
			$request[count($request)-1] = preg_replace( '/\.zip$/', '', $request[count($request)-1] );
		}


		$template_name = '404';
		$gallery = false;
		$image = false;
		$args = [];

		if( count($request) ) {

			$request_object = $core->gallery;
			foreach( $request as $request_part ) {
				$new_request_object = $request_object->get($request_part);
				if( $new_request_object ) {
					$request_object = $new_request_object;

					if( $request_object->is('gallery') ) {
						$gallery = $request_object;
						$template_name = 'overview';
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
				$gallery = $core->gallery;
				$template_name = 'overview';
			}

		}


		if( $gallery && $gallery->is_password_protected() ) {

			if( isset($query_parameters['lock']) ) {
				$gallery->password_lock();

				header( 'Location: '.$gallery->get_url());
				exit;
			}

			if( ! $gallery->password_provided() && ! empty($_POST['gallery-password']) ) {

				if( ! csrf_validate() ) {
					redirect( $gallery->get_url() );
				}

				$rate_limit_key = 'gallery-password-'.$gallery->get_slug();

				if( ! check_rate_limit($rate_limit_key) ) {
					$core->gallery_rate_limit_exceeded = $gallery->get_slug();
				} else {
					$input_password = sanitize_post('gallery-password');

					if( $gallery->check_password($input_password) ) {
						reset_rate_limit($rate_limit_key);
						header('Location: '.get_current_url());
						exit;
					} else {
						record_failed_attempt($rate_limit_key);
					}
				}

			}

			if( ! $gallery->password_provided() ) {
				$template_name = '401-password';
			}

		}

		if( $gallery && $gallery->is_secret() ) {
			
			if( isset($query_parameters['end-session']) ) {
				$gallery->secret_lock();

				header( 'Location: '.$gallery->get_url());
				exit;
			}

			if( ! $gallery->secret_provided() ) {

				if( ! empty($query_parameters['secret']) ) {

					$hash = get_hash($gallery->get_slug());

					$secret = $query_parameters['secret'];

					if( ! isset($_SESSION['secrets']) || ! is_array($_SESSION['secrets']) ) $_SESSION['secrets'] = [];

					if( ! array_key_exists($hash, $_SESSION['secrets']) ) $_SESSION['secrets'][$hash] = [];

					if( ! in_array($secret, $_SESSION['secrets'][$hash]) ) {
						$_SESSION['secrets'][$hash][] = $secret;
					}

					$reload_url = explode_url(get_current_url());
					
					unset($reload_url['query']['secret']);
					
					$reload_url = implode_url($reload_url);

					// remove the secret query parameter from the URL:
					header('Location: '.$reload_url);
					exit;
				}

				$template_name = '401-secret';

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
					$fit = false;
					if( $crop == 'crop' ) {
						$fit = array_shift($image_args);
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
						'fit' => $fit,
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

		} elseif( $mode == 'admin' ) {

			$action = sanitize_post('action');

			if( $action == 'login') {

				if( ! csrf_validate() ) {
					redirect('admin');
				}

				if( ! check_rate_limit('admin-login') ) {
					$core->rate_limit_exceeded = true;
					$template_name = 'admin';
				} else {
					$password = sanitize_post('admin-password');
					if( admin_login($password) ) {
						reset_rate_limit('admin-login');
						redirect('admin');
					} else {
						record_failed_attempt('admin-login');
					}
				}

			} elseif( ! empty($request[0]) && $request[0] == 'logout' ) {

				if( admin_logout() ) redirect('admin');
				
			}

			if( ! empty($request[0]) && $request[0] == 'create-hash' ) {
				$template_name = 'admin_create-hash';
			} elseif( get_config('admin_password') ) {
				// NOTE: only allow admin area, if a login password is set
				$template_name = 'admin';
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
			'query' => $query_parameters,
			'gallery' => $gallery,
			'image' => $image,
			'args' => $args,
		);

	}


	private function get_include_path( $template_name ) {

		$template_path = 'templates/'.$template_name.'.php';
		
		$custom_path = 'custom/'.$template_path;
		$validated_custom = validate_include_path( $custom_path );
		if( $validated_custom ) {
			return $validated_custom;
		} else {
			$system_path = 'system/site/'.$template_path;
			$validated_system = validate_include_path( $system_path );
			if( $validated_system ) {
				return $validated_system;
			}
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


	function is_rate_limit_exceeded(): bool {
		global $core;
		return $core->rate_limit_exceeded;
	}


	function get_gallery_rate_limit_exceeded(): bool|string {
		global $core;
		return $core->gallery_rate_limit_exceeded;
	}


}
