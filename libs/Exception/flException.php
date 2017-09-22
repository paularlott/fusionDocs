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
 * flException Class
 *
 * Base class for exceptions.
 *
 * @package fusionLib
 */
class flException extends Exception {

	/**
	 * Construct a new exception.
	 *
	 * @param string $msg The message for the exception.
	 * @param int $code Optional code to pass along e.g. 403, 404 or 500.
	 */
	function __construct($msg, $code = 500) {
		parent::__construct($msg, $code);
	}

	/**
	 * Get the exception name.
	 *
	 * @return string The human readable exception name.
	 */
	function getName() {
		return trim(substr(get_class($this), 0, -9) . ' Exception');
	}

	/**
	 * Get the stack trace, adjusted version of getTrace().
	 *
	 * @return array Array holding the stack trace.
	 */
	function getStackTrace() {
		return $this->getTrace();
	}
}
