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
$image_slug = $image->get_slug();

$overview_link = $gallery->get_url();
$overview_link .= '#'.$image_slug;

$prev_image = $image->get_adjacent_image('prev');
$next_image = $image->get_adjacent_image('next');

$prev_link = false;
$next_link = false;
if( $prev_image ) $prev_link = $prev_image->get_link();
if( $next_image ) $next_link = $next_image->get_link();

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


$image_args = [
	'width' => get_config('default_image_width')
];


snippet( 'header' );


$more_menu = [];
if( $download_image_url ) {
	$more_menu[] = '<a href="'.$download_image_url.'" download="'.$download_image_filename.'">download image</a>';
}
if( $download_gallery_url ) {
	$more_menu[] = '<a href="'.$download_gallery_url.'">download all</a>';
}


if( ! doing_ajax() ) {
	?>
	<main id="fullscreen-target">
	<?php
}
?>
	<div class="meta meta-top">
		<ul class="action">
			<li><a id="navigate-overview" href="<?= $overview_link ?>">&laquo; overview</a></li>
			<?php
			if( $gallery->is_password_protected() && $gallery->password_provided() ) {
				?>
				<li><a class="button" href="<?= $gallery->get_url() ?>?lock">lock gallery</a></li>
				<?php
			}
			?>
		</ul>
		<ul class="action">
			<li class="button-fullscreen action-js"><a id="action-fullscreen" href="">fullscreen</a></li>
			<?php
			if( count($more_menu) ) {
				?>
				<li class="more-menu-wrapper">
					<span class="button-more">more …</span>
					<ul class="more-menu">
						<?php
						foreach( $more_menu as $more_menu_item ) {
							?>
							<li><?= $more_menu_item ?></li>
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
