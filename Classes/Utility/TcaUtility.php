<?php

namespace JambageCom\Div2007\Utility;

/***************************************************************
*  Copyright notice
*
*  (c) 2016 Franz Holzinger <franz@ttproducts.de>
*  All rights reserved
*
*  This script is part of the Typo3 project. The Typo3 project is
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
 * TCA functions
 *
 * @author	Franz Holzinger <franz@ttproducts.de>
 * @maintainer Franz Holzinger <franz@ttproducts.de>
 * @package TYPO3
 * @subpackage div2007
 */


class TcaUtility {

	/**
	 * removes fields from the TCA of a table
	 *
	 * @param	array		reference to the TCA of a table, e.g. GLOBALS['TCA']['tablename']
	 * @param	array		Array of fields to remove
	 * @return	string		Content stream
	 */
	static public function removeField (&$tableTca, $fieldList) {

		$divClass = '\\TYPO3\\CMS\\Core\\Utility\\GeneralUtility';

		$fieldArray = call_user_func($divClass . '::trimExplode', ',', $fieldList, 1);

		$tmpArray = explode(',', $tableTca['interface']['showRecordFieldList']);
		$tmpArray = array_diff($tmpArray, $fieldArray);
		$tableTca['interface']['showRecordFieldList'] = implode(',', $tmpArray);

		foreach ($fieldArray as $field) {
			if (isset($tableTca['columns'][$field])) {
				unset($tableTca['columns'][$field]);
			}
		}

		$conigTypeArray = array('types', 'palettes');

		foreach ($conigTypeArray as $configType) {
			if (
				isset($tableTca[$configType]) &&
				is_array($tableTca[$configType])
			) {
				foreach ($tableTca[$configType] as $k => $config) {
					if (isset($config) && is_array($config)) {
						$showItemArray = explode(',', $config['showitem']);
						if (isset($showItemArray) && is_array($showItemArray)) {
							foreach ($showItemArray as $k2 => $showItem) {
								$showItem = trim($showItem);
								foreach ($fieldArray as $field) {
									if (
										strpos($showItem, $field) === 0
									) {
										$length = strlen($field);
										if (
											strlen($showItem) == $length ||
											substr($showItem, $length, 1) == ';'
										) {
											unset($showItemArray[$k2]);
										}
									}
								}
							}
							$tableTca[$configType][$k]['showitem'] = implode(',', $showItemArray);
						}
					}
				}
			}
		}
	}
}


