<?php

class Route {

	protected $route;
	protected $request;
	protected $query;
	
	function __construct() {
		global $core;

		$request_string = $_SERVER['REQUEST_URI'];
		$request_string = preg_replace( '/^'.preg_quote(get_basefolder(), '/').'/', '', $request_string );

		$request = explode( '?', $request_string );
		$request = $request[0];

		$request = explode( '/', $request );

		$this->query = $_REQUEST;

		$this->request = $request;

		$template_name = 'index'; // for now, always display index ..


		$template_path = 'templates/'.$template_name.'.php';

		if( file_exists(get_abspath('custom/'.$template_path)) ) {
			$include_path = get_abspath('custom/'.$template_path);
		} else {
			$include_path = get_abspath('system/site/'.$template_path);
		}


		$this->route = array(
			'template_name' => $template_name,
			'template_include' => $include_path,
		);

	}

	function get_route() {
		return $this->route;
	}

	function get_request() {
		return $this->request;
	}

	function get_quety() {
		return $this->query;
	}

}
