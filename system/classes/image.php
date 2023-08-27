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

		$this->path = get_abspath($path);

		$path_exp = explode('/', $path);
		$filename = array_pop($path_exp);

		$path_exp[0] = 'image'; # this replaces 'content/'
		$this->img_path = implode('/', $path_exp).'/'.$filename;

		$this->filename = $filename;

		if( $gallery ) {
			$this->gallery = $gallery;
		}

		$this->slug = sanitize_string($filename, true);

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

		// TODO: check how we want to handle this, if $this->rotated is set to true
		if( $this->src_width > $this->src_height ) {
			$this->format = 'landscape';
		} elseif( $this->src_width < $this->src_height ) {
			$this->format = 'portrait';
		} else {
			$this->format = 'square';
		}

		if( $this->image_type == IMAGETYPE_JPEG
		 || (get_config('image_png_to_jpg') && $this->image_type == IMAGETYPE_PNG) ) {
			$this->mime_type = 'image/jpeg';
		} elseif( $this->image_type == IMAGETYPE_PNG ) {
			$this->mime_type = 'image/png';
		} elseif( $this->image_type == IMAGETYPE_WEBP ) {
			$this->mime_type = 'image/webp';
		} else {
			debug( 'unknown image type '.$this->image_type);
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

		} // TODO: add avif


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


	function output() {
		// NOTE: this assumes we did not output any headers or HTML yet!

		$filesize = filesize( $this->path );
		$jpg_quality = get_config( 'jpg_quality' );
		$png_to_jpg = get_config( 'image_png_to_jpg' );		

		$this->src_width = $this->src_width;
		$this->src_height = $this->src_height;

		$width = $this->width;
		$height = $this->height;

		$cache_string = $this->path.$filesize.$width.$height.$jpg_quality;

		$cache = new Cache( 'image', $cache_string );
		$cache_content = $cache->get_data();
		if( $cache_content ) {
			// return cached file, then end
			$cache->refresh_lifetime();
			header("Content-Type: ".$this->mime_type);
			header("Content-Length: ".$cache->get_filesize());
			echo $cache_content;
			exit;
		}

		$image_blob = $this->read_image();
		if( ! $image_blob ) {
			debug('could not load image', $image);
			exit;
		}

		list( $image_blob, $this->src_width, $this->src_height ) = $this->image_rotate( $image_blob, $this->src_width, $this->src_height );

		if( $this->src_width > $width || $this->src_height > $height ) {

			if( $this->rotated ) {
				$tmp_width = $width;
				$width = $height;
				$height = $tmp_width;
			}

			$image_blob_resized = imagecreatetruecolor( $width, $height );

			if( ( ! $png_to_jpg && $this->image_type == IMAGETYPE_PNG ) 
				|| $this->image_type == IMAGETYPE_WEBP 
			) {
				// handle alpha channel
				imageAlphaBlending( $image_blob_resized, false );
				imageSaveAlpha( $image_blob_resized, true );
			}

			// NOTE: currently, we just center the image on crop
			// TODO: maybe at a 'region of interest' option, somehow ..
			$src_width_cropped = $this->src_width;
			$src_height_cropped = round($src_width_cropped * $height/$width);
			if( $src_height_cropped > $this->src_height ) {
				$src_height_cropped = $this->src_height;
				$src_width_cropped = round($src_height_cropped * $width/$height);
			}
			$src_x = (int) round(($this->src_width - $src_width_cropped)/2);
			$src_y = (int) round(($this->src_height - $src_height_cropped)/2);

			imagecopyresampled( $image_blob_resized, $image_blob, 0, 0, $src_x, $src_y, $width, $height, $src_width_cropped, $src_height_cropped );

			imagedestroy($image_blob);

			$image_blob = $image_blob_resized;

		} else {
			$image_blob_resized = $image_blob;
		}


		if( $this->image_type == IMAGETYPE_JPEG
		 || ($png_to_jpg && $this->image_type == IMAGETYPE_PNG) ) {

			ob_start();
			imagejpeg( $image_blob, NULL, $jpg_quality );
			$data = ob_get_contents();
			ob_end_clean();
			$cache->add_data( $data );

			header( 'Content-Type: image/jpeg' );
			echo $data;

		} elseif( $this->image_type == IMAGETYPE_PNG ) {

			ob_start();
			imagepng( $image_blob );
			$data = ob_get_contents();
			ob_end_clean();
			$cache->add_data( $data );

			header( 'Content-Type: image/png' );
			echo $data;

		} elseif( $this->image_type == IMAGETYPE_WEBP ) {

			ob_start();
			imagewebp( $image_blob );
			$data = ob_get_contents();
			ob_end_clean();
			$cache->add_data( $data );

			header( 'Content-Type: image/webp' );
			echo $data;

		} // TODO: add avif

		imagedestroy( $image_blob );
		exit;
	}

}
