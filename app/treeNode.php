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
 * treeNode class
 *
 * Class to hold a single node in the document tree.
 */
class treeNode {

	/**
	 * The parent node or null if root.
	 *
	 * @var treeNode
	 */
	public $parent = null;

	/**
	 * Child nodes.
	 *
	 * @var treeNode[]
	 */
	public $children = [];

	/**
	 * Child nodes by name.
	 *
	 * @var treeNode[]
	 */
	public $childrenByName = [];

	/**
	 * The list of pages in the node.
	 *
	 * @var contentPage[]
	 */
	public $pages = [];

	/**
	 * The list of pages by name.
	 *
	 * @var contentPage[]
	 */
	public $pagesByName = [];

	/**
	 * The title of the node.
	 *
	 * @var string
	 */
	public $title;

	/**
	 * The name of the folder.
	 *
	 * @var string
	 */
	public $name;

	/**
	 * The original name.
	 *
	 * @var string
	 */
	public $origName;

	/**
	 * Flags if the folder has an index.md file.
	 *
	 * @var bool
	 */
	public $hasIndex = false;

	/**
	 * Add a page to the document tree.
	 *
	 * @param contentPage $page
	 */
	function addPage($page) {
		$addTo = $this;

		// Get the elements of the path
		$parts = explode('/', $page->path);

		// Navigate to the correct child
		while(count($parts) > 1) {
			// Create new node if required
			if(!isset($addTo->children[$parts[0]])) {
				$node = new treeNode();
				$node->parent = $addTo;

				$node->title = preg_replace('/^\d+_/', '', $parts[0]);
				$node->title = str_replace('_', ' ', $node->title);

				$node->name = strtolower(preg_replace('/^\d+_/', '', $parts[0]));
				$node->origName = $parts[0];

				$addTo->children[$parts[0]] = $node;
				$addTo->childrenByName[$node->name] = $node;

				$addTo = $addTo->children[$parts[0]];
			}
			else {
				$addTo = $addTo->children[$parts[0]];
			}

			array_shift($parts);
		}

		// Add the page to the tree
		$page->folder = $addTo;

		// If index.md then move to 1st in list
		if($page->name == 'index.md' || $page->name == '_index.md') {
			array_unshift($addTo->pages, $page);
			$addTo->hasIndex = true;
		}
		else
			$addTo->pages[] = $page;

		$addTo->pagesByName[$page->outputFile] = $page;
	}

	/**
	 * Get the navigation tree.
	 *
	 * @param string $prefix The optional prefix.
	 * @param page $active The active page.
	 * @return string The HTML for the navigation tree.
	 */
	function getNav($prefix = '', $active = null) {

		// If not the root node then go up the tree
		if($this->parent && empty($prefix)) {

			// Work up tree until we hit the root
			$node = $this;
			do {
				$node = $node->parent;
				$prefix .= '../';
			} while($node->parent);

			// Generate from the root
			return $node->getNav($prefix, $active);
		}

		$html = '';

		foreach($this->pages as $page) {
			if(!isset($page->frontMatter['in_menu']) || $page->frontMatter['in_menu']) {
				$html .= '<li' . ($page === $active ? ' class="active"' : '') . '><a href="' . $prefix . $page->outputFile . '">' . $page->title . "</a></li>\n";
			}
		}

		foreach($this->children as $child) {
			$childHTML = $child->getNav($prefix . $child->name . '/', $active);
			if(!empty($childHTML)) {
				if($child->hasIndex) {
					$html .= "<li><a href=\"$prefix{$child->name}/index.html\">$child->title</a> $childHTML</li>\n";
				}
				else
					$html .= "<li><span class=\"folder\">$child->title</span> $childHTML</li>\n";
			}
		}

		if(!empty($html) && $this->parent) {
			$html = "<ul>\n$html</ul>\n";
		}

		return $html;
	}

	/**
	 * Get the search index data.
	 * @param flCliOutput $output The console output object.
	 * @return string The search data.
	 */
	function getSearchIndex($output) {
		$searchIndex = [];
		$this->_getSearchIndex($output, '', $searchIndex);

		return 'var tipuesearch = {"pages": ' . json_encode($searchIndex) . '};';
	}

	/**
	 * Build the search index for a node.
	 *
	 * @param flCliOutput $output The console output object.
	 * @param string $uriPath The current URI path.
	 * @param array $searchIndex The index information.
	 */
	protected function _getSearchIndex($output, $uriPath, & $searchIndex) {

		// Index the pages
		foreach($this->pages as $page) {
			$searchIndex[] = [
				'title' => $page->title,
				'text' => $page->content,
				'tags' => isset($page->frontMatter['tags']) ? $page->frontMatter['tags'] : '',
				'url' => $uriPath . $page->outputFile
			];

			if($output) {
				$output->writePadded("  $page->name", 50)
					->writeLn('[Ok]', flCliOutput::GREEN);
			}
		}

		// Index all the child nodes
		foreach($this->children as $child) {
			$child->_getSearchIndex($output, $uriPath . $child->name . '/', $searchIndex);
		}
	}
}
