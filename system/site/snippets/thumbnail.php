<?php

if( ! $core ) exit;

$image = $args['image'];

$url = $image->get_url();

$width = 300; // TODO
$height = 200; // TODO
$crop = true;

$thumbnail_html = $image->resize( $width, $height, $crop )->get_html();

?>
<a href="<?= $url ?>">
	<?= $thumbnail_html ?>
</a>
