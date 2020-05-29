# Joomla Namespace Checker (JNSC)
For migrating to Joomla 3.8.x

This script scans a directory recursively for PHP files containing calls to the old classes (e.g. JFactory).

**DISCLAIMER:** Keep in mind that the results from this tool are only suggestions and that these changes are *NOT* required for your application to work until Joomla 5 is released. Thanks to @anibalsanchez for pointing this out ([#6](/../../issues/6)).

As a side note you can always find the full definition for the new class names in [Joomlas own repository](https://github.com/joomla/joomla-cms/blob/staging/libraries/classmap.php).

**Note:** I'm not actively supporting this repository at the moment as I'm working with Joomla anymore, but do feel free to submit PRs if you have made any changes or found any bugs.

## Requirements
* PHP 7.x

## Usage

### Using Composer
Add the package to your `composer.json`

```
composer require --dev vortrixs/joomla-namespace-checker
```

Afterwards you can run it using
```
vendor/bin/jnsc path/to/your/project/
```
This should work as long as PHP is in your `$PATH`.


If this does not work you can always execute the PHAR directly
```
php vendor/vortrixs/joomla-namespace-checker/bin/jnsc.phar
```

### Using the PHAR
Download the PHAR from the [latest release](https://github.com/vortrixs/joomla-namespace-checker/releases) and run it like so
```
$ php jnsc.phar path/to/your/project
```

If any calls are found you will get the following output:
```
FILE: path/to/your/project/someFile.php
-------------------------------------------------------------------------------------
Line: 12 | Class found: JTable        | Replace with: Joomla\CMS\Table\Table
Line: 21 | Class found: JPlugin       | Replace with: Joomla\CMS\Plugin\CMSPlugin
Line: 47 | Class found: JPluginHelper | Replace with: Joomla\CMS\Plugin\PluginHelper
Line: 56 | Class found: JFactory      | Replace with: Joomla\CMS\Factory
Line: 75 | Class found: JPluginHelper | Replace with: Joomla\CMS\Plugin\PluginHelper
Line: 84 | Class found: JFactory      | Replace with: Joomla\CMS\Factory
-------------------------------------------------------------------------------------
```

### Excluding paths & files

Pass the `--exclude` option to exclude directories and files.

```
$ php jnsc.phar --exclude=/somepath/,somefile.php,some/other/path/ path/to/your/project
```

The exclusion is very basic so you might have to tweak the paths a bit, this also means wildcards like * are *not* supported.

e.g. if you pass `--exclude=google`, it will filter out anything that has `google` in it's path or filename.

To reliably filter out a whole directory wrap it in `/ /` and for single files use `filename.ext`.
