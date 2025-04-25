<?php

// This file is part of mh.gallery
// Copyright (C) 2023-2025 maxhaesslein
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
// See the file LICENSE.md for more details.

if( ! $core ) exit;

$image = $args['image'];

if( ! $image ) return;

$width = 640;
$height = (int) round($width * (1/get_config('thumbnail_aspect_ratio')));

$image_args = [
	'width' => $width,
	'height' => $height,
	'crop' => true
];
$thumbnail_html = $image->get_html( $image_args, '(min-width: 1700px) 640px, 320px' );

echo $thumbnail_html;
