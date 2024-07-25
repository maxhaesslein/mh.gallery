<?php

// This file is part of mh.gallery
// Copyright (C) 2023-2024 maxhaesslein
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
// See the file LICENSE.md for more details.

if( ! $core ) exit;

$gallery = $core->route->get('gallery');
$image = $core->route->get('image');

$gallery_slug = $gallery->get_slug();

$prev_image = $image->get_adjacent_image('prev');
$next_image = $image->get_adjacent_image('next');

$prev_link = false;
$next_link = false;
if( $prev_image ) $prev_link = $prev_image->get_link();
if( $next_image ) $next_link = $next_image->get_link();

$download_image_url = false;
if( $gallery->is_download_image_enabled() ) {
	$download_filetype = get_config( 'download_filetype' ) ?? 'jpg';
	$original_filename = $image->get_original_filename();
	$download_image = new Image( $original_filename, $gallery );
	$download_image_quality = get_config( 'image_quality_'.$download_filetype ) ?? get_config( 'image_quality_jpg' );
	$query = [];
	if( $download_filetype ) {
		$query['type'] = $download_filetype;
		$query['quality'] = $download_image_quality;
	}
	$download_image_url = $download_image->get_image_url( $query );
	$download_image_filename = remove_fileextension($original_filename).'.'.$download_filetype;
}

$download_gallery_url = false;
if( $gallery->is_download_gallery_enabled() ) {
	$download_gallery_url = $gallery->get_zip_download_url();
}


$image_args = [
	'width' => get_config('default_image_width')
];


snippet( 'header' );


$download_menu = [];
if( $download_image_url ) {
	$download_menu[] = '<a href="'.$download_image_url.'" download="'.$download_image_filename.'">download image</a>';
}
if( $download_gallery_url ) {
	$download_menu[] = '<a href="'.$download_gallery_url.'">download all</a>';
}


$show_camera_information = $gallery->get_config('camera_information', true, true);
if( $show_camera_information ) {
	$camera_information = $image->get_camera_information();
} else {
	$camera_information = false;
}



if( ! doing_ajax() ) {
	?>
	<main id="fullscreen-target">
	<?php
}
?>
	<div class="meta meta-top">
		<?php snippet( 'gallery-action', ['gallery' => $gallery, 'image' => $image] ); ?>
		<ul class="action">
			<li class="button-fullscreen action-js"><a id="action-fullscreen" href="">fullscreen</a></li>
			<?php

			if( ! empty($camera_information) ) {
				?>
				<li class="information-wrapper">
					<details>
						<summary class="button-information">information</summary>
						<ul class="information-content">
							<?php
							if( ! empty($camera_information['Camera']) ) {
								echo '<li>'.$camera_information['Camera'].'</li>';
								unset($camera_information['Camera']);
							}
							if( ! empty($camera_information['Lens']) ) {
								echo '<li>'.$camera_information['Lens'].'</li>';
								unset($camera_information['Lens']);
							}

							echo '<li>';
								echo implode(' | ', $camera_information);
							echo '</li>';
							?>
						</ul>
					</details>
				</li>
				<?php
			}

			if( count($download_menu) ) {
				?>
				<li class="download-menu-wrapper">
					<span class="button-download">download …</span>
					<ul class="download-menu">
						<?php
						foreach( $download_menu as $download_menu_item ) {
							?>
							<li><?= $download_menu_item ?></li>
							<?php
						}
						?>
					</ul>
				</li>
				<?php
			}
			?>
		</ul>
	</div>
	<ul class="navigation">
		<?php
		if( $prev_link ) echo '<li><a id="navigate-prev" class="navigate-prev" href="'.$prev_link.'" data-prev-image-slug="'.$prev_image->get_slug().'" data-gallery-slug="'.$gallery_slug.'" rel="prev">prev</a></li>';
		if( $next_link ) echo '<li><a id="navigate-next" class="navigate-next" href="'.$next_link.'" data-next-image-slug="'.$next_image->get_slug().'" data-gallery-slug="'.$gallery_slug.'" rel="next">next</a></li>';
		?>
	</ul>
	<div id="image-wrapper" class="image-wrapper">
		<?= $image->get_html( $image_args ) ?>
	</div>
	<div class="meta meta-bottom">
		<ul class="info">
			<li><?= $image->get_number() ?>/<?= $gallery->get_image_count() ?></li>
		</ul>
	</div>
<?php
if( ! doing_ajax() ) {
	?>
	</main>
	<?php
}

snippet( 'footer' );
