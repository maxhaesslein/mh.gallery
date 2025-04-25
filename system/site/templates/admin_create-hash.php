<?php

// This file is part of mh.gallery
// Copyright (C) 2023-2025 maxhaesslein
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
// See the file LICENSE.md for more details.

if( ! $core ) exit;

snippet( 'header' );

?>
<main>

	<h1><?= __('create hash') ?></h1>

	<form action="<?= url('admin/create-hash') ?>" method="POST">
		<input type="password" name="password" autofocus autocomplete="off" autocapitalize="off" placeholder="<?= __('string to hash') ?>" required>
		<button><?= __('create hash') ?></button>
	</form>

	<?php
	if( ! empty($_POST['password']) ) {
		echo '<p style="margin-top: 3em;">'.__('the generated hash is:').'<br><input type="text" onclick="javascript:this.focus();this.select();" value="'.password_hash( $_POST['password'], PASSWORD_DEFAULT ).'" style="width: 100%; max-width: 700px;"></p>';
	}
	?>

</main>
<?php

snippet( 'footer' );
