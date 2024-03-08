<?php

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

<div id="darkmode-toggle" class="darkmode-toggle"></div>
<script type="text/javascript">
(function(){

	// also see Darkmode class in assets/js/general.js

	if( typeof(Storage) !== 'undefined' ) {
		// show toggle if we can use localStorage
		document.getElementById('darkmode-toggle').classList.add('visible');
	}

	if( typeof(Storage) === "undefined" || ! localStorage.getItem( 'darkmode_state' ) ) {

		if( window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ) {
			// browser says user prefers darkmode!
			document.body.classList.add('darkmode');
			// we don't set a localStorage item though,
			// because the user did not explicitly choose this view
		}

		return;
	}

	if( localStorage.getItem( 'darkmode_state' ) == 'dark' ) {
		document.body.classList.add('darkmode');
	} else {
		document.body.classList.remove('darkmode');
	}

})();
</script>
