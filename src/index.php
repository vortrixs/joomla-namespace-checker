<?php

require_once 'classmap.php';
require_once 'Checker.php';

if (count($argv) <= 1) {
    echo "Missing arguments. Remember to specify a file or directory." . PHP_EOL;
    exit(1);
}

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

$paths = [];

if (!empty($opts)) {
    $paths = explode(',', $opts['exclude']);
}

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

$checker = new \JNSC\Checker($paths, $classmap);

try {
    $checker->scan($folder);
}
catch (\Throwable $e) {
    echo PHP_EOL . PHP_EOL . $e->getMessage() . PHP_EOL;
    exit(1);
}

echo PHP_EOL;
exit(0);
