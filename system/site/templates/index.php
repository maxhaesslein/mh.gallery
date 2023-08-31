<?php

if( ! $core ) exit;

$galleries = $core->galleries->get();

snippet( 'header' );

?>
<h1><?= get_config('site_title') ?></h1>

<ul class="gallery-list">
	<?php
	foreach( $galleries as $gallery ) {
		if( $gallery->is_hidden() ) continue;

		$url = $gallery->get_url();

		$thumbnail_slug = $gallery->get_thumbnail_slug();
		$image = $gallery->get_image($thumbnail_slug);

		$title = $gallery->get_title();

		?>
		<li>
			<a href="<?= $url ?>">
				<?php snippet( 'thumbnail', [ 'image' => $image ] ); ?>
				<span class="title"><?= $title ?></span>
			</a>
		</li>
		<?php
	}
	?>
</ul>
<?php

snippet( 'footer' );
