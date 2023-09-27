<?php

if( ! $core ) exit;

$collection = $core->collection->get();

snippet( 'header' );

?>
<main>

	<h1><?= get_config('site_title') ?></h1>

	<ul class="gallery-list">
		<?php
		foreach( $collection as $collection_or_gallery ) {

			if( $collection_or_gallery->is_hidden() ) continue;

			$url = $collection_or_gallery->get_url();

			$image = false;
			$thumbnail_slug = $collection_or_gallery->get_thumbnail_slug();
			if( $thumbnail_slug ) {
				$image = $collection_or_gallery->get_image($thumbnail_slug);
			}

			$title = $collection_or_gallery->get_title();

			?>
			<li>
				<a href="<?= $url ?>">
					<?php if( $image ) snippet( 'thumbnail', [ 'image' => $image ] ); ?>
					<span class="title"><?= $title ?></span>
				</a>
			</li>
			<?php
		}
		?>
	</ul>
</main>
<?php

snippet( 'footer' );
