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
 * docset Class
 *
 * Class to hold a set of documentation i.e. a version.
 */
class docset {
	/**
	 * The application object.
	 *
	 * @var generatorApp
	 */
	protected $app;

	/**
	 * The root tree node.
	 *
	 * @var treeNode
	 */
	protected $root;

	/**
	 * List of output files, used in clean up.
	 *
	 * @var string[]
	 */
	protected $outputTargets = [];

	/**
	 * List of assets to be copied over.
	 *
	 * @var string[]
	 */
	protected $assets = [];

	/**
	 * The template engine.
	 *
	 * @var flTemplateEngineLite
	 */
	protected $tpl;

	/**
	 * Data from config.yaml
	 *
	 * @var array
	 */
	protected $config;

	/**
	 * The compiler object.
	 *
	 * @var compiler
	 */
	protected $compiler;

	/**
	 * The source folder data being loaded from.
	 *
	 * @var string
	 */
	protected $sourcePath;

	/**
	 * Site search enabled or not.
	 *
	 * @var bool
	 */
	protected $siteSearch = true;

	/**
	 * The files and folders to exclude.
	 *
	 * @var array
	 */
	protected $excludes = [];

	/**
	 * docset constructor.
	 *
	 * @param generatorApp The application object.
	 * @param array $config The configuration.
	 * @param compiler $compiler The compiler object.
	 */
	function __construct($app, $config, $compiler) {
		$this->app = $app;
		$this->config = $config;
		$this->compiler = $compiler;
	}

	/**
	 * Set the source path to read from and load the source.
	 *
	 * @param string $path The route to load the source documents from.
	 * @param array $exclude The list of files and folders to exclude.
	 * @return compiler This object
	 */
	function loadSource($path, $exclude) {
		$this->excludes = $exclude;
		$this->sourcePath = $path = realpath($path) . '/';

		// Create the template engine and initialise the base paths
		$this->tpl = new flTemplateEngineLite();
		$this->tpl
			->addChunkPath('./layouts')
			->addChunkPath($path . 'layouts');

		// Set the template data based on the config
		$this->tpl->setPlaceholder([
			'site.name'      => $this->getConfigVal('site_name', 'Unknown'),
			'site.copyright' => $this->getConfigVal('copyright', ''),
			'site.theme'     => $this->getConfigVal('theme', 'default')
		]);

		// Set enable / disable search
		if(isset($this->config['site_search']) && !$this->config['site_search']) {
			$this->siteSearch = false;
		}
		else
			$this->siteSearch = true;

		// Build the root
		$this->root = new treeNode();
		$this->outputTargets = [];

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

		// Find all the Markdown files in the document directory
		foreach ($iterator as $filename => $file) {
			/** @var SplFileInfo $file */

			// Make file relative to document root
			$shortPath = substr($filename, strlen($path));

			// If markdown file
			if(preg_match('/\.md$/', $filename)) {
				$page = new contentPage();
				$page->realpath = $filename;
				$page->path = $shortPath;

				// Load the document and parse out the front matter
				$page->load();

				// Add the page to the tree
				$this->root->addPage($page);

				// Create the output path for clean up testing
				$this->outputTargets[] = preg_replace('/\.md$/', '.html', strtolower(preg_replace('#(^|/)\d+_#', '$1', $shortPath)));
			} // If asset file
			else {
				// Skip the layout folder & .DS_Store
				if(!preg_match('#^layouts/#', $shortPath) && !preg_match('#\.DS_Store#', $shortPath))
					$this->assets[$filename] = $shortPath;
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

				$this->outputTargets[] = $page->outputFile;
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
	 * Get a configuration value.
	 *
	 * @param string $name The name of the value to get.
	 * @param string $default The default value to return if the value isn't present.
	 * @return string The value.
	 */
	function getConfigVal($name, $default = '') {
		return isset($this->config[$name]) ? $this->config[$name] : $default;
	}

	/**
	 * Generate the compiled documentation.
	 *
	 * @todo this should be an output driver
	 * @param string $outputDir The directory to output to.
	 * @param string $rootDocPath Path to root document set or '' if root.
	 * @throws flCliException
	 */
	function generateOutput($outputDir, $rootDocPath = '') {

		// If output folder not present
		if(!file_exists($outputDir)) {
			// Make the output directory
			if(!mkdir($outputDir, 0777, true)) {
				throw new flCliException('The destination folder could not be created.');
			}
		}

		$outputDir = realpath($outputDir) . '/';

		// Test if the themes folder is present
		if(empty($rootDocPath) && !is_readable($outputDir . 'themes/')) {

			// Copy the themes folder over
			$this->app->output
				->writeLn('Copying themes to destination...');

			// Copy the theme files over
			$themePath = FLROOTPATH . 'themes/';
			$directory = new RecursiveDirectoryIterator($themePath);
			$iterator = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::SELF_FIRST);
			foreach ($iterator as $filename => $file) {
				if($file->isFile()) {
					$path = substr($filename, strlen($themePath));

					// Create the folder if required
					$dir = dirname($path);
					if($dir !== '.')
						@mkdir($outputDir . 'themes/' . $dir, 0777, true);

					// Copy the file over
					@copy($filename, $outputDir . 'themes/' . $path);

					$this->app->output
						->writePadded('  ' . substr($path, strlen('themes/')), 50)
						->writeLn('[Ok]', flCliOutput::GREEN);
				}
			}

			$this->app->output
				->writePadded('Themes copied', 50)
				->writeLn('[Ok]', flCliOutput::GREEN)
				->newline();
		}

		// Copy assets over
		$this->app->output
			->writeLn('Copying assets to destination...');
		foreach($this->assets as $src => $dst) {

			// Create the folder if required
			$dir = dirname($dst);
			if($dir !== '.')
				@mkdir($outputDir . $dir, 0777, true);

			// Copy the asset over
			@copy($src, $outputDir . $dst);
		}
		$this->app->output
			->writePadded('Assets copied', 50)
			->writeLn('[Ok]', flCliOutput::GREEN)
			->newline();

		// Generate the documentation
		$this->app->output
			->writeLn('Generating documentation...');
		$this->root->writeOutput(
			$this->app->output,
			$outputDir,
			$this->tpl,
			$this->getConfigVal('layout', 'default'),
			$rootDocPath,
			$this->compiler
		);
		$this->app->output
			->writePadded('Document generation', 50)
			->writeLn('[Done]', flCliOutput::GREEN)
			->newline();

		// Generate search data
		$this->app->output
			->writeLn('Generating search index...');
		$this->root->writeSearchIndex(
			$this->app->output,
			$outputDir
		);
		$this->app->output
			->writePadded('Search index', 50)
			->writeLn('[Done]', flCliOutput::GREEN)
			->newline();

		$this->app->output
			->writeLn('Cleanup orphaned files...');

		// Filter to exclude our exclude list
		$filter = function ($file, $key, $iterator) use($outputDir) {
			$include = true;
			$shortPath = substr($file->getPathname(), strlen($outputDir));

			// Test if in exclude list
			foreach($this->excludes as $ex) {

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
			$outputDir,
			RecursiveDirectoryIterator::SKIP_DOTS
		);
		$iterator = new RecursiveIteratorIterator(
			new RecursiveCallbackFilterIterator($directory, $filter)
		);

		// Get the list of files in the output and remove those not present in the input
		foreach ($iterator as $filename => $file) {
			if($file->isFile()) {
				$path = substr($filename, strlen($outputDir));

				// If document
				if(preg_match('/\.html$/', $filename)) {
					if(!in_array($path, $this->outputTargets)) {
						@unlink($filename);
						$this->app->output
							->writePadded("  $path", 50)
							->writeLn('[Removed]', flCliOutput::PURPLE);
					}
				}
				// Else asset
				else if(!in_array($path, $this->assets) && !preg_match('#^themes/#', $path)) {
					@unlink($filename);
					$this->app->output
						->writePadded("  $path", 50)
						->writeLn('[Removed]', flCliOutput::PURPLE);
				}
			}
		}

		$this->app->output
			->writePadded('Cleanup', 50)
			->writeLn('[Done]', flCliOutput::GREEN)
			->newline();
	}

	/**
	 * Get if a page is present in the set.
	 *
	 * @param array $segs The path to the page to test for.
	 * @return bool true if present; else false
	 */
	function pagePresent($segs) {
		$found = $this->root;
		$len = count($segs);

		// Navigate the folders
		for($i=0;$i<$len;$i++) {
			if(isset($found->childrenByName[$segs[$i]])) {
				$realPath[] = $found->childrenByName[$segs[$i]]->origName;
				$found = $found->childrenByName[$segs[$i]];
			}
			else
				break;
		}

		// If 0 items left then got a folder
		if($i == $len) {
			// Try again but with index.html
			$segs[] = 'index.html';
			return $this->hasPage($segs);
		}
		// If 1 item left then try for a page
		else if($len - $i == 1 && isset($found->pagesByName[$segs[$i]])) {
			return true;
		}

		return false;
	}

	/**
	 * Generate a page from a URL.
	 *
	 * @param string[] $segs The URL segments.
	 * @param string $rootDocPath Path to root document set or '' if root.
	 * @return array|false [ 'content', 'realPath', 'page']
	 */
	function generatePage($segs, $rootDocPath = '') {
		$found = $this->root;
		$len = count($segs);
		$realPath = [];
		$pathToRoot = '';

		// Navigate the folders
		for($i=0;$i<$len;$i++) {
			if(isset($found->childrenByName[$segs[$i]])) {
				$realPath[] = $found->childrenByName[$segs[$i]]->origName;
				$found = $found->childrenByName[$segs[$i]];
				$pathToRoot .= '../';
			}
			else
				break;
		}

		// If 0 items left then got a folder
		if($i == $len) {
			// Try again but with index.html
			$segs[] = 'index.html';
			return $this->generatePage($segs);
		}
		// If 1 item left then try for a page
		else if($len - $i == 1) {

			// If page found
			if(isset($found->pagesByName[$segs[$i]])) {
				$page = $found->pagesByName[$segs[$i]];

				$this->tpl->setPlaceholder('site.toRoot', $pathToRoot);
				$this->tpl->setPlaceholder('site.themeRoot', $rootDocPath . $pathToRoot);

				return [
					'content'  => $page->generateHTML(
						$this->tpl,
						$this->getConfigVal('layout', 'default'),
						$this->compiler
					),
					'realPath' => $realPath,
					'page'     => $segs[$i]
				];
			}
			else {
				// If .html page then 404
				if(preg_match('/\.html$/', $segs[$i]))
					return false;

				// Let the caller try and resolve it
				return [
					'content'  => false,
					'realPath' => $realPath,
					'page'     => $segs[$i]
				];
			}
		}

		// 404 not found
		return false;
	}

	/**
	 * Get the search index data.
	 *
	 * @return string The index data.
	 */
	function getSearchIndex() {
		return $this->root->getSearchIndex(null);
	}
}
