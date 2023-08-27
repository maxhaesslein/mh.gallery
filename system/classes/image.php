<?php

class Image {
	
	private $path;
	private $img_path;
	private $gallery;
	private $slug;

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


	function __construct( $path, $gallery = false ) {

		$this->path = $path;

		$path_exp = explode('/', $path);
		$filename = array_pop($path_exp);

		$path_exp[0] = 'image'; # this replaces 'content/'
		$this->img_path = implode('/', $path_exp).'/'.$filename;

		$this->path = $path;

		$this->filename = $filename;

		if( $gallery ) {
			$this->gallery = $gallery;
		}

		$this->slug = sanitize_string($filename, true);

		$this->load_image_meta();

	}


	function load_image_meta() {

		$filepath = get_abspath($this->path);

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

		// TODO: check how we want to handle this, if $this->rotated is set to true
		if( $this->src_width > $this->src_height ) {
			$this->format = 'landscape';
		} elseif( $this->src_width < $this->src_height ) {
			$this->format = 'portrait';
		} else {
			$this->format = 'square';
		}

		$this->width = $this->src_width;
		$this->height = $this->src_height;

		$this->alt = false; // TODO: add support for alt tag

		return $this;
	}


	function get_slug() {
		return $this->slug;
	}


	function get_url() {

		if( ! $this->gallery ) return false;

		$url = $this->gallery->get_url().$this->slug;

		return $url;
	}


	function resize( $width = false, $height = false, $crop = false ) {

		if( ! $crop ) {
			if( $width ) {
				$height = (int) round($width * $this->src_height/$this->src_width);
			} elseif( $height ) {
				$width = (int) round($height * $this->src_width/$this->src_height);
			}
		}

		$this->width = $width;
		$this->height = $height;
		$this->crop = $crop;

		return $this;
	}


	function get_html( $lazyloading = true ) {

		$classes = [ 'image', 'image-'.$this->format ];

		$query = [
			'width' => $this->width,
			'height' => $this->height,
			'crop' => $this->crop,
		];

		$src = get_baseurl($this->img_path).'?'.http_build_query($query);
		$width = $this->width;
		$height = $this->height;
		$alt = $this->alt;

		$lazy = '  loading="lazy"';
		if( ! $lazyloading ) $lazy = '';

		// TODO: srcset
		$html = '<img'.get_class_attribute($classes).' src="'.$src.'" width="'.$width.'" height="'.$height.'" alt="'.$alt.'"'.$lazy.'>';

		return $html;
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
		
			if( get_config( 'png_to_jpg' ) ) {
				// set transparent background to specific color, when converting to jpg:
				$image = $this->fill_with_backgroundcolor( $image );
			}

		} elseif( $this->image_type == IMAGETYPE_WEBP ) {

			$image = imagecreatefromwebp( $this->path );

			// handle transparency loading:
			imageAlphaBlending( $image, false );
			imageSaveAlpha( $image, true );
		
			// set transparent background to specific color, because for now we don't support transparent png
			$image = $this->fill_with_backgroundcolor( $image );

		}

		if( ! $image ) {
			debug( 'could not load image with mime-type '.$this->image_type );
			return false;
		}

		return $image;
	}


	function fill_with_backgroundcolor( $image ) {

		$transparent_color = get_config( 'image_background_color' );
		$background_image = imagecreatetruecolor( $this->src_width, $this->src_height );
		$background_color = imagecolorallocate( $background_image, $transparent_color[0], $transparent_color[1], $transparent_color[2] );
		imagefill( $background_image, 0, 0, $background_color );
		imagecopy( $background_image, $image, 0, 0, 0, 0, $this->src_width, $this->src_height );
		$image = $background_image;
		imagedestroy( $background_image );

		return $image;
	}


	function output() {
		// NOTE: this assumes we did not output any headers or HTML yet!

		// TODO: cache resized images!


		$src_image = $this->read_image();
		if( ! $src_image ) {
			debug('could not load image', $image);
			exit;
		}

		// TODO: $png_to_jpg = get_config( 'image_png_to_jpg' );
		// TODO: resize image

		$jpg_quality = get_config( 'jpg_quality' );

		if( $this->image_type == IMAGETYPE_JPEG
		 || ($png_to_jpg && $this->image_type == IMAGETYPE_PNG) ) {

			ob_start();
			imagejpeg( $src_image, NULL, $jpg_quality );
			$data = ob_get_contents();
			ob_end_clean();
			//$cache->add_data( $data );

			header( 'Content-Type: image/jpeg' );
			echo $data;

		} elseif( $this->image_type == IMAGETYPE_PNG ) {

			ob_start();
			imagepng( $src_image );
			$data = ob_get_contents();
			ob_end_clean();
			//$cache->add_data( $data );

			header( 'Content-Type: image/png' );
			echo $data;

		} elseif( $this->image_type == IMAGETYPE_WEBP ) {

			ob_start();
			imagewebp( $src_image );
			$data = ob_get_contents();
			ob_end_clean();
			//$cache->add_data( $data );

			header( 'Content-Type: image/webp' );
			echo $data;

		} // TODO: add avif

		imagedestroy( $src_image );
		exit;
	}

}
