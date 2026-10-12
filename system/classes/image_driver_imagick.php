<?php

// This file is part of mh.gallery
// Copyright (C) 2023-2026 maxhaesslein
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
// See the file LICENSE.md for more details.

class Image_Driver_Imagick extends Image_Driver {

	private $image = null;


	static function is_available() {
		return class_exists('Imagick');
	}


	function type_supported( $type ) {

		if( ! class_exists('Imagick') ) return false;

		$type = strtolower($type);

		if( $type == 'jpeg' ) $type = 'jpg';

		// config can have 'webp_enabled', 'avif_enabled' and so on ..
		if( $type != 'jpg' && ! get_config($type.'_enabled') ) return false;

		$format = strtoupper($type);
		if( $type == 'jpg' ) $format = 'JPEG';

		try {
			$formats = Imagick::queryFormats( $format );
		} catch( Exception $e ) {
			return false;
		}

		return count($formats) > 0;
	}


	private function get_source_type( $image_type ) {

		if( $image_type == IMAGETYPE_JPEG ) return 'jpg';
		if( $image_type == IMAGETYPE_PNG ) return 'png';
		if( $image_type == IMAGETYPE_WEBP ) return 'webp';
		if( $image_type == IMAGETYPE_AVIF ) return 'avif';
		if( $image_type == IMAGETYPE_GIF ) return 'gif';

		return false;
	}


	private function get_jpeg_size_hint( $path, $max_width, $max_height ) {

		$source_size = @getimagesize( $path );
		if( ! $source_size ) return false;

		// NOTE: libjpeg can decode jpeg images with a reduced scale (steps of 1/8); we use the smallest step that still covers the requested dimensions; this is much faster and needs less memory for large source images

		for( $step = 1; $step <= 7; $step++ ) {

			$width = (int) ceil( $source_size[0] * $step / 8 );
			$height = (int) ceil( $source_size[1] * $step / 8 );

			if( $width >= $max_width && $height >= $max_height ) {
				return $width.'x'.$height;
			}

		}

		return false;
	}


	function load( $path, $image_type, $max_width = 0, $max_height = 0 ) {

		$source_type = $this->get_source_type( $image_type );

		if( ! $source_type || ! $this->type_supported($source_type) ) {
			debug( 'could not load image with image-type '.$image_type );
			return false;
		}

		$scene = '';
		if( in_array($image_type, [ IMAGETYPE_GIF, IMAGETYPE_WEBP, IMAGETYPE_AVIF ]) ) {
			// only load the first frame of animated images (the gd driver behaves the same way)
			$scene = '[0]';
		}

		try {
			$this->image = new Imagick();

			if( $source_type == 'jpg' && $max_width > 0 && $max_height > 0 ) {

				$jpeg_size_hint = $this->get_jpeg_size_hint( $path, $max_width, $max_height );
				if( $jpeg_size_hint ) {
					$this->image->setOption( 'jpeg:size', $jpeg_size_hint );
				}

			}

			$this->image->readImage( $path.$scene );
		} catch( Exception $e ) {
			debug( 'could not load image', $e->getMessage() );
			return false;
		}

		$this->width = $this->image->getImageWidth();
		$this->height = $this->image->getImageHeight();

		return true;
	}


	function rotate( $degrees ) {

		if( ! $degrees ) return true;

		try {
			// NOTE: gd rotates counterclockwise on positive angles, imagick rotates clockwise; we mirror the gd behavior
			$this->image->rotateImage( new ImagickPixel('black'), -$degrees );
		} catch( Exception $e ) {
			debug( 'could not rotate image', $e->getMessage() );
			return false;
		}

		if( $degrees == 90 || $degrees == 270 ) {
			$tmp_width = $this->width;
			$this->width = $this->height;
			$this->height = $tmp_width;
		}

		return true;
	}


	function resize( $width, $height, $crop, $fit, $background_rgb ) {

		$src_width = $this->width;
		$src_height = $this->height;

		if( $src_width <= $width && $src_height <= $height && ! $crop ) {
			// no resizing necessary
			return true;
		}

		$background_color = new ImagickPixel( 'rgb('.$background_rgb[0].','.$background_rgb[1].','.$background_rgb[2].')' );

		try {

			// NOTE: currently, we just center the image on crop; later we may implement a focus area.

			if( $fit === 'contain' ) {

				$scale  = min( $width / $src_width, $height / $src_height );
				$dst_w  = (int) ceil( $src_width  * $scale );
				$dst_h  = (int) ceil( $src_height * $scale );
				$dst_x  = (int) floor( ( $width  - $dst_w ) / 2 );
				$dst_y  = (int) floor( ( $height - $dst_h ) / 2 );

				$this->image->resizeImage( $dst_w, $dst_h, Imagick::FILTER_UNDEFINED, 1 );

				$this->image->setImageBackgroundColor( $background_color );

				// NOTE: extentImage() negates the offsets internally, so we need to pass negative values to center the image
				$this->image->extentImage( $width, $height, -$dst_x, -$dst_y );

			} else { // cover

				$copy_src_w = $src_width;
				$copy_src_h = (int) ceil( $copy_src_w * $height / $width );
				if( $copy_src_h > $src_height ) {
					$copy_src_h = $src_height;
					$copy_src_w = (int) ceil( $copy_src_h * $width / $height );
				}
				$src_x = (int) floor( ( $src_width  - $copy_src_w ) / 2 );
				$src_y = (int) floor( ( $src_height - $copy_src_h ) / 2 );

				$this->image->cropImage( $copy_src_w, $copy_src_h, $src_x, $src_y );
				$this->image->resizeImage( $width, $height, Imagick::FILTER_UNDEFINED, 1 );

			}

		} catch( Exception $e ) {
			debug( 'could not resize image', $e->getMessage() );
			return false;
		}

		$this->width = $width;
		$this->height = $height;

		return true;
	}


	private function get_colorprofile() {

		$colorprofile = false;

		try {
			$colorprofile = $this->image->getImageProfile('icc');
			if( ! $colorprofile ) $colorprofile = $this->image->getImageProfile('icm');
		} catch( Exception $e ) {
			// no color profile; nothing to keep or convert
		}

		return $colorprofile;
	}


	private function convert_to_srgb() {

		try {

			// NOTE: profileImage() only converts the image if the current color profile is stored as 'icc'
			if( ! $this->image->getImageProfile('icc') ) {
				$this->image->setImageProfile( 'icc', $this->image->getImageProfile('icm') );
			}

			$srgb_profile = @file_get_contents( get_abspath('system/data/sRGB.icc') );
			if( ! $srgb_profile ) {
				debug( 'could not load the sRGB color profile' );
				return false;
			}

			$this->image->profileImage( 'icc', $srgb_profile );

		} catch( Exception $e ) {
			debug( 'could not convert image to sRGB', $e->getMessage() );
			return false;
		}

		return true;
	}


	private function remove_colorprofile() {

		foreach( [ 'icc', 'icm' ] as $name ) {
			try {
				$this->image->removeImageProfile( $name );
			} catch( Exception $e ) {
				// profile does not exist; nothing to remove
			}
		}
	}


	function apply_metadata_policy( $keep_exif, $keep_colorprofile ) {

		try {

			$colorprofile = $this->get_colorprofile();

			if( $colorprofile && ! $keep_colorprofile ) {
				// NOTE: convert to sRGB before removing the color profile, so wide gamut images keep their colors
				if( $this->convert_to_srgb() ) {
					$colorprofile = false;
				}
			}

			if( ! $keep_exif ) {

				$this->image->stripImage();

				if( $keep_colorprofile && $colorprofile ) {
					$this->image->setImageProfile( 'icc', $colorprofile );
				}

			} elseif( ! $keep_colorprofile ) {

				$this->remove_colorprofile();

			}

			if( $keep_exif ) {
				// NOTE: the image is already rotated, so the orientation tag in the exif data would rotate the image a second time
				$this->image->setImageOrientation( Imagick::ORIENTATION_TOPLEFT );
			}

		} catch( Exception $e ) {
			debug( 'could not apply metadata policy', $e->getMessage() );
			return false;
		}

		return true;
	}


	function encode( $type, $quality ) {

		if( ! $this->type_supported($type) ) return false;

		$format = $type;
		if( $type == 'jpg' ) $format = 'jpeg';

		try {
			$this->image->setImageFormat( $format );

			if( $type != 'png' ) {
				$this->image->setImageCompressionQuality( (int) $quality );
			}

			$data = $this->image->getImageBlob();
		} catch( Exception $e ) {
			debug( 'could not encode image', $e->getMessage() );
			return false;
		}

		if( ! $data ) return false;

		return $data;
	}


	function destroy() {

		if( $this->image ) {
			$this->image->clear();
			$this->image = null;
		}

		return true;
	}

};
