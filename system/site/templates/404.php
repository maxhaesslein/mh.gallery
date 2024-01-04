<?php

if( ! $core ) exit;

header('HTTP/1.1 404 Not Found');

snippet( 'header' );

?>
<main>
	<h1>Not found.</h1>
	<p>This page does not exist.</p>
</main>
<?php

snippet( 'footer' );
