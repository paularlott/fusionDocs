<?php

/**
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT ANY EXPRESS OR IMPLIED
 * WARRANTIES, INCLUDING, WITHOUT LIMITATION, THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @package fusionLib
 * @copyright Copyright (c) 2017 fusionLib. All rights reserved.
 * @link http://fusionlib.com
 */

/**
 * flCliCommand Class
 *
 * Helper class to define a command line command.
 *
 * @package fusionLib
 */
class flCliCommand {

	/**#@+
	 * Options for parameters.
	 */
	const OPTIONAL = 0;
	const REQUIRED = 1;
	/**#@-*/

	/**
	 * The output object.
	 *
	 * @var flCliOutput
	 */
	protected $output;

	/**
	 * The name of the application.
	 *
	 * @var string
	 */
	protected $name = '';

	/**
	 * The version string.
	 *
	 * @var string
	 */
	protected $version = '';

	/**
	 * The description.
	 *
	 * @var string
	 */
	protected $description = '';

	/**
	 * The copyright string.
	 *
	 * @var string
	 */
	protected $copyright = '';

	/**
	 * The options as key value pairs.
	 *
	 * @var array
	 */
	protected $options;

	/**
	 * The arguments.
	 *
	 * @var array
	 */
	protected $args;

	/**
	 * Option definitions.
	 *
	 * @var array
	 */
	protected $optionDef = [];

	/**
	 * Argument definitions.
	 *
	 * @var array
	 */
	protected $argDef = [];

	/**
	 * Counter for tracking argument defs.
	 *
	 * @var int
	 */
	protected $argDefCounter = 0;

	/**
	 * flCliCommand constructor.
	 *
	 * @param flCliOutput $output The output object.
	 */
	function __construct($output, $options = [], $args = []) {
		$this->output = $output;
		$this->options = $options;
		$this->args = $args;
	}

	/**
	 * Set the name of the application.
	 *
	 * @param string $name Then application name.
	 * @return flCliCommand This object.
	 */
	function setName($name) {
		$this->name = $name;
		return $this;
	}

	/**
	 * Set the version string.
	 *
	 * @param string $version The version.
	 * @return flCliCommand This object.
	 */
	function setVersion($version) {
		$this->version = $version;
		return $this;
	}

	/**
	 * Set the description of the command.
	 *
	 * @param string $description The description.
	 * @return flCliCommand This object.
	 */
	function setDescription($description) {
		$this->description = $description;
		return $this;
	}

	/**
	 * Set the copyright string.
	 *
	 * @param string $copyright The copy right string.
	 * @return flCliCommand This object.
	 */
	function setCopyright($copyright) {
		$this->copyright = $copyright;
		return $this;
	}

	/**
	 * Get the value for a named option.
	 *
	 * @param string $name The name of the option to get.
	 * @param mixed $default The value to return if the option isn't set.
	 * @return mixed The value of the option.
	 */
	function getOption($name, $default = null) {
		return isset($this->options[$name])
			? $this->options[$name]
			: (isset($this->optionDef[$name])
				? $this->optionDef[$name]['default']
				: $default);
	}

	/**
	 * Get the value of an argument.
	 *
	 * @param string $name The name of the argument to get.
	 * @param mixed $default The value to return if the argument is unknown.
	 * @return mixed The value of the argument.
	 */
	function getArg($name, $default = null) {
		return isset($this->argDef[$name])
			? (isset($this->args[$this->argDef[$name]['idx']])
				? $this->args[$this->argDef[$name]['idx']]
				: $this->argDef[$name]['default'])
			: $default;
	}

	/**
	 * Add the definition for an option.
	 *
	 * @param string $name The option name.
	 * @param int $opt The options
	 * @param mixed $default The default value.
	 * @param string $description The description for the option.
	 * @return flCliCommand This object.
	 */
	function addOption($name, $opt = null, $default = null, $description = null) {
		$this->optionDef[$name] = [
			'opt' => $opt,
			'default' => $default,
			'description' => $description
		];
		return $this;
	}

	/**
	 * Add the definition for an argument.
	 *
	 * @param string $name The argument name.
	 * @param int $opt The options
	 * @param mixed $default The default value.
	 * @param string $description The description for the option.
	 * @return flCliCommand This object.
	 */
	function addArgument($name, $opt = null, $default = null, $description = null) {
		$this->argDef[$name] = [
			'opt'         => $opt,
			'default'     => $default,
			'description' => $description,
			'idx'         => $this->argDefCounter++
		];
		return $this;
	}

	/**
	 * Validate the passed in options and arguments against the command definition.
	 * @throws flCliException
	 */
	function validateParameters() {

		// Test options
		foreach($this->optionDef as $name => $opts) {
			// If argument required
			if($opts['opt'] & self::REQUIRED) {
				if(!isset($this->options[$name])) {
					throw new flCliException(sprintf(__('Missing option: --%s', 'fusionLib'), $name));
				}
			}
		}

		// Test if undefined options passed
		foreach($this->options as $name => $val) {

			// If option undefined
			if(!isset($this->optionDef[$name])) {
				throw new flCliException(sprintf(__('The option \'--%s\' does not exist', 'fusionLib'), $name));
			}
		}

		// Test arguments
		foreach($this->argDef as $name => $opts) {
			// If argument required
			if($opts['opt'] & self::REQUIRED) {
				if(!isset($this->args[$opts['idx']])) {
					throw new flCliException(sprintf(__('Missing argument: %s', 'fusionLib'), $name));
				}
			}
		}
	}

	/**
	 * Output the version to the console.
	 *
	 * @return flCliCommand This object.
	 */
	function showVersion() {
		$this->output
			->writeLn($this->name . ' ' . $this->version)
			->writeLn($this->copyright)
			->write("\n");
		return $this;
	}

	/**
	 * Output the help to the console.
	 *
	 * @return flCliCommand This object.
	 */
	function showHelp() {

		$this->output
			->newline()
			->write($this->name . ' ')
			->writeLn($this->version, flCliOutput::GREEN)
			->newline()
			->writeLn($this->description)
			->newline();

		$this->output
			->writeLn('Usage:', flCliOutput::YELLOW)
			->writeLn('  command' . (count($this->optionDef) ? ' [options]' : '') . (count($this->argDef) ? ' [arguments]' : ''))
			->newline();

		if(count($this->optionDef)) {
			$this->output->writeLn('Options:', flCliOutput::YELLOW);

			foreach($this->optionDef as $name => $opts) {
				$this->output
					->writePadded('  --' . $name, 25, flCliOutput::GREEN)
					->write($opts['description'])
					->writeLn($opts['default'] === null ? '' : ' [default: ' . $opts['default'] . ']', flCliOutput::YELLOW);
			}
			$this->output->newline();
		}

		if(count($this->argDef)) {
			$this->output->writeLn('Arguments:', flCliOutput::YELLOW);

			foreach($this->argDef as $name => $opts) {
				$this->output
					->writePadded('  ' . $name, 25, flCliOutput::GREEN)
					->write($opts['description'])
					->writeLn($opts['default'] === null ? '' : ' [default: ' . $opts['default'] . ']', flCliOutput::YELLOW);
			}
			$this->output->newline();
		}

		return $this;
	}
}
