<?php

if( ! $core ) exit;

$gallery = $core->route->get('gallery');


$overview_link = $gallery->get_url();
$refresh_url = $gallery->get_zip_download_url();
$image_count = count($gallery->get_images());

$missing_image_count = 0;

if( ! $gallery->is_zipfile_ready() ) {
	$missing_image_count = $gallery->add_batch_to_zip();
}
$download_url = $gallery->get_zip_url();
$size = $gallery->get_zip_size();
$filename = $gallery->get_zip_filename();

snippet( 'header' );

?>
<main>

	<div class="meta meta-top" style="padding: 0;">
		<ul class="action">
			<li><a id="navigate-overview" href="<?= $overview_link ?>">&laquo; overview</a></li>
		</ul>
	</div>

	<h1><?= $gallery->get_title() ?></h1>

	<?php
	if( $missing_image_count > 0 ) {
		?>
		<p><progress value="<?= $image_count-$missing_image_count ?>" max="<?= $image_count ?>"></p>
		<p>generating zip file (<?= $image_count-$missing_image_count ?>/<?= $image_count ?> images), please wait<?php
		if( $missing_image_count > 40 ) echo ', this may take some time';
		?> …</p>
		<p>(leave this window open while the zip file is being generated)</p>
		<p class="refresh-link-wrapper"><a class="button" href="<?= $refresh_url ?>">reload this page</a></p>
		<?php
	} else {
		?>
		<p>The .zip file contains <em><?= $image_count ?></em> images and weights <em><?= $size ?></em>.</p>
		<p><a class="button" href="<?= $download_url ?>" download="<?= $filename ?>">download .zip (<?= $size ?>)</a></p>
		<?php
	}
	?>


</main>
<?php

snippet( 'footer' );

