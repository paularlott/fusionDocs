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

		// Load the document set
		$docSet = new documentSet($this, $source);
		$versions = $docSet->getVersions();

		// Get the 1st version
		$compilingVersion = reset($versions);

		// If segments see if the 1st one matches a version
		if(count($segs) && count($versions) > 1) {
			// Look to see if one of our versions
			foreach($versions as $ver) {
				if($ver->label == $segs[0]) {
					$compilingVersion = $ver;
					array_shift($segs);
					break;
				}
			}
		}

		// Get the page by URL
		$page = $compilingVersion->getPageByURL($segs);

		$source = realpath($source) . '/';

		// 404 not found
		if(!$page) {

			// Get the file name from the path
			$segsTmp = $segs;
			$file = array_pop($segsTmp);

			// If search data
			if($file == 'tipuesearch_content.js') {
				$idx = $compilingVersion->root->getSearchIndex(null);
				header('Content-Type: ' . flMimeTypes::getType('js'));
				header('Content-Length: ' . strlen($idx));
				echo $idx;
				return;
			}
			// Convert the path to a real path
			else if($path = $compilingVersion->realPathFromURL($segsTmp)) {
				$filename = $source . $path . $file;
			}
			// If theme file
			else if(isset($segs[0]) && $segs[0] == 'themes') {
				$filename = FLROOTPATH . implode('/', $segs);
			}
			else {
				$filename = $source . implode('/', $segs);
			}

			// Test if the file is present in the source
			if(is_readable($filename)) {
				header('Content-Type: ' . flMimeTypes::getType($filename));
				header('Content-Length: ' . filesize($filename));
				readfile($filename);
			}
			// Else 404
			else {
				$this->pageNotFound();
			}
		}
		else {
			$relPath = str_repeat('../', max(0, count($segs) - 1));

			// Setup template variables
			$compilingVersion->tpl->setPlaceholder(
				[
					'site.mainMenu'  => $page->folder->getNav('', $page),
					'page.versions'  => $docSet->buildVersionList(implode('/', $segs), $compilingVersion),
					'site.toRoot'    => $relPath,
					'site.themeRoot' => $relPath
				]
			);
			echo $page->generateHTML($compilingVersion->tpl, $docSet->getConfigVal('layout', 'default'));
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
