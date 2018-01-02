<?php

$pharFile = './joomlaNamespaceChecker.phar';

if (file_exists($pharFile))
{
	unlink($pharFile);
}

$phar = new Phar('joomlaNamespaceChecker.phar');
$phar->setSignatureAlgorithm(Phar::SHA1);

$phar->startBuffering();

$phar->addFile('src/index.php');
$phar->addFile('src/classmap.php');
$phar->addFile('src/Checker.php');

$phar->setStub(
	$phar->createDefaultStub('src/index.php', 'src/index.php')
);

$phar->stopBuffering();