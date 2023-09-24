<?php

spl_autoload_register( function($class_name) {
	global $abspath;
	include $abspath.'system/classes/'.strtolower($class_name).'.php';
} );
