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
 * _version Class
 *
 * Holds the information on a version.
 */
class _version {
	/**
	 * The label.
	 *
	 * @var string
	 */
	public $label;

	/**
	 * The path.
	 *
	 * @var string
	 */
	public $path;

	/**
	 * The document set.
	 *
	 * @var docset
	 */
	public $docset = null;

	/**
	 * _version constructor.
	 *
	 * @param string $label The label.
	 * @param string $path The path relative to the source root.
	 */
	function __construct($label, $path) {
		$this->label = $label;
		$this->path = $path;
	}
}

/**
 * compiler Class
 *
 * Documentation compiler class.
 */
class compiler {

	/**
	 * The application object.
	 *
	 * @var generatorApp
	 */
	protected $app;

	/**
	 * Data from config.yaml
	 *
	 * @var array
	 */
	protected $config = [];

	/**
	 * Array of versions.
	 *
	 * @var _version[]
	 */
	protected $versions = [];

	/**
	 * The version currently being compiled.
	 *
	 * @var _version
	 */
	protected $compilingVersion;

	/**
	 * compiler constructor.
	 *
	 * @param generatorApp $app The application object.
	 */
	function __construct($app) {
		$this->app = $app;
	}

	/**
	 * Set the source path to read from and load the source.
	 *
	 * @param string $path The route to load the source documents from.
	 * @return compiler This object
	 */
	function setSource($path) {
		$path = realpath($path) . '/';

		// Load the config
		if(is_readable($path . 'config.yml')) {
			$yaml = new flYamlParser();
			$this->config = $yaml->parse(file_get_contents($path . 'config.yml'));
		}

		// Get the excludes
		$exclude = [ 'config.yml', 'layouts/' ];
		if(isset($this->config['exclude'])) {
			$exclude = array_merge($exclude, $this->config['exclude']);
		}

		// Get the versions
		$this->versions[] = new _version($this->getConfigVal('version', ''), '');
		if(isset($this->config['versions']) && isset($this->config['version'])) {

			// Add versions to excludes
			foreach($this->config['versions'] as $ver) {
				$exclude[] = $ver . '/';
				$this->versions[] = new _version($ver, $ver . '/');
			}
		}

		// Load for all versions, one docset object per version
		foreach($this->versions as $ver) {
			$ver->docset = new docset($this->app, $this->config, $this);
			$ver->docset->loadSource($path . $ver->path, $exclude);
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
	 * @param string $outputDir The directory to output to.
	 * @throws flCliException
	 */
	function generateOutput($outputDir) {
		$outputDir = rtrim($outputDir, '/') . '/';

		// Generate each version
		foreach($this->versions as $ver) {
			$this->compilingVersion = $ver;

			if(count($this->versions) > 1) {
				$this->app->output
					->newline()
					->writePadded('Version ' . $ver->label, 50, flCliOutput::CYAN)
					->writeLn('[Start]', flCliOutput::YELLOW)
					->newline();
			}

			$ver->docset->generateOutput(
				$outputDir . $ver->path,
				empty($ver->path) ? '' : '../'
			);

			if(count($this->versions) > 1) {
				$this->app->output
					->writePadded('Version ' . $ver->label, 50, flCliOutput::CYAN)
					->writeLn('[Ok]', flCliOutput::GREEN)
					->newline();
			}
		}

		return;
	}

	/**
	 * Build the list of version options.
	 *
	 * @param string $path The path to the document to find the versions of.
	 * @return string The <option>s for the available versions.
	 */
	function buildVersionList($path) {

		// Don't do anything for only 1 version of the code
		if(count($this->versions) <= 1)
			return '';

		$segs = preg_split('#/#', $path, -1, PREG_SPLIT_NO_EMPTY);
		$verList = [];
		$pathToRoot = str_repeat('../', count($segs) - 1);

		if(!empty($this->compilingVersion->path))
			$pathToRoot .= '../';

		foreach($this->versions as $ver) {
			if($ver->docset->pagePresent($segs)) {
				$verList[] = '<option value="' . $pathToRoot . $ver->path . $path . '"' . ($ver->label == $this->compilingVersion->label ? ' selected="selected"' : '') . '>Version ' . $ver->label . '</option>';
			}
		}

		return implode("\n", $verList);
	}

	/**
	 * Generate a page from a URL.
	 *
	 * @param string[] $segs The URL segments.
	 * @return array|false [ 'content', 'realPath', 'page']
	 */
	function generatePage($segs) {

		// Get the 1st version
		$this->compilingVersion = reset($this->versions);

		// If segments see if the 1st one matches a version
		if(count($segs) && count($this->versions) > 1) {
			// Look to see if one of our versions
			foreach($this->versions as $ver) {
				if($ver->label == $segs[0]) {
					$this->compilingVersion = $ver;
					array_shift($segs);
					break;
				}
			}
		}

		return $this->compilingVersion->docset->generatePage(
			$segs,
			empty($this->compilingVersion->path) ? '' : '../'
		);
	}

	/**
	 * Get the search index data.
	 *
	 * @param array $segs The path segments.
	 * @return string The index data.
	 */
	function getSearchIndex($segs) {

		// Get the 1st version
		$this->compilingVersion = reset($this->versions);

		// If segments see if the 1st one matches a version
		if(count($segs) && count($this->versions) > 1) {
			// Look to see if one of our versions
			foreach($this->versions as $ver) {
				if($ver->label == $segs[0]) {
					$this->compilingVersion = $ver;
					array_shift($segs);
					break;
				}
			}
		}

		return $this->compilingVersion->docset->getSearchIndex();
	}
}
