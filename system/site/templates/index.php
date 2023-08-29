<?php

if( ! $core ) exit;

// TODO: move this to route
$galleries = $core->galleries->get();

snippet( 'header' );

echo '<h1>'.get_config('site_title').'</h1>';

echo '<ul>';
foreach( $galleries as $gallery ) {
	echo '<li>';
		echo '<a href="'.$gallery->get_url().'">';
			echo $gallery->get_title();
		echo '</a>';
	echo '</li>';
}
echo '</ul>';

snippet( 'footer' );
