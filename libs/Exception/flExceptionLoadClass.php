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
 * flExceptionLoadClass Class
 *
 * Class load exceptions.
 *
 * @package fusionLib
 */
class flExceptionLoadClass extends flException {

	/**
	 * Construct a new exception.
	 *
	 * @param string $className The name of the class that could not be loaded.
	 */
	function __construct($className) {
		parent::__construct("Class file not present or class not present within the class file for the class '$className'.");
	}

	/**
	 * Get the exception name.
	 *
	 * @return string The human readable exception name.
	 */
	function getName() {
		return 'fusionLib::loadClass Exception';
	}
}
