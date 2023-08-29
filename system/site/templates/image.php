<?php

if( ! $core ) exit;

snippet( 'header' );


// TODO
$gallery_slug = $core->route->get('request')[0];
// TODO: gallery should be loaded in the route? we may want to use it for the image and the single route as well
$gallery = $core->galleries->get_gallery($gallery_slug);

echo '<h1>'.$gallery->get_title().'</h1>';

$image_slug = $core->route->get('request')[1];
$image = $gallery->get_image($image_slug);


$overview_link = $gallery->get_url();
$prev_image_slug = $gallery->get_adjacent_image_slug( $image_slug, 'prev' );
$next_image_slug = $gallery->get_adjacent_image_slug( $image_slug, 'next' );

$prev_link = false;
$next_link = false;

if( $prev_image_slug ) $prev_link = $gallery->get_image_link( $prev_image_slug );
if( $next_image_slug ) $next_link = $gallery->get_image_link( $next_image_slug );

?>
<ul class="navigation">
	<?php
	if( $prev_link ) echo '<li><a href="'.$prev_link.'">prev</a></li>';
	echo '<li><a href="'.$overview_link.'">overview</a></li>';
	if( $next_link ) echo '<li><a href="'.$next_link.'">next</a></li>';
	?>
</ul>
<?php

$image->resize(1200);
echo $image->get_html();


snippet( 'footer' );
