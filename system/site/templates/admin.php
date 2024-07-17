<?php

// This file is part of mh.gallery
// Copyright (C) 2023-2024 maxhaesslein
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
// See the file LICENSE.md for more details.

if( ! $core ) exit;


function print_sub_galleries( $gallery ) {

	$sub_galleries = $gallery->get_sub_galleries();

	if( ! is_array($sub_galleries) || ! count($sub_galleries) ) return;

	?>
	<ul>
		<?php
		foreach( $sub_galleries as $sub_gallery ) {

			$hidden = $sub_gallery->is_hidden();

			$classes = ['admin-gallery-list_subgallery'];
			if( $hidden ) $classes[] = 'is-hidden';

			$secret = $sub_gallery->is_secret();

			?>
			<li<?= get_class_attribute($classes) ?>>
				<a href="<?= $sub_gallery->get_url() ?>" target="_blank"><?= $sub_gallery->get_title() ?></a>
				<?php

				if( $sub_gallery->is_hidden() ) {
					echo ' [hidden]';
				}

				if( $sub_gallery->is_secret() ) {
					$secret = $sub_gallery->get_secret();
					echo ' [secret: <a href="'.$sub_gallery->get_url().'?secret='.$secret.'" target="_blank">'.$secret.'</a>]';
				}

				if( $sub_gallery->is_password_protected() ) {
					echo ' [password protected]';
				}

				print_sub_galleries($sub_gallery);
				?>
			</li>
			<?php
		}
		?>
	</ul>
	<?php
}


snippet( 'header' );

?>
<main>

	<h2>Admin Area</h2>

	<?php
	if( admin_verify() ) {
		?>
		<p><a href="<?= url('admin/logout') ?>">Logout</a></p>
		<?php

		$root_gallery = $core->gallery;

		?>
		<div class="admin-gallery-list">
			<?php
			print_sub_galleries($root_gallery);
			?>
		</div>
		<?php

	} else {

		?>
		<form action="<?= url('admin') ?>" method="POST">
			<input type="password" name="admin-password" autofocus autocomplete="current-password" autocapitalize="off" placeholder="password" required>
			<input type="hidden" name="action" value="login">
			<button>login</button>

			<?php
			if( ! empty($_POST['admin-password']) ) {
				echo '<p class="login-error">wrong password</p>';
			}
			?>
			
		</form>
		<?php

	}

	?>

</main>
<?php

snippet( 'footer' );
