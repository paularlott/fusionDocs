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
 * contentPage Class
 *
 * Class to hold the information for a single page.
 */
class contentPage {
	/**
	 * The full OS based path to the page.
	 *
	 * @var string
	 */
	public $realpath;

	/**
	 * The path to the page from the document root.
	 *
	 * @var string
	 */
	public $path;

	/**
	 * The name of the output file.
	 *
	 * @var string
	 */
	public $outputFile;

	/**
	 * The file name.
	 *
	 * @var string
	 */
	public $name;

	/**
	 * Array of front matter data for the document.
	 *
	 * @var array
	 */
	public $frontMatter;

	/**
	 * The content for the page.
	 *
	 * @var string
	 */
	public $content;

	/**
	 * The page title
	 *
	 * @var string
	 */
	public $title;

	/**
	 * The folder that the page is on.
	 *
	 * @var treeNode
	 */
	public $folder;

	/**
	 * Load the document defined by $realpath.
	 *
	 * @return void
	 */
	function load() {
		$document = file_get_contents($this->realpath);

		// Get the front matter
		$r = flYamlFrontMatter::parse($document);
		$this->frontMatter = $r->frontMatter;
		$this->content = $r->document;

		// Create the output file name
		$this->outputFile = preg_replace('/^\d+_/', '', $this->name = basename($this->path));
		$this->outputFile = preg_replace('/\.md$/', '', $this->outputFile);

		// Create the page title
		if(isset($this->frontMatter['title']))
			$this->title = $this->frontMatter['title'];
		else {
			$this->title = str_replace('_', ' ', $this->outputFile);
		}

		// Set the file extension for the output file
		$this->outputFile = strtolower($this->outputFile . '.html');
	}

	/**
	 * Get the navigation relative to this page.
	 *
	 * @return string The HTML for the navigation.
	 */
	function getNav() {
		return $this->folder->getNav('', $this);
	}

	/**
	 * Generate the HTML content for a page.
	 *
	 * @param flTemplateEngineLite $tpl The template engine.
	 * @param string $defaultLayout The default layout to use.
	 * @param compiler $compiler The compiler object.
	 * @return string The page HTML.
	 */
	function generateHTML($tpl, $defaultLayout, $compiler) {
		$parsedown = new Parsedown();
		$content = $parsedown->text($this->content);

		// Convert [ & ] to entity codes so that template engine doesn't process them
		$content = str_replace(
			['[', ']'],
			['&#91;', '&#93;'],
			$content
		);

		// Fix URLs
		$content = preg_replace_callback(
			'/(<a\s.*href=)"([^"]+)"/',
			function($matches) {
				$url = preg_replace('#(^|/)\d+_#', '$1', $matches[2]);
				$url = preg_replace('/\.md$/', '.html', $url);

				return $matches[1] . '"' . strtolower($url) . '"';
			},
			$content
		);

		// Fix up images
		$content = preg_replace_callback(
			'/(<img\s.*src=)"([^"]+)"/',
			function($matches) {
				return $matches[1] . '"' . strtolower(preg_replace('#(^|/)\d+_#', '$1', $matches[2])) . '"';
			},
			$content
		);

		return $tpl->parseChunk(
			isset($this->frontMatter['layout'])
				? $this->frontMatter['layout']
				: $defaultLayout,
			[

				'site.mainMenu' => $this->getNav(),
				'page.title'    => $this->title,
				'page.content'  => $content,
				'page.versions' => $compiler->buildVersionList(
					preg_replace('/\.md$/', '.html', strtolower(preg_replace('#(^|/)\d+_#', '$1', $this->path)))
				)
			]
		);
	}
}
