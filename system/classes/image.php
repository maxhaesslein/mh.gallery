<?php

class Image {
	
	private $filename;
	private $path;
	private $gallery;
	private $slug; // this is the slug for the url
	private $key; // this is the key this image inside the gallery images array

	private $src_width;
	private $src_height;
	private $image_type;
	private $orientation;
	private $rotated = false;
	private $format;
	private $alt;

	private $width;
	private $height;
	private $crop = false;
	private $quality;
	private $output_type;

	private $adjacent_image = [];
	private $file_mod_time = NULL;
	private $exif_data = NULL;


	function __construct( $filename, $gallery ) {

		$this->filename = $filename;
		$this->gallery = $gallery;

		$this->path = get_abspath(trailing_slash_it($gallery->get_path()).$filename);

		$slug = remove_fileextension($filename);
		$this->slug = sanitize_string($slug);

		$this->key = $this->slug.'.'; // the key needs to be a string, because we use it as a key in a associative array, so we append a '.' to force this to be a string, even if the slug would be '1' (and therefore be cast to an int in the array)

		$this->quality = get_config( 'default_image_quality' );

		$this->load_image_meta();

	}


	function load_image_meta() {

		$filepath = $this->path;

		if( ! file_exists($filepath) ) {
			debug("image file not found", $filepath);
			return false;
		}


		$image_meta = getimagesize( $filepath );
		if( ! $image_meta ) {
			debug("no image meta", $filepath);
			return false;
		}

		$this->src_width = $image_meta[0];
		$this->src_height = $image_meta[1];
		$this->image_type = $image_meta[2];


		$exif = @exif_read_data( $filepath );
		if( $exif && ! empty($exif['Orientation']) ) {
			$this->orientation = $exif['Orientation'];
		}

		if( $this->orientation == 6 || $this->orientation == 8 || $this->orientation == 5 || $this->orientation == 7 ) {
			$this->rotated = true;
		}

		if( $this->src_width > $this->src_height ) {
			$this->format = 'landscape';
		} elseif( $this->src_width < $this->src_height ) {
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
		} elseif( $this->avif_supported() && $this->image_type == IMAGETYPE_AVIF ) {
			$this->set_image_type('avif');
		} else {
			debug( 'unknown image type '.$this->image_type);
		}

		$this->width = $this->src_width;
		$this->height = $this->src_height;

		$this->alt = false; // TODO: add support for alt tag

		return $this;
	}


	function avif_supported() {

		if( ! get_config('avif_enabled') ) return false;

		if( ! defined('IMAGETYPE_AVIF') ) return false;

		if( ! function_exists('imagecreatefromavif') ) return false;

		if( ! function_exists('imageavif') ) return false;

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


	function get_index() {

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


	function resize( $width = false, $height = false, $crop = false ) {

		if( ! $crop ) {
			if( $width ) {
				$ratio = $this->src_height/$this->src_width;
				if( $this->rotated ) {
					$ratio = $this->src_width/$this->src_height;
				}
				$height = (int) round($width * $ratio);
			} elseif( $height ) {
				$ratio = $this->src_width/$this->src_height;
				if( $this->rotated ) {
					$ratio = $this->src_height/$this->src_width;
				}
				$width = (int) round($height * $ratio);
			}
		}

		$this->width = $width;
		$this->height = $height;
		$this->crop = $crop;

		return $this;
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


	function get_filename( $query = [] ) {

		$defaults = [
			'width' => $this->width,
			'height' => $this->height,
			'crop' => $this->crop,
			'type' => $this->output_type,
			'quality' => $this->quality,
		];

		$args = array_merge($defaults, $query);

		$filename = $this->slug.'_'.$args['width'].'x'.$args['height'];
		if( $args['crop'] ) $filename .= '-crop';
		$filename .= '-'.$args['quality'].'-'.$args['type'].'.'.$args['type'];

		return $filename;
	}


	function get_image_path( $query = [] ) {

		$cache = $this->get_cache( $query );
		$cache_filename = $cache->get_file_name();

		$path = 'img/'.trailing_slash_it($this->gallery->get_url(false)).$cache_filename;

		return $path;
	}


	function get_image_url( $query = [] ) {

		$url = get_baseurl($this->get_image_path($query));

		return $url;
	}


	function get_picture_srcset() {

		$width = $this->width;
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


	function get_html( $lazyloading = true, $skip_container = false, $sizes = false ) {

		$classes = [ 'image', 'image-'.$this->format ];

		$width = $this->width;
		$height = $this->height;

		$picture = $this->get_picture_srcset();

		$main_src = $this->get_image_url([
			'width' => $this->width,
			'height' => $this->height,
			'crop' => $this->crop,
		]);
		$alt = $this->alt;

		$html = '';

		if( ! $skip_container ) $html .= '<div class="image-container" style="aspect-ratio: '.$width.'/'.$height.';">';
		$html .= '<picture'.get_class_attribute($classes).' style="aspect-ratio: '.$width.'/'.$height.';">'; // TODO: backgroundcolor

		foreach( $picture as $type => $sources ) {

			$srcset = [];
			foreach( $sources as $size => $image_url_args ) {

				$image_url_args['type'] = $type;

				if( $this->crop ) {
					$image_url_args['height'] = round($image_url_args['width']*$height/$width);
					$image_url_args['crop'] = true;
				}

				$image_url = $this->get_image_url($image_url_args);
				$srcset[] = $image_url.' '.$size;
			}

			$srcset = implode(', ', $srcset);

			if( $type != 'jpg' ) { // webp/avif/...

				if( $type == 'avif' && ! $this->avif_supported() ) continue; // skip avif, if we do not support generating avif images

				$html .= '<source ';
				if( $sizes ) $html .= 'sizes="'.$sizes.'" ';
				$html .= 'srcset="'.$srcset.'" type="image/'.$type.'">';

			} else { // jpg

				$html .= '<img src="'.$main_src.'" width="'.$width.'" height="'.$height.'"';
					if( $lazyloading ) $html .= ' loading="lazy" decoding="async"';
					else $html .= ' fetchpriority="high" decoding="sync"';
				if( $sizes ) $html .= 'sizes="'.$sizes.'" ';
				$html .= 'srcset="'.$srcset.'" alt="'.$alt.'">';

			}

		}

		$html .= '</picture>';
		if( ! $skip_container ) $html .= '</div>';

		return $html;
	}


	function set_image_type( $new_type ) {

		if( $new_type == 'jpg' ) {
			$this->mime_type = 'image/jpeg';
			$this->output_type = 'jpg';
		} elseif( $new_type == 'png' ) {
			$this->mime_type = 'image/png';
			$this->output_type = 'png';
		} elseif( $new_type == 'webp' ) {
			$this->mime_type = 'image/webp';
			$this->output_type = 'webp';
		} elseif( $this->avif_supported() && $new_type == 'avif' ) {
			$this->mime_type = 'image/avif';
			$this->output_type = 'avif';
		}

		return $this;
	}


	function set_quality( $new_quality ) {

		if( ! $new_quality ) return $this;

		$this->quality = $new_quality;

		return $this;
	}


	function read_image() {

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

		} elseif( $this->avif_supported() && $this->image_type == IMAGETYPE_AVIF ) {

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


	function fill_with_backgroundcolor( $image, $transparent_color = [255, 255, 255] ) {

		$src_width = $this->src_width;
		$src_height = $this->src_height;
		if( $this->rotated ) {
			$src_width = $this->src_height;
			$src_height = $this->src_width;
		}

		$background_image = imagecreatetruecolor( $src_width, $src_height );
		$background_color = imagecolorallocate( $background_image, $transparent_color[0], $transparent_color[1], $transparent_color[2] );

		imagefill( $background_image, 0, 0, $background_color );
		imagecopy( $background_image, $image, 0, 0, 0, 0, $src_width, $src_height );

		$image = $background_image;

		imagedestroy( $background_image );

		return $image;
	}


	function image_rotate( $image, $src_width, $src_height ) {

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


	function get_cache( $query = [] ) {

		$cache_filename = trailing_slash_it($this->gallery->get_url(false)).$this->get_filename( $query );
		$cache = new Cache( 'image', $cache_filename, true, false, true );

		return $cache;
	}


	function output() {
		// NOTE: this assumes we did not output any headers or HTML yet!

		$quality = $this->quality;
		$type = $this->output_type;

		$src_width = $this->src_width;
		$src_height = $this->src_height;

		$width = $this->width;
		$height = $this->height;

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

		$query = [
			'width' => $width,
			'height' => $height,
			'crop' => $this->crop,
			'type' => $type,
			'quality' => $quality,
		];
		$cache = $this->get_cache( $query );

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

		} elseif( $this->avif_supported() && $type == 'avif' ) {

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
