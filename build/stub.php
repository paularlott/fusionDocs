#!/usr/bin/env php
<?php

/**
 * Stub file for phar.
 *
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT ANY EXPRESS OR IMPLIED
 * WARRANTIES, INCLUDING, WITHOUT LIMITATION, THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @package fusionLib.docs
 * @copyright Copyright (c) 2017 clearFusionCMS. All rights reserved.
 * @link http://fusionlib.com
 */

Phar::mapPhar('fusionLibDocs.phar');

define('FLROOTPATH', 'phar://fusionLibDocs.phar/');
define('FLSYSFOLDER', '');

require_once 'phar://fusionLibDocs.phar/libs/fusionLib.php';
require_once 'phar://fusionLibDocs.phar/app/parsedown/parsedown.php';
require_once 'phar://fusionLibDocs.phar/app/bootstrap.php';

__HALT_COMPILER();
