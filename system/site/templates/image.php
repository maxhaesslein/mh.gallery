<?php

// This file is part of mh.gallery
// Copyright (C) 2023-2025 maxhaesslein
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
	$download_menu[] = '<a href="'.$download_image_url.'" download="'.$download_image_filename.'" title="'.__('download this image in maximum quality').'">'.__('download image').'</a>';
}
if( $download_gallery_url ) {
	$download_menu[] = '<a href="'.$download_gallery_url.'" title="'.__('download all images in this gallery in maximum quality as a .zip file').'">'.__('download all').'</a>';
}


$camera_information = $image->get_camera_information();


if( ! doing_ajax() ) {
	?>
	<main id="fullscreen-target">
	<?php
}
?>
	<div class="meta meta-top">
		<?php snippet( 'gallery-action', ['gallery' => $gallery, 'image' => $image] ); ?>
		<ul class="action">
			<li class="button-fullscreen action-js"><a id="action-fullscreen" href="" title="<?= __('fullscreen') ?>"><?= __('fullscreen') ?></a></li>
			<?php

			if( ! empty($camera_information) ) {
				?>
				<li class="button-information" id="action-information" title="<?= __('information') ?>"><?= __('information') ?></li>
				<?php
			}

			if( count($download_menu) ) {
				?>
				<li class="download-menu-wrapper" id="download-overlay">
					<span class="button-download"><?= __('download') ?> …</span>
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
		if( $prev_link ) echo '<li><a id="navigate-prev" class="navigate-prev" href="'.$prev_link.'" data-prev-image-slug="'.$prev_image->get_slug().'" data-gallery-slug="'.$gallery_slug.'" rel="prev">'.__('prev').'</a></li>';
		if( $next_link ) echo '<li><a id="navigate-next" class="navigate-next" href="'.$next_link.'" data-next-image-slug="'.$next_image->get_slug().'" data-gallery-slug="'.$gallery_slug.'" rel="next">'.__('next').'</a></li>';
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
	if( ! empty($camera_information) ) {
		?>
		<dialog id="image-information" class="image-information">
			<ul>
				<?php

				if( ! empty($camera_information['Date']) || ! empty($camera_information['Time']) ) {

					$datetime = [];
					if( ! empty($camera_information['Date']) ) {
						$datetime[] = $camera_information['Date'];
						unset($camera_information['Date']);
					}
					if( ! empty($camera_information['Time']) ) {
						$datetime[] = $camera_information['Time'];
						unset($camera_information['Time']);
					}

					echo '<li>'.implode(' ', $datetime).'</li>';
				}

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
			<span id="image-information-close" class="image-information-close" title="<?= __('close') ?>"><?= __('close') ?></span>
		</dialog>
		<?php
	}


if( ! doing_ajax() ) {
	?>
	</main>
	<?php
}

snippet( 'footer' );
