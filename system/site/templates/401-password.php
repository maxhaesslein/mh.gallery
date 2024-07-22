<?php

// This file is part of mh.gallery
// Copyright (C) 2023-2024 maxhaesslein
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
	
	<form action="<?= get_current_url() ?>" method="POST">
		<p>This gallery is password protected.</p>

		<p>
			<input type="password" name="gallery-password" autofocus autocomplete="current-password" autocapitalize="off" placeholder="password" required><input type="hidden" name="action" value="login">
			<button>login</button>
		</p>

		<?php
		if( ! empty($_POST['gallery-password']) ) {
			echo '<p class="login-error">wrong password</p>';
		}
		?>

	</form>


</main>
<?php

snippet( 'footer' );
