<?php

namespace JambageCom\Div2007\Utility;


/***************************************************************
*  Copyright notice
*
*  (c) 2018 Kasper Skårhøj (kasperYYYY@typo3.com)
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
 * table functions. It requires TYPO3 6.2
 *
 * @author	Franz Holzinger <franz@ttproducts.de>
 * @maintainer Franz Holzinger <franz@ttproducts.de>
 * @package TYPO3
 * @subpackage div2007
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;

 
class TableUtility {

	/**
	 * Fields that are considered as system.
	 *
	 * @var array
	 */
	static protected $systemFields = array(
		'uid',
		'pid',
		'tstamp',
		'crdate',
		'deleted',
		'hidden',
		'starttime',
		'endtime',
		'sys_language_uid',
		'l18n_parent',
		'l18n_diffsource',
		't3ver_oid',
		't3ver_id',
		't3ver_wsid',
		't3ver_label',
		't3ver_state',
		't3ver_stage',
		't3ver_count',
		't3ver_tstamp',
		't3_origuid',
	);

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
		$foreign_table = $fieldValue['config'][$prefix . 'foreign_table'];
		$typoVersion = \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version);
		if ($typoVersion < '6002000') {
			\TYPO3\CMS\Core\Utility\GeneralUtility::loadTCA($foreign_table);
		}
		$rootLevel = $GLOBALS['TCA'][$foreign_table]['ctrl']['rootLevel'];

		$fTWHERE = $fieldValue['config'][$prefix . 'foreign_table_where'];

		if (strstr($fTWHERE, '###REC_FIELD_')) {
			$fTWHERE_parts = explode('###REC_FIELD_', $fTWHERE);
			foreach($fTWHERE_parts as $kk => $vv) {
				if ($kk) {
					$fTWHERE_subpart = explode('###', $vv, 2);
					$fTWHERE_parts[$kk] = $TSconfig['_THIS_ROW'][$fTWHERE_subpart[0]] . $fTWHERE_subpart[1];
				}
			}
			$fTWHERE = implode('', $fTWHERE_parts);
		}

		$currentPid = intval($TSconfig['_CURRENT_PID']);
		$fTWHERE = str_replace('###CURRENT_PID###', $currentPid, $fTWHERE);
		$fTWHERE = str_replace('###THIS_UID###', intval($TSconfig['_THIS_UID']), $fTWHERE);
		$fTWHERE = str_replace('###THIS_CID###', intval($TSconfig['_THIS_CID']), $fTWHERE);
		$fTWHERE = str_replace('###STORAGE_PID###', intval($TSconfig['_STORAGE_PID']), $fTWHERE);
		$fTWHERE = str_replace('###SITEROOT###', intval($TSconfig['_SITEROOT']), $fTWHERE);

		if (isset($TSconfig[$field]) && is_array($TSconfig[$field])) {
			$fTWHERE = str_replace('###PAGE_TSCONFIG_ID###', intval($TSconfig[$field]['PAGE_TSCONFIG_ID']), $fTWHERE);
			$fTWHERE = str_replace('###PAGE_TSCONFIG_IDLIST###', $GLOBALS['TYPO3_DB']->cleanIntList($TSconfig[$field]['PAGE_TSCONFIG_IDLIST']), $fTWHERE);

			$fTWHERE = str_replace('###PAGE_TSCONFIG_STR###', $GLOBALS['TYPO3_DB']->quoteStr($TSconfig[$field]['PAGE_TSCONFIG_STR'], $foreign_table), $fTWHERE);
		} else {
			$fTWHERE = str_replace('###PAGE_TSCONFIG_ID###', $currentPid, $fTWHERE);
			$fTWHERE = str_replace('###PAGE_TSCONFIG_IDLIST###', $currentPid, $fTWHERE);
			$fTWHERE = str_replace('###PAGE_TSCONFIG_STR###', '', $fTWHERE);
		}
		return $fTWHERE;
	}


	/**
	 * Returns the "AND NOT deleted" clause for the tablename given IF $GLOBALS['TCA'] configuration points to such a field.
	 *
	 * @param	string		Tablename
	 * @return	string
	 * @see enableFields()
	 */
	static public function deleteClause ($table) {
		if (!strcmp($table, 'pages')) { // Hardcode for pages because TCA might not be loaded yet (early frontend initialization)
			return ' AND pages.deleted=0';
		} else {
			return $GLOBALS['TCA'][$table]['ctrl']['delete'] ? ' AND ' . $table . '.' . $GLOBALS['TCA'][$table]['ctrl']['delete'] . '=0' : '';
		}
	}

	/**
	 * Creating where-clause for checking group access to elements in enableFields function
	 *
	 * @param	string		Field with group list
	 * @param	string		Table name
	 * @return	string		AND sql-clause
	 * @see enableFields()
	 */
	static public function getMultipleGroupsWhereClause ($field, $table) {
		$memberGroups = GeneralUtility::intExplode(',', $GLOBALS['TSFE']->gr_list);
		$orChecks = array();
		$orChecks[] = $field . '=\'\''; // If the field is empty, then OK
		$orChecks[] = $field . ' IS NULL'; // If the field is NULL, then OK
		$orChecks[] = $field . '=\'0\''; // If the field contsains zero, then OK

		foreach ($memberGroups as $value) {
			$orChecks[] = $GLOBALS['TYPO3_DB']->listQuery($field, $value, $table);
		}

		return ' AND (' . implode(' OR ', $orChecks) . ')';
	}

	/**
	 * Returns a part of a WHERE clause which will filter out records with start/end times or hidden/fe_groups fields set to values that should de-select them according to the current time, preview settings or user login. Definitely a frontend function.
	 * Is using the $GLOBALS['TCA'] arrays "ctrl" part where the key "enablefields" determines for each table which of these features applies to that table.
	 *
	 * @param	string		Table name found in the $GLOBALS['TCA'] array
	 * @param	integer		If $show_hidden is set (0/1), any hidden-fields in records are ignored. NOTICE: If you call this function, consider what to do with the show_hidden parameter. Maybe it should be set? See tslib_cObj->enableFields where it's implemented correctly.
	 * @param	array		Array you can pass where keys can be "disabled", "starttime", "endtime", "fe_group" (keys from "enablefields" in TCA) and if set they will make sure that part of the clause is not added. Thus disables the specific part of the clause. For previewing etc.
	 * @param	boolean		If set, enableFields will be applied regardless of any versioning preview settings which might otherwise disable enableFields
	 * @return	string		The clause starting like " AND ...=... AND ...=..."
	 * @see tslib_cObj::enableFields(), deleteClause()
	 */
	static public function enableFields ($table, $show_hidden = -1, $ignore_array = array(), $noVersionPreview = true) {
		if ($show_hidden == -1 && is_object($GLOBALS['TSFE'])) { // If show_hidden was not set from outside and if TSFE is an object, set it based on showHiddenPage and showHiddenRecords from TSFE
			$show_hidden = $table == 'pages' ? $GLOBALS['TSFE']->showHiddenPage : $GLOBALS['TSFE']->showHiddenRecords;
		}
		if ($show_hidden == -1) {
			$show_hidden = 0;
		} // If show_hidden was not changed during the previous evaluation, do it here.

		$ctrl = $GLOBALS['TCA'][$table]['ctrl'];
		$query = '';
		if (is_array($ctrl)) {

				// Delete field check:
			if ($ctrl['delete']) {
				$query .= ' AND ' . $table . '.' . $ctrl['delete'] . '=0';
			}

				// Filter out new place-holder records in case we are NOT in a versioning preview (that means we are online!)
			if ($ctrl['versioningWS'] && $noVersionPreview) {
				$query .= ' AND ' . $table . '.t3ver_state<=0 AND ' . $table . '.pid<>-1'; // Shadow state for new items MUST be ignored!
			}

				// Enable fields:
			if (is_array($ctrl['enablecolumns'])) {
				if (!$ctrl['versioningWS'] || $noVersionPreview) { // In case of versioning-preview, enableFields are ignored (checked in versionOL())
					if (
						$ctrl['enablecolumns']['disabled'] &&
						!$show_hidden &&
						!$ignore_array['disabled']
					) {
						$field = $table . '.' . $ctrl['enablecolumns']['disabled'];
						$query .= ' AND ' . $field . '=0';
					}
					if (
						$ctrl['enablecolumns']['starttime'] &&
						!$ignore_array['starttime']
					) {
						$field = $table . '.' . $ctrl['enablecolumns']['starttime'];
						$query .= ' AND ' . $field . '<=' . $GLOBALS['SIM_ACCESS_TIME'];
					}
					if (
						$ctrl['enablecolumns']['endtime'] &&
						!$ignore_array['endtime']
					) {
						$field = $table . '.' . $ctrl['enablecolumns']['endtime'];
						$query .= ' AND (' . $field . '=0 OR ' . $field . '>' . $GLOBALS['SIM_ACCESS_TIME'] . ')';
					}
					if (
						$ctrl['enablecolumns']['fe_group'] &&
						!$ignore_array['fe_group']
					) {
						$field = $table . '.' . $ctrl['enablecolumns']['fe_group'];
						$query .= static::getMultipleGroupsWhereClause($field, $table);
					}

						// Call hook functions for additional enableColumns
						// It is used by the extension ingmar_accessctrl which enables assigning more than one usergroup to content and page records
					if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_page.php']['addEnableColumns'])) {
						$_params = array(
							'table' => $table,
							'show_hidden' => $show_hidden,
							'ignore_array' => $ignore_array,
							'ctrl' => $ctrl
						);
						foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_page.php']['addEnableColumns'] as $_funcRef) {
							$query .= GeneralUtility::callUserFunction($_funcRef, $_params, $tmp = 'TableUtility');
						}
					}
				}
			}
		} else {
			throw new \InvalidArgumentException(
				'There is no entry in the $GLOBALS[\'TCA\'] array for the table "' . $table .
				'". This means that the function enableFields() is ' .
				'called with an invalid table name as argument.',
				1283790586
			);
		}

		return $query;
	}


	/**
	 * Removes Page UID numbers from the input array which are not available due to enableFields() or the list of bad doktype numbers ($this->checkPid_badDoktypeList)
	 *
	 * @param array $listArr Array of Page UID numbers for select and for which pages with enablefields and bad doktypes should be removed.
	 * @return array Returns the array of remaining page UID numbers
	 * @access private
	 * @see getWhere(),checkPid()
	 * @todo Define visibility
	 */
	static public function checkPidArray ($listArr) {
		$outArr = array();
		if (is_array($listArr) && count($listArr)) {
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', 'pages', 'uid IN (' . implode(',', $listArr) . ')' . static::enableFields('pages') . ' AND doktype NOT IN (' . $this->checkPid_badDoktypeList . ')');
			if ($error = $GLOBALS['TYPO3_DB']->sql_error()) {
				$GLOBALS['TT']->setTSlogMessage($error . ': ' . $GLOBALS['TYPO3_DB']->debug_lastBuiltQuery, 3);
			} else {
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
					$outArr[] = $row['uid'];
				}
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($res);
		}
		return $outArr;
	}


	/**
	 * @return array
	 */
	static public function getSystemFields () {
		return static::$systemFields;
	}


	/**
	 * Returns an array containing the regular field names.
	 *
	 * @return array
	 */
	static public function getFields ($table, $prefix = false) {
		$result = false;

		if (is_array($GLOBALS['TCA'][$table]['columns'])) {
			$tcaFields = array_keys($GLOBALS['TCA'][$table]['columns']);
			$systemFields = static::getSystemFields();
			$result = array_diff($tcaFields, $systemFields);
			if ($prefix) {
				$prefixArray = array();
				foreach ($result as $key => $value) {
					$prefixArray[] = $table . '.' . $value;
				}
				$result = $prefixArray;
			}
		}

		return $result;
	}


	/**
	 * Returns informations about the table and foreign table
	 * This is used by various tables.
	 *
	 * @param	string		name of the table
	 * @param	string		field of the table
	 *
	 * @return	array		infos about the table and foreign table:
					table         ... name of the table
					foreign_table ... name of the foreign table
					foreign_table_field ... name of the field which contains the table name of the first table
					mmtable       ... name of the mm table
					foreign_field ... name of the field in the mm table which joins with
					                  the foreign table
	 * @access	public
	 *
	 */
	static public function getForeignTableInfo ($tablename, $fieldname) {
		$result = array();
		if (
			$tablename != '' &&
			$fieldname != '' &&
			isset($GLOBALS['TCA'][$tablename]['columns'][$fieldname]) &&
			isset($GLOBALS['TCA'][$tablename]['columns'][$fieldname]['config'])
		) {
			$tableConf = $GLOBALS['TCA'][$tablename]['columns'][$fieldname]['config'];
			$localFieldname = '';
			$foreignFieldname = '';
			$foreignTableFieldname = '';
			$foreignTable = '';
			$mmTablename = '';
			$mmTableConf = '';

			$type = $tableConf['type'];
			if ($type == 'group') {
				$type = 'select';
			}

			if ($type == 'inline') {
				$mmTablename = $tableConf['foreign_table'];
				$localFieldname = $tableConf['foreign_field'];
				$foreignFieldname = $tableConf['foreign_selector'];
				$foreignTableFieldname = $tableConf['foreign_table_field'];
			} else if (
				$type == 'select' &&
				isset($tableConf['MM'])
			) {
				$mmTablename = $tableConf['MM'];
				$localFieldname = 'uid_local';
				$foreignFieldname = 'uid_foreign';
			}

			if ($foreignFieldname != '') {
				$mmTableConf = $GLOBALS['TCA'][$mmTablename]['columns'][$foreignFieldname]['config'];
			}

			if ($type == 'inline' && is_array($mmTableConf)) {
				$foreignTable = $mmTableConf['foreign_table'];
			} else if ($type == 'select') {
				$foreignTable = $tableConf['foreign_table'];
			}

			$result['table'] = $tablename;
			$result['foreign_table'] = $foreignTable;
			$result['foreign_table_field'] = $foreignTableFieldname;
			$result['mmtable'] = $mmTablename;
			$result['local_field'] = $localFieldname;
			$result['foreign_field'] = $foreignFieldname;
		}
		return $result;
	}
}

