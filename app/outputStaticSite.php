<?php

/**
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT ANY EXPRESS OR IMPLIED
 * WARRANTIES, INCLUDING, WITHOUT LIMITATION, THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @package fusionDocs
 * @copyright Copyright (c) 2017 - 2018 clearFusionCMS. All rights reserved.
 * @link http://fusionlib.com
 */

/**
 * outputStaticSite Class
 *
 * Class to output the source documents as a static site.
 */
class outputStaticSite {

	/**
	 * The application object.
	 *
	 * @var generatorApp
	 */
	protected $app;

	/**
	 * outputStaticSite constructor.
	 * @param generatorApp $app The application object.
	 */
	function __construct($app) {
		$this->app = $app;
	}

	/**
	 * outputStaticSite constructor.
	 *
	 * @param string $outputDir The destination folder.
	 * @param documentSet $docSet The document set to generate the output from.
	 * @throws flCliException
	 */
	function writeOutput($outputDir, $docSet) {
		$outputDir = rtrim($outputDir, '/') . '/';

		// Generate each version
		$versions = $docSet->getVersions();
		$verCount = count($versions);
		foreach($versions as $ver) {

			if($verCount > 1) {
				$this->app->output
					->newline()
					->writePadded('Version ' . $ver->label, 50, flCliOutput::CYAN)
					->writeLn('[Start]', flCliOutput::YELLOW)
					->newline();
			}

			$this->writeVersion($outputDir, $docSet, $ver);

			if($verCount > 1) {
				$this->app->output
					->writePadded('Version ' . $ver->label, 50, flCliOutput::CYAN)
					->writeLn('[Ok]', flCliOutput::GREEN)
					->newline();
			}
		}
	}

	/**
	 * Write a version to the output.
	 *
	 * @param string $outputDir The directory to output to.
	 * @param documentSet $docSet The document set to generate the output from.
	 * @param documentVersion $version The version to generate.
	 * @throws flCliException
	 */
	protected function writeVersion($outputDir, $docSet, $version) {
		$outputDir .= $version->path;

		// If output folder not present
		if(!file_exists($outputDir)) {
			// Make the output directory
			if(!mkdir($outputDir, 0777, true)) {
				throw new flCliException('The destination folder could not be created.');
			}
		}

		$outputDir = realpath($outputDir) . '/';

		// Test if the themes folder is present
		if(empty($version->path) && !is_readable($outputDir . 'themes/')) {

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
		foreach($version->assets as $src => $dst) {

			// Create the folder if required
			$dir = dirname($dst);
			if($dir !== '.')
				@mkdir($outputDir . $dir, 0777, true);

			// Copy the asset over
			@copy($src, $outputDir . $dst);

			$this->app->output
				->writePadded('  ' . $dst, 50)
				->writeLn('[Ok]', flCliOutput::GREEN);
		}
		$this->app->output
			->writePadded('Assets copied', 50)
			->writeLn('[Ok]', flCliOutput::GREEN)
			->newline();

		// Generate the documentation
		$this->app->output
			->writeLn('Generating documentation...');

		$this->writePages(
			$outputDir,
			$docSet,
			'',
			'',
			$version,
			$version->root,
			$docSet->getConfigVal('layout', 'default')
		);

		$this->app->output
			->writePadded('Document generation', 50)
			->writeLn('[Done]', flCliOutput::GREEN)
			->newline();

		// Generate search data
		$this->app->output
			->writeLn('Generating search index...');

		file_put_contents(
			$outputDir . 'tipuesearch_content.js',
			$version->root->getSearchIndex($this->app->output)
		);

		$this->app->output
			->writePadded('Search index', 50)
			->writeLn('[Done]', flCliOutput::GREEN)
			->newline();

		$this->app->output
			->writeLn('Cleanup orphaned files...');

		// Filter to exclude our exclude list
		$excludes = $docSet->getExcludes();
		$filter = function ($file, $key, $iterator) use($outputDir, $excludes) {
			$include = true;
			$shortPath = substr($file->getPathname(), strlen($outputDir));

			// Test if in exclude list
			foreach($excludes as $ex) {

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
					$segs = explode('/', $path);
					if(!$version->getPageByURL($segs)) {
						@unlink($filename);
						$this->app->output
							->writePadded("  $path", 50)
							->writeLn('[Removed]', flCliOutput::PURPLE);
					}
				}
				// Else asset
				else if(!preg_match('#^themes/#', $path) && !in_array($path, $version->assets)) {
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
	 * @param string $baseDir The path to write to.
	 * @param documentSet $docSet The document set to generate the output from.
	 * @param string $pathToRoot The path to the root.
	 * @param string $relPath The path relative to the baseDir.
	 * @param documentVersion $version The version object.
	 * @param treeNode $node The node to write the pages for.
	 * @param string $defaultLayout The default layout.
	 */
	protected function writePages($baseDir, $docSet, $pathToRoot, $relPath, $version, $node, $defaultLayout) {
		$outputDir = $baseDir . $relPath;
		$rootDocPath = empty($version->path) ? '' : '../';

		// Output the pages
		if(count($node->pages)) {
			@mkdir($outputDir, 0777, true);

			$version->tpl->setPlaceholder('site.toRoot', $pathToRoot);
			$version->tpl->setPlaceholder('site.themeRoot', $rootDocPath . $pathToRoot);

			foreach($node->pages as $page) {

				// Define the main menu and version navigation
				$version->tpl->setPlaceholder(
					'site.mainMenu',
					$node->getNav('', $page)
				);

				$version->tpl->setPlaceholder(
					'page.versions',
					$docSet->buildVersionList($relPath . $page->outputFile, $version)
				);

				// Generate the page HTML
				$template = $page->generateHTML($version->tpl, $defaultLayout);

				// Store the page
				file_put_contents($outputDir . $page->outputFile, $template);
				$this->app->output
					->writePadded("  {$relPath}$page->outputFile", 50)
					->writeLn('[Ok]', flCliOutput::GREEN);
			}
		}

		// Output the child nodes
		foreach($node->children as $child) {
			$this->writePages(
				$baseDir,
				$docSet,
				$pathToRoot . '../',
				$relPath . $child->name . '/',
				$version,
				$child,
				$defaultLayout
			);
		}
	}
}
