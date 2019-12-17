<?php

namespace JambageCom\Div2007\Utility;

/***************************************************************
*  Copyright notice
*
*  (c) 2016 Franz Holzinger (franz@ttproducts.de)
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
 *
 * functions for the TYPO3 extension static_info_tables
 *
 * @author Franz Holzinger <franz@ttproducts.de>
 * @maintainer Franz Holzinger <franz@ttproducts.de>
 * @package TYPO3
 * @subpackage div2007
 */



class SystemCategoryUtility {

	const type_local = 0;
	const type_foreign = 1;

	/**
		* The table name collections are stored to
		*
		* @var string
		*/
	static protected $storageTableName = 'sys_category';

	/**
		* Name of the categories-relation field (used in the MM_match_fields/fieldname property of the TCA)
		*
		* @var string
		*/
	static protected $relationFieldName = 'categories';

	/**
	* Gets the uids by
	* looking up the MM relations of this record to the
	* table name defined in the local field 'table_name'.
	*
	* @return array
	*/
	static public function getForeignUids (
		$tableName,
		$fieldName,
		array $uidArray = array(),
		$orderBy = ''
	) {
		return static::getUids($tableName, $fieldName, $uidArray, $orderBy, type_foreign);
	}

	/**
	* Gets the uids by
	* looking up the MM relations of this record to the
	* table name defined in the local field 'table_name'.
	*
	* @return array
	*/
	static public function getUids (
		$tableName,
		$fieldName,
		array $uidArray = array(),
		$orderBy = '',
		$type = type_local
	) {
		$relatedRecords = array();
		// Assemble where clause

		// Add condition on tablenames fields
		$where .= ' AND sys_category_record_mm.tablenames = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr(
			$tableName,
			'sys_category_record_mm'
		);
		// Add condition on fieldname field
		$where .= ' AND sys_category_record_mm.fieldname = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr(
			$fieldName,
			'sys_category_record_mm'
		);

		if (!empty($uidArray)) {
			$uidArray = $GLOBALS['TYPO3_DB']->cleanIntArray($uidArray);
			$inputTable = $tableName;
			if ($type == type_foreign) {
				$inputTable = static::$storageTableName;
			}

			// Add condition on uid field
			$where .= ' AND ' . $inputTable . '.uid IN (' .
				implode(',', $uidArray) .
				')';
		}

		$outputTable = static::$storageTableName;
		if ($type == type_foreign) {
			$outputTable = $tableName;
		}

		$resource = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
			'DISTINCT ' . $outputTable . '.uid',
			static::$storageTableName,
			'sys_category_record_mm',
			$tableName,
			$where,
			'',
			$orderBy
		);

		if ($resource) {
			while ($record = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resource)) {
				$relatedRecords[] = $record['uid'];
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($resource);
		}

		return $relatedRecords;
	}
}

