<?php

class Folder {
	
	private $path;
	private $files = [];

	function __construct( $path, $filter = false ) {

		$path = get_abspath($path);

		$this->path = $path;

		if( ! is_dir($path) ) return;

		$files = [];

		if( $handle = opendir($path) ){
			while( false !== ($filename = readdir($handle)) ){

				if( str_starts_with($filename, '.') ) continue; // skip everything that starts with '.', and thus also './' and '../'

				if( $filter ) {
					$file_extension = pathinfo( $path.$filename, PATHINFO_EXTENSION );
					if( strtolower($file_extension) != strtolower($filter) ) continue;
				}

				$files[] = $filename;
			}
			closedir($handle);
		}

		$this->files = $files;

	}


	function order() {

		if( ! count($this->files) ) return $this;

		asort( $this->files );

		return $this;
	}


	function get() {
		return $this->files;
	}

}
