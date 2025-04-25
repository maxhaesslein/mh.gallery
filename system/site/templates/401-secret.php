<?php

// This file is part of mh.gallery
// Copyright (C) 2023-2025 maxhaesslein
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
// See the file LICENSE.md for more details.

if( ! $core ) exit;

header('HTTP/1.1 401 Unauthorized');

snippet( 'header' );

$gallery = $core->route->get('gallery');

$title = $gallery->get_title();

?>
<main>

	<div class="meta meta-top">
		<?php snippet( 'gallery-action', ['gallery' => $gallery] ); ?>
	</div>

	<?php
	if( $title ) {
		?>
		<h1><?= $title ?></h1>
		<?php
	}
	?>

	<p style="text-align: center;"><?= __('You are not allowed to view this gallery.') ?></p>

</main>
<?php

snippet( 'footer' );
