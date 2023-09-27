<?php

if( ! $core ) exit;

$collection = $core->route->get('collection');

snippet( 'header' );


if( $collection ) {
	$collection_list = $collection->get();
}

?>
<main>

	<h1><?= get_config('site_title') ?></h1>

	<?php
	if( count($collection_list) ) {
		?>
		<ul class="gallery-list">
			<?php

			foreach( $collection_list as $collection_or_gallery ) {

				if( $collection_or_gallery->is_hidden() ) continue;

				$url = $collection_or_gallery->get_url();

				$image = false;
				$image = $collection_or_gallery->get_thumbnail();
				
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
		<?php
	}
	?>
	
</main>
<?php

snippet( 'footer' );
