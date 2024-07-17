<?php

// This file is part of mh.gallery
// Copyright (C) 2023-2024 maxhaesslein
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
// See the file LICENSE.md for more details.

if( ! $core ) exit;

?>
<form action="<?= current_url() ?>" method="POST">
	<p>this gallery is password protected</p>
	<input type="password" name="gallery-password" autofocus autocomplete="current-password" autocapitalize="off" placeholder="password" required>
	<input type="hidden" name="action" value="login">
	<button>login</button>

	<?php
	if( ! empty($_POST['gallery-password']) ) {
		echo '<p class="login-error">wrong password</p>';
	}
	?>

</form>
