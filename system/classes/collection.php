<?php

class Collection {

	private $galleries = [];
	private $collections = [];

	private $parent_collection = false;
	private $path;
	private $file = false; // root collection may not have a file; all other collections must have one
	private $slug;

	private $settings;
	private $hidden;

	function __construct( $path = 'content/', $parent_collection = false ) {

		$this->path = $path;

		$collection_file = $path.'collection.txt';
		if( file_exists(get_abspath($collection_file)) ) {
			$this->file = $collection_file;
			$this->read_collection_file();
		}

		if( $parent_collection ) $this->parent_collection = $parent_collection;


		$galleries = [];
		$galleries_folder = new Folder( $path, 'gallery.txt', true ); // TODO: don't add galleries, that are inside other collections
		$subgalleries = $galleries_folder->get();
		if( ! count($subgalleries) ) $subgalleries = [];
		foreach( $subgalleries as $subgallery_file ) {

			$gallery = new Gallery($subgallery_file, $this);

			$slug = $gallery->get_slug( false, true );

			if( ! $slug ) continue;

			$galleries[$slug] = $gallery;
		}

		$this->galleries = $galleries;


		$collections = [];
		$collections_folder = new Folder( $path, 'collection.txt', true );
		$subcollections = $collections_folder->get();
		if( ! count($subcollections) ) $subcollections = [];
		foreach( $subcollections as $subcollection_file ) {

			$subcollection_folder = str_replace('collection.txt', '', $subcollection_file);

			if( $subcollection_folder == $path ) continue;

			$collection = new Collection($subcollection_folder, $this);

			$slug = $collection->get_slug( true );

			if( ! $slug ) continue;

			$collections[$slug] = $collection;
		}

		$this->collections = $collections;

	}


	function read_collection_file() { 

		// NOTE: the structure of the collection.txt file is as follows: every key/value combination has its own line. key and value are seperated by a ':'. if there are multiple instances of the same key, the last instance will overwrite all the instances before. example file contents:
		/*
		slug: this-is-my-collection-slug
		title: this is the collection title: it can also have a colon in the title.
		this line will be ignored
		hidden: true

		the root collection may not have a collection file.
		*/

		if( ! $this->file ) return $this;

		$file = $this->file;

		$settings = read_settings_file( $file );

		$this->settings = $settings;

		if( isset($settings['hidden']) ) {
			$hidden = $settings['hidden'];
			if( $hidden == 'false' || $hidden == '0' ) $hidden = false;
			$hidden = !! $hidden; // make bool
			$this->hidden = $hidden;
		}

		// TODO: add secret for collections

		return $this;
	}


	function is_hidden(){
		return $this->hidden;
	}


	function get_config( $option ) {

		if( ! $this->settings ) return NULL;

		if( array_key_exists($option, $this->settings) ) {
			return $this->settings[$option];
		}

		return NULL;
	}


	function get_slug( $skip_parent = false ) {

		$slug = $this->get_config('slug');

		if( ! $slug ) {
			$path = explode('/', $this->path);
			$path = array_filter($path); // remove empty elements
			$slug = end($path);
		}

		$slug = sanitize_string($slug, true);

		if( $slug == 'content' ) $slug = ''; // the root collection should return an empty slug

		if( ! $skip_parent && $this->parent_collection ) {
			$parent_slug = $this->parent_collection->get_slug();
			if( $parent_slug ) $parent_slug = trailing_slash_it($parent_slug);
			$slug = $parent_slug.$slug;
		}
		
		return $slug;
	}


	function get_title() {
		$title = $this->get_config('title');

		if( ! $title ) {
			$title = $this->get_slug( true );
		}

		$allowed_tags = get_config('allowed_tags');
		if( $allowed_tags ) {
			$title = strip_tags( $title, $allowed_tags );
		}

		return $title;
	}


	function get_description(){
		$description = $this->get_config('description');

		$allowed_tags = get_config('allowed_tags');
		if( $allowed_tags ) {
			$description = strip_tags( $description, $allowed_tags );
		}

		return $description;
	}


	function get_thumbnail_slug() {
		// TODO
		return false;
	}


	function get_path() {
		return $this->path;
	}


	function get_url( $full_url = true ) {
		$url = $this->get_slug();

		if( $full_url ) $url = url($url);

		return $url;
	}


	function gallery_exists( $slug ) {
		
		if( $this->get_gallery($slug) ) {
			return true;
		}

		return false;
	}

	function collection_exists( $slug ) {
		
		if( $this->get_collection($slug) ) {
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

	function get_collection( $slug ) {
		
		if( ! array_key_exists($slug, $this->collections) ) {
			return false;
		}

		return $this->collections[$slug];
	}


	function get( $slug = false ) {

		if( $slug ) {

			if( $this->get_collection($slug) ) {
				return $this->get_collection($slug);
			} elseif( $this->get_gallery($slug) ) {
				return $this->get_gallery($slug);
			}

			return false;

		}

		$collection_content = array_merge($this->collections, $this->galleries);

		ksort($collection_content);

		return $collection_content;
	}


	function is( $test ) {
		if( $test == 'collection' ) return true;

		return false;
	}

}