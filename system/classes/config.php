<?php

class Config {

	private $options = [];

	function __construct() {

		// load default config
		$this->load_config_file( get_abspath('system/config.php') );

		// overwrite with custom local config
		$this->load_config_file( get_abspath('custom/config.php') );

	}


	function load_config_file( $config_file ) {

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

};
