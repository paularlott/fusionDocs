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
 * flUtilSortingIterator Class
 *
 * Iterator class perform sorting.
 *
 * @package fusionLib
 */
class flUtilSortingIterator implements IteratorAggregate {

	/**
	 * The array iterator.
	 *
	 * @var ArrayIterator
	 */
	private $iterator = null;

	/**
	 * flUtilSortingIterator constructor.
	 *
	 * @param Traversable $iterator The object being traversed.
	 * @param callable $callback The callable to use to sort.
	 * @throws flExceptionPHP
	 */
	public function __construct($iterator, $callback) {
		if (!is_callable($callback)) {
			throw new flExceptionPHP(E_ERROR, 'Callback is not callable!', __FILE__, __LINE__);
		}

		// Convert to an array and sort
		$array = iterator_to_array($iterator);
		usort($array, $callback);
		$this->iterator = new ArrayIterator($array);
	}

	/**
	 * @inheritdoc
	 */
	public function getIterator() {
		return $this->iterator;
	}
}
