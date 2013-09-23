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
 * table functions.
 *
 * @author	Franz Holzinger <franz@ttproducts.de>
 * $Id$
 */
class TableUtility {

	/**
	 * Returns select statement for MM relations (as used by TCEFORMs etc) . Code borrowed from class.t3lib_befunc.php
	 * Usage: 3
	 *
	 * @param	array		Configuration array for the field, taken from $TCA
	 * @param	string		Field name
	 * @param	array		TSconfig array from which to get further configuration settings for the field name
	 * @param	string		Prefix string for the key "*foreign_table_where" from $fieldValue array
	 * @return	string		resulting where string with accomplished marker substitution
	 * @internal
	 * @see t3lib_transferData::renderRecord(), t3lib_TCEforms::foreignTable()
	 */
	static public function foreign_table_where_query ($fieldValue, $field = '', $TSconfig = array(), $prefix = '') {
		global $TCA;

		$foreign_table = $fieldValue['config'][$prefix . 'foreign_table'];
		\TYPO3\CMS\Core\Utility\GeneralUtility::loadTCA($foreign_table);
		$rootLevel = $TCA[$foreign_table]['ctrl']['rootLevel'];

		$fTWHERE = $fieldValue['config'][$prefix . 'foreign_table_where'];

		if (strstr($fTWHERE, '###REC_FIELD_')) {
			$fTWHERE_parts = explode('###REC_FIELD_', $fTWHERE);
			foreach($fTWHERE_parts as $kk => $vv) {
				if ($kk) {
					$fTWHERE_subpart = explode('###', $vv, 2);
					$fTWHERE_parts[$kk] = $TSconfig['_THIS_ROW'][$fTWHERE_subpart[0]].$fTWHERE_subpart[1];
				}
			}
			$fTWHERE = implode('', $fTWHERE_parts);
		}

		$fTWHERE = str_replace('###CURRENT_PID###', intval($TSconfig['_CURRENT_PID']), $fTWHERE);
		$fTWHERE = str_replace('###THIS_UID###', intval($TSconfig['_THIS_UID']), $fTWHERE);
		$fTWHERE = str_replace('###THIS_CID###', intval($TSconfig['_THIS_CID']), $fTWHERE);
		$fTWHERE = str_replace('###STORAGE_PID###', intval($TSconfig['_STORAGE_PID']), $fTWHERE);
		$fTWHERE = str_replace('###SITEROOT###', intval($TSconfig['_SITEROOT']), $fTWHERE);

		if (isset($TSconfig[$field]) && is_array($TSconfig[$field])) {
			$fTWHERE = str_replace('###PAGE_TSCONFIG_ID###', intval($TSconfig[$field]['PAGE_TSCONFIG_ID']), $fTWHERE);
			$fTWHERE = str_replace('###PAGE_TSCONFIG_IDLIST###', $GLOBALS['TYPO3_DB']->cleanIntList($TSconfig[$field]['PAGE_TSCONFIG_IDLIST']), $fTWHERE);

			$fTWHERE = str_replace('###PAGE_TSCONFIG_STR###', $GLOBALS['TYPO3_DB']->quoteStr($TSconfig[$field]['PAGE_TSCONFIG_STR'], $foreign_table), $fTWHERE);
		} else {
			$fTWHERE = str_replace('###PAGE_TSCONFIG_ID###', 0, $fTWHERE);
			$fTWHERE = str_replace('###PAGE_TSCONFIG_IDLIST###', 0, $fTWHERE);
			$fTWHERE = str_replace('###PAGE_TSCONFIG_STR###', 0, $fTWHERE);
		}

		return $fTWHERE;
	}
}

?>