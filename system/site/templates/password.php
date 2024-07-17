<?php

// This file is part of mh.gallery
// Copyright (C) 2023-2024 maxhaesslein
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
// See the file LICENSE.md for more details.

if( ! $core ) exit;

$gallery = $core->route->get('gallery');

snippet( 'header' );

$title = $gallery->get_title();

$overview_link = $gallery->get_parent_url();
if( $overview_link ) {
	$overview_link .= '#'.$gallery->get_slug();
}

?>
<main>

	<?php

	if( $overview_link ) {
		?>
		<a class="button" href="<?= $overview_link ?>">&laquo; overview</a>
		<?php
	}

	if( $title ) {
		?>
		<h1><?= $title ?></h1>
		<?php
	}

	snippet( 'password-form' );

	?>

</main>
<?php

snippet( 'footer' );
