<?php

class Folder {
	
	private $path;
	private $files = [];

	function __construct( $path, $filter = false, $recursive = false, $stop = false ) {

		if( ! is_dir(get_abspath($path)) ) return;

		$this->path = $path;

		$files = $this->read_folder( $path, $filter, $recursive, $stop );

		$this->files = $files;

	}


	function read_folder( $path = false, $filter = false, $recursive = false, $stop = false, $depth = 0 ) {

		if( ! $path ) $path = $this->path;

		$path = trailing_slash_it($path);

		$files = [];

		if( $handle = opendir(get_abspath($path)) ){
			while( false !== ($filename = readdir($handle)) ){

				if( str_starts_with($filename, '.') ) continue; // skip everything that starts with '.', and thus also './' and '../'

				if( $stop && $filename == $stop && $depth > 0 ) {
					return [];
				}

				if( $recursive && is_dir($path.$filename) ) {
					$subfolder_files = $this->read_folder( $path.$filename, $filter, $recursive, $stop, $depth+1 );
					if( count($subfolder_files) ) {
						$files = array_merge( $files, $subfolder_files );
					}
				}

				if( $filter == 'folder' ) {

					if( ! is_dir(get_abspath($path.$filename)) ) {
						continue;
					}

				} elseif( str_starts_with($filter, 'extension=') ) {

					$file_extension = strtolower(pathinfo( get_abspath($path.$filename), PATHINFO_EXTENSION ));
					$possible_extensions = explode(',', str_replace('extension=', '', $filter));
					$possible_extensions = array_map( 'strtolower', $possible_extensions );

					if( ! in_array($file_extension, $possible_extensions) ) {
						continue;
					}

				} elseif( $filter ) {

					if( $filename != $filter ) {
						continue;
					}

				}

				$files[] = $path.$filename;
			}
			closedir($handle);
		}

		if( count($files) ) {
			asort($files);
		}

		return $files;
	}

	function get() {
		return $this->files;
	}

}
