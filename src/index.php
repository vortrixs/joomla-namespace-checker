<?php

require_once 'classmap.php';
require_once 'Checker.php';

/**
 * Paths/files not to check
 *
 * e.g. to exclude mpdf add `$ php joomlaNamespaceChecker.phar --exclude=/mpdf/`
 *
 * Don't add vendor or node_modules, those are included by default
 *
 * @var  $options  array
 */
 $opts = getopt('', ['exclude:']);
 
 $paths = explode(',', $opts['exclude']);

/**
 * The folder to scan
 *
 * @var   string
 */
$folder = end($argv);

/**
 * Classmap found in classmap.php
 *
 * @var   array
 */
$classmap = classmap();

$checker = new Checker($paths, $classmap);

try
{
	$checker->scan($folder);
}
catch (Error $e)
{
	echo str_repeat(PHP_EOL, 2);
	echo $e->getMessage();
	exit(1);
}

exit(0);
