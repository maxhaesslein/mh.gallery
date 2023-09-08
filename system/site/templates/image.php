<?php

if( ! $core ) exit;

$gallery = $core->route->get('gallery');
$image = $core->route->get('image');

$gallery_slug = $gallery->get_slug();
$image_slug = $image->get_slug();

$overview_link = $gallery->get_url();
$prev_image_slug = $gallery->get_adjacent_image_slug( $image_slug, 'prev' );
$next_image_slug = $gallery->get_adjacent_image_slug( $image_slug, 'next' );

$prev_link = false;
$next_link = false;

if( $prev_image_slug ) $prev_link = $gallery->get_image_link( $prev_image_slug );
if( $next_image_slug ) $next_link = $gallery->get_image_link( $next_image_slug );

$download_image_url = false;
if( $gallery->is_download_image_enabled() ) {
	$download_image_filename = $image->get_original_filename();
	$download_image = new Image( $download_image_filename, $gallery );
	$download_filetype = get_config( 'download_filetype' );
	$query = [];
	if( $download_filetype ) {
		$query['type'] = $download_filetype;
	}
	$download_image_url = $download_image->get_image_url( $query );
}

$download_gallery_url = false;
if( $gallery->is_download_gallery_enabled() ) {
	$download_gallery_url = $gallery->get_zip_download_url();
}

$image->resize(get_config('default_image_size'));

snippet( 'header' );

if( ! doing_ajax() ) {
	?>
	<main id="fullscreen-target">
	<?php
}
?>
	<div class="meta meta-top">
		<ul class="info">
			<li><?= $image->get_number() ?>/<?= $gallery->get_image_count() ?></li>
		</ul>
		<ul class="navigation">
			<?php
			if( $prev_link ) echo '<li><a id="navigate-prev" href="'.$prev_link.'"data-prev-image-slug="'.$prev_image_slug.'" data-gallery-slug="'.$gallery_slug.'">prev</a></li>';
			if( $next_link ) echo '<li><a id="navigate-next" href="'.$next_link.'" data-next-image-slug="'.$next_image_slug.'" data-gallery-slug="'.$gallery_slug.'">next</a></li>';
			?>
		</ul>
	</div>
	<div class="image-wrapper">
		<?= $image->get_html() ?>
	</div>
	<div class="meta meta-bottom">
		<ul class="action">
			<li><a href="<?= $overview_link ?>">overview</a></li>
			<li class="button-fullscreen action-js"><a id="action-fullscreen" href="">fullscreen</a></li>
			<?php
			if( $download_image_url ) {
				?>
				<li><a href="<?= $download_image_url ?>" download="<?= $download_image_filename ?>">download image</a></li>
				<?php
			}
			if( $download_gallery_url ) {
				?>
				<li><a href="<?= $download_gallery_url ?>">download gallery</a></li>
				<?php
			}
			?>
		</ul>
	</div>
<?php
if( ! doing_ajax() ) {
	?>
	</main>
	<?php
}

snippet( 'footer' );
