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

## Update

Make a backup of the `content/` and `custom/` folders, then delete the content of the `cache/` folder as well as the complete `system/` folder and all the files in the root directory (`index.php`, `changelog.txt`, `README.md`, `todo.txt`, `.htaccess` and `.gitignore`). Then download the latest release .zip file, extract it and upload everything. (But make sure to not overwrite the `content/` and `custom` folder!)

## Customiation

This will be expanded later.

Create a `custom/config.php` file, with your custom options:

```php
<?php
return [
	'site_title' => 'My Very Own Site Title',
];
```

these options will overwrite the default options.

Create a folder called `custom/snippets/`, copy files from `system/site/snippets/` into this folder and edit them. They will be loaded instead of their equivalent.

Create a folder called `custom/templates/`, copy files from `system/site/templates/` into this folder and edit them. They will be loaded instead of their equivalent.

Create a folder called `custom/assets/css/` and add `.css` files into this folder. The css files will get loaded automatically, after the system css files. If you want to disable the system css files, you can add `'system_css' => false` to the `custom/config.php`.

Create a folder called `custom/assets/js/` and add `.js` files into this folder. The js files will get loaded automatically (but `async`, so they may get executed before oder after the systems JavaScript). If you want to disable the system js files, you can add `'system_js' => false` to the `custom/config.php`.
