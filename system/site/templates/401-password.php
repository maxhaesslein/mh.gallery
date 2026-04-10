<?php

// This file is part of mh.gallery
// Copyright (C) 2023-2026 maxhaesslein
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
		<h1><?= escape_html($title) ?></h1>
		<?php
	}
	?>
	
	<form action="<?= escape_html(get_current_url()) ?>" method="POST">
		<p><?= __('This gallery is password protected.') ?></p>

		<p>
			<input type="password" name="gallery-password" autofocus autocomplete="current-password" autocapitalize="off" placeholder="<?= __('password') ?>" required><input type="hidden" name="action" value="login">
			<button><?= __('login') ?></button>
		</p>

		<?php
		if( ! empty(sanitize_post('gallery-password')) ) {
			?>
			<p class="login-error"><?= __('wrong password') ?></p>
			<?php
		}
		?>

	</form>


</main>
<?php

snippet( 'footer' );
