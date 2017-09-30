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
 * documentSet Class
 *
 * Class to hold a set of documentation versions and configurations.
 */
class documentSet {

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
	 * @var documentVersion[]
	 */
	protected $versions = [];

	/**
	 * Array of files and folders to exclude.
	 *
	 * @var array
	 */
	protected $excludes = [];

	/**
	 * Constructor.
	 *
	 * @param generatorApp $app The application object.
	 * @param string $path The path to load the documentation from.
	 */
	function __construct($app, $path) {
		$this->app = $app;
		$path = realpath($path) . '/';

		// Load the config
		if(is_readable($path . 'config.yml')) {
			$yaml = new flYamlParser();
			$this->config = $yaml->parse(file_get_contents($path . 'config.yml'));
		}

		// Get the excludes
		$this->excludes = [ 'config.yml', 'layouts/', 'tipuesearch_content.js' ];
		if(isset($this->config['exclude'])) {
			$this->excludes = array_merge($this->excludes, $this->config['exclude']);
		}

		// Get the versions
		$this->versions[] = new documentVersion($this->getConfigVal('version', ''), '');
		if(isset($this->config['versions']) && isset($this->config['version'])) {

			// Add versions to excludes
			foreach($this->config['versions'] as $ver) {
				$this->excludes[] = $ver . '/';
				$this->versions[] = new documentVersion($ver, $ver . '/');
			}
		}

		// Load all versions
		foreach($this->versions as $ver) {
			$ver->loadSource($path, $this->excludes, $this->config);
		}
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
	 * Get the list of document versions.
	 *
	 * @return documentVersion[] The list of document versions.
	 */
	function getVersions() {
		return $this->versions;
	}

	/**
	 * The list of files and folders to exclude.
	 *
	 * @return array
	 */
	function getExcludes() {
		return $this->excludes;
	}

	/**
	 * Build the list of version options.
	 *
	 * @param string $path The path to the document to find the versions of.
	 * @param documentVersion $version The document version being compiled.
	 * @return string The <option>s for the available versions.
	 */
	function buildVersionList($path, $version) {

		// Don't do anything for only 1 version of the code
		if(count($this->versions) <= 1)
			return '';

		$segs = preg_split('#/#', $path, -1, PREG_SPLIT_NO_EMPTY);
		$verList = [];
		$pathToRoot = str_repeat('../', count($segs) - 1);

		if(!empty($version->path))
			$pathToRoot .= '../';

		// For all versions see if the page exists and add it to the version list
		foreach($this->versions as $ver) {
			if($ver->getPageByURL($segs)) {
				$verList[] = '<option value="' . $pathToRoot . $ver->path . $path . '"' . ($ver->label == $version->label ? ' selected="selected"' : '') . '>Version ' . $ver->label . '</option>';
			}
		}

		return implode("\n", $verList);
	}
}
