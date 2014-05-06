<?php

namespace JambageCom\Div2007\Utility;


/***************************************************************
*  Copyright notice
*
*  (c) 2013 Franz Holzinger (franz@ttproducts.de)
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
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * extension functions.
 *
 * @author	Franz Holzinger <franz@ttproducts.de>
 * $Id$
 */
class ExtensionUtility {

	/**
	 * Gets information for an extension, eg. version and most-recently-edited-script
	 *
	 * @param	string		Extension key
	 * @param	string		predefined path ... needed if you have the extension in another place
	 * @return	array		Information array (unless an error occured)
	 */
	static public function getExtensionInfo ($extKey, $path = '') {
		$result = '';

		if (!$path) {
			$path = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($extKey);
		}

		if (is_dir($path)) {
			$file = $path . 'ext_emconf.php';

			if (@is_file($file)) {
				$_EXTKEY = $extKey;
				$EM_CONF = array();
				include($file);

				$eInfo = array();
				$fieldArray = array(
					'author',
					'author_company',
					'author_email',
					'category',
					'constraints',
					'description',
					'lastuploaddate',
					'reviewstate',
					'state',
					'title',
					'version',
					'CGLcompliance',
					'CGLcompliance_note'
				);
				$extConf = $EM_CONF[$extKey];

				if (isset($extConf) && is_array($extConf)) {
					foreach ($extConf as $field => $value) {
						if (in_array($field, $fieldArray)) {
							$eInfo[$field] = $value;
						}
					}

					foreach ($fieldArray as $field) {
						// Info from emconf:
						$eInfo[$field] = $extConf[$field];
					}

					if (is_array($extConf['constraints']) && is_array($EM_CONF[$extKey]['constraints']['depends'])) {
						$eInfo['TYPO3_version'] = $extConf['constraints']['depends']['typo3'];
					} else {
						$eInfo['TYPO3_version'] = $extConf['TYPO3_version'];
					}
					$filesHash = unserialize($extConf['_md5_values_when_last_written']);
					$eInfo['manual'] = @is_file($path . '/doc/manual.sxw');
					$result = $eInfo;
				} else {
					$result = 'ERROR: The array $EM_CONF is wrong in file: ' . $file;
				}
			} else {
				$result = 'ERROR: No ext_emconf.php file: ' . $file;
			}
		} else {
			$result = 'ERROR: Path not found: ' . $path;
		}

		return $result;
	}

}

?>