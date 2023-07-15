<?php

class Core {
	
	protected $abspath;
	protected $basefolder;
	protected $baseurl;

	public $config;
	public $route;

	function __construct( $abspath ){

		global $core;
		$core = $this;

		$this->abspath = $abspath;

		$basefolder = str_replace( 'index.php', '', $_SERVER['PHP_SELF']);
		$this->basefolder = $basefolder;

		if( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ) {
			$baseurl = 'https://';
		} else {
			$baseurl = 'http://';
		}
		$baseurl .= $_SERVER['HTTP_HOST'];
		$baseurl .= $basefolder;
		$this->baseurl = $baseurl;

		$this->config = new Config();

		$this->route = new Route();

	}


	function get_abspath( $path = false ) {

		$abspath = $this->abspath;

		if( $path ) {
			$abspath = trailing_slash_it($abspath).$path;
		}

		return $abspath;
	}


	function get_basefolder( $path = false ) {

		$basefolder = $this->basefolder;

		if( $path ) {
			$basefolder = trailing_slash_it($basefolder).$path;
		}

		return $basefolder;
	}


	function get_baseurl( $path = false ) {

		$baseurl = $this->baseurl;

		if( $path ) {
			$baseurl = trailing_slash_it($baseurl).$path;
		}

		return $baseurl;
	}

}
