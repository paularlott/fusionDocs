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
 * flYamlException Class
 *
 * Exception object for YAML.
 *
 * @package fusionLib
 */
class flYamlException extends flException {
	/**
	 * Construct a new exception.
	 *
	 * @param int $line The line number.
	 * @param int $code Optional code to pass along e.g. 403, 404 or 500.
	 */
	function __construct($line, $code = 500) {
		parent::__construct(sprintf('Syntax error in line %d', $line), $code);
	}
}
