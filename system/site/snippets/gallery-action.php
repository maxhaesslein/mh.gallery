<?php

// This file is part of mh.gallery
// Copyright (C) 2023-2024 maxhaesslein
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
// See the file LICENSE.md for more details.

if( ! $core ) exit;

$gallery = $args['gallery'];
$image = $args['image'] ?? false;

if( $image ) {	
	$overview_link = $gallery->get_url();
	$overview_link .= '#'.$image->get_slug();;
} else {
	$overview_link = $gallery->get_parent_url();
	if( $overview_link ) {
		$overview_link .= '#'.$gallery->get_slug();
	}
}

?>
<ul class="action">
	<li><a id="navigate-overview" href="<?= $overview_link ?>">&laquo; overview</a></li>
	<?php
	if( $gallery->is_secret() && $gallery->secret_provided() ) {
		?>
		<li><a class="button" href="<?= $gallery->get_url() ?>?end-session" title="end secret session">end session</a></li>
		<?php
	}
	if( $gallery->is_password_protected() && $gallery->password_provided() ) {
		?>
		<li><a class="button" href="<?= $gallery->get_url() ?>?lock" title="lock gallery">lock</a></li>
		<?php
	}
	?>
</ul>