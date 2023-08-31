<?php

class Cache {

	private $cache_folder = 'cache/';
	private $cache_file;

	private $type;
	private $name;
	private $hash;
	private $cache_file_name;
	private $filesize;
	private $lifetime;
	
	function __construct( $type, $input, $input_is_hash = false, $lifetime = false ) {

		if( ! $type && ! $input ) return;

		$this->cache_folder .= $type.'/';

		$this->check_cache_folder();

		$this->type = $type;

		if( $input_is_hash ) {
			$this->hash = $hash;
		} else {
			$this->hash = get_hash( $input );
		}

		if( ! $lifetime ) {
			$lifetime = get_config( 'cache_lifetime' );
		}
		$this->lifetime = $lifetime;

		$this->cache_file_name = $this->get_file_name();
		$this->cache_file = $this->get_file_path();

	}


	function get_file_name( $force_new_filename = false ){

		if( ! $force_new_filename ) {
			$folderpath = $this->cache_folder;
			$folder = new Folder($this->cache_folder);
			$files = $folder->get();
			foreach( $files as $filepath ) {
				$filename = str_replace($this->cache_folder, '', $filepath);
				if( str_starts_with($filename, $this->hash) ) {
					$current_timestamp = time();
					$expire_timestamp = $this->get_expire_timestamp( $filename );
					if( $expire_timestamp < $current_timestamp ) { // cachefile too old
						@unlink(get_abspath($filepath)); // delete old cache file; fail silently
						break;
					}
					return $filename;
				}
			}
		}

		// no file yet, create new name:
		
		$target_timestamp = time() + $this->lifetime;

		$filename = $this->hash.'_'.$target_timestamp;

		return $filename;
	}


	function get_file_path(){
		return $this->cache_folder.$this->cache_file_name;
	}


	function exists() {
		
		if( ! file_exists(get_abspath($this->cache_file)) ) return false;

		return true;
	}


	function get_data() {

		if( ! $this->exists() ) return false;

		if( get_config('cache_disabled') ) return false; // cache is disabled

		$cache_content = file_get_contents(get_abspath($this->cache_file));

		return $cache_content;
	}


	function get_filesize() {

		if( get_config('cache_disabled') ) return 0;

		if( $this->filesize ) return $this->filesize;

		$this->filesize = filesize(get_abspath($this->cache_file));

		return $this->filesize;
	}


	function add_data( $data ) {

		if( get_config('cache_disabled') ) return $this; // cache is disabled, don't write any data

		if( ! file_put_contents( get_abspath($this->cache_file), $data ) ) {
			debug( 'could not create cache file', $this->cache_file );
		}

		return $this;
	}


	function remove() {
		if( ! $this->exists() ) return;

		unlink(get_abspath($this->cache_file));
	}


	function refresh_lifetime() {
		if( ! $this->exists() ) return $this;

		$old_filename = $this->get_file_name();
		$new_filename = $this->get_file_name(true);
		if( rename( $this->cache_folder.$old_filename, $this->cache_folder.$new_filename ) ) {
			$this->cache_file_name = $new_filename;
			$this->cache_file = $this->cache_folder.$this->cache_file_name;
		}

		return $this;
	}


	private function check_cache_folder(){
		
		if( is_dir(get_abspath($this->cache_folder)) ) return;

		$oldumask = umask(0); // we need this for permissions of mkdir to be set correctly
		if( mkdir( get_abspath($this->cache_folder), 0777, true ) === false ) {
			debug( 'could not create cache dir', $this->cache_folder );
		}
		umask($oldumask); // we need this after changing permissions with mkdir

		return $this;
	}


	function clear_cache_folder(){
		// this function clears out old cache files.

		$folder = new Folder('cache/', false, true);
		$files = $folder->get();

		$current_timestamp = time();

		foreach( $files as $file ) {

			$expire_timestamp = $this->get_expire_timestamp( $file );

			if( $expire_timestamp < $current_timestamp ) { // cachefile too old
				@unlink(get_abspath($file)); // delete old cache file; fail silently
			}

		}
	}


	function get_expire_timestamp( $file ) {
		$file_explode = explode( '_', $file );
		$expire_timestamp = (int) end($file_explode);

		return $expire_timestamp;
	}

};
