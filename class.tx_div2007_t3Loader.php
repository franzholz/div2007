<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 Elmar Hinz (elmar.hinz@team)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Collection of functions to load t3 classes and instanciate them.
 *
 * @package    TYPO3
 * @subpackage div2007
 * @author     Elmar Hinz <elmar.hinz@team-red.net>
 * @copyright  2006-2007 Elmar Hinz
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @since      0.1
 */

// require_once(PATH_BE_div2007 . 'class.tx_div2007.php');

/**
 * Collection of functions to load t3 classes and instanciate them.
 *
 * @author     Elmar Hinz <elmar.hinz@team-red.net>
 * @package    TYPO3
 * @subpackage lib
 */
class tx_div2007_t3Loader {

	/**
	 * Load a t3 class
	 *
	 * Loads from extension directories ext, sysext, etc.
	 *
	 * Loading: '.../ext/key/subs/prefix.class.suffix
	 *
	 * The files are searched on two levels:
	 *
	 * <pre>
	 * tx_key           '.../ext/key/class.tx_key.php'
	 * tx_key_file      '.../ext/key/class.tx_key_file.php'
	 * tx_key_file      '.../ext/key/file/class.tx_key_file.php'
	 * tx_key_subs_file '.../ext/key/subs/class.tx_key_subs_file.php'
	 * tx_key_subs_file '.../ext/key/subs/file/class.tx_key_subs_file.php'
	 * </pre>
	 *
	 * @param	string		classname or speaking part of path
	 * @param	string		extension key that varies from classname
	 * @param	string		prefix of classname
	 * @param	string		ending of classname
	 * @return	boolean		TRUE if class was loaded
	 */
	public function load ($minimalInformation, $alternativeKey = '', $prefix = 'class.', $suffix = '.php') {
		$path = tx_div2007_t3Loader::_find($minimalInformation, $alternativeKey, $prefix, $suffix);
		if($path) {
// 			require_once($path);
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * Load a t3 class and make an instance
	 *
	 * Returns ux_ extension class if any by make use of \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance
	 *
	 * @param	string		classname
	 * @param	string		extension key that varies from classnames
	 * @param	string		prefix of classname
	 * @param	string		ending of classname
	 * @return	object		instance of the class or false if it fails
	 * @see		\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance
	 * @see		load()
	 */
	public function makeInstance ($class, $alternativeKey = '', $prefix = 'class.', $suffix = '.php') {
		if(class_exists($class)) {
			return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance($class); // includes ux_ classes
		} else {
			return false;
		}
	}

	//--------------------------------------------------------------
	// Private functions
	//--------------------------------------------------------------

	/**
	 * Find path to load
	 *
	 * see load
	 *
	 * @param	string		classname
	 * @param	string		extension key that varies from classnames
	 * @param	string		prefix of classname
	 * @param	string		ending of classname
	 * @return	string		the path, FALSE if invalid
	 * @see		load()
	 */
	public function _find ($minimalInformation, $alternativeKey = '', $prefix = 'class.', $suffix = '.php') {
		$info=trim($minimalInformation);
		$path = '';
		if(!$info) {
			$error = 'emptyParameter';
		}
		if(!$error) {
			$qSuffix = preg_quote ($suffix, '/');
			// If it is a path extract the key first.
			// Either the relevant part starts with a slash: xyz/[tx_].....php
			if(preg_match('/^.*\/([0-9A-Za-z_]+)' . $qSuffix . '$/', $info, $matches)) {
				$class = $matches[1];
			}elseif(preg_match('/^.*\.([0-9A-Za-z_]+)' . $qSuffix . '$/', $info, $matches)) {
				// Or it starts with a Dot: class.[tx_]....php

				$class = $matches[1];
			}elseif(preg_match('/^([0-9A-Za-z_]+)' . $qSuffix . '$/', $info, $matches)) {
				// Or it starts directly with the relevant part
				$class = $matches[1];
			}elseif(preg_match('/^[0-9a-zA-Z_]+$/', trim($info), $matches)) {
				// It may be the key itself
				$class = $info;
			}else{
				$error = 'classError';
			}
		}
		// With this a possible alternative Key is also validated
		if(!$error && !$key = tx_div2007::guessKey($alternativeKey ? $alternativeKey : $class)) {
			$error = 'classError';
		}
		if(!$error) {
			if(preg_match('/^tx_[0-9A-Za-z_]*$/', $class)) {  // with tx_ prefix
				$parts=explode('_', trim($class));
				array_shift($parts); // strip tx
			}elseif(preg_match('/^[0-9A-Za-z_]*$/', $class)) { // without tx_ prefix
				$parts=explode('_', trim($class));
			}else{
				$error = 'classError';
			}
		}
		if(!$error) {

			// Set extPath for key (first element)
			$first = array_shift($parts);

			// Save last element of path
			if(count($parts) > 0) {
				$last = array_pop($parts) . '/';
			}

			$dir = '';
			// Build the relative path if any
			foreach((array)$parts as $part) {
				$dir .= $part . '/';
			}

			// if an alternative Key is given use that
			$ext = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($key);

			// First we try ABOVE last directory (dir and last may be empty)
			// ext(/dir)/last
			// ext(/dir)/prefix.tx_key_parts_last.php.
			if(!$path && !is_file($path =  $ext . $dir . $prefix . $class . $suffix)) {
				$path = FALSE;
			}

			// Now we try INSIDE the last directory (dir and last may be empty)
			// ext(/dir)/last
			// ext(/dir)/last/prefix.tx_key_parts_last.php.
			if(!$path && !is_file($path =  $ext . $dir . $last . $prefix . $class . $suffix)) {
				$path = FALSE;
			}
		}
		return $path;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/div2007/lib/class.tx_div2007_t3Loader.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/div2007/lib/class.tx_div2007_t3Loader.php']);
}

?>