<?php

class Gallery {

	private $gallery_file;
	private $path;

	private $parent_gallery = false;
	private $is_root_gallery = false;

	private $sub_galleries = [];

	private $settings;
	private $images = NULL;
	private $hidden = false;
	private $secret = false;
	private $download_image_enabled = NULL;
	private $download_gallery_enabled = NULL;
	private $bridge_sort_order = NULL;

	function __construct( $gallery_file, $parent_gallery ) {

		if( ! $gallery_file ) {
			$path = 'content/';
			$this->is_root_gallery = true;
			if( file_exists($path.'gallery.txt') ) {
				$gallery_file = $path.'gallery.txt';
			}
		} else {
			$path = str_replace( 'gallery.txt', '', $gallery_file);
		}

		$this->path = $path;
		$this->parent_gallery = $parent_gallery;

		if( ! $parent_gallery ) {
			$this->is_root_gallery = true;
		}

		if( $gallery_file ) {
			$this->gallery_file = $gallery_file;
			$this->read_gallery_file();
		} else {
			$this->is_root_gallery = true;
		}

		$this->load_sub_galleries();

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

		if( ! $file ) return $this;

		$settings = read_settings_file( $file );

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


		$download_image_enabled = NULL;
		if( isset($settings['download_image_enabled']) ) {
			// own setting
			$download_image_enabled = $settings['download_image_enabled'];
			if( $download_image_enabled == 'false' || $download_image_enabled == '0' ) $download_image_enabled = false;
			$download_image_enabled = !! $download_image_enabled; // make bool
		} elseif( $this->parent_gallery ) {
			// setting from parent gallery
			$download_image_enabled = $this->parent_gallery->get_config( 'download_image_enabled', true );
		}
		if( is_null($download_image_enabled) ) {
			// fall back to config.php
			$download_image_enabled = get_config( 'download_image_enabled' );
		}
		$this->download_image_enabled = $download_image_enabled;


		$download_gallery_enabled = NULL;
		if( isset($settings['download_gallery_enabled']) ) {
			// own setting
			$download_gallery_enabled = $settings['download_gallery_enabled'];
			if( $download_gallery_enabled == 'false' || $download_gallery_enabled == '0' ) $download_gallery_enabled = false;
			$download_gallery_enabled = !! $download_gallery_enabled; // make bool
		} elseif( $this->parent_gallery ) {
			// setting from parent gallery
			$download_gallery_enabled = $this->parent_gallery->get_config( 'download_gallery_enabled', true );
		}
		if( is_null($download_gallery_enabled) ) {
			// fall back to config.php
			$download_gallery_enabled = get_config( 'download_gallery_enabled' );
		}
		$this->download_gallery_enabled = $download_gallery_enabled;


		return $this;
	}


	function load_sub_galleries(){

		$galleries_folder = new Folder( $this->path, 'gallery.txt', true );
		$subgallery_paths = $galleries_folder->get();

		// make sure that parent-galleries are listed before subgalleries:
		$subgallery_paths = array_map(function( $el ){
			return str_replace('gallery.txt','',$el);
		}, $subgallery_paths);
		sort($subgallery_paths);

		// only include one depth of subgalleries
		$used_subgallery_paths = [];
		foreach( $subgallery_paths as $subgallery_path ) {

			if( $subgallery_path == $this->path ) continue; // skip self

			foreach( $used_subgallery_paths as $used_subgallery_path ) {
				if( str_starts_with($subgallery_path, $used_subgallery_path) ) {
					continue 2;
				}
			}

			$used_subgallery_paths[] = $subgallery_path;
		}

		$gallery_sort_order = $this->get_config( 'gallery_sort_order', true );
		if( ! $gallery_sort_order ) { // fall back to config.php
			$gallery_sort_order = get_config( 'gallery_sort_order' );
		}

		$sub_galleries = [];
		$galleries_sort = [];

		foreach( $used_subgallery_paths as $subgallery_path ) {

			$gallery = new Gallery($subgallery_path.'gallery.txt', $this);

			$slug = $gallery->get_slug( true );

			if( ! $slug ) continue;

			if( $gallery_sort_order == 'slug' ) {
				$sort = $slug;
			} elseif( $gallery_sort_order == 'title' ) {
				$sort = $gallery->get_title();
			} elseif( $gallery_sort_order == 'foldername' ) {
				$subgallery_path_exp = explode('/', un_trailing_slash_it($subgallery_path));
				$sort = array_pop($subgallery_path_exp);
			} elseif( $gallery_sort_order == 'order' ) {
				$sort = $gallery->get_config( 'order' );
				if( ! $sort ) $sort = 'zzz-'.$gallery->get_title();
			} else { // fallback: slug
				$sort = $slug;
			}

			if( array_key_exists($slug, $sub_galleries) ) continue;

			$sub_galleries[$slug] = $gallery;
			$galleries_sort[] = $sort;

		}

		array_multisort( $galleries_sort, SORT_ASC, SORT_NATURAL|SORT_FLAG_CASE,  $sub_galleries );

		$this->sub_galleries = $sub_galleries;

		return $this;
	}


	function is_hidden(){
		return $this->hidden;
	}


	function is_secret() {

		if( $this->secret ) {
			return true;
		}

		return false;
	}


	function secret_provided() {

		if( ! $this->is_secret() ) return true;

		if( empty($_SESSION['secrets']) ) return false;

		$secrets = $_SESSION['secrets'];

		if( ! is_array($secrets) || ! count($secrets) ) return false;

		$hash = get_hash($this->get_slug());
		if( ! isset($secrets[$hash]) || ! is_array($secrets[$hash]) ) return false;

		foreach( $secrets[$hash] as $secret ) {
			if( $secret == $this->secret ) return true;
		}

		return false;
	}


	function is_download_image_enabled() {
		return $this->download_image_enabled;
	}


	function is_download_gallery_enabled() {
		return $this->download_gallery_enabled;
	}


	function get_zip_url() {

		if( ! $this->is_download_gallery_enabled() ) {
			return false;
		}

		$cache = $this->get_zip_cache();


		$cache->refresh_lifetime();

		$filename = $cache->get_file_name();

		$url = get_baseurl('zip/').un_trailing_slash_it($filename).'.zip';

		return $url;
	}


	function get_zip_download_url() {

		$url = get_baseurl('download/').un_trailing_slash_it($this->get_url(false)).'.zip';

		return $url;
	}


	function get_zip_filename() {
		return $this->get_slug().".zip";
	}


	function get_config( $option, $inherit = false ) {

		// get option from current gallery
		if( $this->settings && array_key_exists($option, $this->settings) ) {
			return $this->settings[$option];
		}

		if( $inherit && $this->parent_gallery ) {
			// try to get option from parent gallery
			$option = $this->parent_gallery->get_config( $option, $inherit );
			return $option;
		}

		// no config found
		return NULL;
	}


	function get_title() {
		$title = $this->get_config('title');

		if( ! $title && $this->is_root() ) {
			$title = get_config('site_title');
		}

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


	function get_path() {
		return $this->path;
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

		if( ! $skip_parent && $this->parent_gallery ) {
			$parent_slug = $this->parent_gallery->get_slug();
			if( $parent_slug ) $parent_slug = trailing_slash_it($parent_slug);
			$slug = $parent_slug.$slug;
		}
		
		return $slug;
	}


	function get_url( $full_url = true ) {
		$url = $this->get_slug();

		if( $full_url ) $url = url($url);

		return $url;
	}


	function get_parent_url( $full_url = true ) {

		if( ! $this->parent_gallery ) {
			return false;
		}

		if( $this->parent_gallery->is_root() && ! get_config('allow_overview') ) {
			return false;
		}

		$url = $this->parent_gallery->get_url(false);

		if( $full_url ) $url = url($url);

		return $url;
	}


	function get_sub_galleries() {

		if( $this->sub_galleries == NULL ) $this->load_sub_galleries();

		return $this->sub_galleries;
	}


	function get_sub_gallery( $slug ) {

		if( ! array_key_exists($slug, $this->sub_galleries) ) {
			return false;
		}

		return $this->sub_galleries[$slug];
	}


	function sub_gallery_exists( $slug ) {

		if( $this->get_sub_gallery($slug) ) {
			return true;
		}

		return false;
	}


	function get_images() {

		if( $this->images == NULL ) $this->load_images();

		return $this->images;
	}


	function get_image( $slug ) {

		// NOTE: in our $this->images array, the key of an image is their slug, but with a trailing '.'; this is important to have the key exist as a string, instead of an int. when receiving the image, we need to append the '.' again.

		if( $this->images == NULL ) $this->load_images();

		if( ! array_key_exists($slug.'.', $this->images) ) return false;

		return $this->images[$slug.'.'];
	}


	function get( $slug = false ) {

		if( $slug ) {

			if( $this->get_sub_gallery($slug) ) {
				return $this->get_sub_gallery($slug);
			} else if( $this->get_image($slug) ) {
				return $this->get_image($slug);
			}

			return false;
		}

		$gallery_content = array_merge($this->sub_galleries, $this->images);

		return $gallery_content;
	}


	function get_thumbnail_slug( $thumbnail_slug = false ) {

		$images = $this->get_images();

		if( ! count($images) ) return false;

		if( $thumbnail_slug ) {
			$thumbnail_slug = $this->sanitize_thumbnail_slug($thumbnail_slug);
			if( array_key_exists($thumbnail_slug.'.', $images) ) {
				return $thumbnail_slug;
			}
		}

		// no thumbnail defined, use the first image:
		$thumbnail_slug = array_keys($images)[0];
		$thumbnail_slug = substr($thumbnail_slug, 0, -1); // removed additional dot from slug

		return $thumbnail_slug;
	}


	function sanitize_thumbnail_slug( $thumbnail_slug ) {
		$thumbnail_slug = explode('.', $thumbnail_slug);
		unset($thumbnail_slug[count($thumbnail_slug)-1]);
		$thumbnail_slug = sanitize_string(implode('.', $thumbnail_slug), true);

		return $thumbnail_slug;
	}


	function get_thumbnail( $slug = false ) {

		if( ! $slug ) {

			$thumbnail_slug = $this->get_config('thumbnail');

			if( $thumbnail_slug ) {
				// the slug may include a path to a sub-gallery
				$thumbnail_slug_exp = explode('/', $thumbnail_slug);
				if( count($thumbnail_slug_exp) > 1 ) {
					// sub-gallery
					$sub_gallery = $this;
					$thumbnail_slug = array_pop($thumbnail_slug_exp);
					foreach( $thumbnail_slug_exp as $thumbnail_slug_part ) {
						$thumbnail_slug_part = sanitize_string($thumbnail_slug_part);
						$sub_gallery = $sub_gallery->get_sub_gallery($thumbnail_slug_part);
						if( ! $sub_gallery ) break;
					}

					if( $sub_gallery ) {
						$thumbnail_slug = $this->sanitize_thumbnail_slug($thumbnail_slug);
						return $sub_gallery->get_thumbnail($thumbnail_slug);
					}
				} else {
					// no sub-gallery, check current gallery for image or use first image
					$slug = $this->get_thumbnail_slug( $thumbnail_slug );
				}
			} else {
				$slug = $this->get_thumbnail_slug();
			}

			if( ! $slug ) {
				$sub_galleries = $this->get_sub_galleries();
				if( count($sub_galleries) > 0 ) {
					// use the first sub-gallery (that is not hidden)
					foreach( $sub_galleries as $sub_gallery ) {
						if( $sub_gallery->is_hidden() ) {
							$sub_gallery = false;
							continue;
						}

						break;
					}
					if( ! $sub_gallery ) return false;
					return $sub_gallery->get_thumbnail();
				}
			}

		}
		
		if( ! $slug ) {
			return false;
		}

		return $this->get_image($slug);
	}


	function get_image_link( $slug ) {

		if( $this->images == NULL ) $this->load_images();

		$slug .= '.';

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

		$current_index = array_search($current_image_slug.'.', $indexes);

		if( $direction == 'next' ) {
			$next_index = $current_index+1;
			if( $next_index >= count($indexes) ) return false;
		} elseif( $direction == 'prev' ) {
			$next_index = $current_index-1;
			if( $next_index < 0 ) return false;
		} else {
			return false;
		}

		$slug = $indexes[$next_index];

		$slug = substr( $slug, 0, -1 );

		return $slug;
	}


	function load_images() {

		$extensions = get_config('image_extensions');

		$folder = new Folder( $this->path, 'extension='.implode(',', $extensions) );

		$files = $folder->get();

		$image_sort_order = $this->get_config( 'image_sort_order', true );
		if( ! $image_sort_order ) { // fall back to config.php
			$image_sort_order = get_config( 'image_sort_order' );
		}

		$images = [];
		$images_sort = [];

		foreach( $files as $file ) {

			$file = explode('/', $file);
			$filename = end($file);
			
			$image = new Image($filename, $this);

			if( $image_sort_order == 'filedate' ) {

				$sort = $image->get_filedate();

			} elseif( $image_sort_order == 'exifdate' ) {

				$sort = $image->get_exif_data('DateTimeOriginal');
				if( ! $sort ) $sort = $image->get_exif_data('DateTime');
				if( ! $sort ) $sort = $image->get_exif_data('DateTimeDigitized');

				if( $sort ) {
					$sort = strtotime( $sort );
				} else {
					$sort = $image->get_filedate(); // fallback to file modification date
				}

			} elseif( $image_sort_order == 'bridge' ) {

				$bridge_position = $image->get_bridge_position();

				if( $bridge_position === false ) {
					$bridge_position = 999999999999; // TODO: check, how we want to handle this
				}

				$sort = str_pad( $bridge_position, 6, 0, STR_PAD_LEFT ); // add leading zeros

			} else {

				// filename
				$sort = $image->get_slug();

			}

			$key = $image->get_key();

			if( array_key_exists($key, $images) ) continue;

			$images[$key] = $image;
			$images_sort[] = $sort;
		}

		array_multisort($images_sort, $images);

		$this->images = $images;

		return $this;
	}


	function get_bridge_sort_order() {

		// NOTE: images may be sorted in Adobe Bridge; this creates a .BridgeSort file. We can use this file, to determine the position of the images in this gallery

		if( $this->bridge_sort_order === NULL ) {

			$bridge_file = get_abspath($this->path.'.BridgeSort');

			if( ! file_exists($bridge_file) ) return false;

			$bridge = simplexml_load_file($bridge_file);

			if( $bridge === false ) return false;

			$bridge_sort_order = [];

			foreach( $bridge->files->item as $item ) {
				$item_name = (string) $item['key'];

				// names have the modification date (?) appended to them, so we need to remove it;
				// this changes '01.jpg20230908174751' to '01.jpg'
				$item_name = substr($item_name, 0, -14);

				$bridge_sort_order[] = $item_name;
			}

			$this->bridge_sort_order = $bridge_sort_order;

		}

		return $this->bridge_sort_order;
	}


	function get_zip_cache() {
		// TODO: currently, when getting the cache file, we only check if the gallery slug or the number of images changed. maybe we want to add something to check if individual images changed, like a complete count of filesizes of all images or something like that.
		$cache_filename = $this->get_zip_filename().$this->get_image_count();

		$cache_lifetime = get_config( 'zip_lifetime' );
		$cache = new Cache( 'zip', $cache_filename, false, $cache_lifetime );
		
		return $cache;
	}


	function get_missing_zip_images() {

		$expected_images = $this->get_images();

		$cache = $this->get_zip_cache();

		if( ! $cache->exists() ) {
			return $expected_images;
		}

		$zip_target = get_abspath($cache->get_file_path());

		$zip = new ZipArchive;
		$zip->open( $zip_target );

		$missing_images = [];
		foreach( $expected_images as $image ) {
			if( $zip->locateName($image->get_original_filename()) !== false ) continue;

			$missing_images[] = $image;
		}

		return $missing_images;
	}


	function is_zipfile_ready() {

		if( count($this->get_missing_zip_images()) > 0 ) return false;

		return true;
	}


	function get_zip_size() {
		
		$cache = $this->get_zip_cache();

		$size = filesize(get_abspath($cache->get_file_path()));

		$size = format_filesize($size);

		return $size;
	}


	function add_batch_to_zip() {

		$cache = $this->get_zip_cache();

		$missing_images = $this->get_missing_zip_images();

		$images_per_batch = 10;

		$missing_images = array_slice($missing_images, 0, $images_per_batch, true);

		$zip_target = get_abspath($cache->get_file_path());
		$zip = new ZipArchive;
		if( $zip->open($zip_target, ZipArchive::CREATE) !== TRUE ) {
			debug("could not create zip file");
			exit;
		}

		foreach( $missing_images as $image ) {
			$zip->addFile( $image->get_original_filepath(), $image->get_original_filename() );
		}

		$zip->close();

		return count($this->get_missing_zip_images());
	}


	function is( $test ) {

		if( $test == 'gallery' ) return true;

		return false;
	}


	function is_root() {
		return $this->is_root_gallery;
	}

	
}
