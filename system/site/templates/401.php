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

?>
<main>
	<h1>Unauthorized.</h1>
	<p>You are not allowed to view this content.</p>
</main>
<?php

snippet( 'footer' );
