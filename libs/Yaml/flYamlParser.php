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
 * flYamlParser Class
 *
 * Simple YAML parser supporting a limited subset of YAML.
 *
 * @package fusionLib
 */
class flYamlParser {

	/**
	 * The current line number.
	 *
	 * @var int
	 */
	protected $lineNumber;

	/**
	 * The lines to parse.
	 *
	 * @var string[]
	 */
	protected $lines;

	/**
	 * Parse a sting containing YAML into an array.
	 *
	 * @param string $string The YAML string to parse.
	 * @return array The array of data.
	 */
	function parse($string) {
		$this->lineNumber = 0;
		$this->lines = preg_split("/\n/", $string, -1);
		return $this->parseLine(0);
	}

	/**
	 * Parse lines from the input.
	 *
	 * @param $currentDepth
	 * @return array The array of data.
	 * @throws flYamlException
	 */
	protected function parseLine($currentDepth) {
		$data = [];

		while(($line = current($this->lines)) !== false) {
			$r = [];
			$line = rtrim($line);

			// If mappings
			if(preg_match('/^(\s*)?(\S+):\s*(.*)/', $line, $r)) {
				$depth = ceil(strlen($r[1]) / 2);
				$key = $r[2];
				$val = trim($r[3]);
			}
			// If sequences
			else if(preg_match('/^(\s*)?-\s*(.*)/', $line, $r)) {
				$depth = ceil(strlen($r[1]) / 2);
				$key = null;
				$val = trim($r[2]);
			}
			// Blank line or error
			else {

				// Try removing comments
				$line = trim(self::removeComment($line));

				// If line now empty just skip
				if(empty($line)) {
					next($this->lines);
					$this->lineNumber++;
					continue;
				}
				else
					throw new flYamlException($this->lineNumber + 1);
			}

			// Strip comments
			$val = self::removeComment($val);

			// If depth changed to deeper
			if($depth > $currentDepth) {
				if($depth > $currentDepth + 1)
					throw new flYamlException($this->lineNumber + 1);

				end($data);
				$k = key($data);
				$data[$k] = $this->parseLine($depth);
			}
			// If depth change to higher
			else if($depth < $currentDepth) {
				prev($this->lines);
				$this->lineNumber--;
				return $data;
			}
			// Staying at same depth
			else {
				// Sort out values
				if(empty($val))
					$val = [];
				elseif($val == '~' || $val == 'null')
					$val = null;
				elseif($val == 'true')
					$val = true;
				elseif($val == 'false')
					$val = false;
				// Octal
				elseif(preg_match('/^[\+-]?0[0-7]+$/', $val))
					$val = base_convert($val, 8, 10);
				// Hex
				elseif(preg_match('/^0x[0-9a-z]+$/i', $val))
					$val = base_convert($val, 16, 10);
				elseif(filter_var($val, FILTER_VALIDATE_INT) !== false)
					$val = (int)$val;
				elseif(filter_var($val, FILTER_VALIDATE_FLOAT) !== false)
					$val = (float)$val;
				elseif($val[0] == '"' && substr($val, -1, 1) == '"') {
					// Extract the string and resolve slashes
					$val = preg_replace_callback(
						'/\\\\([nrtvf\\\\$"]|[0-7]{1,3}|x[0-9A-Fa-f]{1,2})/',
						function($matches) {
							return stripcslashes($matches[0]);
						},
						substr($val, 1, -1)
					);
				}
				elseif($val[0] == "'" && substr($val, -1, 1) == "'")
					$val = str_replace("''", "'", substr($val, 1, -1));
				// Folded style string
				elseif($val[0] == '>')
					$val = $this->fetchAllAtLevel($currentDepth + 1, ' ');
				// Literal style string
				elseif($val[0] == '|')
					$val = $this->fetchAllAtLevel($currentDepth + 1, "\n");

				// Store the key / value
				if($key)
					$data[$key] = $val;
				else
					$data[] = $val;
			}

			// Next line
			next($this->lines);
			$this->lineNumber++;
		}

		return $data;
	}

	/**
	 * Fetch all the lines at the current level.
	 *
	 * @param int $fetchDepth The depth to fetch all at.
	 * @param string $joinChar The character to join lines with.
	 * @return string The combined string.
	 * @throws flYamlException
	 */
	protected function fetchAllAtLevel($fetchDepth, $joinChar) {
		$s = '';
		next($this->lines);
		$this->lineNumber++;

		while(($line = current($this->lines)) !== false) {

			// Get the next line
			$r = [];
			if(!preg_match('/^(\s+)?(.*)/', $line, $r))
				throw new flYamlException($this->lineNumber + 1);

			// If levels match
			$depth = ceil(strlen($r[1]) / 2);
			if($depth == $fetchDepth)
				$s .= $joinChar . trim($r[2]);
			// Level moved
			else
				break;

			// Next line
			next($this->lines);
			$this->lineNumber++;
		}

		prev($this->lines);
		$this->lineNumber--;

		return $s;
	}

	/**
	 * Remove comments from a string.
	 *
	 * @param string $string The string to remove the comments from.
	 * @return string The string without the comments.
	 */
	protected function removeComment($string) {
		// Strip comments
		if($string) {
			if($string == '"' || $string == "'") {

				// If comment possibly present
				if(strpos($string, '#') !== false) {

					// Look through the string for the end
					$inStr = true;
					for($i=1;$i<strlen($string);$i++) {
						if($string[$i] == '#' && !$inStr) {
							$string = trim(substr($string, 0, $i));
							break;
						}
						else if($string[$i] == $string[0] && ($string[0] != "'" || @$string[$i+1] != "'"))
							$inStr = false;
					}
				}
			}
			else
				$string = preg_replace('/\s*#.*/', '', $string);
		}

		return $string;
	}
}
