# mh.gallery

A simple and lightweight PHP gallery, without any dependencies.

This is an early version, expect things to not work or change.

## Requirements

- PHP 8.0 or higher
- write access to the folder where it's installed
- .htaccess with mod_rewrite support
- php-mbstring
- php-gd
- simplexml, if you want to sort by a .BridgeSort file
- maybe more? this list will be expanded later

## Installation

### manual

Download the latest release .zip file, extract it, upload it to a folder on your webserver and open the URL to this folder in a browser. Missing files and folders will be automatically created.

### git

`cd` into the directory you want to use, then call

```bash
git clone https://github.com/maxhaesslein/mh.gallery.git ./
```

(this will use the `main` branch, which should always contain the latest release)
Then open the URL pointing to this directory in a browser. All the missing files and folders will be automatically created.

## Galleries

create a `content/` folder and add subfolders for your galleries. You can also organize them in additional subfolders, like  `content/2023/gallery-name/`. In each gallery folder, create a new text file called  `gallery.txt`. This file is needed to detect this folder as a gallery, you can also add some additional information to this file:

```txt
title: my cool gallery
slug: my-cool-gallery
description: this was a very nice and sunny day, and I took some pictures.
hidden: true
secret: 1234
thumbnail: image-01.jpg
download_image_enabled: true
download_gallery_enabled: true
sort_order: filename
```

If you ommit the title or slug, it gets automatically generated based on the folder name. The slug and secret should only consist of URL safe charactes; use only `a-z`, `0-9` and `-_` to be safe.

Add `.jpg` files into the gallery folder, the images get detected automatically. For supported extensions see the `image_extensions` option in `system/config.php`.

Setting `hidden: true` will hide this gallery from the index (homepage). You can also disable the index with a config option, see 'Customization' below.

Setting a `secret` will hide this gallery from the index, and only make the url accessible with the secret appenden to the slug. For example, if the slug is `gallery-01` and the secret is set to `secret: a1b2c3`, the URL to open the gallery will be `https://www.example.com/gallery-01-a1b2c3`.

The `sort_order` can be set via `config.php` or on a per-gallery-basis via the `gallery.txt`. The available sort orders are `filename`, `filedate` (file modification date), `exifdate` (date recorded in the exif metadata, if available, or file modification date otherwise) or `bridge` (sort by a .BridgeSort file, created by Adobe Bridge).

## Collections

Galleries can be organized inside 'collections'. A collection is like a folder that contains multiple galleries, and can also contain other collections with their own galleries. By default, there is always one root collection, that contains all galleries (and collections) that you create.

To create a collection, add a `collection.txt` file inside a folder, and add one or more subfolders with a `gallery.txt`inside that collection. The `collection.txt` file can contain some settings, like the `gallery.txt` file:

```txt
title: my nice collection
slug: my-nice-collection
description: here is a collection of some galleries
hidden: true
thumbnail: gallery-slug/image-01.jpg
```

Currently, everything inside a collection is always sorted alphabetically by slug, this will be expanded later.

## Update

### manual

Make a backup of the `content/` and `custom/` folders, then delete the content of the `cache/` folder as well as the complete `system/` folder and all the files in the root directory (`index.php`, `README.md`, `.htaccess` and `.gitignore`). Then download the latest release .zip file, extract it and upload everything. (But make sure to not overwrite the `content/` and `custom` folders!)

### git

`cd` into the directory you want to use, then call

```bash
git pull
```

this should update (and checkout) the `main` branch to the latest release. Make sure to read the release notes in `system/changelog.txt`, because you may need to manually delete a file or folder.

## Customization

Create a `custom/config.php` file, with your custom options:

```php
<?php
return [
	'site_title' => 'My Very Own Site Title',
	'allow_overview' => true,
	// all config options are listed in 'system/config.php'
];
```

these options will overwrite the default options. You can see a list of all available options in `system/config.php`, including their default values.

Create a folder called `custom/snippets/`, copy files from `system/site/snippets/` into this folder and edit them. They will be loaded instead of the equivalent file inside the `system/site/snippets/` folder. Create a folder called `custom/templates/`, copy files from `system/site/templates/` into this folder and edit them. They will be loaded instead of their equivalent.

Create a folder called `custom/assets/css/` and add `.css` files into this folder. The css files will get loaded automatically, after the system css files. If you want to disable the system css files, you can add `'system_css' => false` to the `custom/config.php`.

Create a folder called `custom/assets/js/` and add `.js` files into this folder. The js files will get loaded automatically (but `async`, so they may get executed before or after the systems JavaScript). If you want to disable the system js files, you can add `'system_js' => false` to the `custom/config.php`.

All thumbnails and images will be resized on view. The resized images will be automatically cached inside the `cache/` subfolder. Old cached files will be cleared out automatically after about 30 days. You can disable the cache to save space (at the cost of loading time) via the config option `'cache_disabled' => true` or change the lifetime of cache files (in seconds) via the config option `cache_lifetime`.
