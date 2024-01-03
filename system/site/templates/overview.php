<?php

if( ! $core ) exit;

$gallery = $core->route->get('gallery');
$sub_galleries = $gallery->get_sub_galleries();
$images = $gallery->get_images();

snippet( 'header' );

$download_gallery_url = false;
if( $gallery->is_download_gallery_enabled() ) {
	$download_gallery_url = $gallery->get_zip_download_url();
}

$title = $gallery->get_title();
$description = $gallery->get_description();
$overview_url = $gallery->get_parent_url();

?>
<main>

	<?php

	if( $overview_url ) {
		?>
		<a class="button" href="<?= $overview_url ?>">&laquo; overview</a>
		<?php
	}

	if( $title ) {
		?>
		<h1><?= $title ?></h1>
		<?php
	}

	if( $description ) {
		?>
		<p class="description"><?= $description ?></p>
		<?php
	}

	if( count($sub_galleries) ) {
		?>
		<ul class="gallery-list">
			<?php

			foreach( $sub_galleries as $gallery ) {

				if( $gallery->is_hidden() ) continue;

				$url = $gallery->get_url();
				$thumbnail = $gallery->get_thumbnail();
				$title = $gallery->get_title();
				?>
				<li>
					<a href="<?= $url ?>">
						<?php
						if( $thumbnail ) {
							snippet( 'thumbnail', [ 'image' => $thumbnail ] );
						} else {
							echo '<span class="empty-thumbnail"></span>';
						}
						?>
						<span class="title"><?= $title ?></span>
					</a>
				</li>
				<?php
			}
			?>
		</ul>
		<?php
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
