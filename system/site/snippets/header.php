<?php

if( ! $core ) exit;

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

