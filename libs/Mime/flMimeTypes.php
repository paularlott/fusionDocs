<?php

/**
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT ANY EXPRESS OR IMPLIED
 * WARRANTIES, INCLUDING, WITHOUT LIMITATION, THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @package fusionLib
 * @copyright Copyright (c) 2008 - 2017 fusionLib. All rights reserved.
 * @link http://fusionlib.com
 */

/**
 * flMimeTypes Class
 *
 * Class to handle mime type information.
 * @static
 * @package fusionLib
 */
class flMimeTypes {

	/**
	 * An array of known mime types.
	 *
	 * @see http://www.w3schoold.com/media/media_mimeref.asp
	 * @see http://www.webmaster-toolkit.com/mime-types.shtml
	 *
	 * @var array
	 */
	static private $mimeTypes = array(
			'avi' => array('video/x-msvideo'),
			'bmp' => array('image/bmp'),
			'css' => array('text/css'),
			'doc' => array('application/msword'),
			'docx' => array('application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
			'exe' => array('application/octect-stream'),
			'gif' => array('image/gif'),
			'gz' => array('application/x-gzip'),
			'html' => array('text/html'),
			'htm' => array('text/html'),
			'jpg' => array('image/jpeg', 'image/pjpeg'),
			'jpe' => array('image/jpeg', 'image/pjpeg'),
			'jpeg' => array('image/jpeg', 'image/pjpeg'),
			'js' => array('application/x-javascript'),
			'md' => array('text/markdown'),
			'mid' => array('audio/midi'),
			'midi' => array('audio/midi'),
			'mpeg' => array('video/mpeg'),
			'mpg' => array('video/mpeg'),
			'mpe' => array('video/mpeg'),
			'mp3' => array('audio/mpeg3'),
			'pdf' => array('application/pdf'),
			'png' => array('image/png', 'image/x-png'),
			'qt' => array('video/quicktime'),
			'shtml' => array('text/html'),
			'swf' => array('application/x-shockwave-flash'),
			'txt' => array('text/plain'),
			'rtf' => array('application/rtf'),
			'mov' => array('video/quicktime'),
			'tar' => array('application/x-tar'),
			'tgz' => array('application/gnutar', 'application/x-compressed'),
			'tif' => array('image/tiff'),
			'tiff' => array('image/tiff'),
			'xls' => array('application/excel', 'application/vnd.ms-excel'),
			'xml' => array('text/xml'),
			'zip' => array('application/zip', 'application/x-compressed')
		);

	/**
	 * Lookup a mime type for the given file extension.
	 *
	 * @param string $fileExt The file extension to look up the mime type of or a complete filename.
	 * @param string $default The default type to return if the type is unknown.
	 * @return string The mime type or $default if the file extension is not known.
	 */
	static function getType($fileExt, $default = 'application/octect-stream') {
		// If . present then assume a filename given
		if(strstr($fileExt, '.') !== false) {
			$path = pathinfo($fileExt);
			$fileExt = $path['extension'];
		}

		return isset(self::$mimeTypes[$fileExt]) ? self::$mimeTypes[$fileExt][0] : $default;
	}

	/**
	 * Lookup a file type from a mime type.
	 *
	 * Querying for text/xml will return xml.
	 *
	 * @param string $type The mime type to query for.
	 * @return string The first type that matches the query or false if no matches.
	 */
	static function lookupType($type) {

		// Run the list of types matching the 1st
		foreach(self::$mimeTypes as $t => $mime) {
			if(in_array($type, $mime))
				return $t;
		}

		// Failed to match
		return false;
	}
}
