<?php

if( ! $core ) exit;

$gallery = $core->route->get('gallery');
$image = $core->route->get('image');

$image_slug = $image->get_slug();


snippet( 'header' );

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
<ul class="info">
	<li><?= $image->get_number() ?>/<?= $gallery->get_image_count() ?></li>
</ul>
<?php

$image->resize(2000);

?>
<div class="image-wrapper">
	<?= $image->get_html() ?>
</div>
<?php

snippet( 'footer' );
