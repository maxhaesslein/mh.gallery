<?php

$abspath = realpath(dirname(__FILE__)).'/';
$abspath = preg_replace( '/system\/$/', '', $abspath );

include_once( $abspath.'system/functions.php' );
include_once( $abspath.'system/classes.php' );

$core = new Core( $abspath );

$route = $core->route->get_route();

include( $route['template_include'] );
