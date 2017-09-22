<?php

/**
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT ANY EXPRESS OR IMPLIED
 * WARRANTIES, INCLUDING, WITHOUT LIMITATION, THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @package fusionDocs
 * @copyright Copyright (c) 2017 clearFusionCMS. All rights reserved.
 * @link http://fusionlib.com
 */

/**
 * serverApp Class
 *
 * Preview server application class.
 */
class serverApp extends flCliApp {

	/**
	 * @inheritdoc
	 */
	function run() {

		// Get the segments of the request
		$uri = preg_replace('/\?.*$/', '', $_SERVER['REQUEST_URI']);
		$segs = preg_split('#/#', $uri, -1, PREG_SPLIT_NO_EMPTY);

		// Get the source & test readable
		$source = $this->getOption('source', './docs/');
		if(!is_readable($source)) {
			throw new flCliException('The documentation source folder is not readable');
		}

		// Create the compiler and set the source
		$compiler = new compiler($this);
		$compiler->setSource($source);

		// Compile the page
		$page = $compiler->generatePage($segs);

		$source = realpath($source) . '/';

		// 404 not found
		if(!$page) {
			// Test if the file is present in the source
			if(is_readable($file = $source . implode('/', $segs))) {
				header('Content-Type: ' . flMimeTypes::getType($file));
				header('Content-Length: ' . filesize($file));
				readfile($file);
			}
			// Test if themes
			else if(isset($segs[0]) && $segs[0] == 'themes' && is_readable($file = FLROOTPATH . '/' . implode('/', $segs))) {
				header('Content-Type: ' . flMimeTypes::getType($file));
				header('Content-Length: ' . filesize($file));
				readfile($file);
			}
			// Else 404
			else {
				$this->pageNotFound();
			}
		}
		// If got a page
		elseif($page['content']) {
			echo $page['content'];
		}
		// Else looking for an asset
		else {
			$file = $source . implode('/', $page['realPath']) . '/' . $page['page'];

			// If file found
			if(is_readable($file)) {
				header('Content-Type: ' . flMimeTypes::getType($page['page']));
				header('Content-Length: ' . filesize($file));
				readfile($file);
			}
			// If after search data
			else if($page['page'] == 'tipuesearch_content.js') {
				$idx = $compiler->getSearchIndex($segs);
				header('Content-Type: ' . flMimeTypes::getType('js'));
				header('Content-Length: ' . strlen($idx));
				echo $idx;
			}
			// Else 404
			else {
				$this->pageNotFound();
			}
		}
	}

	/**
	 * Output 404 error.
	 */
	function pageNotFound() {
		header('HTTP/1.0 404 not found');
		echo "Page not found";
		exit;
	}

}
