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
 * flYamlFrontMatter Class
 *
 * Strip front matter from a document string and return the parsed data.
 *
 * @package fusionLib
 */
class flYamlFrontMatterResult {

	/**
	 * The document.
	 *
	 * @var string
	 */
	public $document;

	/**
	 * The parsed front matter.
	 *
	 * @var array
	 */
	public $frontMatter;
}

/**
 * flYamlFrontMatter Class
 *
 * Strip front matter from a document string and return the parsed data.
 *
 * @package fusionLib
 */
abstract class flYamlFrontMatter {

	/**
	 * Parse the front matter data on a document.
	 *
	 * @param string $document The document data to parse.
	 * @return flYamlFrontMatterResult The results.
	 */
	static function parse($document) {
		$result = new flYamlFrontMatterResult();
		$r = [];

		// Break out the YAML
		if(preg_match('/^\s*---(.*)---(.*)$/Uus', $document, $r)) {
			$result->document = $r[2];

			// Parse the YAML
			$yaml = new flYamlParser();
			$result->frontMatter = $yaml->parse(trim($r[1]));
		}
		else {
			$result->document = $document;
			$result->frontMatter = [];
		}

		return $result;
	}
}
