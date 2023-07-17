<?php

if( ! $core ) exit;

$image = $args['image'];

$url = $image->get_url();

?>
<a href="<?= $url ?>">
	<?= $url ?>
</a>
