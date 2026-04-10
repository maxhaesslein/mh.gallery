<?php

// This file is part of mh.gallery
// Copyright (C) 2023-2026 maxhaesslein
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
// See the file LICENSE.md for more details.

if( ! $core ) exit;

// NOTE: there is also the download_refresh() function inside sysetm/functions/site.php which is responsible for auto-reloading this page (without JavaScript)

$gallery = $core->route->get('gallery');

$overview_link = $gallery->get_url();
$refresh_url = $gallery->get_zip_download_url( true );
$image_count = count($gallery->get_images());
$missing_image_count = $gallery->get_missing_image_count();

if( ! $gallery->is_zipfile_ready() && isset($query_parameters['create']) ) {
	$missing_image_count = $gallery->add_batch_to_zip();
}

$zip_file_exists = $gallery->is_zipfile_created();
if( $zip_file_exists ) {
	$download_url = $gallery->get_zip_url();
	$size = $gallery->get_zip_size();
	$filename = $gallery->get_zip_filename();
}

snippet( 'header' );

?>
<main>

	<div class="meta meta-top" style="padding: 0;">
		<ul class="action">
			<li><a id="navigate-overview" href="<?= $overview_link ?>">&laquo; <?= __('overview') ?></a></li>
		</ul>
	</div>

	<h1><?= escape_html($gallery->get_title()) ?></h1>

	<?php
	if( $missing_image_count > 0 ) {
		?>
		<p><progress value="<?= escape_html($image_count-$missing_image_count) ?>" max="<?= escape_html($image_count) ?>"></p>
		<p><?php
		echo sprintf( __('generating zip file (%d/%d images), please wait'), escape_html($image_count-$missing_image_count), escape_html($image_count) );
		if( $missing_image_count > 40 ) echo ', '.__('this may take some time');
		?> …</p>
		<p>(<?= __('leave this window open while the zip file is being generated') ?>)</p>
		<p class="refresh-link-wrapper"><?php
		echo sprintf( __('this page should %sreload automatically%s in a few seconds'), '<a class="button" href="'.escape_html($refresh_url).'">', '</a>');
		?>
		</p>
		<?php

	} else {
		?>
		<p><?php
		echo sprintf( __('The .zip file contains %s images and weights %s.'), '<em>'.escape_html($image_count).'</em>', '<em>'.escape_html($size).'</em>' );
		?></p>
		<p><a class="button" href="<?= escape_html($download_url) ?>" download="<?= escape_html($filename) ?>"><?= __('download .zip') ?> (<?= escape_html($size) ?>)</a></p>
		<?php
	}
	?>

</main>
<?php

snippet( 'footer' );
