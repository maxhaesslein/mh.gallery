<?php

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
