<?php

// This file is part of mh.gallery
// Copyright (C) 2023-2024 maxhaesslein
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
// See the file LICENSE.md for more details.

if( ! $core ) exit;

// TODO: split this into multiple files; handle action elsewhere.

$request = $core->route->get('request');

$action = $_POST['action'] ?? false;
$auth = $_SESSION['admin-auth'] ?? false;

if( $action == 'login') {
	
	$input_password = $_POST['admin-password'] ?? false;
	$stored_password = get_config('admin_password');

	if( password_verify($input_password, $stored_password) || $input_password === $stored_password ) {

		// NOTE: currently, we only hash the stored password and save it directly in the session; TODO: generate a temporary key in the cache that expires after some time, don't use the password directly.
		$_SESSION['admin-auth'] = get_hash(get_config('admin_password'));
		redirect('admin');

	}

} elseif( ! empty($request[0]) && $request[0] == 'logout' ) {
	unset($_SESSION['admin-auth']);
	redirect('admin');
}


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

			?>
			<li<?= get_class_attribute($classes) ?>>
				<a href="<?= $sub_gallery->get_url( true, true ) ?>" target="_blank">
					<?php
					echo $sub_gallery->get_title();
					?>
				</a>
				<?php
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

	if( $auth == get_hash(get_config('admin_password')) ) {

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
			<input type="password" name="admin-password" autofocus>
			<input type="hidden" name="action" value="login">
			<button>login</button>
		</form>
		<?php

		if( $action == 'login' ) {
			echo '<p class="login-error">wrong password</p>';
		}

	}

	?>

</main>
<?php

snippet( 'footer' );
