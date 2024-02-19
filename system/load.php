<?php

session_start(); // we use this for secret links

$global_script_execution_start_time = hrtime(true); // this is used in the measure_execution_time() function to render the execution time

$abspath = realpath(dirname(__FILE__)).'/';
$abspath = preg_replace( '/system\/$/', '', $abspath );

include_once( $abspath.'system/functions.php' );
include_once( $abspath.'system/classes.php' );

$core = new Core( $abspath );

include( $core->route->get('template_include') );
