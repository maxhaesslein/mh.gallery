<?php

if( ! $core ) exit;

snippet( 'header' );

echo '<h1>Hello World.</h1>';

// TODO
$galleries = $core->galleries->get();

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
