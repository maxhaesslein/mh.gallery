<?php

if( ! $core ) exit;

$gallery = $core->route->get('gallery');
$images = $gallery->get_images();

snippet( 'header' );

$download_gallery_url = false;
if( $gallery->is_download_gallery_enabled() ) {
	$download_gallery_url = $gallery->get_zip_url();
	$download_gallery_filename = $gallery->get_zip_filename();
}

?>
<h1><?= $gallery->get_title() ?></h1>

<div class="meta">
	<ul class="info">
		<li><?= $gallery->get_image_count() ?> images</li>
	</ul>
	<ul class="action">
		<?php
		if( $download_gallery_url ) {
			?>
			<li><a href="<?= $download_gallery_url ?>" download="<?= $download_gallery_filename ?>">download gallery</a></li>
			<?php
		}
		?>
	</ul>
</div>

<ul class="gallery-list">
	<?php
	foreach( $images as $image ) {
		$url = $image->get_link();
		?>
		<li>
			<a href="<?= $url ?>">
				<?php snippet( 'thumbnail', [ 'image' => $image ] ); ?>
			</a>
		</li>
		<?php
	}
	?>
</ul>
<?php

snippet( 'footer' );
