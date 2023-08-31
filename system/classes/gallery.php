<?php

class Gallery {

	private $gallery_file;
	private $path;
	private $settings;
	private $images = NULL;
	private $hidden = false;
	private $secret = false;
	private $download_image_enabled;
	private $download_gallery_enabled;

	function __construct( $gallery_file ) {

		$path = str_replace( 'gallery.txt', '', $gallery_file);

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
		hidden: true
		secret: 12345
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

		if( isset($settings['hidden']) ) {
			$hidden = $settings['hidden'];
			if( $hidden == 'false' || $hidden == '0' ) $hidden = false;
			$hidden = !! $hidden; // make bool
			$this->hidden = $hidden;
		}

		if( isset($settings['secret']) ) {
			$this->secret = $settings['secret'];
			$this->hidden = true; // force gallery to be hidden, if a secret is set
		}

		if( isset($settings['download_image_enabled']) ) {
			$download_image_enabled = $settings['download_image_enabled'];
			if( $download_image_enabled == 'false' || $download_image_enabled == '0' ) $download_image_enabled = false;
			$download_image_enabled = !! $download_image_enabled; // make bool
		} else {
			$download_image_enabled = get_config( 'download_image_enabled' );
		}
		$this->download_image_enabled = $download_image_enabled;

		if( isset($settings['download_gallery_enabled']) ) {
			$download_gallery_enabled = $settings['download_gallery_enabled'];
			if( $download_gallery_enabled == 'false' || $download_gallery_enabled == '0' ) $download_gallery_enabled = false;
			$download_gallery_enabled = !! $download_gallery_enabled; // make bool
		} else {
			$download_gallery_enabled = get_config( 'download_gallery_enabled' );
		}
		$this->download_gallery_enabled = $download_gallery_enabled;


		return $this;
	}


	function is_hidden(){
		return $this->hidden;
	}


	function is_download_image_enabled() {
		return $this->download_image_enabled;
	}


	function is_download_gallery_enabled() {
		return $this->download_gallery_enabled;
	}


	function get_config( $option ) {

		if( array_key_exists($option, $this->settings) ) {
			return $this->settings[$option];
		}

		return false;
	}


	function get_title() {
		$title = $this->get_config('title');

		if( ! $title ) {
			$title = $this->get_slug( true );
		}

		return $title;
	}


	function get_path() {
		return $this->path;
	}


	function get_slug( $skip_secret = false ) {
		$slug = $this->get_config('slug');

		if( ! $slug ) {
			$path = explode('/', $this->path);
			$path = array_filter($path); // remove empty elements
			$slug = end($path);
		}

		$slug = sanitize_string($slug);

		if( ! $skip_secret && $this->secret ) {
			$slug .= '-'.trim($this->secret);
		}
		
		return $slug;
	}


	function get_url( $full_url = true ) {
		$url = $this->get_slug();

		if( $full_url ) $url = url($url);

		return $url;
	}


	function get_images() {

		if( $this->images == NULL ) $this->load_images();

		return $this->images;
	}


	function get_image( $slug ) {

		if( $this->images == NULL ) $this->load_images();

		if( ! array_key_exists($slug, $this->images) ) return false;

		return $this->images[$slug];
	}


	function get_thumbnail_slug() {

		$images = $this->get_images();

		if( ! count($images) ) return false;

		$thumbnail_slug = $this->get_config('thumbnail');
		if( $thumbnail_slug ) {
			$thumbnail_slug = explode('.', $thumbnail_slug);
			unset($thumbnail_slug[count($thumbnail_slug)-1]);
			$thumbnail_slug = sanitize_string(implode('.', $thumbnail_slug));
			if( array_key_exists($thumbnail_slug, $images) ) {
				return $thumbnail_slug;
			}
		}

		$thumbnail_slug = array_keys($images)[0];

		return $thumbnail_slug;
	}


	function get_image_link( $slug ) {

		if( $this->images == NULL ) $this->load_images();

		if( ! array_key_exists($slug, $this->images) ) return false;

		return $this->images[$slug]->get_link();
	}


	function get_image_count() {

		if( $this->images == NULL ) $this->load_images();

		return count($this->images);
	}


	function get_adjacent_image_slug( $current_image_slug, $direction = 'next' ) {

		if( $this->images == NULL ) $this->load_images();

		$indexes = array_keys($this->images);

		$current_index = array_search($current_image_slug, $indexes);

		if( $direction == 'next' ) {
			$next_index = $current_index+1;
			if( $next_index >= count($indexes) ) return false;
		} elseif( $direction == 'prev' ) {
			$next_index = $current_index-1;
			if( $next_index < 0 ) return false;
		} else {
			return false;
		}

		return $indexes[$next_index];
	}


	function load_images() {

		$extensions = get_config('image_extensions');

		$folder = new Folder( $this->path, 'extension='.implode(',', $extensions) );

		$files = $folder->get();

		$images = [];

		foreach( $files as $file ) {

			$file = explode('/', $file);
			$filename = end($file);
			
			$image = new Image($filename, $this);

			$slug = $image->get_slug();

			$images[$slug] = $image;
		}

		ksort($images);

		$this->images = $images;

		return $this;
	}
	
}
