<?php

if( ! $core ) exit;

snippet( 'header' );

echo '<h1>Hello World.</h1>';

// TODO
$galleries = $core->galleries->get();

echo '<ul>';
foreach( $galleries as $slug => $gallery ) {
	$title = $gallery->get_config('title');

	echo '<li>';
		echo '<a href="'.url($slug).'">';
			if( $title ) echo $title;
			else echo $slug;
		echo '</a>';
	echo '</li>';
}
echo '</ul>';

snippet( 'footer' );
