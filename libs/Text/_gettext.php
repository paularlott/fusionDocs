<?php

/**
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT ANY EXPRESS OR IMPLIED
 * WARRANTIES, INCLUDING, WITHOUT LIMITATION, THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE.
 *
 * Functions used to manage translations when gettext is not available.
 *
 * @package fusionLib
 * @copyright Copyright (c) 2013 fusionLib. All rights reserved.
 * @link http://fusionlib.com
 */

/**
 * Translate a string.
 *
 * @param string $text The text to translate.
 * @param string $domain The domain.
 * @return string The translated string.
 */
function __($text, $domain = null) {
	return $text;
}

/**
 * Translate a string with a plural form.
 *
 * @param string $single The singular version to translate.
 * @param string $plural The plural version to translate.
 * @param int $number The value.
 * @param string $domain The domain.
 * @return string The translated string.
 */
function _n($single, $plural, $number, $domain = null) {
	return $number == 1 ? $single : $plural;
}

/**
 * Fake function to allow code to run without gettext being installed.
 *
 * @param string $d The domain.
 * @param string $p The path.
 */
function bindtextdomain($d, $p) {}

/**
 * Fake function to allow code to run without gettext being installed.
 *
 * @param string $d The domain.
 * @param string $c The code set.
 */
function bind_textdomain_codeset($d, $c) {}
