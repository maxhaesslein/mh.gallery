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

		<?php
		$rate_limit_exceeded = $core->route->get_gallery_rate_limit_exceeded();
		$rate_limit_exceeded = $rate_limit_exceeded && $rate_limit_exceeded === $gallery->get_slug();
		?>
		<p>
			<input type="password" name="gallery-password" <?= $rate_limit_exceeded ? 'disabled' : 'autofocus' ?> autocomplete="current-password" autocapitalize="off" placeholder="<?= __('password') ?>" <?= $rate_limit_exceeded ? '' : 'required' ?>>
			<input type="hidden" name="action" value="login">
			<input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
			<button <?= $rate_limit_exceeded ? 'disabled' : '' ?>><?= __('login') ?></button>
		</p>

		<?php
		if( $rate_limit_exceeded ) {
			?>
			<p class="login-error"><?= __('rate limit exceeded') ?></p>
			<?php
		} elseif( ! empty(sanitize_post('gallery-password')) ) {
			?>
			<p class="login-error"><?= __('wrong password') ?></p>
			<?php
		}
		?>

	</form>


</main>
<?php

snippet( 'footer' );
