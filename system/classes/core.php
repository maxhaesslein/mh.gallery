<?php

class Core {
	
	private $abspath;
	private $basefolder;
	private $baseurl;

	public $config;
	public $galleries;
	public $route;

	function __construct( $abspath ){

		global $core;
		$core = $this;

		$this->abspath = $abspath;

		$basefolder = str_replace( 'index.php', '', $_SERVER['PHP_SELF']);
		$this->basefolder = $basefolder;

		if( (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ||
			(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ) {
			$baseurl = 'https://';
		} else {
			$baseurl = 'http://';
		}
		$baseurl .= $_SERVER['HTTP_HOST'];
		$baseurl .= $basefolder;
		$this->baseurl = $baseurl;

		$this->config = new Config(); // needs to be there before check_required_files_and_folders()

		$this->check_required_files_and_folders();

		$this->galleries = new Collection(); // needs to be there before new Route()

		$this->route = new Route();

		$this->refresh_cache();

	}


	function check_required_files_and_folders() {
		// NOTE: this checks, if all the files we need are there, and creates them if they are missing

		// TODO: also check, if our content block with "# BEGIN mh.gallery" until "END mh.gallery" is present in the .htaccess file, because the user could have created their own .htaccess file.
		if( ! file_exists($this->get_abspath('.htaccess')) ) {
			// .htaccess is missing

			$rewrite_base = $this->basefolder;
			if( $rewrite_base == '' ) $rewrite_base = '/';

			$content = "# BEGIN mh.gallery\r\n<IfModule mod_rewrite.c>\r\nRewriteEngine on\r\nRewriteBase ".$rewrite_base."\r\n\r\nRewriteRule ^zip/(.*).zip$ cache/zip/$1 [L]\r\n\r\nRewriteRule ^custom/assets/(.*)$ - [L]\r\nRewriteRule ^system/site/assets/(.*)$ - [L]\r\nRewriteRule ^content/(.*)$ index.php [L]\r\nRewriteRule ^system/(.*) index.php [L]\r\nRewriteRule ^cache/image/(.*) index.php [L]\r\n\r\nRewriteCond %{REQUEST_FILENAME} !-d\r\nRewriteCond %{REQUEST_FILENAME} !-f\r\nRewriteRule . index.php [L]\r\n</IfModule>\r\n# END mh.gallery\r\n";
				if( file_put_contents( $this->get_abspath('.htaccess'), $content ) === false ) {
					debug( 'could not create ".htaccess" file; aborting.' );
					exit;
				}

		}

		if( ! is_dir($this->get_abspath('content/')) ) {
			// content/ folder is missing

			$oldumask = umask(0); // we need this for permissions of mkdir to be set correctly
			if( mkdir( $abspath.'content/', 0777, true ) === false ) {
				debug( 'could not create "content/" folder; aborting.' );
				exit;
			}
			umask($oldumask); // we need this after changing permissions with mkdir

		}

		if( ! is_dir($this->get_abspath('cache/')) ) {
			// cache/ folder is missing

			$oldumask = umask(0); // we need this for permissions of mkdir to be set correctly
			if( mkdir( $abspath.'cache/', 0777, true ) === false ) {
				debug( 'could not create "cache/" folder; aborting.' );
				exit;
			}
			umask($oldumask); // we need this after changing permissions with mkdir

		}

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


	function refresh_cache(){

		$cache = new Cache( false, false );

		$cache->clear_cache_folder();
		
	}


}
