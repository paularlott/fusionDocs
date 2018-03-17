<?php

/**
 * File to bootstrap the application.
 *
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT ANY EXPRESS OR IMPLIED
 * WARRANTIES, INCLUDING, WITHOUT LIMITATION, THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @package fusionDocs
 * @copyright Copyright (c) 2017 - 2018 clearFusionCMS. All rights reserved.
 * @link http://fusionlib.com
 */

// Hide the #!/usr/bin/env php
@ob_end_clean();

try {

	// If running the cli-server run the server application
	if(php_sapi_name() == 'cli-server') {
		$app = new serverApp();
		$app->run();
	}
	// Run the generator application
	else {
		$app = new generatorApp();
		$app->run();
	}

} catch(flException $e) {
	$output = new flCliOutput();
	$output
		->newline()
		->writePadded('', 70, flCliOutput::WHITE, flCliOutput::RED)
		->newline()
		->writePadded(' ' . $e->getMessage() . ' ', 70, flCliOutput::WHITE, flCliOutput::RED)
		->newline()
		->writePadded('', 70, flCliOutput::WHITE, flCliOutput::RED)
/*		->newline()
		->writePadded(' ' . $e->getFile(), 70, flCliOutput::WHITE, flCliOutput::RED)
		->newline()
		->writePadded(' On line ' . $e->getLine(), 70, flCliOutput::WHITE, flCliOutput::RED)
		->newline()
		->writePadded('', 70, flCliOutput::WHITE, flCliOutput::RED)*/
		->newline()
		->newline();
}
