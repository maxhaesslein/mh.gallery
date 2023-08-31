<?php

if( ! $core ) exit;

$gallery = $core->route->get('gallery');


$overview_link = $gallery->get_url();
$refresh_url = $gallery->get_zip_url();

if( ! $gallery->is_zipfile_ready() ) {
	$image_count = count($gallery->get_images());
	$missing_image_count = $gallery->add_batch_to_zip();
} else {
	$gallery->output_zipfile();
	exit;
}

snippet( 'header' );

?>
<main>

	<h1><a href="<?= $overview_link ?>"><?= $gallery->get_title() ?></a></h1>

	<p><progress value="<?= $image_count-$missing_image_count ?>" max="<?= $image_count ?>"></p>

	<?php
	if( $missing_image_count > 0 ) {
		?>
		<p>generating zip file (<?= $image_count-$missing_image_count ?>/<?= $image_count ?> images), please wait …</p>
		<p class="refresh-link-wrapper"><a href="<?= $refresh_url ?>">reload this page</a></p>

		<script type="text/javascript">
			setTimeout(function(){
				window.location.href = '<?= $refresh_url ?>';
			}, 500);
		</script>
		<?php
	} else {
		?>
		<p>finished generating zip file, starting download …</p>
		<p>if your download does not start, <a href="<?= $refresh_url ?>">reload this page</a></p>
		<?php
	}
	?>


</main>
<?php

snippet( 'footer' );

