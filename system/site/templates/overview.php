<?php

if( ! $core ) exit;

$gallery = $core->route->get('gallery');
$images = $gallery->get_images();

snippet( 'header' );

$download_gallery_url = false;
if( $gallery->is_download_gallery_enabled() ) {
	$download_gallery_url = $gallery->get_zip_download_url();
}

$description = $gallery->get_description();

$overview_url = $gallery->get_parent_url();

?>
<main>

	<?php
	if( $overview_url ) {
		echo '<a class="button" href="'.$overview_url.'">&laquo; overview</a>';
	}
	?>

	<h1><?= $gallery->get_title() ?></h1>

	<?php
	if( $description ) {
		echo '<p class="description">'.$description.'</p>';
	}
	?>

	<div class="meta">
		<ul class="info">
			<li><?= $gallery->get_image_count() ?> images</li>
			<?php
			if( $download_gallery_url ) {
				?>
				<li><a href="<?= $download_gallery_url ?>">download all</a></li>
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

</main>
<?php

snippet( 'footer' );
