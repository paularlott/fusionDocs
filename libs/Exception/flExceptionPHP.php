<?php

/**
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT ANY EXPRESS OR IMPLIED
 * WARRANTIES, INCLUDING, WITHOUT LIMITATION, THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @package fusionLib
 * @copyright Copyright (c) 2008 - 2013 fusionLib. All rights reserved.
 * @link http://fusionlib.com
 */

/**
 * flExceptionPHP Class
 *
 * Exception class for PHP error.
 *
 * @package fusionLib
 */
class flExceptionPHP extends flException {
	/**
	 * The name of the exception.
	 *
	 * @var string
	 */
	private $name;

	/**
	 * The backtrace information.
	 *
	 * @var array
	 */
	private $backtrace;

	/**
	 * Construct the exception object.
	 *
	 * @param int $level The level of the error raised.
	 * @param string $msg The error message.
	 * @param string $file The name of the file where the error occured.
	 * @param int $line The line number the error occurred on.
	 */
	function __construct($level, $msg, $file, $line) {
		$levelTxt = array(
			E_ERROR => 'Error',
			E_WARNING => 'Warning',
			E_PARSE => 'Parsing Error',
			E_NOTICE => 'Notice',
			E_CORE_ERROR => 'Core Error',
			E_CORE_WARNING => 'Core Warning',
			E_COMPILE_ERROR => 'Compile Error',
			E_COMPILE_WARNING => 'Compile Warning',
			E_USER_ERROR => 'User Error',
			E_USER_WARNING => 'User Warning',
			E_USER_NOTICE => 'User Notice',
			E_STRICT => 'Run-time Notice',
			E_RECOVERABLE_ERROR => 'Catchable fatal error.');

		if(defined('E_DEPRECATED'))
			$levelTxt[E_DEPRECATED] = 'Deprecated';

		parent::__construct($msg);
		$this->file = $file;
		$this->line = $line;
		$this->name = isset($levelTxt[$level]) ? $levelTxt[$level] : "Unknown Error: $level";
		$this->backtrace = array_slice(debug_backtrace(), 2);
	}

	/**
	 * Get the exception name.
	 *
	 * @return string The human readable exception name.
	 */
	function getName() {
		return $this->name;
	}

	/**
	 * Get the stack trace.
	 *
	 * @return array Array holding the stack trace.
	 */
	function getStackTrace() {
		return $this->backtrace;
	}
}
