# mh.gallery

A simple and lightweight PHP gallery, without any dependencies.

This is a very early version, expect things to not work or change. This readme will be expanded later.

## Requirements

- PHP 8.0 or higher
- write access to the folder where it's installed
- mod_rewrite support
- maybe more? this list will be expanded later

## Installation

Download the latest release .zip file, extract it, upload it to a folder on your webserver and open the URL to this folder in a browser. Missing files and folders will be automatically created.

## Create Galleries

create a `content/` folder and add subfolders for your galleries. You can also organize them in additional subfolders, like  `content/2023/gallery-name/`. In each gallery folder, create a new text file called  `gallery.txt`. This file is needed to detect this folder as a gallery, you can also add some additional information to this file:

```txt
title: my cool gallery
slug: my-cool-gallery
hidden: true
secret: 1234
```

If you ommit the title or slug, it gets automatically generated based on the folder name. The slug and secret should only consist of URL safe charactes; use only `a-z`, `0-9` and `-_` to be safe.

Add `.jpg` files into the gallery folder, the images get detected automatically. For supported extensions see the `image_extensions` option in `system/config.php`.

Setting `hidden: true` will hide this gallery from the index (homepage). You can also disable the index with a config option, see 'Customization' below.

Setting a `secret` will hide this gallery from the index, and only make the url accessible with the secret appenden to the slug. For example, if the slug is `gallery-01` and the secret is set to `secret: a1b2c3`, the URL to open the gallery will be `https://www.example.com/gallery-01-a1b2c3`.

## Update

Make a backup of the `content/` and `custom/` folders, then delete the content of the `cache/` folder as well as the complete `system/` folder and all the files in the root directory (`index.php`, `README.md`, `.htaccess` and `.gitignore`). Then download the latest release .zip file, extract it and upload everything. (But make sure to not overwrite the `content/` and `custom` folders!)

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
