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

		$images = $gallery->get_images();

		$image = $images[array_keys($images)[0]];

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
