<?php

/**
 * Class file for the core library functions.
 *
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT ANY EXPRESS OR IMPLIED
 * WARRANTIES, INCLUDING, WITHOUT LIMITATION, THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @package fusionLib
 * @copyright Copyright (c) 2007 - 2017 fusionLib. All rights reserved.
 * @link http://fusionlib.com
 */

// Disable magic quotes
if(!fusionLib::isPHP('5.3.0'))
	@set_magic_quotes_runtime(0);

// Select error reporting level and add our own handler
error_reporting(E_ALL | E_STRICT);
set_error_handler('_flErrorHandler', E_ALL);

// Set a default timezone to UTC
date_default_timezone_set('UTC');

// Unregister auto registered globals
if(@ini_get('register_globals')) {
	foreach($_REQUEST as $key => $value)
		unset($$key);
}

/**
 * The root.
 * @package fusionLib
 */
if(!defined('FLROOTPATH'))
	define('FLROOTPATH', realpath(dirname(__FILE__) . '/../..') . DIRECTORY_SEPARATOR);

/**
 * The base path for the system folder.
 * @package fusionLib
 */
define('FLSYSPATH', FLROOTPATH . (defined('FLSYSFOLDER') ? FLSYSFOLDER : 'system/'));

/**
 * The path to the fusionLib library folder.
 * @package fusionLib
 */
define('FLLIBPATH', FLSYSPATH . 'libs/');

/**
 * The path to the app folder.
 * @package fusionLib
 */
define('FLAPPPATH', FLSYSPATH . 'app/');

/**
 * The path to the modules folder.
 * @package fusionLib
 */
define('FLMODPATH', FLSYSPATH . 'modules/');

/**
 * The path to the themes folder.
 * @package fusionLib
 */
define('FLTHEMEPATH', FLSYSPATH . 'themes/');

/**
 * Internal function to handle errors
 *
 * @param int $level The level of the error raised.
 * @param string $errMsg The error message.
 * @param string $errFile The name of the file where the error occured.
 * @param int $errLine The line number the error occured on.
 * @throws flExceptionPHP
 */
function _flErrorHandler($level, $errMsg, $errFile, $errLine) {
	// If should show the error
	if($level & error_reporting())
		throw new flExceptionPHP($level, $errMsg, $errFile, $errLine);
}

/**
 * fusionLib Class
 *
 * The core class for accessing the library functions.
 *
 * @package fusionLib
 */
abstract class fusionLib {

	/**
	 * Cache of found application class prefixes.
	 *
	 * @var array
	 */
	protected static $appPrefix = array();

	/**
	 * Cache of found module class prefixes.
	 *
	 * @var array
	 */
	protected static $modulePrefix = array();

	/**
	 * Track loaded bound domains.
	 *
	 * @var array
	 */
	protected static $boundDomains = array();

	/**
	 * The last domain.
	 *
	 * @var string
	 */
	protected static $lastDomain = '';

	/**
	 * The current locale in use.
	 *
	 * @var string
	 */
	protected static $locale = 'en_US';

	/**
	 * The library version number.
	 *
	 * @return string The fusionLib version number.
	 */
	static function getVersion() {
		return '5.0.0';
	}

	/**
	 * Private constructor to stop instance being created.
	 */
	private function __construct() { }

	/**
	 * Load the named class.
	 *
	 * The class name is used to select the path to load the class from.
	 *
	 * @param string $className The name of the class to load, may only
	 * contain a-z, A-Z, 0-9 and underscore.
	 * @throws flExceptionLoadClass
	 */
	static function loadClass($className) {
		if(strpos($className, '\\') === false && !class_exists($className, false) && !interface_exists($className, false)) {
			$r = array();
			if(preg_match('/^([a-z0-9]+)([A-Z]+[a-z0-9_]*)?/', $className, $r)) {
				// fusionLib
				if($r[1] == 'fl' && isset($r[2]))
					$file = FLLIBPATH . $r[2] . '/' . $className . '.php';
				// known application prefix
				else if(isset(self::$appPrefix[$r[1]]))
					$file = FLAPPPATH . $className . '.php';
				// module known or new
				else if(isset(self::$modulePrefix[$r[1]]) || is_dir(FLMODPATH . $r[1])) {
					$file = FLMODPATH . $r[1] . '/' . $className . '.php';
					self::$modulePrefix[$r[1]] = 1; // Cache the prefix
				}
				// assume application class
				else {
					$file = FLAPPPATH . $className . '.php';
					self::$appPrefix[$r[1]] = 1; // Cache the prefix
				}
			}
			// Assume app class of some type
			else
				$file = FLAPPPATH . $className . '.php';

			try { include $file; } catch(Exception $e) { }

			// Check class/interface now exists
			if(!class_exists($className, false) && !interface_exists($className, false))
				throw new flExceptionLoadClass($className);
		}
	}

	/**
	 * Encode HTML special characters within a string to UTF-8.
	 *
	 * @param string $string The source string to encode.
	 * @return string The encoded string.
	 */
	static function htmlChars($string) {
		return htmlentities($string, ENT_COMPAT, 'UTF-8');
	}

	/**
	 * Test if the current version of PHP is at least the given version.
	 *
	 * @param string $version The minimum required version of PHP.
	 * @return bool true if the current version is newer than the give.
	 */
	static function isPHP($version) {
		return version_compare(PHP_VERSION, $version) < 0 ? false : true;
	}

	/**
	 * Set the system locale.
	 *
	 * @param string $locale The locale e.g. en_US
	 */
	static function setLocale($locale) {
		self::$locale = $locale;
		if(function_exists('dgettext')) {
			setlocale(LC_ALL, (self::$locale = $locale) . '.UTF-8');
			putenv('LANGUAGE=' . $locale);
		}
	}

	/**
	 * Get the current system locale.
	 *
	 * @param bool $html5 true if HTML5 formatted string should be returned e.g. en-US; else en_US
	 * @return string The current locale e.g. en_US
	 */
	static function getLocale($html5 = false) {
		return $html5 ? str_replace('_', '-', self::$locale) : self::$locale;
	}

	/**
	 * Bind a text domain to a path.
	 *
	 * @param string $domain The domain to bin.
	 * @return string The bound domain, if $domain is null then previously bound domain.
	 */
	static function bindTextDomain($domain) {
		if((self::$lastDomain = $domain === null ? self::$lastDomain : $domain) == '')
			return '';

		// Load the domain if not already loaded
		if(!isset(self::$boundDomains[self::$lastDomain])) {
			self::$boundDomains[self::$lastDomain] = 1;

			// Test if customized file available, if not search
			if(!is_readable(($path = FLSYSPATH . 'lang/') . self::$locale . "/LC_MESSAGES/$domain.mo")) {
				$r = array();
				if(preg_match('/^([a-z0-9]+)([A-Z][a-z0-9_]+)?/', self::$lastDomain, $r)) {
					// fusionLib
					if($r[1] == 'fusion' && $r[2] == 'Lib')
						$path = FLLIBPATH . 'lang/';
					// known application prefix
					else if(isset(self::$appPrefix[$r[1]]))
						$path = FLAPPPATH . 'lang/';
					// module known or new
					else if(isset(self::$modulePrefix[$r[1]]) || is_dir(FLMODPATH . $r[1])) {
						$path = FLMODPATH . $r[1] . '/lang/';
						self::$modulePrefix[$r[1]] = 1; // Cache the prefix
					}
					// assume application class
					else {
						$path = FLAPPPATH . 'lang/';
						self::$appPrefix[$r[1]] = 1; // Cache the prefix
					}
				}
				// Assume app class of some type
				else
					$path = FLAPPPATH . 'lang/';
			}

			// Bind the text domain
			bindtextdomain(self::$lastDomain, $path);
			bind_textdomain_codeset(self::$lastDomain, 'UTF-8');
		}

		return self::$lastDomain;
	}
}

if(function_exists('dgettext')) {

	/**
	 * Translate a string.
	 *
	 * @param string $text The text to translate.
	 * @param string $domain The domain.
	 * @return string The translated string.
	 */
	function __($text, $domain = null) {
		return ($domain = fusionLib::bindTextDomain($domain)) == '' ? $text : dgettext($domain, $text);
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
		return ($domain = fusionLib::bindTextDomain($domain)) == '' ? _n_noop($single, $plural, $number) : dngettext($domain, $single, $plural, $number);
	}
}
else
	require_once FLLIBPATH . 'Text/_gettext.php';

/**
 * String translation, used to register strings in POT files but don't translate them.
 *
 * @param string $text The text to translate.
 * @return string The translated string.
 */
function __noop($text) {
	return $text;
}

/**
 * Plural string translation, used to register strings in POT files but don't translate them.
 *
 * @param string $single The singular version to translate.
 * @param string $plural The plural version to translate.
 * @param int $number The value.
 * @return string The translated string.
 */
function _n_noop($single, $plural, $number) {
	return $number == 1 ? $single : $plural;
}

// Register the class autoloader
spl_autoload_register('fusionLib::loadClass');

// Set the default locale
fusionLib::setLocale('en_US');
