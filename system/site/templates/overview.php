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

$overview_link = $gallery->get_parent_url();
if( $overview_link ) {
	$overview_link .= '#'.$gallery->get_slug();
}

$imagecount = $gallery->get_image_count();

?>
<main>

	<?php

	if( $overview_link ) {
		?>
		<a class="button" href="<?= $overview_link ?>">&laquo; overview</a>
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

			foreach( $sub_galleries as $sub_gallery ) {

				if( $sub_gallery->is_hidden() ) continue;

				$url = $sub_gallery->get_url();
				$thumbnail = $sub_gallery->get_thumbnail();
				$title = $sub_gallery->get_title();
				?>
				<li>
					<a class="thumbnail-anchor anchor" name="<?= $sub_gallery->get_slug() ?>"></a>
					<a class="gallery-link" href="<?= $url ?>">
						<?php
						if( $thumbnail ) {
							snippet( 'thumbnail', [ 'image' => $thumbnail ] );
						} else {
							echo '<span class="empty-thumbnail" style="padding-top: calc('.(1/get_config('thumbnail_aspect_ratio')).' * 100%);"></span>';
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


	if( $imagecount > 0 ) {
		?>
		<div class="meta">
			<ul class="info">
				<li><?= $imagecount ?> images</li>
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
					<a class="thumbnail-anchor anchor" name="<?= $image->get_slug() ?>"></a>
					<a class="image-link" href="<?= $url ?>">
						<?php snippet( 'thumbnail', [ 'image' => $image ] ); ?>
					</a>
				</li>
				<?php
			}
			?>
		</ul>
		<?php
	}
	?>

</main>
<?php

snippet( 'footer' );
