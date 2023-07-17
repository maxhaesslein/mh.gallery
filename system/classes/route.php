<?php

class Route {

	private $route;
	
	function __construct() {
		global $core;

		$request_string = $_SERVER['REQUEST_URI'];
		$request_string = preg_replace( '/^'.preg_quote(get_basefolder(), '/').'/', '', $request_string );

		$request = explode( '?', $request_string );
		$request = $request[0];
		$request = explode( '/', $request );
		$request = array_filter($request); // remove empty elements


		$template_name = 'index';

		if( count($request) > 0 ) {

			$slug = $request[0];

			$secret = false;
			if( ! empty($request[1]) ) {
				$secret = $request[1]; // TODO: check secret
			}

			if( $core->galleries->exists($slug) ) {
				$template_name = 'overview';
			} else {
				$template_name = '404';
			}

		}

		$template_path = 'templates/'.$template_name.'.php';

		if( file_exists(get_abspath('custom/'.$template_path)) ) {
			$include_path = get_abspath('custom/'.$template_path);
		} else {
			$include_path = get_abspath('system/site/'.$template_path);
		}


		$this->route = array(
			'template_name' => $template_name,
			'template_include' => $include_path,
			'request' => $request,
			'query' => $_REQUEST
		);

	}

	function get_route( $option = false ) {

		if( $option ) {
			if( array_key_exists($option, $this->route) ) {
				return $this->route[$option];
			}
			return false;
		}

		return $this->route;
	}

}
