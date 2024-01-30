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
	private $file_extension;
	
	function __construct( $type, $input, $input_is_hash = false, $lifetime = false, $keep_file_extension = false ) {

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

		if( ! $lifetime ) {
			$lifetime = get_config( 'cache_lifetime' );
		}
		$this->lifetime = $lifetime;

		$this->cache_file_name = $this->get_file_name( false );
		$this->cache_file = $this->get_file_path();

	}


	function get_file_name( $force_new_filename = false ){

		if( ! $force_new_filename ) {
			$folderpath = $this->cache_folder;
			$folder = new Folder($this->cache_folder);
			$files = $folder->get();
			foreach( $files as $filepath ) {

				if( is_dir($filepath) ) continue;
				if( str_ends_with($filepath, '.placeholder') ) continue;
				
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

		if( get_config('cache_disabled') ) return false; // cache is disabled

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

		if( get_config('cache_disabled') ) return $this; // cache is disabled, don't write any data

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
		if( mkdir( get_abspath($this->cache_folder), get_config('chmod_folder'), true ) === false ) {
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

			if( is_dir($file) ) continue;
			if( str_ends_with($file, '.placeholder') ) continue;

			$expire_timestamp = $this->get_expire_timestamp( $file );

			if( $expire_timestamp < $current_timestamp ) { // cachefile too old
				// delete old cache file; fail silently

				@unlink(get_abspath($file)); // delete old cache file; fail silently

			}

		}
	}


	function get_expire_timestamp( $file ) {
		
		$file_explode = explode( '_', $file );
		$expire_timestamp = end($file_explode);

		// remove file extension, if provided:
		$expire_timestamp = explode('.', $expire_timestamp);
		$expire_timestamp = (int) $expire_timestamp[0];

		return $expire_timestamp;
	}


	function get_placeholder_filename( $include_path = false ) {

		$filename = $this->get_file_name();

		// remove timestamp and file extension
		$filename_explode = explode( '_', $filename );
		unset( $filename_explode[count($filename_explode)-1] );
		$filename = implode('_', $filename_explode);

		$filename .= '.placeholder';

		if( $include_path ) {
			$filename = $this->cache_folder.$filename;
		}

		return $filename;
	}


	function add_placeholder_file() {

		// NOTE: we may need a placeholder file to determine, if the cache file is allowed to be created at a later point. a placeholder file has no timestamp and no content.

		if( $this->exists() ) return $this;

		$placeholder_file = $this->get_placeholder_filename(true);

		if( ! touch(get_abspath($placeholder_file)) ) {
			debug( 'could not create cache placeholder file', $placeholder_file );
		}

		return $this;
	}


	function remove_placeholder_file() {

		$placeholder_file = $this->get_placeholder_filename(true);

		if( ! file_exists(get_abspath($placeholder_file)) ) return $this;

		@unlink(get_abspath($placeholder_file));

		return $this;
	}

};
