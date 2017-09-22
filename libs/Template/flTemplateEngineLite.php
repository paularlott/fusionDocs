<?php

/**
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT ANY EXPRESS OR IMPLIED
 * WARRANTIES, INCLUDING, WITHOUT LIMITATION, THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @package fusionLib
 * @copyright Copyright (c) 2007 - 2017 fusionLib. All rights reserved.
 * @link http://fusionlib.com
 */

/**
 * flTemplateEngineLite Class
 *
 * Class to implement an extensible template engine which supports
 * snippets (code blocks), chunks (html blocks) and placeholders (variables).
 *
 * Syntax:
 * [[snippet]]
 * [[$chunk]]
 * [[+placeholder]]
 *
 * All but placeholders can take parameters e.g. [[snippet &var=`1` &var=`2`]]
 * in the case of a chunk placeholders of the same name as the parameters are
 * replaced with the passed variables.
 *
 * @package fusionLib
 */
class flTemplateEngineLite {
	/**
	 * Array holding placeholder values.
	 *
	 * @var array
	 */
	protected $placeholders = array();

	/**
	 * Cache of chunks and those registered through registerChunk.
	 *
	 * @var array
	 */
	protected $chunkCache = array();

	/**
	 * Cache of snippets and those registered through registerSnippet.
	 *
	 * @var array
	 */
	protected $snippetCache = array();

	/**
	 * Supported tag characters.
	 *
	 * @var string
	 */
	protected $tagChars = '+$*';

	/**
	 * Array of paths to load chunks from.
	 *
	 * @var string[]
	 */
	protected $chunkPaths = [];

	/**
	 * Array of paths to load snippets from.
	 *
	 * @var string[]
	 */
	protected $snippetPaths = [];

	/**
	 * Add a path to search for chunks.
	 *
	 * @param string $path The path to add.
	 * @return flTemplateEngineLite This object.
	 */
	function addChunkPath($path) {
		array_unshift($this->chunkPaths, realpath($path) . '/');
		return $this;
	}

	/**
	 * Add a path to search for snippets.
	 *
	 * @param string $path The path to add.
	 * @return flTemplateEngineLite This object.
	 */
	function addSnippetPath($path) {
		array_unshift($this->snippetPaths, realpath($path) . '/');
		return $this;
	}

	/**
	 * Set the value of a placeholder.
	 *
	 * @param mixed $n The name of the placeholder or an associative array or an object.
	 * @param mixed $v The value to set for the placeholder only used if $n is a string.
	 */
	function setPlaceholder($n, $v = null) {
		if(is_array($n))
			$this->placeholders = array_merge($this->placeholders, $n);
		else if(is_object($n))
			$this->placeholders = array_merge($this->placeholders, get_object_vars($n));
		else
			$this->placeholders[$n] = $v;
	}

	/**
	 * Unset an existing placeholder.
	 *
	 * @param string $name The name of the placeholder to unset.
	 */
	function unsetPlaceholder($name) {
		unset($this->placeholders[$name]);
	}

	/**
	 * Get the value of the placeholder.
	 *
	 * @param string $name The name of the placeholder to get the value of.
	 * @param string $default The default value to return.
	 * @return string The value of the placeholder.
	 */
	function getPlaceholder($name, $default = '') {
		return isset($this->placeholders[$name]) ? $this->placeholders[$name] : $default;
	}

	/**
	 * Register a chunk with the chunk cache.
	 *
	 * @param string $name The name for the chunk.
	 * @param string $html The HTML for the chunk.
	 */
	function registerChunk($name, $html) {
		$this->chunkCache[$name] = $html;
	}

	/**
	 * Test if a chunk is registered or cached.
	 *
	 * @param string $name The name of the chunk to test for.
	 * @return bool true if the chunk is registered or loaded into the chunk cache.
	 */
	function chunkLoaded($name) {
		return isset($this->chunkCache[$name]);
	}

	/**
	 * Get a chunk and return it unmodified.
	 *
	 * @param string $name The name of the chunk to get.
	 * @return string The HTML that is the chunk or '' if no chunk.
	 */
	function getChunk($name) {
		if(!isset($this->chunkCache[$name])) {
			$content = false;

			// Attempt to load from the available paths
			foreach($this->chunkPaths as $path) {
				$f = $path . $name . '.html';
				if(is_readable($f)) {
					$content = @file_get_contents($f);
					break;
				}
			}

			$this->chunkCache[$name] = $content ? $content : '';
		}

		return $this->chunkCache[$name];
	}

	/**
	 * Get the contents of a named chunk parsing it and replacing tags.
	 *
	 * @param string $name The name of the chunk to get.
	 * @param array $placeholders Name value pair array of placeholders.
	 * @return string The HTML that is the chunk or '' if no chunk.
	 */
	function parseChunk($name, $placeholders = array()) {
		return $this->parseString($this->getChunk($name), $placeholders);
	}

	/**
	 * Register a snippet with the snippet cache.
	 *
	 * @param string $name The name of the snippet.
	 * @param callback $fn The callable function.
	 * The 1st parameter is the template engine flTemplateEngine and the 2nd is the array
	 * of parameters.
	 * @throws flTemplateEngineException
	 */
	function registerSnippet($name, $fn) {
		if(!is_callable($fn))
			throw new flTemplateEngineException("Function is not callable when registering snippet '$name'.");
		$this->snippetCache[$name] = $fn;
	}

	/**
	 * Run a snippet.
	 *
	 * @param string $name The name of the snippet to run.
	 * @param array $params Array of name value pairs to pass to the snippet.
	 * @return string The results of executing the snippet.
	 */
	function runSnippet($name, $params = array()) {
		// If not already loaded load snippet
		if(!isset($this->snippetCache[$name])) {
			$code = false;

			// Attempt to load from the available paths
			foreach($this->chunkPaths as $path) {
				$f = $path . $name . '.php';
				if(is_readable($f)) {
					if($code = @file_get_contents($f))
						$code = '?>' . $code;
					break;
				}
			}

			if(!$code) $code = '';
			$this->snippetCache[$name] = eval('return function($tplEngine, $_params) { extract($_params); ' . $code . ' };');
		}

		ob_start();
		call_user_func($this->snippetCache[$name], $this, $params);
		return ob_get_clean();
	}

	/**
	 * Private function to parse a tag and execute it if not cached,
	 * parsed data is sent to the output.
	 *
	 * @param int $pos The position in the data.
	 * @param string $source The input data.
	 * @param int $length The length of the data.
	 * @param bool $dirty Flags if data updated.
	 */
	private function _parseTag(& $pos, $source, $length, & $dirty) {

		$curPos = $pos;
		$params = array();
		$isCached = $isOpen = true;
		$type = $var = $val = '';

		// Test if cached
		if(@$source[$pos] == '!') {
			$pos++;
			$isCached = false;
		}

		// Tag type
		if(strpos($this->tagChars, @$source[$pos]) !== false)
			$type = $source[$pos++];

		// Get the name
		$tag = '';
		$r = [];
		if(preg_match('/([a-z0-9_\.\/]+)[\] &]/i', $source, $r, 0, $pos)) {
			$tag = $r[1];
			$pos += strlen($tag);
			$inValue = false;
			while($pos < $length) {
				if($inValue && $source[$pos] == '[' && @$source[$pos+1] == '[') {
					$pos+=2;
					ob_start();
					$this->_parseTag($pos, $source, $length, $dirty);
					$val .= ob_get_clean();
				}
				else if($source[$pos] == ']' && @$source[$pos+1] == ']') {
					$pos+=2;
					$isOpen = false;
					break;
				}
				else if(!$inValue && $source[$pos] == '&') {
					$pos++;

					// Skip over &nbsp; the same as spaces
					if($source[$pos] == 'n' && substr($source, $pos, 5) == 'nbsp;')
						$pos += 5;
					else {

						// Ignore a placeholder with parameters
						if($type == '+')
							break;

						// &amp; is same as &
						if($source[$pos] == 'a' && substr($source, $pos, 4) == 'amp;')
							$pos += 4;

						$var = $val = '';

						// get the parameter name
						if(!preg_match('/([a-z0-9_]+)=`/i', $source, $r, 0, $pos))
							break;
						$var = $r[1];
						$pos += strlen($var) + 2;
						$inValue = true;
					}
				}
				else if($inValue) {
					if($source[$pos] == '`') {
						$inValue = false;
						$params[$var] = $val;
					}
					else
						$val .= $source[$pos];
					$pos++;
				}
				else if(ctype_space($source[$pos]))
					$pos++;
				else {
					$pos++;
					break;
				}
			}
		}

		// If get to end without ]] then output as text
		if($isOpen)
			echo '[[' . substr($source, $curPos, $pos - $curPos);
		// If cached then process the tag
		else if($isCached) {
			$dirty = true;
			$this->_processTag($type, $tag, $params);
		}
		// Else convert to cached tag
		else {
			$dirty = true;
			echo "[[{$type}{$tag}";
			foreach($params as $n => $v)
				echo " &{$n}=`$v`";
			echo ']]';
		}
	}

	/**
	 * Internal function to process a tag, override to add tags.
	 *
	 * @param string $type The type character.
	 * @param string $tag The tag string.
	 * @param array $params Array of parameters passed to the tag.
	 */
	protected function _processTag($type, $tag, $params) {
		if($type == '+' || $type == '*') {
			if(isset($this->placeholders[$tag]))
				echo $this->placeholders[$tag];
		}
		else if($type == '$')
			echo $this->_parseLoop($this->getChunk($tag), $params);
		else if($type == '')
			echo $this->runSnippet($tag, $params);
	}

	/**
	 * Internal function to run a parse loop.
	 *
	 * @param string $string The string to parse.
	 * @param array $params Placeholder parameters.
	 * @return string The parsed string.
	 */
	private function _parseLoop($string, $params) {
		// Save the current placeholders & merge in new placeholders
		$savedPlaceholders = $this->placeholders;
		$this->placeholders = array_merge($this->placeholders, $params);

		$loops = 10;
		do {
			$dirty = false;
			$string = $this->_parseString($string, $dirty);
		} while(--$loops && $dirty);

		// Restore placeholders
		$this->placeholders = $savedPlaceholders;

		return $string;
	}

	/**
	 * Internal function to parse a string replacing tags as required.
	 *
	 * @param string $string The string to parse.
	 * @param bool $dirty Set to true if any tags were expanded.
	 * @return string The parsed string.
	 */
	private function _parseString($string, & $dirty) {
		ob_start();

		$pos = 0;
		$length = strlen($string);

		while($pos < $length) {
			if(($p = strpos($string, '[', $pos)) === false) {
				echo substr($string, $pos);
				break;
			}
			else if(@$string[$p+1] == '[') {
				echo substr($string, $pos, $p - $pos);
				$pos = $p + 2;
				$this->_parseTag($pos, $string, $length, $dirty);
			}
			else {
				$p++;
				echo substr($string, $pos, $p - $pos);
				$pos = $p;
			}
		}

		return ob_get_clean();
	}

	/**
	 * Parse a string replacing tags as required.
	 *
	 * @param string $string The string to parse.
	 * @param array $placeholders Name value pair array of placeholders.
	 * @return string The parsed string.
	 */
	function parseString($string, $placeholders = array()) {
		return $this->_parseLoop($string, $placeholders);
	}
}
