<?php

// This file is part of mh.gallery
// Copyright (C) 2023-2026 maxhaesslein
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
// See the file LICENSE.md for more details.

class Image_Driver_GD extends Image_Driver {

	private $image = null;
	private $has_alpha = false;

	private static $warned_keep_exif = false;


	static function is_available() {
		return function_exists('imagecreatefromjpeg');
	}


	function type_supported( $type ) {

		$type = strtolower($type);

		if( $type == 'jpg' || $type == 'jpeg' ) return true;

		// config can have 'webp_enabled', 'avif_enabled' and so on ..
		if( ! get_config($type.'_enabled') ) return false;

		if( ! defined('IMAGETYPE_'.strtoupper($type)) ) return false;

		if( ! function_exists('imagecreatefrom'.$type) ) return false;

		if( ! function_exists('image'.$type) ) return false;

		return true;
	}


	private function get_source_type( $image_type ) {

		if( $image_type == IMAGETYPE_JPEG ) return 'jpg';
		if( $image_type == IMAGETYPE_PNG ) return 'png';
		if( $image_type == IMAGETYPE_WEBP ) return 'webp';
		if( $image_type == IMAGETYPE_AVIF ) return 'avif';
		if( $image_type == IMAGETYPE_GIF ) return 'gif';

		return false;
	}


	function load( $path, $image_type ) {

		$source_type = $this->get_source_type( $image_type );

		if( ! $source_type || ! $this->type_supported($source_type) ) {
			debug( 'could not load image with image-type '.$image_type );
			return false;
		}

		if( $source_type == 'jpg' ) {

			$this->image = imagecreatefromjpeg( $path );

		} elseif( $source_type == 'png' ) {

			$this->image = imagecreatefrompng( $path );
			$this->has_alpha = true;

			// handle transparency loading:
			imagealphablending( $this->image, false );
			imagesavealpha( $this->image, true );

		} elseif( $source_type == 'webp' ) {

			$this->image = imagecreatefromwebp( $path );
			$this->has_alpha = true;

			// handle transparency loading:
			imagealphablending( $this->image, false );
			imagesavealpha( $this->image, true );

		} elseif( $source_type == 'avif' ) {

			// NOTE: unlike png and webp, we do not treat avif as an alpha channel image, to keep the behavior of previous versions

			$this->image = imagecreatefromavif( $path );

			// handle transparency loading:
			imagealphablending( $this->image, false );
			imagesavealpha( $this->image, true );

		} elseif( $source_type == 'gif' ) {

			$this->image = imagecreatefromgif( $path );

			// we need to make sure to convert this to true color, for other formats:
			imagepalettetotruecolor( $this->image );

		}

		if( ! $this->image ) {
			debug( 'could not load image with image-type '.$image_type );
			return false;
		}

		$this->width = imagesx( $this->image );
		$this->height = imagesy( $this->image );

		return true;
	}


	function rotate( $degrees ) {

		if( ! $degrees ) return true;

		$image_rotated = imagerotate( $this->image, $degrees, 0 );

		if( ! $image_rotated ) return false;

		imagedestroy($this->image);

		$this->image = $image_rotated;

		if( $degrees == 90 || $degrees == 270 ) {
			$tmp_width = $this->width;
			$this->width = $this->height;
			$this->height = $tmp_width;
		}

		return true;
	}


	private function fill_with_backgroundcolor( $image, $width, $height, $background_rgb ) {

		$background_image = imagecreatetruecolor( $width, $height );
		$background_color = imagecolorallocate( $background_image, $background_rgb[0], $background_rgb[1], $background_rgb[2] );

		imagefill( $background_image, 0, 0, $background_color );
		imagecopy( $background_image, $image, 0, 0, 0, 0, $width, $height );

		return $background_image;
	}


	function resize( $width, $height, $crop, $fit, $background_rgb ) {

		$src_width = $this->width;
		$src_height = $this->height;

		if( ! $this->has_alpha ) {
			// no alpha channel; fill with background color
			$this->image = $this->fill_with_backgroundcolor( $this->image, $src_width, $src_height, $background_rgb );
		}

		if( $src_width <= $width && $src_height <= $height && ! $crop ) {
			// no resizing necessary
			return true;
		}

		$image_resized = imagecreatetruecolor( $width, $height );

		if( $this->has_alpha ) {
			// handle alpha channel
			imagealphablending( $image_resized, false );
			imagesavealpha( $image_resized, true );
		}

		// NOTE: currently, we just center the image on crop; later we may implement a focus area.

		if( $fit === 'contain' ) {
			$scale  = min( $width / $src_width, $height / $src_height );
			$dst_w  = (int) ceil( $src_width  * $scale );
			$dst_h  = (int) ceil( $src_height * $scale );
			$dst_x  = (int) floor( ( $width  - $dst_w ) / 2 );
			$dst_y  = (int) floor( ( $height - $dst_h ) / 2 );
			$src_x  = 0;
			$src_y  = 0;
			$copy_src_w = $src_width;
			$copy_src_h = $src_height;

			$fill_color = imagecolorallocate( $image_resized, $background_rgb[0], $background_rgb[1], $background_rgb[2] );
			imagefill( $image_resized, 0, 0, $fill_color );

		} else { // cover
			$dst_w  = $width;
			$dst_h  = $height;
			$dst_x  = 0;
			$dst_y  = 0;

			$copy_src_w = $src_width;
			$copy_src_h = (int) ceil( $copy_src_w * $height / $width );
			if( $copy_src_h > $src_height ) {
				$copy_src_h = $src_height;
				$copy_src_w = (int) ceil( $copy_src_h * $width / $height );
			}
			$src_x = (int) floor( ( $src_width  - $copy_src_w ) / 2 );
			$src_y = (int) floor( ( $src_height - $copy_src_h ) / 2 );
		}

		imagecopyresampled( $image_resized, $this->image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $copy_src_w, $copy_src_h );

		imagedestroy($this->image);

		$this->image = $image_resized;
		$this->width = $width;
		$this->height = $height;

		return true;
	}


	function apply_metadata_policy( $keep_exif, $keep_colorprofile ) {

		// NOTE: the gd driver cannot write exif data or color profiles

		if( $keep_exif && ! self::$warned_keep_exif ) {
			debug( 'the gd image driver cannot keep exif data; use the imagick driver if you need to keep exif data' );
			self::$warned_keep_exif = true;
		}

		return true;
	}


	function encode( $type, $quality ) {

		if( ! $this->type_supported($type) ) return false;

		ob_start();

		if( $type == 'jpg' ) {

			imagejpeg( $this->image, NULL, $quality );

		} elseif( $type == 'png' ) {

			imagepng( $this->image );

		} elseif( $type == 'webp' ) {

			imagewebp( $this->image, null, $quality );

		} elseif( $type == 'avif' ) {

			imageavif( $this->image, null, $quality );

		} elseif( $type == 'gif' ) {

			imagetruecolortopalette($this->image, true, 256);

			imagegif( $this->image, null );

		} else {

			ob_end_clean();

			return false;

		}

		$data = ob_get_contents();
		ob_end_clean();

		return $data;
	}


	function destroy() {

		if( $this->image ) imagedestroy($this->image);

		$this->image = null;

		return true;
	}

};
