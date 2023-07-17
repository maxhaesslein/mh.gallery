<?php

class Image {
	
	private $path;
	private $gallery;
	private $slug;

	function __construct( $path, $gallery ) {

		$this->path = $path;

		$path_exp = explode('/', $path);
		$filename = array_pop($path_exp);
		$gallery_path = implode('/', $path_exp);

		$this->filename = $filename;

		$this->gallery = $gallery;

		$this->slug = sanitize_string($filename, true);

	}

	function get_slug() {
		return $this->slug;
	}

	function get_url() {

		$url = $this->gallery->get_url().$this->slug;

		return $url;
	}

}
