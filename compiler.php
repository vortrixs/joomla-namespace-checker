<?php

$pharFile = './joomlaNamespaceChecker.phar';

if (file_exists($pharFile))
{
	unlink($pharFile);
}

$phar = new Phar('joomlaNamespaceChecker.phar');
$phar->setSignatureAlgorithm(Phar::SHA1);

$phar->startBuffering();

$phar->addFile('source/index.php');
$phar->addFile('source/classmap.php');
$phar->addFile('source/Checker.php');

$phar->setStub(
	$phar->createDefaultStub('source/index.php', 'source/index.php')
);

$phar->stopBuffering();
