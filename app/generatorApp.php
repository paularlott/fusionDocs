<?php

/**
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT ANY EXPRESS OR IMPLIED
 * WARRANTIES, INCLUDING, WITHOUT LIMITATION, THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @package fusionDocs
 * @copyright Copyright (c) 2017 clearFusionCMS. All rights reserved.
 * @link http://fusionlib.com
 */

/**
 * generatorApp Class
 *
 * Main application class for fusionLib.Docs
 */
class generatorApp extends flCliApp {

	/**
	 * The output object.
	 *
	 * @var flCliOutput
	 */
	public $output;

	/**
	 * @inheritdoc
	 */
	function run() {
		$this->output = new flCliOutput();

		// Define the command interface
		$command = new flCliCommand($this->output, $this->options, $this->args);
		$command->setName('fusionLib.docs')
			->setVersion('0.0.1dev1')
			->setCopyright('Copyright (c) clearFusionCMS 2017')
			->setDescription('Generate documentation from a set of Markdown files');

		// Options
		$command
			->addOption(
				'source',
				flCliCommand::OPTIONAL,
				'./docs/',
				'The documentation source'
			)
			->addOption(
				'destination',
				flCliCommand::OPTIONAL,
				'./output/',
				'The output destination'
			)
			->addOption(
				'help',
				flCliCommand::OPTIONAL,
				null,
				'Display this help page'
			)
			->addOption(
				'version',
				flCliCommand::OPTIONAL,
				null,
				'Display the version information'
			)
			->addOption(
				'preview',
				flCliCommand::OPTIONAL,
				null,
				'Run as a local server to preview documentation'
			)
			->addOption(
				'host',
				flCliCommand::OPTIONAL,
				'0.0.0.0',
				'The IP to bind preview to'
			)
			->addOption(
				'port',
				flCliCommand::OPTIONAL,
				'8080',
				'The port to bind preview to'
			);

		// Validate the parameters
		$command->validateParameters();

		// If requesting help
		if($command->getOption('help')) {
			$command->showHelp();
		}
		// If request for version
		elseif($command->getOption('version')) {
			$command->showVersion();
		}
		// If running in preview mode
		elseif($command->getOption('preview')) {
			flush();
			$pipes = [];
			$process = proc_open(
				'php -S '
				. $command->getOption('host') . ':' . $command->getOption('port')
				. ' '
				. $_SERVER['PHP_SELF'] . ' --source=' . $command->getOption('source'),
				[
					0 => ['pipe', 'r'],   // stdin is a pipe that the child will read from
					1 => ['pipe', 'w'],   // stdout is a pipe that the child will write to
					2 => ['pipe', 'w']    // stderr is a pipe that the child will write to
				],
				$pipes
			);
			if (is_resource($process)) {
				$this->output
					->writeLn('Preview server started')
					->writeLn('Listening on http://' . $command->getOption('host') . ':' . $command->getOption('port'))
					->writeLn('Press Ctrl-C to quit.')
					->newline();

				// Watch the error output pipe
				while ($s = fgets($pipes[2])) {
					echo $s;
					flush();
				}
			}
			else {
				$this->output
					->writeLn('Failed to start preview server.', flCliOutput::MAGENTA)
					->newline();
			}
		}
		// Else generating documents
		else {
			// Get the source & test readable
			$source = $command->getOption('source');
			if(!is_readable($source)) {
				throw new flCliException('The documentation source folder is not readable');
			}

			// Create the compiler and set the source
			$compiler = new compiler($this);
			$compiler->setSource($source);

			// Get the output
			$compiler->generateOutput($command->getOption('destination'));
		}

		$this->output->writeLn('Memory Usage: ' . (memory_get_peak_usage(true) / 1024 / 1024) . " MiB\n", flCliOutput::CYAN);
	}
}
