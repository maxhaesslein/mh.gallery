<?php

// This file is part of mh.gallery
// Copyright (C) 2023-2024 maxhaesslein
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
// See the file LICENSE.md for more details.

if( ! $core ) exit;

if( doing_ajax() ) return;

?>


<footer>

	<?php menu('footer'); ?>

	<span class="spacer"></span>

	<a class="gallery-copyright" href="https://github.com/maxhaesslein/mh.gallery/" target="_blank" rel="noopener">mh.gallery v.<?= get_version() ?></a>

</footer>

<?php measure_execution_time(); ?>

</body>
</html>