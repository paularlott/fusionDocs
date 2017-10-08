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
 * documentVersion Class
 *
 * Class to hold a version of the documentation.
 */
class documentVersion {
	/**
	 * The label.
	 *
	 * @var string
	 */
	public $label;

	/**
	 * The path to load the documentation from, relative to the doc source.
	 *
	 * @var string
	 */
	public $path;

	/**
	 * The root tree node.
	 *
	 * @var treeNode
	 */
	public $root;

	/**
	 * List of assets to copy over.
	 *
	 * @var array
	 */
	public $assets = [];

	/**
	 * The template engine.
	 *
	 * @var flTemplateEngineLite
	 */
	public $tpl;

	/**
	 * Flags if site search enabled.
	 *
	 * @var bool
	 */
	protected $siteSearch = true;

	/**
	 * Constructor.
	 *
	 * @param string $label The label.
	 * @param string $path The path relative to the source root.
	 */
	function __construct($label, $path) {
		$this->label = $label;
		$this->path = $path;
		$this->root = new treeNode();
		$this->tpl = new flTemplateEngineLite();
	}

	/**
	 * Load the source documents.
	 *
	 * @param string $basePath The route to load the source documents from.
	 * @param array $exclude The list of files and folders to exclude.
	 * @param array $config The site configuration.
	 * @return documentVersion This object
	 */
	function loadSource($basePath, $exclude, $config) {
		$path = realpath($basePath . $this->path) . '/';

		// Create the template engine and initialise the base paths
		$this->tpl
			->addChunkPath('./layouts')
			->addChunkPath($basePath . 'layouts');

		if(!empty($this->path))
			$this->tpl->addChunkPath($path . 'layouts');

		// Set the template data based on the config
		$this->tpl->setPlaceholder([
			'site.name'      => isset($config['site_name']) ? $config['site_name'] : 'Unknown',
			'site.copyright' => isset($config['copyright']) ? $config['copyright'] : '',
			'site.theme'     => isset($config['theme']) ? $config['theme'] : 'default'
		]);

		// Set enable / disable search
		if(isset($config['site_search']) && !$config['site_search']) {
			$this->siteSearch = false;
		}
		else
			$this->siteSearch = true;

		// Filter to exclude our exclude list
		$filter = function ($file, $key, $iterator) use($path, $exclude) {
			$include = true;
			$shortPath = substr($file->getPathname(), strlen($path));

			// Test if in exclude list
			foreach($exclude as $ex) {

				// If ends / then excluding a path
				if(substr($ex, -1) == '/') {
					if(preg_match('#^' . preg_quote($ex, '#') . '#', $shortPath)) {
						$include = false;
						break;
					}
				}
				else if($ex == $shortPath) {
					$include = false;
					break;
				}
			}

			return $include;
		};

		$directory = new RecursiveDirectoryIterator(
			$path,
			RecursiveDirectoryIterator::SKIP_DOTS
		);
		$iterator = new RecursiveIteratorIterator(
			new RecursiveCallbackFilterIterator($directory, $filter)
		);

		// Sort the results
		$iterator = new flUtilSortingIterator($iterator, function($a, $b) {
			return $a->getPathname() > $b->getPathname();
		});

		// Find all the Markdown files in the document directory
		foreach ($iterator as $file) {
			/** @var SplFileInfo $file */

			// Make file relative to document root
			$shortPath = substr($file->getPathname(), strlen($path));

			// If markdown file
			if(preg_match('/\.md$/', $file->getFilename())) {
				$page = new contentPage();
				$page->realpath = $file->getPathname();
				$page->path = $shortPath;

				// Load the document and parse out the front matter
				$page->load();

				// Add the page to the tree
				$this->root->addPage($page);

			} // If asset file
			else {
				// Skip the layout folder & .DS_Store
				if(!preg_match('#^layouts/#', $shortPath) && !preg_match('#\.DS_Store#', $shortPath))
					$this->assets[$file->getPathname()] = strtolower(preg_replace('#(^|/)\d+_#', '$1', $shortPath));
			}
		}

		// Configure site search
		if($this->siteSearch) {
			// If search results page isn't provided create one
			if(!isset($this->root->pagesByName['_search_results.html'])) {
				// Add a search results page
				$page = new contentPage();
				$page->realpath = '_search_results.md';
				$page->path = '_search_results.md';
				$page->outputFile = '_search_results.html';
				$page->name = '_search_results.md';
				$page->frontMatter = [
					'in_menu' => false // Hide from menu
				];
				$page->content = '<div id="tipue_search_content"></div>';
				$page->title = 'Search Results';

				$this->root->addPage($page);
			}

			// Stop search contents being removed from the output
			$this->assets['tipuesearch_content.js'] = 'tipuesearch_content.js';

			// Add to template engine
			$this->tpl->setPlaceholder('site.search', 1);
		}

		return $this;
	}

	/**
	 * Get page using a URL path.
	 *
	 * @param array $segs The path to the page to test for.
	 * @return contentPage The page or null if not found.
	 */
	function getPageByURL($segs) {
		$found = $this->root;
		$len = count($segs);

		// Navigate the folders
		for($i=0;$i<$len;$i++) {
			if(isset($found->childrenByName[$segs[$i]]))
				$found = $found->childrenByName[$segs[$i]];
			else
				break;
		}

		// If 0 items left then got a folder
		if($i == $len) {
			// Try again but with index.html
			$segs[] = 'index.html';
			return $this->getPageByURL($segs);
		}
		// If 1 item left then try for a page
		else if($len - $i == 1 && isset($found->pagesByName[$segs[$i]])) {
			return $found->pagesByName[$segs[$i]];
		}

		return null;
	}

	/**
	 * Convert a URL to a real source path.
	 *
	 * @param array $segs The URL segments to covert.
	 * @return string The source path or false if not known.
	 */
	function realPathFromURL($segs) {
		$realPath = [];
		$found = $this->root;
		$len = count($segs);

		// Navigate the folders
		for($i=0;$i<$len;$i++) {
			if(isset($found->childrenByName[$segs[$i]])) {
				$realPath[] = $found->childrenByName[$segs[$i]]->origName;
			}
			else
				return false;
		}

		return implode('/', $realPath) . '/';
	}
}
