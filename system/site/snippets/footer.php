<?php

if( ! $core ) exit;

if( doing_ajax() ) return;

?>


<footer>

	<?php menu('footer'); ?>

	<a class="gallery-copyright" href="https://github.com/maxhaesslein/mh.gallery/" target="_blank" rel="noopener">mh.gallery v.<?= get_version() ?></a>

</footer>

<?php measure_execution_time(); ?>

</body>
</html>