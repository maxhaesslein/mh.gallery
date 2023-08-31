<?php

if( ! $core ) exit;

$galleries = $core->galleries->get();

snippet( 'header' );

echo '<h1>'.get_config('site_title').'</h1>';

echo '<ul>';
foreach( $galleries as $gallery ) {
	if( $gallery->is_hidden() ) continue;
	echo '<li>';
		echo '<a href="'.$gallery->get_url().'">';
			echo $gallery->get_title();
		echo '</a>';
	echo '</li>';
}
echo '</ul>';

snippet( 'footer' );
