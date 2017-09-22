<?php

/**
 * Script to build the phar for the application.
 *
 * Build the phar with:
 * php -dphar.readonly=0 build/buildphar.php
 *
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT ANY EXPRESS OR IMPLIED
 * WARRANTIES, INCLUDING, WITHOUT LIMITATION, THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @package fusionLib.docs
 * @copyright Copyright (c) 2017 clearFusionCMS. All rights reserved.
 * @link http://fusionlib.com
 */

require_once dirname(__FILE__) . '/../libs/CLi/flCliOutput.php';

$output = new flCliOutput();

$phar = new Phar('fusiondocs.phar');
$phar->setSignatureAlgorithm(\Phar::SHA1);

$phar->startBuffering();

// Add all the files to the phar
$directory = new RecursiveDirectoryIterator('./');
$iterator = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::SELF_FIRST);
foreach ($iterator as $filename => $file) {
	$path = substr($filename, 2);

	// Filter out the paths of interest
	if(preg_match('#^(app|layouts|libs|themes)/#', $path)) { //} || $path == 'index.php') {

		// Filter out based on file extension
		if(preg_match('#\.(js|css|eot|svg|ttg|woff|woff2|gif|png|jpg|html|txt|php)$#', $path)) {
			$phar->addFile($path);
			$output->writePadded($path, 65)
				->writeLn('[Ok]', flCliOutput::GREEN);
		}
	}
}

$phar->setStub(file_get_contents('./build/stub.php'));
$phar->compressFiles(\Phar::GZ);
$phar->stopBuffering();

$output->newline()
	->writeLn('phar created')
	->newline();
