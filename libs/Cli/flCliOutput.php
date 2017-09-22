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
 * flCliOutput Class
 *
 * Helper class to output to the console.
 *
 * @package fusionLib
 */
class flCliOutput {

	/**#@+
	 * Helper names for the available colours.
	 */
	const BLACK = 'black';
	const DARK_GRAY = 'dark_gray';
	const BLUE = 'blue';
	const LIGHT_BLUE = 'light_blue';
	const MAGENTA = 'magenta';
	const GREEN = 'green';
	const LIGHT_GREEN = 'light_green';
	const CYAN = 'cyan';
	const LIGHT_CYAN = 'light_cyan';
	const RED = 'red';
	const LIGHT_RED = 'light_red';
	const PURPLE = 'purple';
	const LIGHT_PURPLE = 'light_purple';
	const BROWN = 'brown';
	const YELLOW = 'yellow';
	const LIGHT_GRAY = 'light_gray';
	const WHITE = 'white';
	/**#@-*/

	/**
	 * The available foreground colors.
	 * @var array
	 */
	static protected $fgColors = [
		'black'        => '0;30',
		'dark_gray'    => '1;30',
		'blue'         => '0;34',
		'light_blue'   => '1;34',
		'magenta'      => "1;35",
		'green'        => '0;32',
		'light_green'  => '1;32',
		'cyan'         => '0;36',
		'light_cyan'   => '1;36',
		'red'          => '0;31',
		'light_red'    => '1;31',
		'purple'       => '0;35',
		'light_purple' => '1;35',
		'brown'        => '0;33',
		'yellow'       => '1;33',
		'light_gray'   => '0;37',
		'white'        => '1;37'
	];

	/**
	 * The available background colours.
	 * @var array
	 */
	static protected $bgColors = [
		'black'      => '40',
		'red'        => '41',
		'green'      => '42',
		'yellow'     => '43',
		'blue'       => '44',
		'magenta'    => '45',
		'cyan'       => '46',
		'light_gray' => '47'
	];

	/**
	 * Write the string to the output.
	 *
	 * @param string $string The string to output.
	 * @param string $fgColor The optional foreground colour.
	 * @param string $bgColor The optional background colour.
	 * @return flCliOutput This object.
	 */
	function write($string, $fgColor = null, $bgColor = null) {

		// Check if given foreground color found
		if(isset(self::$fgColors[$fgColor]))
			echo "\033[" . self::$fgColors[$fgColor] . "m";

		// Check if given background color found
		if(isset(self::$bgColors[$bgColor]))
			echo "\033[" . self::$bgColors[$bgColor] . "m";

		// Output the text
		echo $string;

		// Restore the colour to default
		echo "\033[0m";

		return $this;
	}

	/**
	 * Write the string to the output right padding it to the given number of characters.
	 *
	 * @param string $string The string to output.
	 * @param int $pad Pad the string to this length.
	 * @param string $fgColor The optional foreground colour.
	 * @param string $bgColor The optional background colour.
	 * @return flCliOutput This object.
	 */
	function writePadded($string, $pad, $fgColor = null, $bgColor = null) {
		return $this->write($string . str_repeat(' ', max(0, $pad - strlen($string))), $fgColor, $bgColor);
	}

	/**
	 * Write the string to the output and append a newline.
	 *
	 * @param string $string The string to output.
	 * @param string $fgColor The optional foreground colour.
	 * @param string $bgColor The optional background colour.
	 * @return flCliOutput This object.
	 */
	function writeLn($string, $fgColor = null, $bgColor = null) {
		return $this->write($string . "\n", $fgColor, $bgColor);
	}

	/**
	 * Write a newline to the output.
	 *
	 * @return flCliOutput This object.
	 */
	function newline() {
		echo "\n";
		return $this;
	}
}
