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
 * flCliApp Class
 *
 * The base class from which all command line applications should be derived.
 *
 * @package fusionLib
 */
 abstract class flCliApp {

	/**
	 * The arguments read from the command line.
	 *
	 * @var array
	 */
	protected $args = [];

	/**
	 * The list of options.
	 *
	 * @var array
	 */
	protected $options = [];

	 /**
	  * flCliApp constructor.
	  */
	function __construct() {
		// Get options and arguments
		$args = isset($_SERVER['argv']) ? $_SERVER['argv'] : [];
		foreach($args as $idx => $a) {
			if($idx) {
				$d = [];
				if(preg_match('/^--([^=]+)(?:=(.+))?$/', $a, $d)) {
					$this->options[$d[1]] = isset($d[2]) ? $d[2] : true;
				} else
					$this->args[] = $a;
			}
		}
	}

	 /**
	  * Return the array of arguments.
	  *
	  * @return array The raw list of arguments.
	  */
	 function getArgs() {
		 return $this->args;
	 }

	 /**
	  * Return the array of options.
	  *
	  * @return array The raw list of options.
	  */
	 function getOptions() {
		 return $this->options;
	 }

	 /**
	  * Get the value for a named option.
	  *
	  * @param string $name The name of the option to get.
	  * @param mixed $default The value to return if the option isn't set.
	  * @return mixed The value of the option.
	  */
	 function getOption($name, $default = null) {
		 return isset($this->options[$name]) ? $this->options[$name] : $default;
	 }

	 /**
	  * Run the application.
	  */
	 abstract function run();
}