<?php

/**
 * Snippet to perform simple if-then-else.
 *
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT ANY EXPRESS OR IMPLIED
 * WARRANTIES, INCLUDING, WITHOUT LIMITATION, THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @package fusionLib.docs
 * @copyright Copyright (c) 2017 clearFusionCMS. All rights reserved.
 * @link http://fusionlib.com
 */

$subject = isset($subject) ? $subject : '';
$operator = isset($operator) ? $operator : '';
$operand = isset($operand) ? $operand : '';

$isTrue = false;
switch($operator) {
	case '==': $isTrue = $subject == $operand; break;
	case '!=': $isTrue = $subject != $operand; break;
	case '<': $isTrue = $subject < $operand; break;
	case '<=': $isTrue = $subject <= $operand; break;
	case '>': $isTrue = $subject > $operand; break;
	case '>=': $isTrue = $subject >= $operand; break;
	case 'empty': $subject = trim($subject); $isTrue = empty($subject); break;
	case '!empty': $subject = trim($subject); $isTrue = !empty($subject); break;
}

if($isTrue)
	echo isset($then) ? $then : '';
else if(isset($else))
	echo $else;
