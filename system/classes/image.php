<?php

class Image {
	
	private $filename;
	private $file_extension;
	private $path;
	private $gallery;
	private $slug; // this is the slug for the url
	private $key; // this is the key this image inside the gallery images array

	private $image_type;
	private $orientation;
	private $rotated = false;
	private $format;

	private $width;
	private $height;

	private $adjacent_image = [];
	private $file_mod_time = NULL;
	private $exif_data = NULL;

	private $image_meta_loaded = false;


	function __construct( $filename, $gallery ) {

		$this->filename = $filename;
		$this->gallery = $gallery;

		$this->path = get_abspath(trailing_slash_it($gallery->get_path()).$filename);

		$slug = remove_fileextension($filename);
		$this->slug = sanitize_string($slug);

		$this->key = get_image_key_from_slug($this->slug);


		if( ! file_exists($this->path) ) {
			debug("image file not found", $this->path);
			return false;
		}

	}


	private function load_image_meta() {

		if( $this->image_meta_loaded ) return $this;

		$filepath = $this->path;

		$image_meta = getimagesize( $filepath );
		if( ! $image_meta ) {
			debug("no image meta", $filepath);
			return false;
		}

		$this->width = $image_meta[0];
		$this->height = $image_meta[1];
		$this->image_type = $image_meta[2];


		$exif = @exif_read_data( $filepath );
		if( $exif && ! empty($exif['Orientation']) ) {
			$this->orientation = $exif['Orientation'];
		}

		if( $this->orientation == 6 || $this->orientation == 8 || $this->orientation == 5 || $this->orientation == 7 ) {
			$this->rotated = true;
		}

		if( $this->width > $this->height ) {
			$this->format = 'landscape';
		} elseif( $this->width < $this->height ) {
			$this->format = 'portrait';
		} else {
			$this->format = 'square';
		}

		if( $this->image_type == IMAGETYPE_JPEG ) {
			$this->set_image_type('jpg');
		} elseif( $this->image_type == IMAGETYPE_PNG ) {
			$this->set_image_type('png');
		} elseif( $this->image_type == IMAGETYPE_WEBP ) {
			$this->set_image_type('webp');
		} elseif( $this->type_supported('avif') && $this->image_type == IMAGETYPE_AVIF ) {
			$this->set_image_type('avif');
		} else {
			debug( 'unknown image type '.$this->image_type);
		}

		$this->image_meta_loaded = true;

		return $this;
	}


	private function type_supported( $type ) {

		if( $type == 'jpg' || $type == 'jpeg' ) return true;

		// config can have 'webp_enabled', 'avif_enabled' and so on ..
		if( ! get_config($type.'_enabled') ) return false;

		if( ! defined('IMAGETYPE_'.strtoupper($type)) ) return false;

		if( ! function_exists('imagecreatefrom'.$type) ) return false;

		if( ! function_exists('image'.$type) ) return false;

		return true;
	}


	function get_slug() {
		return $this->slug;
	}


	function get_key() {
		return $this->key;
	}


	function get_filedate() {

		if( $this->file_mod_time === NULL ) {
			$this->file_mod_time = filemtime( $this->path );
		}

		return $this->file_mod_time;
	}


	function get_exif_data( $field_key, $group_key = 'EXIF' ) {

		if( $this->exif_data === NULL ) {
			$this->exif_data = exif_read_data( $this->path, false, true );
		}

		if( ! array_key_exists($group_key, $this->exif_data) ) {
			return false;
		}

		$group = $this->exif_data[$group_key];

		if( ! array_key_exists($field_key, $group) ) return false;

		return $group[$field_key];
	}


	function get_bridge_position() {
		// NOTE: images may be sorted in Adobe Bridge; this creates a .BridgeSort file. We can use this file, to determine the position of this image

		$bridge_sort_order = $this->gallery->get_bridge_sort_order();

		if( ! $bridge_sort_order ) return false;

		return array_search($this->filename, $bridge_sort_order); // if image is not found, this returns false, otherwise the position in the array
	}


	function get_link() {

		if( ! $this->gallery ) return false;

		$url = trailing_slash_it($this->gallery->get_url().$this->slug);

		return $url;
	}


	private function get_index() {

		if( ! $this->gallery ) return false;

		$indexes = array_keys($this->gallery->get_images());

		$index = array_search($this->key, $indexes);

		return $index;
	}


	function get_number() {
		$index = $this->get_index();

		if( $index === false ) return false;

		return $index+1;
	}


	function get_adjacent_image( $direction = 'next' ) {

		$adjacent_image = false;

		if( $direction != 'prev' ) $direction = 'next';

		if( array_key_exists($direction, $this->adjacent_image) ) {
			return $this->adjacent_image[$direction];
		}
		
		$adjacent_image_slug = $this->gallery->get_adjacent_image_slug( $this->get_slug(), $direction );

		if( $adjacent_image_slug ) {
			$adjacent_image = $this->gallery->get_image($adjacent_image_slug);
		}

		$this->adjacent_image[$direction] = $adjacent_image;

		return $adjacent_image;
	}


	function get_original_filename(){
		return $this->filename;
	}


	function get_original_filepath(){
		return $this->path;
	}


	private function get_default_args() {

		$this->load_image_meta();

		$defaults = [
			'width' => $this->width,
			'height' => $this->height,
			'crop' => false,
			'type' => $this->file_extension,
			'quality' => get_config( 'default_image_quality' ),
		];

		return $defaults;
	}


	private function get_filename( $args = [] ) {

		$args = array_merge($this->get_default_args(), $args);

		$filename = $this->slug.'_'.$args['width'].'x'.$args['height'];
		if( $args['crop'] ) $filename .= '-crop';
		$filename .= '-'.$args['quality'].'.'.$args['type'];

		return $filename;
	}


	private function get_image_path( $args = [] ) {

		$cache = $this->get_cache( $args );
		$cache_filename = $cache->get_file_name();

		$path = 'img/'.trailing_slash_it($this->gallery->get_url(false)).$cache_filename;

		return $path;
	}


	function get_image_url( $args = [] ) {

		$this->create_placeholder_file($args);

		$url = get_baseurl($this->get_image_path($args));

		return $url;
	}


	private function create_placeholder_file( $args ) {

		// NOTE: this checks, if the cached version of this image file exists. if it does not exist, it creates an empty placeholder file, so that when we generate the file, we know that we are approved to create this image file. this is a safety measure, so that the server can't be killed by accessing a lot of different versions of a file.

		$cache = $this->get_cache($args);

		$cache->add_placeholder_file();

		return $this;
	}


	private function get_picture_srcset( $width ) {

		$default_image_quality = get_config('default_image_quality');
		$default_modern_image_quality = get_config('default_modern_image_quality');

		$picture = [

			'avif' => [
				'320w' => [
					'width' => 320,
					'quality' => $default_modern_image_quality,
				],
				'640w' => [
					'width' => 640,
					'quality' => $default_modern_image_quality,
				],
			],

			'webp' => [
				'320w' => [
					'width' => 320,
					'quality' => $default_modern_image_quality,
				],
				'640w' => [
					'width' => 640,
					'quality' => $default_modern_image_quality,
				],
			],

			'jpg' => [
				'320w' => [
					'width' => 320,
					'quality' => $default_image_quality,
				],
			],

		];

		if( $width > 640 ) {

			$picture['jpg']['640w'] = [
				'width' => 640,
				'quality' => $default_image_quality,
			];

			$picture['avif']['800w'] = [
				'width' => 800,
				'quality' => $default_modern_image_quality,
			];

			$picture['webp']['800w'] = [
				'width' => 800,
				'quality' => $default_modern_image_quality,
			];

		}

		if( $width > 800 ) {

			$picture['jpg']['800w'] = [
				'width' => 800,
				'quality' => $default_image_quality,
			];

			$picture['avif']['1200w'] = [
				'width' => 1200,
				'quality' => $default_modern_image_quality,
			];

			$picture['webp']['1200w'] = [
				'width' => 1200,
				'quality' => $default_modern_image_quality,
			];

		}

		if( $width > 1200 ) {

			$picture['jpg']['1200w'] = [
				'width' => 1200,
				'quality' => $default_image_quality,
			];

			$picture['avif']['2000w'] = [
				'width' => 2000,
				'quality' => $default_modern_image_quality,
			];

			$picture['webp']['2000w'] = [
				'width' => 2000,
				'quality' => $default_modern_image_quality,
			];

		}

		return $picture;
	}


	function get_html( $args = [], $skip_container = false, $sizes = false, $lazyloading = true ) {

		$this->load_image_meta();

		$classes = [ 'image', 'image-'.$this->format ];

		if( ! empty($args['width']) ) {
			$width = $args['width'];
		} else {
			$width = $this->width;
		}

		if( ! empty($args['height']) ) {
			$height = $args['height'];
		} else {
			$height = round($width*$this->height/$this->width);
		}

		$crop = false;
		if( ! empty($args['crop']) ) $crop = $args['crop'];

		if( $this->rotated && ! $crop ) {
			$tmp_height = $height;
			$height = $width;
			$width = $tmp_height;
		}

		$picture = $this->get_picture_srcset( $width );

		$main_src = $this->get_image_url([
			'width' => $width,
			'height' => $height,
			'crop' => $crop,
		]);

		$html = '';

		if( ! $skip_container ) $html .= '<div class="image-container" style="aspect-ratio: '.$width.'/'.$height.';">';
		$html .= '<picture'.get_class_attribute($classes).' style="aspect-ratio: '.$width.'/'.$height.';">';

		foreach( $picture as $type => $sources ) {

			if( ! $this->type_supported($type) ) {
				continue;
			}

			$srcset = [];

			foreach( $sources as $size => $image_url_args ) {

				$image_url_args['type'] = $type;

				if( $crop ) {
					$image_url_args['height'] = round($image_url_args['width']*$height/$width);
					$image_url_args['crop'] = true;
				} else {
					$image_url_args['height'] = round($image_url_args['width']*$this->height/$this->width);
					$image_url_args['crop'] = false;
				}

				$image_url = $this->get_image_url($image_url_args);
				$srcset[] = $image_url.' '.$size;
			}

			$srcset = implode(', ', $srcset);


			if( $type == 'jpg' ) { 

				$html .= '<img src="'.$main_src.'" width="'.$width.'" height="'.$height.'"';
					if( $lazyloading ) $html .= ' loading="lazy" decoding="async"';
					else $html .= ' fetchpriority="high" decoding="sync"';
				if( $sizes ) $html .= 'sizes="'.$sizes.'" ';
				$html .= 'srcset="'.$srcset.'">';

			} else { // webp/avif/...

				$html .= '<source ';
				if( $sizes ) $html .= 'sizes="'.$sizes.'" ';
				$html .= 'srcset="'.$srcset.'" type="image/'.$type.'">';

			}


		}

		$html .= '</picture>';
		if( ! $skip_container ) $html .= '</div>';

		return $html;
	}


	private function set_image_type( $new_type ) {

		if( $new_type == 'jpg' ) {
			$this->mime_type = 'image/jpeg';
			$this->file_extension = 'jpg';
		} elseif( $new_type == 'png' ) {
			$this->mime_type = 'image/png';
			$this->file_extension = 'png';
		} elseif( $new_type == 'webp' ) {
			$this->mime_type = 'image/webp';
			$this->file_extension = 'webp';
		} elseif( $this->type_supported('avif') && $new_type == 'avif' ) {
			$this->mime_type = 'image/avif';
			$this->file_extension = 'avif';
		}

		return $this;
	}


	private function read_image() {

		$this->load_image_meta();

		$image = false;

		if( $this->image_type == IMAGETYPE_JPEG ) {

			$image = imagecreatefromjpeg( $this->path );

		} elseif( $this->image_type == IMAGETYPE_PNG ) {

			$image = imagecreatefrompng( $this->path );

			// handle transparency loading:
			imageAlphaBlending( $image, false );
			imageSaveAlpha( $image, true );

		} elseif( $this->image_type == IMAGETYPE_WEBP ) {

			$image = imagecreatefromwebp( $this->path );

			// handle transparency loading:
			imageAlphaBlending( $image, false );
			imageSaveAlpha( $image, true );

		} elseif( $this->type_supported('avif') && $this->image_type == IMAGETYPE_AVIF ) {

			$image = imagecreatefromavif( $this->path );

			// handle transparency loading:
			imageAlphaBlending( $image, false );
			imageSaveAlpha( $image, true );

		}


		if( ! $image ) {
			debug( 'could not load image with image-type '.$this->image_type );
			return false;
		}


		return $image;
	}


	private function fill_with_backgroundcolor( $image, $transparent_color = [255, 255, 255] ) {

		$this->load_image_meta();

		$width = $this->width;
		$height = $this->height;
		if( $this->rotated ) {
			$width = $this->height;
			$height = $this->width;
		}

		$background_image = imagecreatetruecolor( $width, $height );
		$background_color = imagecolorallocate( $background_image, $transparent_color[0], $transparent_color[1], $transparent_color[2] );

		imagefill( $background_image, 0, 0, $background_color );
		imagecopy( $background_image, $image, 0, 0, 0, 0, $width, $height );

		$image = $background_image;

		imagedestroy( $background_image );

		return $image;
	}


	private function image_rotate( $image, $src_width, $src_height ) {

		$this->load_image_meta();

		$width = $src_width;
		$height = $src_height;

		$degrees = false;
		// NOTE: we ignore mirrored images (4, 5, 7) for now, and just rotate them like they would be non-mirrored (3, 6, 8)
		if( $this->orientation == 3 || $this->orientation == 4 ) {
			$degrees = 180;
		} elseif( $this->orientation == 6 || $this->orientation == 5 ) {
			$degrees = 270;
			$width = $src_height;
			$height = $src_width;
		} elseif( $this->orientation == 8 || $this->orientation == 7 ) {
			$degrees = 90;
			$width = $src_height;
			$height = $src_width;
		}

		if( $degrees ) $image = imagerotate( $image, $degrees, 0 );

		return [ $image, $width, $height ];
	}


	private function get_cache( $args = [] ) {

		$cache_filename = trailing_slash_it($this->gallery->get_url(false)).$this->get_filename( $args );
		$cache = new Cache( 'image', $cache_filename, true, true );

		return $cache;
	}


	function output( $args = [] ) {
		// NOTE: this assumes we did not output any headers or HTML yet!

		$this->load_image_meta();

		if( $args['width'] && ! $args['height'] ) {
			$args['height'] = $args['width']*$this->height/$this->width;
			$args['crop'] = false;
		}

		$args = array_merge($this->get_default_args(), $args);

		$src_width = $this->width;
		$src_height = $this->height;

		$crop = false;
		if( ! empty($args['crop']) ) $crop = $args['crop'];

		$width = $args['width'];
		$height = $args['height'];
		if( $this->rotated && ! $crop ) {
			$width = $args['height'];
			$height = $args['width'];
		}

		$type = $args['type'];
		$quality = $args['quality'];

		$image_blob = $this->read_image();
		if( ! $image_blob ) {
			debug('could not load image', $image);
			exit;
		}

		list( $image_blob, $src_width, $src_height ) = $this->image_rotate( $image_blob, $src_width, $src_height );

		if( $src_width > $width || $src_height > $height ) {

			$image_blob_resized = imagecreatetruecolor( $width, $height );

			if( $type == 'jpg' ) {

				$image_blob = $this->fill_with_backgroundcolor( $image_blob );

			} elseif( ( $this->image_type == IMAGETYPE_PNG ) 
				|| $this->image_type == IMAGETYPE_WEBP 
			) {
				// handle alpha channel
				imageAlphaBlending( $image_blob_resized, false );
				imageSaveAlpha( $image_blob_resized, true );
			}

			// NOTE: currently, we just center the image on crop
			$src_width_cropped = $src_width;
			$src_height_cropped = (int) round($src_width_cropped * $height/$width);
			if( $src_height_cropped > $src_height ) {
				$src_height_cropped = $src_height;
				$src_width_cropped = (int) round($src_height_cropped * $width/$height);
			}
			$src_x = (int) round(($src_width - $src_width_cropped)/2);
			$src_y = (int) round(($src_height - $src_height_cropped)/2);

			imagecopyresampled( $image_blob_resized, $image_blob, 0, 0, $src_x, $src_y, $width, $height, $src_width_cropped, $src_height_cropped );

			imagedestroy($image_blob);

			$image_blob = $image_blob_resized;

		}


		$cache = $this->get_cache( $args );


		// check, if a placeholder file exists. if it does not exist, we are not allowed to create this image! see create_placeholder_file() for more info.
		if( ! $cache->has_placeholder() ) {
			return false;
		}

		if( $type == 'jpg' ) {

			ob_start();
			imagejpeg( $image_blob, NULL, $quality );
			$data = ob_get_contents();
			ob_end_clean();
			$cache->add_data( $data );

			header( 'Content-Type: image/jpeg' );
			echo $data;

		} elseif( $type == 'png' ) {

			ob_start();
			imagepng( $image_blob );
			$data = ob_get_contents();
			ob_end_clean();
			$cache->add_data( $data );

			header( 'Content-Type: image/png' );
			echo $data;

		} elseif( $type == 'webp' ) {

			ob_start();
			imagewebp( $image_blob );
			$data = ob_get_contents();
			ob_end_clean();
			$cache->add_data( $data );

			header( 'Content-Type: image/webp' );
			echo $data;

		} elseif( $this->type_supported('avif') && $type == 'avif' ) {

			ob_start();
			imageavif( $image_blob );
			$data = ob_get_contents();
			ob_end_clean();
			$cache->add_data( $data );

			header( 'Content-Type: image/avif' );
			echo $data;			

		}

		imagedestroy( $image_blob );
		exit;
	}


	function is( $test ) {
		if( $test == 'image' ) return true;
		
		return false;
	}

}
