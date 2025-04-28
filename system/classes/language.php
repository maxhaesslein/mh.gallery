<?php

// This file is part of mh.gallery
// Copyright (C) 2023-2025 maxhaesslein
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
// See the file LICENSE.md for more details.

class Language {

	private $texts = [];
	private $language_code = 'en-US';

	function __construct() {
		global $core;

		$lang = $core->config->get('site_lang');

		$language_file_path = get_abspath('system/languages/'.$lang.'.php');

		if( file_exists('custom/languages/'.$lang.'.php') ) {
			// load a custom language file
			$language_file_path = get_abspath('custom/languages/'.$lang.'.php');
		}

		$this->load_language_file( $language_file_path );

		if( isset($this->texts['language_code']) ) {
			$this->language_code = $this->texts['language_code'];
		} else {
			$this->language_code = $lang;
		}

	}


	private function load_language_file( $language_file ) {

		if( ! file_exists($language_file) ) {
			debug( 'language file not found', $language_file );
			exit;
		}

		$this->texts = include( $language_file );

		return $this;
	}
	

	function get( $text, $fallback = null ) {

		if( ! array_key_exists( $text, $this->texts ) ) {
			if( $fallback !== null ) return $fallback;
			return $text;
		}

		return $this->texts[$text];
	}


	function get_language_code() {
		return $this->language_code;
	}

};
