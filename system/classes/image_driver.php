<?php

// This file is part of mh.gallery
// Copyright (C) 2023-2026 maxhaesslein
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
// See the file LICENSE.md for more details.

abstract class Image_Driver {

	private static $drivers = [
		'gd' => 'Image_Driver_GD',
		'imagick' => 'Image_Driver_Imagick',
	];

	protected $width = 0;
	protected $height = 0;


	static function create( $driver_name ) {

		$driver_name = self::sanitize_driver_name( $driver_name );

		$driver_class = self::$drivers[$driver_name];

		return new $driver_class();

	}


	static function sanitize_driver_name( $driver_name ) {

		$driver_name = strtolower((string) $driver_name);

		if( ! array_key_exists($driver_name, self::$drivers) ) {
			debug( 'unknown image driver "'.$driver_name.'"; falling back to gd' );
			$driver_name = 'gd';
		}

		if( $driver_name == 'imagick' && ! Image_Driver_Imagick::is_available() ) {
			debug( 'imagick driver is not available; falling back to gd' );
			$driver_name = 'gd';
		}

		return $driver_name;
	}


	static function supports_type( $driver_name, $type ) {

		$driver = self::create( $driver_name );

		return $driver->type_supported( $type );
	}


	abstract static function is_available();

	abstract function type_supported( $type );

	abstract function load( $path, $image_type );

	abstract function rotate( $degrees );

	abstract function resize( $width, $height, $crop, $fit, $background_rgb );

	abstract function apply_metadata_policy( $keep_exif, $keep_colorprofile );

	abstract function encode( $type, $quality );

	abstract function destroy();


	function get_width() {
		return $this->width;
	}


	function get_height() {
		return $this->height;
	}

};
