<?php

class Image {
	
	private $path;
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
	private $quality;
	private $output_type;


	function __construct( $filename, $gallery ) {

		$this->filename = $filename;
		$this->gallery = $gallery;

		$this->path = get_abspath(trailing_slash_it($gallery->get_path()).$filename);

		$slug = explode('.', $filename);
		unset($slug[count($slug)-1]);
		$this->slug = sanitize_string(implode('.', $slug));

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

		// TODO: check how we want to handle this, if $this->rotated is set to true
		if( $this->src_width > $this->src_height ) {
			$this->format = 'landscape';
		} elseif( $this->src_width < $this->src_height ) {
			$this->format = 'portrait';
		} else {
			$this->format = 'square';
		}

		if( $this->image_type == IMAGETYPE_JPEG ) {
			$this->mime_type = 'image/jpeg';
			$this->output_type = 'jpg';
		} elseif( $this->image_type == IMAGETYPE_PNG ) {
			$this->mime_type = 'image/png';
			$this->output_type = 'png';
		} elseif( $this->image_type == IMAGETYPE_WEBP ) {
			$this->mime_type = 'image/webp';
			$this->output_type = 'webp';
		} else {
			debug( 'unknown image type '.$this->image_type);
		}
		// TODO: support avif, if PHP >= 8.2

		$this->width = $this->src_width;
		$this->height = $this->src_height;

		$this->alt = false; // TODO: add support for alt tag

		return $this;
	}


	function get_slug() {
		return $this->slug;
	}


	function get_link() {

		if( ! $this->gallery ) return false;

		$url = trailing_slash_it($this->gallery->get_url().$this->slug);

		return $url;
	}


	function get_index() {

		if( ! $this->gallery ) return false;

		$indexes = array_keys($this->gallery->get_images());

		$index = array_search($this->slug, $indexes);

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
		$filename .= '-'.$args['quality'].'.'.$args['type'];

		return $filename;
	}


	function get_image_url( $query = [] ) {

		$url = get_baseurl('img/').trailing_slash_it($this->gallery->get_url(false)).$this->get_filename($query);

		return $url;
	}


	function get_html( $lazyloading = true ) {

		$classes = [ 'image', 'image-'.$this->format ];

		$width = $this->width;
		$height = $this->height;

		$mobile_width = $width/2; // TODO: check, how we want to set the mobile width and breakpoint
		$mobile_height = (int) round($mobile_width * $height / $width);

		$picture = [
			[
				'mimetype' => 'image/avif',
				'media' => '(max-width: '.$mobile_width.'px)',
				'srcset' => [
					'1x' => [
						'width' => $mobile_width,
						'height' => $mobile_height,
						'quality' => 70,
						'type' => 'avif',
					],
					'2x' => [
						'width' => (int) ceil($mobile_width*1.75),
						'height' => (int) ceil($mobile_width*1.75 * $height/$width),
						'quality' => 60,
						'type' => 'avif',
					],
					'3x' => [
						'width' => (int) ceil($mobile_width*2.5),
						'height' => (int) ceil($mobile_width*2.5 * $height/$width),
						'quality' => 60,
						'type' => 'avif',
					],
				]
			],
			[
				'mimetype' => 'image/webp',
				'media' => '(max-width: '.$mobile_width.'px)',
				'srcset' => [
					'1x' => [
						'width' => $mobile_width,
						'height' => $mobile_height,
						'quality' => 70,
						'type' => 'webp',
					],
					'2x' => [
						'width' => (int) ceil($mobile_width*1.75),
						'height' => (int) ceil($mobile_width*1.75 * $height/$width),
						'quality' => 60,
						'type' => 'webp',
					],
					'3x' => [
						'width' => (int) ceil($mobile_width*2.5),
						'height' => (int) ceil($mobile_width*2.5 * $height/$width),
						'quality' => 60,
						'type' => 'webp',
					],
				],
			],
			[
				'mimetype' => 'image/avif',
				'media' => '(min-width: '.$mobile_width.'px)',
				'srcset' => [
					'1x' => [
						'width' => $width,
						'height' => $height,
						'quality' => 80,
						'type' => 'avif',
					],
					'2x' => [
						'width' => (int) ceil($width*1.75),
						'height' => (int) ceil($width*1.75 * $height/$width),
						'quality' => 60,
						'type' => 'avif',
					],
				],
			],
			[
				'mimetype' => 'image/webp',
				'media' => '(min-width: '.$mobile_width.'px)',
				'srcset' => [
					'1x' => [
						'width' => $width,
						'height' => $height,
						'quality' => 80,
						'type' => 'webp',
					],
					'2x' => [
						'width' => (int) ceil($width*1.75),
						'height' => (int) ceil($width*1.75 * $height/$width),
						'quality' => 60,
						'type' => 'webp',
					],
				],
			],
		];

		$html = '<picture'.get_class_attribute($classes).'>'; // TODO: backgroundcolor

			foreach( $picture as $source ) {
				$images = [];
				foreach( $source['srcset'] as $size => $query ) {
					$images[] = $this->get_image_url($query).' '.$size;
				}
				$html .= '<source media="'.$source['media'].'" srcset="'.implode(', ',$images).'" type="'.$source['mimetype'].'">';
			}

			$src = $this->get_image_url([
				'width' => $this->width,
				'height' => $this->height,
				'crop' => $this->crop,
			]);
			$alt = $this->alt;

			$html .= '<img src="'.$src.'" width="'.$width.'" height="'.$height.'"';
				if( $lazyloading ) $html .= ' loading="lazy" decoding="async"';
				else $html .= ' fetchpriority="high" decoding="sync"';
			$html .= ' alt="'.$alt.'">';

		$html .= '</picture>';

		return $html;
	}


	function set_image_type( $new_type ) {

		if( $new_type == 'jpg' ) {
			$this->mime_type = 'image/jpeg';
			$this->output_type = $new_type;

		} elseif( $new_type == 'png' ) {
			$this->mime_type = 'image/png';
			$this->output_type = $new_type;

		} elseif( $new_type == 'webp' ) {
			$this->mime_type = 'image/webp';
			$this->output_type = $new_type;

		}

		// TODO: add support for avif if PHP >= 8.2

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

		} // TODO: read avif files, if PHP >= 8.2


		if( ! $image ) {
			debug( 'could not load image with image-type '.$this->image_type );
			return false;
		}


		return $image;
	}


	function fill_with_backgroundcolor( $image, $transparent_color = [255, 255, 255] ) {

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

		$quality = $this->quality;
		$type = $this->output_type;

		$this->src_width = $this->src_width;
		$this->src_height = $this->src_height;

		$width = $this->width;
		$height = $this->height;

		$cache_filename = $this->get_filename().$filesize;
		$cache = new Cache( 'image', $cache_filename );
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

		}
		// TODO: add avif

		imagedestroy( $image_blob );
		exit;
	}

}
