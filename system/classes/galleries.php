<?php

class Galleries {

	private $galleries = [];

	function __construct() {

		$folder = new Folder( 'content/', 'gallery.txt', true );

		$files = $folder->get();

		if( ! count($files) ) return;

		$galleries = [];

		foreach( $files as $file ) {
			$gallery = new Gallery($file);

			$slug = $gallery->get_slug();

			if( ! $slug ) continue;

			$galleries[$slug] = $gallery;
		}

		$this->galleries = $galleries;

	}

	function exists( $slug ) {
		
		if( $this->get_gallery($slug) ) {
			return true;
		}

		return false;
	}
	
	function get_gallery( $slug ) {
		
		if( ! array_key_exists($slug, $this->galleries) ) {
			return false;
		}

		return $this->galleries[$slug];
	}

	function get() {
		return $this->galleries;
	}

}
