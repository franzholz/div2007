<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008-2012 Franz Holzinger <franz@ttproducts.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
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
 * Part of the div2007 (Static Methods for Extensions End of 2007) extension.
 *
 * hook functions for the TYPO3 cms
 *
 * @author	Franz Holzinger <franz@ttproducts.de>
 * @maintainer	Franz Holzinger <franz@ttproducts.de>
 * @package TYPO3
 * @subpackage div2007
 *
 *
 */


class tx_div2007_hooks_cms implements t3lib_Singleton {
	public $extKey = '';	// extension key must be overridden

	/**
	 * Draw the item in the page module
	 *
	 * @param	array		parameters
	 * @param	object		the parent object
	 * @return	  string
	 */

	public function pmDrawItem ($params, $pObj) {
		$callingClassName = '\\TYPO3\\CMS\\Core\\Utility\\ExtensionManagementUtility';

		if (
			class_exists($callingClassName)
		) {
			// nothing
		} else {
			$callingClassName = 't3lib_extMgm';
		}

		if (
			$this->extKey != '' &&
			call_user_func($callingClassName . '::isLoaded', ($this->extKey)) &&
			in_array(
				intval($pObj->pageRecord['doktype']),
				array(1, 2, 5)
			) &&
			$params['row']['pi_flexform'] != ''
		) {
			tx_div2007_ff::load(
				$params['row']['pi_flexform'],
				$this->extKey
			);
			$codes =
				'CODE: ' . tx_div2007_ff::get(
					$this->extKey,
					'display_mode'
				);
		}
		return $codes;
	}
}

