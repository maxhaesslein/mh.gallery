<?php

class Folder {
	
	private $path;
	private $files = [];

	function __construct( $path, $filter = false, $recursive = false, $stop = false, $stop_depth = 0 ) {

		if( ! is_dir(get_abspath($path)) ) return;

		$this->path = $path;

		$files = $this->read_folder( $path, $filter, $recursive, $stop, $stop_depth, 0 );

		$this->files = $files;

	}


	function read_folder( $path = false, $filter = false, $recursive = false, $stop = false, $stop_depth = 0, $depth = 0 ) {

		if( ! $path ) $path = $this->path;

		$path = trailing_slash_it($path);

		$files = [];

		if( $handle = opendir(get_abspath($path)) ){
			while( false !== ($filename = readdir($handle)) ){

				if( str_starts_with($filename, '.') ) continue; // skip everything that starts with '.', and thus also './' and '../'

				if( $filter == 'folder' ) {

					if( is_dir(get_abspath($path.$filename)) ) {
						$files[] = $path.$filename;
					}

				} elseif( str_starts_with($filter, 'extension=') ) {

					$file_extension = strtolower(pathinfo( get_abspath($path.$filename), PATHINFO_EXTENSION ));
					$possible_extensions = explode(',', str_replace('extension=', '', $filter));
					$possible_extensions = array_map( 'strtolower', $possible_extensions );

					if( in_array($file_extension, $possible_extensions) ) {
						$files[] = $path.$filename;
					}

				} elseif( $filter ) {

					if( $filename == $filter ) {
						$files[] = $path.$filename;
					}

				} else {
					
					$files[] = $path.$filename;

				}

				if( $recursive && is_dir($path.$filename) ) {

					if( $stop && file_exists(get_abspath($path.$filename.'/'.$stop)) ) {
						if( $depth >= $stop_depth ) {
							continue;
						}
						$depth++;
					}

					$subfolder_files = $this->read_folder( $path.$filename, $filter, $recursive, $stop, $stop_depth, $depth );
					if( count($subfolder_files) ) {
						$files = array_merge( $files, $subfolder_files );
					}
				}

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
