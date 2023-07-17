<?php

class Gallery {

	private $gallery_file;
	private $path;
	private $settings;

	function __construct( $gallery_file ) {

		$path = get_abspath( str_replace( 'gallery.txt', '', $gallery_file) );

		$this->path = $path;
		$this->gallery_file = $gallery_file;

		$this->read_gallery_file();

	}
	

	function read_gallery_file() { 

		// NOTE: the structure of the gallery.txt file is as follows: every key/value combination has its own line. key and value are seperated by a ':'. if there are multiple instances of the same key, the last instance will overwrite all the instances before. example file contents:
		/*
		slug: this-is-my-gallery-slug
		title: this is the gallery title: it can also have a colon in the title.
		this line will be ignored
		*/

		$file = $this->gallery_file;

		$file_contents = file_get_contents($file);

		$file_contents = str_replace( ["\r\n", "\r"], "\n", $file_contents );

		$file_contents = explode( "\n", $file_contents );

		$settings = [];

		foreach( $file_contents as $line ) {

			$line = explode( ':', $line );

			if( count($line) < 2 ) continue;

			$key = trim(array_shift($line));
			$value = trim(implode(':', $line));

			$settings[$key] = $value;

		}

		$this->settings = $settings;

		return $this;
	}


	function get_config( $option ) {

		if( array_key_exists($option, $this->settings) ) {
			return $this->settings[$option];
		}

		return false;
	}
	
}
