<?php

// This file is part of mh.gallery
// Copyright (C) 2023-2024 maxhaesslein
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
// See the file LICENSE.md for more details.

class Config {

	private $options = [];

	function __construct() {

		// load default config
		$this->load_config_file( get_abspath('system/config.php') );

		// overwrite with custom local config
		if( file_exists(get_abspath('custom/config.php')) ) {
			$this->load_config_file( get_abspath('custom/config.php') );
		}

	}


	private function load_config_file( $config_file ) {

		if( ! file_exists($config_file) ) {
			debug( 'config file not found', $config_file );
			exit;
		}

		$options = include( $config_file );

		$this->options = array_merge( $this->options, $options );

		return $this;
	}
	

	function get( $option, $fallback = false ) {

		if( ! array_key_exists( $option, $this->options ) ) {
			return $fallback;
		}

		return $this->options[$option];
	}


	function overwrite( $option, $new_value ) {

		if( ! array_key_exists( $option, $this->options ) ) return false;

		$this->options[$option] = $new_value;

		return true;
	}

};
