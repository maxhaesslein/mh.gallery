<?php

// This file is part of mh.gallery
// Copyright (C) 2023-2024 maxhaesslein
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
// See the file LICENSE.md for more details.

if( ! $core ) exit;

if( doing_ajax() ) return;

$template_name = $core->route->get('template_name');

$classes = [ 'nojs', 'template-'.$template_name ];

?><!DOCTYPE html>
<!--
        _                _ _              
  _ __ | |_    __ _ __ _| | |___ _ _ _  _ 
 | '  \| ' \ _/ _` / _` | | / -_) '_| || |
 |_|_|_|_||_(_)__, \__,_|_|_\___|_|  \_, |
              |___/                  |__/ 
-->
<html lang="<?= get_config( 'site_lang' ) ?>">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1.0">
<?php head(); ?>

</head>
<body<?= get_class_attribute($classes) ?>>
<script type="text/javascript">document.body.classList.remove('nojs');</script>

<div id="lightmode-toggle" class="lightmode-toggle" title="switch between light- and darkmode"></div>
<script type="text/javascript">
(function(){

	// also see Darkmode class in assets/js/general.js

	if( typeof(Storage) !== 'undefined' ) {
		// show toggle if we can use localStorage
		document.getElementById('lightmode-toggle').classList.add('visible');
	}

	if( typeof(Storage) === "undefined" || ! localStorage.getItem( 'lightmode_state' ) ) {

		if( window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches ) {
			// browser says user prefers lightmode, or has no preference set
			document.body.classList.add('lightmode');
			// we don't set a localStorage item though,
			// because the user did not explicitly choose this view
		}

		return;
	}

	if( localStorage.getItem( 'lightmode_state' ) == 'light' ) {
		document.body.classList.add('lightmode');
	} else {
		document.body.classList.remove('lightmode');
	}

})();
</script>
