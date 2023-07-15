# mh.gallery

A simple PHP gallery site, without any dependencies.

This is a very early version, expect things to not work or change. This readme will be expanded later.

## Requirements

- PHP 8.0 or higher
- write access to the folder where it's installed
- mod_rewrite support
- maybe more? this list will be expanded later

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
