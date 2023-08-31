<?php

if( ! $core ) exit;

$gallery = $core->route->get('gallery');
$images = $gallery->get_images();

snippet( 'header' );

?>
<h1><?= $gallery->get_title() ?></h1>

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
<?php

snippet( 'footer' );
