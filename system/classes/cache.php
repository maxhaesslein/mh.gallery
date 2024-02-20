<?php

class Cache {

	private $cache_folder = 'cache/';
	private $cache_file;

	private $type;
	private $name;
	private $hash;
	private $cache_file_name;
	private $filesize;
	private $file_extension;
	
	function __construct( $type, $input, $input_is_hash = false, $keep_file_extension = false ) {

		if( ! $type && ! $input ) return;

		$this->cache_folder .= $type.'/';

		$subfolder = false;
		$input_exp = explode('/', $input);
		if( count($input_exp) > 1 ) {
			$input = array_pop($input_exp);
			$subfolder = implode('/', $input_exp);
		}

		if( $subfolder ) $this->cache_folder .= trailing_slash_it($subfolder);

		$this->check_cache_folder();

		$this->type = $type;

		if( $keep_file_extension ) {
			$file_extension = false;
			$input_exp = explode('.', $input);
			if( count($input_exp) > 1 ) $file_extension = array_pop( $input_exp );
			$input = implode('.', $input_exp);

			if( $file_extension ) $this->file_extension = $file_extension;
		}

		if( $input_is_hash ) {
			$this->hash = $input;
		} else {
			$this->hash = get_hash( $input );
		}

		$this->cache_file_name = $this->get_file_name();
		$this->cache_file = $this->get_file_path();

	}


	function get_file_name(){

		$filename = $this->hash;

		if( $this->file_extension ) $filename .= '.'.$this->file_extension;

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

		if( get_config('cache_disabled') ) return false;

		$cache_content = file_get_contents(get_abspath($this->cache_file));

		return $cache_content;
	}


	function get_filesize() {

		if( get_config('cache_disabled') ) return 0;

		if( ! $this->exists() ) return 0;

		if( $this->filesize ) return $this->filesize;

		$this->filesize = filesize(get_abspath($this->cache_file));

		return $this->filesize;
	}


	function add_data( $data ) {

		if( get_config('cache_disabled') ) return $this; // don't write any data

		if( ! file_put_contents( get_abspath($this->cache_file), $data ) ) {
			debug( 'could not create cache file', $this->cache_file );
		}

		// remove placeholder file, we no longer need it
		$this->remove_placeholder_file();

		return $this;
	}


	function remove() {
		if( ! $this->exists() ) return;

		$this->remove_placeholder_file(); // delete placeholder file, if it exists
		unlink(get_abspath($this->cache_file));
	}


	function refresh_lifetime() {
		if( ! $this->exists() ) return $this;

		touch( $this->cache_file );

		return $this;
	}


	private function check_cache_folder(){
		
		if( is_dir(get_abspath($this->cache_folder)) ) return;

		$oldumask = umask(0); // we need this for permissions of mkdir to be set correctly
		if( mkdir( get_abspath($this->cache_folder), get_config('chmod_folder'), true ) === false ) {
			debug( 'could not create cache dir', $this->cache_folder );
		}
		umask($oldumask); // we need this after changing permissions with mkdir

		return $this;
	}


	function get_expire_timestamp( $file ) {

		$timestamp = filemtime( $this->cache_file );

		$expire_timestamp = $timestamp + get_config( 'cache_lifetime' );

		return $expire_timestamp;
	}


	function get_placeholder_filename( $include_path = false ) {

		$filename = $this->hash;
		
		$filename .= '.placeholder';

		if( $include_path ) {
			$filename = $this->cache_folder.$filename;
		}

		return $filename;
	}


	function add_placeholder_file() {

		// NOTE: we may need a placeholder file to determine, if the cache file is allowed to be created at a later point. a placeholder file has no content.

		if( $this->exists() ) {

			$this->refresh_lifetime();

			return $this;
		}

		$placeholder_file = $this->get_placeholder_filename(true);

		if( ! touch(get_abspath($placeholder_file)) ) {
			debug( 'could not create cache placeholder file', $placeholder_file );
		}

		return $this;
	}


	function has_placeholder() {

		$placeholder_file = $this->get_placeholder_filename(true);

		if( file_exists(get_abspath($placeholder_file)) ) return true;

		return false;
	}


	function remove_placeholder_file() {

		$placeholder_file = $this->get_placeholder_filename(true);

		if( ! file_exists(get_abspath($placeholder_file)) ) return $this;

		@unlink(get_abspath($placeholder_file));

		return $this;
	}

};
