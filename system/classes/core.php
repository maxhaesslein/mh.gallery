<?php

// This file is part of mh.gallery
// Copyright (C) 2023-2025 maxhaesslein
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
// See the file LICENSE.md for more details.

class Core {
	
	private $abspath;
	private $basefolder;
	private $baseurl;

	public $config;
	public $gallery;
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

		$this->check_hash_algo();

		$this->gallery = new Gallery( false, false ); // needs to be there before new Route()

		$this->route = new Route();

		$this->refresh_cache();

	}


	private function check_required_files_and_folders() {

		// NOTE: this checks, if all the files we need are there, and creates them if they are missing

		// TODO: also check, if our content block with "# BEGIN mh.gallery" until "END mh.gallery" is present in the .htaccess file, because the user could have created their own .htaccess file.
		if( ! file_exists($this->get_abspath('.htaccess')) ) {
			// .htaccess is missing

			$rewrite_base = $this->basefolder;
			if( $rewrite_base == '' ) $rewrite_base = '/';

			$content = "# BEGIN mh.gallery\r\n<IfModule mod_rewrite.c>\r\nRewriteEngine on\r\nRewriteBase ".$rewrite_base."\r\n\r\nRewriteRule ^zip/(.*).zip$ cache/zip/$1 [L]\r\n\r\nRewriteRule ^custom/assets/(.*)$ - [L]\r\nRewriteRule ^system/site/assets/(.*)$ - [L]\r\nRewriteRule ^content/(.*)$ index.php [L]\r\nRewriteRule ^system/(.*) index.php [L]\r\n\r\nRewriteRule ^img/(.*)$ cache/image/$1\r\n\r\nRewriteCond %{REQUEST_FILENAME} !-d\r\nRewriteCond %{REQUEST_FILENAME} !-f\r\nRewriteRule . index.php [L]\r\n</IfModule>\r\n# END mh.gallery\r\n";
				if( file_put_contents( $this->get_abspath('.htaccess'), $content ) === false ) {
					debug( 'could not create ".htaccess" file; aborting.' );
					exit;
				}

		}

		if( ! is_dir($this->get_abspath('content/')) ) {
			// content/ folder is missing

			$oldumask = umask(0); // we need this for permissions of mkdir to be set correctly
			if( mkdir( $abspath.'content/', get_config('chmod_folder'), true ) === false ) {
				debug( 'could not create "content/" folder; aborting.' );
				exit;
			}
			umask($oldumask); // we need this after changing permissions with mkdir

		}

		if( ! is_dir($this->get_abspath('cache/')) ) {
			// cache/ folder is missing

			$oldumask = umask(0); // we need this for permissions of mkdir to be set correctly
			if( mkdir( $abspath.'cache/', get_config('chmod_folder'), true ) === false ) {
				debug( 'could not create "cache/" folder; aborting.' );
				exit;
			}
			umask($oldumask); // we need this after changing permissions with mkdir

		}

	}


	private function check_hash_algo() {

		$possible_algorithms = $this->config->get('hash_algorithm');

		$available_algorithms = hash_algos();

		$algorithm = false;
		foreach( $possible_algorithms as $algorithm ) {
			if( in_array($algorithm, $available_algorithms) ) break;
			$algorithm = false;
		}
		if( ! $algorithm ) $algorithm = 'md5';

		$this->config->overwrite('hash_algorithm', $algorithm);

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


	private function refresh_cache(){

		// only refresh the cache once a day:
		$last_refresh_cache = new Cache( 'global', 'last_cache_refresh' );
		$last_refresh_timestamp = $last_refresh_cache->get_data();

		$day_in_seconds = 60*60*24;
		if( $last_refresh_timestamp && $last_refresh_timestamp < time()+$day_in_seconds ) {
			return;
		}

		$last_refresh_cache->add_data(time());


		// clear out old cache files:

		$folder = new Folder('cache/', false, true);
		$files = $folder->get();

		$current_time = time();

		foreach( $files as $file ) {

			if( is_dir($file) ) continue;
		
			$file_exp = explode( '/', $file );
			$type = $file_exp[1]; // type is deduced from the filepath: cache/{type}/...

			$lifetime = get_config( $type.'_cache_lifetime' );
			if( ! $lifetime ) {
				$lifetime = get_config( 'cache_lifetime' );
			}

			$timestamp_limit = $current_time + $lifetime;

			$timestamp = filemtime( get_abspath($file) );

			if( $timestamp > $timestamp_limit ) { // cachefile too old
				@unlink(get_abspath($file)); // delete old cache file; fail silently
			}

		}


		// delete empty folders:

		$subfolders_collection = new Folder('cache/', 'folder', true);
		$subfolders = $subfolders_collection->get();

		foreach( $subfolders as $subfolder ) {
			$files_collection = new Folder( $subfolder, false, true );
			$files = $files_collection->get();

			if( count($files) === 0 ) {
				@rmdir(get_abspath($subfolder));
			}
		}

	}


}
