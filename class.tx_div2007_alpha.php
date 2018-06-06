<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2016 Kasper Skårhøj (kasperYYYY@typo3.com)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
// *
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
 * deprecated
 *
 * Collection of static functions to work in cooperation with the extension lib (lib/div)
 *
 * @package    TYPO3
 * @subpackage div2007
 * @author     Kasper Skårhøj <kasperYYYY@typo3.com>
 * @author     Elmar Hinz <elmar.hinz@team-red.net>
 * @author     Franz Holzinger <franz@ttproducts.de>
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @since      0.1
 */

/**
 * Deprecated
 *
 * Collection of static functions contributed by different people
 *
 * This class contains diverse staticfunctions in "alpha" status.
 * It is a kind of quarantine for newly suggested functions.
 *
 * The class offers the possibilty to quickly add new functions to div2007,
 * without much planning before. In a second step the functions will be reviewed,
 * adapted and fully implemented into the system of div2007 classes.
 *
 * @package    TYPO3
 * @subpackage div2007
 * @author     different Members of the Extension Coordination Team
 */

class tx_div2007_alpha {

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
					mmtable       ... name of the mm table
					foreign_field ... name of the field in the mm table which joins with
					                  the foreign table
	 * @access	public
	 *
	 */
	function getForeignTableInfo_fh002 ($tablename, $fieldname) {
		global $TCA, $TYPO3_DB;

		$rc = array();
		if ($fieldname != '') {
			$tableConf = $TCA[$tablename]['columns'][$fieldname]['config'];

			if ($tableConf['type'] == 'inline') {
				$mmTablename = $tableConf['foreign_table'];
				$foreignFieldname = $tableConf['foreign_selector'];
			} else if ($tableConf['type'] == 'select' && isset($tableConf['MM'])) {
				$mmTablename = $tableConf['MM'];
				$foreignFieldname = 'uid_foreign';
			}

			$mmTableConf = $TCA[$mmTablename]['columns'][$foreignFieldname]['config'];

			if ($tableConf['type'] == 'inline') {
				$foreignTable = $mmTableConf['foreign_table'];
			} else if ($tableConf['type'] == 'select') {
				$foreignTable = $tableConf['foreign_table'];
			}

			$rc['table'] = $tablename;
			$rc['foreign_table'] = $foreignTable;
			$rc['mmtable'] = $mmTablename;
			$rc['foreign_field'] = $foreignFieldname;
		}
		return $rc;
	}


	/**
	 * Returns informations about the table and foreign table
	 * This is used by IRRE compatible tables.
	 *
	 * @param	string		name of the table
	 * @param	string		field of the table
	 * @param	string		reference to the mm table
	 * @param	string		reference to the foreign field
	 * @param	string		reference to the foreign selector
	 * @param	string		field of the table
	 * @param	string		field of the table
	 *
	 * @return	void
	 * @access	public
	 *
	 */
	function getTablenames_fh001 ($theTable, $field, &$foreignMMtable, &$foreignField, &$foreignSelector, &$foreignTable, $bIsMMRelation = TRUE) {
		global $TCA;

		$foreignMMtable = $TCA[$theTable]['columns'][$field]['config']['foreign_table'];
		$foreignField = $TCA[$theTable]['columns'][$field]['config']['foreign_field'];
		$foreignSelector = $TCA[$theTable]['columns'][$field]['config']['foreign_selector'];
		$foreignTable = $TCA[$foreignMMtable]['columns'][$foreignSelector]['config']['foreign_table'];

		if ($bIsMMRelation && (!$foreignMMtable || !$foreignField || !$foreignSelector || !$foreignTable)) {
			die ('internal error: no #2 TCA tables for field \'' . $field . '\' of table \'' . $theTable . '\' are missing.  $foreignMMtable=' . $foreignMMtable . '  $foreignField=' . $foreignField . '  $foreignSelector=' . $foreignSelector . '  $foreignTable=' . $foreignTable);
		}
	}


	/**
	 * Returns the field which forms the relation between the local table and the foreign table
	 * Only the first field will be returned even if there is more than one field.
	 *
	 * @param	string		name of the local table
	 * @param	string		name of the foreign table
	 *
	 * @return	string		field of the table
	 * @access	public
	 *
	 */
	function getLocalTableField_fh001 ($theTable, $foreignTable) {
		global $TCA;

		$rc = '';
		if (!isset($TCA[$theTable]['columns'])) {
			\TYPO3\CMS\Core\Utility\GeneralUtility::loadTCA($theTable);
			\TYPO3\CMS\Core\Utility\GeneralUtility::loadTCA($foreignTable);
		}

		if (isset($TCA[$theTable]['columns']) && is_array($TCA[$theTable]['columns'])) {

			foreach ($TCA[$theTable]['columns'] as $field => $ConfigArray) {

				if ($TCA[$theTable]['columns'][$field]['config']['foreign_table'] == $foreignTable)	{
					$rc = $field;
					break;
				}
			}
		}
		return $rc;
	}


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
	function foreign_table_where_query ($fieldValue, $field = '', $TSconfig = array(), $prefix = '') {
		global $TCA;

		$foreign_table = $fieldValue['config'][$prefix . 'foreign_table'];
		\TYPO3\CMS\Core\Utility\GeneralUtility::loadTCA($foreign_table);
		$rootLevel = $TCA[$foreign_table]['ctrl']['rootLevel'];

		$fTWHERE = $fieldValue['config'][$prefix . 'foreign_table_where'];

		if (strstr($fTWHERE, '###REC_FIELD_')) {
			$fTWHERE_parts = explode('###REC_FIELD_', $fTWHERE);
			while(list($kk, $vv) = each($fTWHERE_parts)) {
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


	/**
	 * Returns the help page with a mini guide how to setup the extension
	 *
	 * example:
	 * 	$content .= tx_fhlibrary_view::displayHelpPage($this->cObj->fileResource('EXT:'.TT_PRODUCTS_EXTkey.'/template/products_help.tmpl'));
	 * 	unset($this->errorMessage);
	 *
	 * @param	object		tx_div2007_alpha_language_base
	 * @param	string		path and filename of the template file
	 *
	 * @return	string		HTML to display the help page
	 * @access	public
	 * deprecated, use displayHelpPage_fh002 instead
	 */
	function displayHelpPage_fh001 ($langObj, $helpTemplate, $extKey, $errorMessage = '', $theCode = '') {
			// Get language version
		$helpTemplate_lang = '';
		if ($langObj->LLkey) {
			$helpTemplate_lang = $this->cObj->getSubpart($helpTemplate, '###TEMPLATE_' . $langObj->LLkey . '###');
		}

		$helpTemplate = $helpTemplate_lang ? $helpTemplate_lang : $this->cObj->getSubpart($helpTemplate,'###TEMPLATE_DEFAULT###');
			// Markers and substitution:
		$markerArray['###PATH###'] = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath($extKey);
		$markerArray['###ERROR_MESSAGE###'] = ($errorMessage ? '<b>' . $errorMessage . '</b><br/>' : '');
		$markerArray['###CODE###'] = $theCode;
		$rc = $langObj->cObj->substituteMarkerArray($helpTemplate, $markerArray);
		return $rc;
	}


	/**
	 * Returns the help page with a mini guide how to setup the extension
	 *
	 * example:
	 * 	$content .= tx_fhlibrary_view::displayHelpPage($this->cObj->fileResource('EXT:'.TT_PRODUCTS_EXTkey.'/template/products_help.tmpl'));
	 * 	unset($this->errorMessage);
	 *
	 * @param	object		tx_div2007_alpha_language_base
	 * @param	object		cObj
	 * @param	string		HTML template content
	 * @param	string		extension key
	 * @param	string		error message for the marker ###ERROR_MESSAGE###
	 * @param	string		CODE of plugin
	 *
	 * @return	string		HTML to display the help page
	 * @access	public
	 *
	 */
	function displayHelpPage_fh002 ($langObj, $cObj, $helpTemplate, $extKey, $errorMessage = '', $theCode = '') {
			// Get language version
		$helpTemplate_lang='';
		if ($langObj->LLkey) {
			$helpTemplate_lang = $cObj->getSubpart($helpTemplate, '###TEMPLATE_' . $langObj->LLkey . '###');
		}

		$helpTemplate = $helpTemplate_lang ? $helpTemplate_lang : $cObj->getSubpart($helpTemplate,'###TEMPLATE_DEFAULT###');
			// Markers and substitution:

		$markerArray['###PATH###'] = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath($extKey);
		$markerArray['###ERROR_MESSAGE###'] = ($errorMessage ? '<b>' . $errorMessage . '</b><br/>' : '');
		$markerArray['###CODE###'] = $theCode;
		$rc = $cObj->substituteMarkerArray($helpTemplate, $markerArray);
		return $rc;
	}


	/* loadTcaAdditions($ext_keys)
	*
	* Your extension may depend on fields that are added by other
	* extensios. For reasons of performance parts of the TCA are only
	* loaded on demand. To ensure that the extended TCA is loaded for
	* the extensions you depend on or which extend your extension by
	* hooks, you shall apply this function.
	*
	* @param array     extension keys which have TCA additions to load
	*/
	function loadTcaAdditions_fh001 ($ext_keys) {
		global $_EXTKEY, $TCA;

		$loadTcaAdditions = TRUE;

		//Merge all ext_keys
		if (is_array($ext_keys)) {

			foreach ($ext_keys as $_EXTKEY) {

				if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded($_EXTKEY)) {
					//Include the ext_table
					include_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'ext_tables.php');
				}
			}
		}

			// ext-script
		if (TYPO3_extTableDef_script) {
			include (PATH_typo3conf . TYPO3_extTableDef_script);
		}
	}


	/**
	 * Gets information for an extension, eg. version and most-recently-edited-script
	 *
	 * @param	string		Extension key
	 * @return	array		Information array (unless an error occured)
	 */
	function getExtensionInfo_fh001 ($extKey) {
		$rc = '';

		if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded($extKey)) {
			$path = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($extKey);

			$file = $path.'/ext_emconf.php';
			if (@is_file($file)) {
				$_EXTKEY = $extKey;
				$EM_CONF = array();
				include($file);

				$eInfo = array();
					// Info from emconf:
				$eInfo['title'] = $EM_CONF[$extKey]['title'];
				$eInfo['author'] = $EM_CONF[$extKey]['author'];
				$eInfo['author_email'] = $EM_CONF[$extKey]['author_email'];
				$eInfo['author_company'] = $EM_CONF[$extKey]['author_company'];
				$eInfo['version'] = $EM_CONF[$extKey]['version'];
				$eInfo['CGLcompliance'] = $EM_CONF[$extKey]['CGLcompliance'];
				$eInfo['CGLcompliance_note'] = $EM_CONF[$extKey]['CGLcompliance_note'];
				if (is_array($EM_CONF[$extKey]['constraints']) && is_array($EM_CONF[$extKey]['constraints']['depends'])) {
					$eInfo['TYPO3_version'] = $EM_CONF[$extKey]['constraints']['depends']['typo3'];
				} else {
					$eInfo['TYPO3_version'] = $EM_CONF[$extKey]['TYPO3_version'];
				}
				$filesHash = unserialize($EM_CONF[$extKey]['_md5_values_when_last_written']);
				$eInfo['manual'] = @is_file($path . '/doc/manual.sxw');
				$rc = $eInfo;
			} else {
				$rc = 'ERROR: No emconf.php file: ' . $file;
			}
		} else {
			$rc = 'Error: Extension ' . $extKey . ' has not been installed. (tx_fhlibrary_system::getExtensionInfo)';
		}

		return $rc;
	}


	/**
	 * Gets information for an extension, eg. version and most-recently-edited-script
	 *
	 * @param	string		Extension key
	 * @param	string		predefined path ... needed if you have the extension in another place
	 * @return	array		Information array (unless an error occured)
	 */
	function getExtensionInfo_fh002 ($extKey, $path = '') {
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
				$result = 'ERROR: No emconf.php file: ' . $file;
			}
		} else {
			$result = 'ERROR: Path not found: ' . $path;
		}

		return $result;
	}


	/**
	 * This is the original pi_getLL from tslib_pibase
	 * Returns the localized label of the LOCAL_LANG key, $key
	 * Notice that for debugging purposes prefixes for the output values can be set with the internal vars ->LLtestPrefixAlt and ->LLtestPrefix
	 *
	 * @param	object		tx_div2007_alpha_language_base object
	 * @param	string		The key from the LOCAL_LANG array for which to return the value.
	 * @param	string		Alternative string to return IF no value is found set for the key, neither for the local language nor the default.
	 * @param	boolean		If true, the output label is passed through htmlspecialchars()
	 * @return	string		The value from LOCAL_LANG.
	 */
	function getLL (&$langObj, $key, $alt = '', $hsc = FALSE) {

		if (is_object($langObj)) {
			if (isset($langObj->LOCAL_LANG[$langObj->LLkey][$key])) {
				$word = $GLOBALS['TSFE']->csConv($langObj->LOCAL_LANG[$langObj->LLkey][$key], $langObj->LOCAL_LANG_charset[$langObj->LLkey][$key]);	// The "from" charset is normally empty and thus it will convert from the charset of the system language, but if it is set (see ->pi_loadLL()) it will be used.
			} elseif ($langObj->altLLkey && isset($langObj->LOCAL_LANG[$langObj->altLLkey][$key])) {
				$word = $GLOBALS['TSFE']->csConv($langObj->LOCAL_LANG[$langObj->altLLkey][$key], $langObj->LOCAL_LANG_charset[$langObj->altLLkey][$key]);	// The "from" charset is normally empty and thus it will convert from the charset of the system language, but if it is set (see ->pi_loadLL()) it will be used.
			} elseif (isset($langObj->LOCAL_LANG['default'][$key])) {
				$word = $langObj->LOCAL_LANG['default'][$key];	// No charset conversion because default is english and thereby ASCII
			} else {
				$word = $langObj->LLtestPrefixAlt . $alt;
			}
			$output = $langObj->LLtestPrefix . $word;
			if ($hsc)	$output = htmlspecialchars($output);
		} else {
			$output = 'error in call of tx_div2007_alpha::getLL: parameter $langObj is not an object';
			debug ($output, '$output'); // keep this
		}

		return $output;
	}


	/**
	 * This is a variant of the original pi_getLL from tslib_pibase
	 * Returns the localized label of the LOCAL_LANG key, $key
	 * Notice that for debugging purposes prefixes for the output values can be set with the internal vars ->LLtestPrefixAlt and ->LLtestPrefix
	 *
	 * @param	object		tx_div2007_alpha_language_base object
	 * @param	string		output: the used language
	 * @param	string		The key from the LOCAL_LANG array for which to return the value.
	 * @param	string		Alternative string to return IF no value is found set for the key, neither for the local language nor the default.
	 * @param	boolean		If true, the output label is passed through htmlspecialchars()
	 * @return	string		The value from LOCAL_LANG.
	 */
	function getLL_fh001 (&$langObj, &$usedLang, $key, $alt = '', $hsc = FALSE) {

		if (isset($langObj->LOCAL_LANG[$langObj->LLkey][$key])) {
			$usedLang = $langObj->LLkey;
			$word = $GLOBALS['TSFE']->csConv($langObj->LOCAL_LANG[$usedLang][$key], $langObj->LOCAL_LANG_charset[$usedLang][$key]);	// The "from" charset is normally empty and thus it will convert from the charset of the system language, but if it is set (see ->pi_loadLL()) it will be used.
		} elseif ($langObj->altLLkey && isset($langObj->LOCAL_LANG[$langObj->altLLkey][$key])) {
			$usedLang = $langObj->altLLkey;
			$word = $GLOBALS['TSFE']->csConv($langObj->LOCAL_LANG[$usedLang][$key], $langObj->LOCAL_LANG_charset[$usedLang][$key]);	// The "from" charset is normally empty and thus it will convert from the charset of the system language, but if it is set (see ->pi_loadLL()) it will be used.
		} elseif (isset($langObj->LOCAL_LANG['default'][$key])) {
			$usedLang = 'default';
			$word = $langObj->LOCAL_LANG[$usedLang][$key];	// No charset conversion because default is English and thereby ASCII
		} else {
			$word = $langObj->LLtestPrefixAlt . $alt;
		}
		$output = $langObj->LLtestPrefix . $word;
		if ($hsc) {
			$output = htmlspecialchars($output);
		}

		return $output;
	}


	/**
	 * Loads local-language values by looking for a "locallang.php" file in the plugin class directory ($this->scriptRelPath) and if found includes it.
	 * Also locallang values set in the TypoScript property "_LOCAL_LANG" are merged onto the values found in the "locallang.php" file.
	 * Allows to add a language file name like this: 'EXT:tt_products/locallang_db.xml'
	 *
	 * @param	object		tx_div2007_alpha_language_base object
	 * @param	string		relative path and filename of the language file
	 * @param	boolean		overwrite ... if current settings should be overwritten
	 *
	 * @return	void
	 */
	function loadLL_fh001 (&$langObj, $langFileParam, $overwrite = TRUE) {
		global $TSFE;

		if (is_object($langObj)) {
			$langFile = ($langFileParam ? $langFileParam : 'locallang.php');

			if (substr($langFile, 0, 4) === 'EXT:' || substr($langFile, 0, 5) === 'typo3' || substr($langFile, 0, 9) === 'fileadmin') {
				$basePath = $langFile;
			} else {
				$basePath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($langObj->extKey) . ($langObj->scriptRelPath ? dirname($langObj->scriptRelPath) . '/' : '') . $langFile;
			}
				// php or xml as source: In any case the charset will be that of the system language.
				// However, this function guarantees only return output for default language plus the specified language (which is different from how 3.7.0 dealt with it)
			$tempLOCAL_LANG = \TYPO3\CMS\Core\Utility\GeneralUtility::readLLfile($basePath, $langObj->LLkey, 'UTF-8');

			if (count($langObj->LOCAL_LANG) && is_array($tempLOCAL_LANG)) {
				foreach ($langObj->LOCAL_LANG as $langKey => $tempArray) {
					if (is_array($tempLOCAL_LANG[$langKey])) {
						if ($overwrite) {
							$langObj->LOCAL_LANG[$langKey] = array_merge($langObj->LOCAL_LANG[$langKey],$tempLOCAL_LANG[$langKey]);
						} else {
							$langObj->LOCAL_LANG[$langKey] = array_merge($tempLOCAL_LANG[$langKey], $langObj->LOCAL_LANG[$langKey]);
						}
					}
				}
			} else {
				$langObj->LOCAL_LANG = $tempLOCAL_LANG;
			}
			if ($langObj->altLLkey) {
				$tempLOCAL_LANG = \TYPO3\CMS\Core\Utility\GeneralUtility::readLLfile($basePath, $langObj->altLLkey, 'UTF-8');

				if (count($langObj->LOCAL_LANG) && is_array($tempLOCAL_LANG)) {
					foreach ($langObj->LOCAL_LANG as $langKey => $tempArray) {
						if (is_array($tempLOCAL_LANG[$langKey])) {
							if ($overwrite) {
								$langObj->LOCAL_LANG[$langKey] = array_merge($langObj->LOCAL_LANG[$langKey],$tempLOCAL_LANG[$langKey]);
							} else {
								$langObj->LOCAL_LANG[$langKey] = array_merge($tempLOCAL_LANG[$langKey],$langObj->LOCAL_LANG[$langKey]);
							}
						}
					}
				} else {
					$langObj->LOCAL_LANG = $tempLOCAL_LANG;
				}
			}

				// Overlaying labels from TypoScript (including fictious language keys for non-system languages!):
			if (is_array($langObj->conf['_LOCAL_LANG.'])) {

				foreach($langObj->conf['_LOCAL_LANG.'] as $k => $lA) {
					if (is_array($lA)) {
						$k = substr($k,0,-1);
						foreach($lA as $llK => $llV) {
							if (is_array($llV)) {
								foreach ($llV as $llk2 => $llV2) {
									if (is_array($llV2)) {
										foreach ($llV2 as $llk3 => $llV3) {
											if (is_array($llV3)) {
												foreach ($llV3 as $llk4 => $llV4) {
														if (is_array($llV4)) {
														} else {
														$langObj->LOCAL_LANG[$k][$llK.$llk2.$llk3.$llk4] = $llV4;
														if ($k != 'default') {
															$langObj->LOCAL_LANG_charset[$k][$llK.$llk2.$llk3.$llk4] = $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'];	// For labels coming from the TypoScript (database) the charset is assumed to be "forceCharset" and if that is not set, assumed to be that of the individual system languages (thus no conversion)
														}
													}
												}
											} else {
												$langObj->LOCAL_LANG[$k][$llK.$llk2.$llk3] = $llV3;
												if ($k != 'default') {
													$langObj->LOCAL_LANG_charset[$k][$llK.$llk2.$llk3] = $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'];	// For labels coming from the TypoScript (database) the charset is assumed to be "forceCharset" and if that is not set, assumed to be that of the individual system languages (thus no conversion)
												}
											}
										}
									} else {
										$langObj->LOCAL_LANG[$k][$llK.$llk2] = $llV2;
										if ($k != 'default') {
											$langObj->LOCAL_LANG_charset[$k][$llK.$llk2] = $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'];	// For labels coming from the TypoScript (database) the charset is assumed to be "forceCharset" and if that is not set, assumed to be that of the individual system languages (thus no conversion)
										}
									}
								}
							} else {
								$langObj->LOCAL_LANG[$k][$llK] = $llV;
								if ($k != 'default') {
									$langObj->LOCAL_LANG_charset[$k][$llK] = $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'];	// For labels coming from the TypoScript (database) the charset is assumed to be "forceCharset" and if that is not set, assumed to be that of the individual system languages (thus no conversion)
								}
							}
						}
					}
				}
			}
		} else {
			$output = 'error in call of tx_div2007_alpha::loadLL_fh001: parameter $langObj is not an object';
			debug ($output, '$output'); // keep this
		}
	}


	/**
	 * Split Label function for front-end applications.
	 *
	 * @param	string		Key string. Accepts the "LLL:" prefix.
	 * @return	string		Label value, if any.
	 */
	function sL_fh001 ($input) {
		$restStr = trim(substr($input, 4));
		$extPrfx = '';
		if (!strcmp(substr($restStr, 0, 4), 'EXT:')) {
			$restStr = trim(substr($restStr, 4));
			$extPrfx = 'EXT:';
		}
		$parts = explode(':', $restStr);
		return ($parts[1]);
	}


	/**
	 * Returns the values from the setup field or the field of the flexform converted into the value
	 * The default value will be used if no return value would be available.
	 * This can be used fine to get the CODE values or the display mode dependant if flexforms are used or not.
	 * And all others fields of the flexforms can be read.
	 *
	 * example:
	 * 	$config['code'] = tx_fhlibrary_flexform::getSetupOrFFvalue(
	 * 					$this->conf['code'],
	 * 					$this->conf['code.'],
	 * 					$this->conf['defaultCode'],
	 * 					$this->cObj->data['pi_flexform'],
	 * 					'display_mode',
	 * 					$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][TT_PRODUCTS_EXTkey]['useFlexforms']);
	 *
	 * You have to call $this->pi_initPIflexForm(); before you call this method!
	 * @param	object		tx_div2007_alpha_language_base object
	 * @param	string		TypoScript configuration
	 * @param	string		extended TypoScript configuration
	 * @param	string		default value to use if the result would be empty
	 * @param	boolean		if flexforms are used or not
	 * @param	string		name of the flexform which has been used in ext_tables.php
	 * 						$TCA['tt_content']['types']['list']['subtypes_addlist']['5']='pi_flexform';
	 * @return	string		name of the field to look for in the flexform
	 * @access	public
	 *
	 * @see fhlibrary_pibase::pi_getSetupOrFFvalue
	 */
	function getSetupOrFFvalue_fh001 (
		$langObj,
		$code,
		$codeExt,
		$defaultCode,
		$T3FlexForm_array,
		$fieldName = 'display_mode',
		$useFlexforms = 1,
		$sheet = 'sDEF',
		$lang = 'lDEF',
		$value = 'vDEF'
	) {
		$rc = '';
		if (is_object($langObj)) {
			if (empty($code)) {
				if ($useFlexforms) {
					// Converting flexform data into array:
					$rc = $langObj->pi_getFFvalue($T3FlexForm_array, $fieldName, $sheet, $lang, $value);
				} else {
					$rc = strtoupper(trim($langObj->cObj->stdWrap($code, $codeExt)));
				}
				if (empty($rc)) {
					$rc = strtoupper($defaultCode);
				}
			} else {
				$rc = $code;
			}
		} else {
			$rc = 'error in call of tx_div2007_alpha::getSetupOrFFvalue_fh001: parameter $langObj is not an object';
			debug ($rc, '$rc'); // keep this
		}
		return $rc;
	}


	/**
	 * Returns the values from the setup field or the field of the flexform converted into the value
	 * The default value will be used if no return value would be available.
	 * This can be used fine to get the CODE values or the display mode dependant if flexforms are used or not.
	 * And all others fields of the flexforms can be read.
	 *
	 * example:
	 * 	$config['code'] = tx_div2007_alpha::getSetupOrFFvalue_fh002(
	 *					$langObj,
	 * 					$this->conf['code'],
	 * 					$this->conf['code.'],
	 * 					$this->conf['defaultCode'],
	 * 					$this->cObj->data['pi_flexform'],
	 * 					'display_mode',
	 * 					$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][TT_PRODUCTS_EXTkey]['useFlexforms']);
	 *
	 * You have to call $this->pi_initPIflexForm(); before you call this method!
	 * @param	object		tx_div2007_alpha_language_base object
	 * @param	string		TypoScript configuration
	 * @param	string		extended TypoScript configuration
	 * @param	string		default value to use if the result would be empty
	 * @param	boolean		if flexforms are used or not
	 * @param	string		name of the flexform which has been used in ext_tables.php
	 * 						$TCA['tt_content']['types']['list']['subtypes_addlist']['5']='pi_flexform';
	 * @return	string		name of the field to look for in the flexform
	 * @access	public
	 *
	 */
	function getSetupOrFFvalue_fh002 (
		$langObj,
		$code,
		$codeExt,
		$defaultCode,
		$T3FlexForm_array,
		$fieldName='display_mode',
		$bUseFlexforms = TRUE,
		$sheet = 'sDEF',
		$lang = 'lDEF',
		$value = 'vDEF'
	) {
		$rc = '';
		if (is_object($langObj)) {
			if (empty($code)) {
				if ($bUseFlexforms) {
					include_once(PATH_BE_div2007 . 'class.tx_div2007_ff.php');

					// Converting flexform data into array:
					$rc = tx_div2007_ff::get($T3FlexForm_array, $fieldName, $sheet, $lang, $value);
				} else {
					$rc = strtoupper(trim($langObj->cObj->stdWrap($code, $codeExt)));
				}
				if (empty($rc)) {
					$rc = strtoupper($defaultCode);
				}
			} else {
				$rc = $code;
			}
		} else {
			$rc = 'error in call of tx_div2007_alpha::getSetupOrFFvalue_fh002: parameter $langObj is not an object';
			debug ($rc, '$rc'); // keep this
		}
		return $rc;
	}


	/**
	 * Returns the values from the setup field or the field of the flexform converted into the value
	 * The default value will be used if no return value would be available.
	 * This can be used fine to get the CODE values or the display mode dependant if flexforms are used or not.
	 * And all others fields of the flexforms can be read.
	 *
	 * example:
	 * 	$config['code'] = tx_div2007_alpha::getSetupOrFFvalue_fh003(
	 *					$cObj,
	 * 					$this->conf['code'],
	 * 					$this->conf['code.'],
	 * 					$this->conf['defaultCode'],
	 * 					$this->cObj->data['pi_flexform'],
	 * 					'display_mode',
	 * 					$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][TT_PRODUCTS_EXTkey]['useFlexforms']);
	 *
	 * You have to call $this->pi_initPIflexForm(); before you call this method!
	 * @param	object		tx_div2007_alpha_language_base object
	 * @param	string		TypoScript configuration
	 * @param	string		extended TypoScript configuration
	 * @param	string		default value to use if the result would be empty
	 * @param	boolean		if flexforms are used or not
	 * @param	string		name of the flexform which has been used in ext_tables.php
	 * 						$TCA['tt_content']['types']['list']['subtypes_addlist']['5']='pi_flexform';
	 * @return	string		name of the field to look for in the flexform
	 * @access	public
	 *
	 */
	function getSetupOrFFvalue_fh003 (
		$cObj,
		$code,
		$codeExt,
		$defaultCode,
		$T3FlexForm_array,
		$fieldName = 'display_mode',
		$bUseFlexforms = TRUE,
		$sheet = 'sDEF',
		$lang = 'lDEF',
		$value = 'vDEF'
	) {
		$rc = '';
		if (is_object($cObj)) {
			if (empty($code)) {
				if ($bUseFlexforms) {
					// Converting flexform data into array:
					$rc = tx_div2007_ff::get($T3FlexForm_array, $fieldName, $sheet, $lang, $value);
				} else {
					$rc = strtoupper(trim($cObj->stdWrap($code, $codeExt)));
				}
				if (empty($rc)) {
					$rc = strtoupper($defaultCode);
				}
			} else {
				$rc = $code;
			}
		} else {
			$rc = 'error in call of tx_div2007_alpha::getSetupOrFFvalue_fh003: parameter $cObj is not an object';
			debug ($rc, '$rc'); // keep this
		}
		return $rc;
	}


	/**
	 * Returns a linked string made from typoLink parameters.
	 *
	 * This function takes $label as a string, wraps it in a link-tag based on the $params string, which should contain data like that you would normally pass to the popular <LINK>-tag in the TSFE.
	 * Optionally you can supply $urlParameters which is an array with key/value pairs that are rawurlencoded and appended to the resulting url.
	 *
	 * @param	object		tx_div2007_alpha_language_base object
	 * @param	string		Text string being wrapped by the link.
	 * @param	string		Link parameter; eg. "123" for page id, "kasperYYYY@typo3.com" for email address, "http://...." for URL, "fileadmin/blabla.txt" for file.
	 * @param	array		An array with key/value pairs representing URL parameters to set. Values NOT URL-encoded yet.
	 * @param	string		Specific target set, if any. (Default is using the current)
	 * @param	array		Configuration
 	 * @return	string		The wrapped $label-text string
	 * @see getTypoLink_URL()
	 */
	function getTypoLink_fh001 (
		$langObj,
		$label,
		$params,
		$urlParameters = array(),
		$target = '',
		$conf = array()
	) {
		if (is_object($langObj)) {
			$conf['parameter'] = $params;
			if ($target) {
				$conf['target']=$target;
				$conf['extTarget']=$target;
			}
			if (is_array($urlParameters)) {
				if (count($urlParameters)) {
					$conf['additionalParams'].= \TYPO3\CMS\Core\Utility\GeneralUtility::implodeArrayForUrl('', $urlParameters);
				}
			} else {
				$conf['additionalParams'] .= $urlParameters;
			}
			$out = $langObj->cObj->typolink($label,$conf);
		} else {
			$out = 'error in call of tx_div2007_alpha::getTypoLink_fh001: parameter $langObj is not an object';
			debug ($out, '$out'); // keep this
		}
		return $out;
	}


	/**
	 * Returns a linked string made from typoLink parameters.
	 *
	 * This function takes $label as a string, wraps it in a link-tag based on the $params string, which should contain data like that you would normally pass to the popular <LINK>-tag in the TSFE.
	 * Optionally you can supply $urlParameters which is an array with key/value pairs that are rawurlencoded and appended to the resulting url.
	 *
	 * @param	object		cObject
	 * @param	string		Text string being wrapped by the link.
	 * @param	string		Link parameter; eg. "123" for page id, "kasperYYYY@typo3.com" for email address, "http://...." for URL, "fileadmin/blabla.txt" for file.
	 * @param	array		An array with key/value pairs representing URL parameters to set. Values NOT URL-encoded yet.
	 * @param	string		Specific target set, if any. (Default is using the current)
	 * @param	array		Configuration
 	 * @return	string		The wrapped $label-text string
	 * @see getTypoLink_URL()
	 */
	function getTypoLink_fh002 (
		$cObj,
		$label,
		$params,
		$urlParameters = array(),
		$target = '',
		$conf = array()
	)	{
		if (is_object($cObj)) {
			$rc = FALSE;
			$conf['parameter'] = $params;
			if ($target) {
				$conf['target'] = $target;
				$conf['extTarget'] = $target;
			}
			if (is_array($urlParameters)) {
				if (count($urlParameters)) {
					$conf['additionalParams'] .= \TYPO3\CMS\Core\Utility\GeneralUtility::implodeArrayForUrl('', $urlParameters);
				}
			} else {
				$conf['additionalParams'] .= $urlParameters;
			}
			if (is_object($cObj)) {
				$rc = $cObj->typolink($label, $conf);
			}
		} else {
			$out = 'error in call of tx_div2007_alpha::getTypoLink_fh002: parameter $cObj is not an object';
			debug ($out, '$out'); // keep this
		}
		return $rc;
	}


	/**
	 * Returns the URL of a "typolink" create from the input parameter string, url-parameters and target
	 *
	 * @param	object		tx_div2007_alpha_language_base object
	 * @param	string		Link parameter; eg. "123" for page id, "kasperYYYY@typo3.com" for email address, "http://...." for URL, "fileadmin/blabla.txt" for file.
	 * @param	array		An array with key/value pairs representing URL parameters to set. Values NOT URL-encoded yet.
	 * @param	string		Specific target set, if any. (Default is using the current)
	 * @param	array		Configuration
	 * @return	string		The URL
	 * @see getTypoLink()
	 */
	function getTypoLink_URL_fh001 (
		$langObj,
		$params,
		$urlParameters = array(),
		$target = '',
		$conf = array()
	) {
		tx_div2007_alpha::getTypoLink_fh001(
			$langObj,
			'',
			$params,
			$urlParameters,
			$target,
			$conf
		);
		$rc = $langObj->cObj->lastTypoLinkUrl;
		return $rc;
	}


	/**
	 * Returns the URL of a "typolink" create from the input parameter string, url-parameters and target
	 *
	 * @param	object		cObject
	 * @param	string		Link parameter; eg. "123" for page id, "kasperYYYY@typo3.com" for email address, "http://...." for URL, "fileadmin/blabla.txt" for file.
	 * @param	array		An array with key/value pairs representing URL parameters to set. Values NOT URL-encoded yet.
	 * @param	string		Specific target set, if any. (Default is using the current)
	 * @param	array		Configuration
	 * @return	string		The URL
	 * @see getTypoLink()
	 */
	function getTypoLink_URL_fh002 (
		$cObj,
		$params,
		$urlParameters = array(),
		$target = '',
		$conf = array()
	) {
		$rc = FALSE;
		if (is_object($cObj)) {
			$out = tx_div2007_alpha::getTypoLink_fh002(
				$cObj,
				'',
				$params,
				$urlParameters,
				$target,
				$conf
			);
			$rc = $cObj->lastTypoLinkUrl;
		}
		return $rc;
	}


	/***************************
	 *
	 * Link functions
	 *
	 **************************/

	/**
	 * Get URL to some page.
	 * Returns the URL to page $id with $target and an array of additional url-parameters, $urlParameters
	 * Simple example: $this->pi_getPageLink(123) to get the URL for page-id 123.
	 *
	 * The function basically calls $this->cObj->getTypoLink_URL()
	 *
	 * @param	object		tx_div2007_alpha_language_base object
	 * @param	integer		Page id
	 * @param	string		Target value to use. Affects the &type-value of the URL, defaults to current.
	 * @param	array		Additional URL parameters to set (key/value pairs)
	 * @param	array		Configuration
	 * @return	string		The resulting URL
	 * @see pi_linkToPage()
	 */
	function getPageLink_fh001 (
		$langObj,
		$id,
		$target = '',
		$urlParameters = array(),
		$conf = array()
	) {
		$rc = tx_div2007_alpha::getTypoLink_URL_fh001(
			$langObj,
			$id,
			$urlParameters,
			$target,
			$conf
		);
		return $rc;
	}


	/**
	 * Get URL to some page.
	 * Returns the URL to page $id with $target and an array of additional url-parameters, $urlParameters
	 * Simple example: $this->pi_getPageLink(123) to get the URL for page-id 123.
	 *
	 * The function basically calls $this->cObj->getTypoLink_URL()
	 *
	 * @param	object		cObject
	 * @param	integer		Page id
	 * @param	string		Target value to use. Affects the &type-value of the URL, defaults to current.
	 * @param	array		Additional URL parameters to set (key/value pairs)
	 * @param	array		Configuration
	 * @return	string		The resulting URL
	 * @see pi_linkToPage()
	 */
	function getPageLink_fh002 (
		$cObj,
		$id,
		$target = '',
		$urlParameters = array(),
		$conf = array()
	) {
		$rc = tx_div2007_alpha::getTypoLink_URL_fh002(
			$cObj,
			$id,
			$urlParameters,
			$target,
			$conf
		);
		return $rc;
	}


	/**
	 * Wrap content with the plugin code
	 * wraps the content of the plugin before the final output
	 *
	 * @param	string		content
	 * @param	string		CODE of plugin
	 * @param	string		prefix id of the plugin
	 * @param	string		template suffix of the used template subpart marker
	 * @return	string		The resulting content
	 * @see pi_linkToPage()
	 */
	function wrapContentCode_fh001 (
		&$content,
		$theCode,
		$prefixId,
		$templateSuffix
	) {
		$result = '';

		$idNumber = str_replace('_', '-', $prefixId . '-' . strtolower($theCode));
		if ($templateSuffix) {
			$idNumber .= strtolower(str_replace('_', '-', $templateSuffix));
		}
		$result = '<!-- START: ' . $idNumber . ' --><div id="' . $idNumber . '">' . ($content != '' ? $content : '') . '</div><!-- END: ' . $idNumber . ' -->';
		return $result;
	}


	/**
	 * Wrap content with the plugin code
	 * wraps the content of the plugin before the final output
	 *
	 * @param	string		content
	 * @param	string		CODE of plugin
	 * @param	string		prefix id of the plugin
	 * @param	string		content uid
	 * @return	string		The resulting content
	 * @see pi_linkToPage()
	 */
	function wrapContentCode_fh002 (
		&$content,
		$theCode,
		$prefixId,
		$uid
	) {
		$rc = '';

		$idNumber = str_replace('_', '-', $prefixId . '-' . strtolower($theCode));
		$idNumber .= '-' . $uid;
		$rc ='<!-- START: ' . $idNumber . ' --><div id="' . $idNumber . '">' . ($content != '' ? $content : '') . '</div><!-- END: ' . $idNumber . ' -->';
		return $rc;
	}


	/**
	 * Wrap content with the plugin code
	 * wraps the content of the plugin before the final output
	 *
	 * @param	string		content
	 * @param	string		CODE of plugin
	 * @param	string		prefix id of the plugin
	 * @param	string		content uid
	 * @return	string		The resulting content
	 * @see pi_linkToPage()
	 */
	function wrapContentCode_fh003 (
		&$content,
		$theCode,
		$prefixId,
		$uid
	) {
		$rc = '';

		$idNumber = str_replace('_', '-', $prefixId . '-' . strtolower($theCode));
		if ($uid != '')	{
			$idNumber .= '-' . $uid;
		}
		$rc ='<!-- START: ' . $idNumber . ' --><div id="' . $idNumber . '">' .
			($content != '' ? $content : '') . '</div><!-- END: ' . $idNumber . ' -->';
		return $rc;
	}


	/**
	 * Wraps the input string in a <div> tag with the class attribute set to the prefixId.
	 * All content returned from your plugins should be returned through this function so all content from your plugin is encapsulated in a <div>-tag nicely identifying the content of your plugin.
	 *
	 * @param	string		HTML content to wrap in the div-tags with the "main class" of the plugin
	 * @return	string		HTML content wrapped, ready to return to the parent object.
	 * @see pi_wrapInBaseClass()
	 */
	function wrapInBaseClass_fh001 (
		$str,
		$prefixId,
		$extKey
	) {
		$content = '<div class="' . str_replace('_','-',$prefixId) . '">
		' . $str . '
	</div>
	';

		if(!$GLOBALS['TSFE']->config['config']['disablePrefixComment']) {
			$content = '


	<!--

		BEGIN: Content of extension "' . $extKey . '", plugin "' . $prefixId . '"

	-->
	' . $content . '
	<!-- END: Content of extension "' . $extKey . '", plugin "' . $prefixId . '" -->

	';
		}

		return $content;
	}


	/**
	 * Get External CObjects
	 * @param	object		tx_div2007_alpha_language_base object
	 * @param	string		Configuration Key
	 */
	function getExternalCObject_fh001 (
		$pOb,
		$mConfKey
	) {
		if ($pOb->conf[$mConfKey] && $pOb->conf[$mConfKey.'.']) {
			$pOb->cObj->regObj = $pOb;
			return $pOb->cObj->cObjGetSingle($pOb->conf[$mConfKey], $pOb->conf[$mConfKey . '.'], '/' . $mConfKey . '/') . '';
		}
	}


	/**
	 * Get External CObjects
	 * @param	object		tx_div2007_alpha_language_base object
	 * @param	string		Configuration Key
	 */
	function getExternalCObject_fh002 (
		$pOb,
		$mConfKey
	) {
		$result = '';

		if ($pOb->conf[$mConfKey] && $pOb->conf[$mConfKey . '.']) {
			$pOb->cObj->regObj = $pOb;
			$result = $pOb->cObj->cObjGetSingle(
				$pOb->conf[$mConfKey],
				$pOb->conf[$mConfKey . '.'],
				'/' . $mConfKey . '/'
			) .
			'';
		}
		return $result;
	}


	/**
	 * run function from external cObject
	 * @param	object		tx_div2007_alpha_language_base object
	 */
	function load_noLinkExtCobj_fh001 ($langObj) {
		if ($langObj->conf['externalProcessing_final'] || is_array($langObj->conf['externalProcessing_final.']))	{	// If there is given another cObject for the final order confirmation template!
			$langObj->externalCObject = tx_div2007_alpha::getExternalCObject_fh001($langObj, 'externalProcessing_final');
		}
	} // load_noLinkExtCobj


	/**
	* Invokes a user process
	*
	* @param object $pObject: the name of the parent object
	* @param array  $conf:    the base TypoScript setup
	* @param array  $mConfKey: the configuration array of the user process
	* @param array  $passVar: the array of variables to be passed to the user process
	* @return array the updated array of passed variables
	*/
	public function userProcess_fh001 (
		$pObject,
		$conf,
		$mConfKey,
		$passVar
	) {
		if (isset($conf) && is_array($conf) && $conf[$mConfKey]) {
			$funcConf = $conf[$mConfKey . '.'];
			$funcConf['parentObj'] = $pObject;
			$passVar = $GLOBALS['TSFE']->cObj->callUserFunction(
				$conf[$mConfKey],
				$funcConf,
				$passVar
			);
		}
		return $passVar;
	} // userProcess


	/**
	 * This is the original pi_RTEcssText from tslib_pibase
	 * Will process the input string with the parseFunc function from TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer based on configuration set in "lib.parseFunc_RTE" in the current TypoScript template.
	 * This is useful for rendering of content in RTE fields where the transformation mode is set to "ts_css" or so.
	 * Notice that this requires the use of "css_styled_content" to work right.
	 *
	 * @param	object		cOject of class TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
	 * @param	string		The input text string to process
	 * @return	string		The processed string
	 * @see TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::parseFunc()
	 */
	function RTEcssText ($cObj, $str) {
		global $TSFE;

		$parseFunc = $TSFE->tmpl->setup['lib.']['parseFunc_RTE.'];
		if (is_array($parseFunc)) {
			$str = $cObj->parseFunc($str, $parseFunc);
		}
		return $str;
	}


	/**
	 * Returns a class-name prefixed with $prefixId and with all underscores substituted to dashes (-)
	 *
	 * @param	string		The class name (or the END of it since it will be prefixed by $prefixId.'-')
	 * @param	string		$prefixId
	 * @return	string		The combined class name (with the correct prefix)
	 */
	function getClassName ($class, $prefixId = '') {
		return str_replace('_', '-', $prefixId) . ($prefixId ? '-' : '') . $class;
	}


	/**
	 * Returns a results browser. This means a bar of page numbers plus a "previous" and "next" link. For each entry in the bar the piVars "pointer" will be pointing to the "result page" to show.
	 * Using $this->piVars['pointer'] as pointer to the page to display. Can be overwritten with another string ($pointerName) to make it possible to have more than one pagebrowser on a page)
	 * Using $this->internal['res_count'], $this->internal['results_at_a_time'] and $this->internal['maxPages'] for count number, how many results to show and the max number of pages to include in the browse bar.
	 * Using $this->internal['dontLinkActivePage'] as switch if the active (current) page should be displayed as pure text or as a link to itself
	 * Using $this->internal['showFirstLast'] as switch if the two links named "<< First" and "LAST >>" will be shown and point to the first or last page.
	 * Using $this->internal['pagefloat']: this defines were the current page is shown in the list of pages in the Pagebrowser. If this var is an integer it will be interpreted as position in the list of pages. If its value is the keyword "center" the current page will be shown in the middle of the pagelist.
	 * Using $this->internal['showRange']: this var switches the display of the pagelinks from pagenumbers to ranges f.e.: 1-5 6-10 11-15... instead of 1 2 3...
	 * Using $this->pi_isOnlyFields: this holds a comma-separated list of fieldnames which - if they are among the GETvars - will not disable caching for the page with pagebrowser.
	 *
	 * The third parameter is an array with several wraps for the parts of the pagebrowser. The following elements will be recognized:
	 * disabledLinkWrap, inactiveLinkWrap, activeLinkWrap, browseLinksWrap, showResultsWrap, showResultsNumbersWrap, browseBoxWrap.
	 *
	 * If $wrapArr['showResultsNumbersWrap'] is set, the formatting string is expected to hold template markers (###FROM###, ###TO###, ###OUT_OF###, ###FROM_TO###, ###CURRENT_PAGE###, ###TOTAL_PAGES###)
	 * otherwise the formatting string is expected to hold sprintf-markers (%s) for from, to, outof (in that sequence)
	 *
	 * @param	object		tslib_pibase object
	 * @param	integer		determines how the results of the pagerowser will be shown. See description below
	 * @param	string		Attributes for the table tag which is wrapped around the table cells containing the browse links
	 * @param	array		Array with elements to overwrite the default $wrapper-array.
	 * @param	string		varname for the pointer.
	 * @param	boolean		enable htmlspecialchars() for the pi_getLL function (set this to FALSE if you want f.e use images instead of text for links like 'previous' and 'next').
	 * @return	string		Output HTML-Table, wrapped in <div>-tags with a class attribute (if $wrapArr is not passed,
	 */
	function list_browseresults_fh001 (
		&$pObject,
		$showResultCount = 1,
		$tableParams = '',
		$wrapArr = array(),
		$pointerName = 'pointer',
		$hscText = TRUE
	) {
			// Initializing variables:
		$pointer = intval($pObject->piVars[$pointerName]);
		$count = intval($pObject->internal['res_count']);
		$results_at_a_time = tx_div2007_core::intInRange($pObject->internal['results_at_a_time'], 1, 1000);
		$totalPages = ceil($count/$results_at_a_time);
		$maxPages = tx_div2007_core::intInRange($pObject->internal['maxPages'], 1, 100);
		$pi_isOnlyFields = $pObject->pi_isOnlyFields($pObject->pi_isOnlyFields);

			// $showResultCount determines how the results of the pagerowser will be shown.
			// If set to 0: only the result-browser will be shown
			//	 		 1: (default) the text "Displaying results..." and the result-browser will be shown.
			//	 		 2: only the text "Displaying results..." will be shown
		$showResultCount = intval($showResultCount);

			// if this is set, two links named "<< First" and "LAST >>" will be shown and point to the very first or last page.
		$showFirstLast = $pObject->internal['showFirstLast'];

			// if this has a value the "previous" button is always visible (will be forced if "showFirstLast" is set)
		$alwaysPrev = $showFirstLast?1:$pObject->pi_alwaysPrev;

		if (isset($pObject->internal['pagefloat'])) {
			if (strtoupper($pObject->internal['pagefloat']) == 'CENTER') {
				$pagefloat = ceil(($maxPages - 1)/2);
			} else {
				// pagefloat set as integer. 0 = left, value >= $pObject->internal['maxPages'] = right
				$pagefloat = tx_div2007_core::intInRange($pObject->internal['pagefloat'], -1, $maxPages - 1);
			}
		} else {
			$pagefloat = -1; // pagefloat disabled
		}

			// default values for "traditional" wrapping with a table. Can be overwritten by vars from $wrapArr
		$wrapper['disabledLinkWrap'] = '<td nowrap="nowrap"><p>|</p></td>';
		$wrapper['inactiveLinkWrap'] = '<td nowrap="nowrap"><p>|</p></td>';
		$wrapper['activeLinkWrap'] = '<td' . $pObject->pi_classParam('browsebox-SCell') . ' nowrap="nowrap"><p>|</p></td>';
		$wrapper['browseLinksWrap'] = trim('<table ' . $tableParams) . '><tr>|</tr></table>';

		if ($pObject->internal['imagePath'])	{
			$onMouseOver = ($pObject->internal['imageOnMouseOver'] ? 'onmouseover="' . $pObject->internal['imageOnMouseOver'] . '" ': '');
			$onMouseOut = ($pObject->internal['imageOnMouseOut'] ? 'onmouseout="' . $pObject->internal['imageOnMouseOut'] . '" ': '');
			$onMouseOverActive = ($pObject->internal['imageActiveOnMouseOver'] ? 'onmouseover="' . $pObject->internal['imageActiveOnMouseOver'] . '" ': '');
			$onMouseOutActive = ($pObject->internal['imageActiveOnMouseOut'] ? 'onmouseout="' . $pObject->internal['imageActiveOnMouseOut'] . '" ': '');
			$wrapper['browseTextWrap'] = '<img src="' . $pObject->internal['imagePath'] . $pObject->internal['imageFilemask'].'" ' . $onMouseOver . $onMouseOut . '>';
			$wrapper['activeBrowseTextWrap'] = '<img src="' . $pObject->internal['imagePath'] . $pObject->internal['imageActiveFilemask'] . '" ' . $onMouseOverActive . $onMouseOutActive . '>';
		}
		$wrapper['showResultsWrap'] = '<p>|</p>';
		$wrapper['browseBoxWrap'] = '
		<!--
			List browsing box:
		-->
		<div '.$pObject->pi_classParam('browsebox').'>
			|
		</div>';

			// now overwrite all entries in $wrapper which are also in $wrapArr
		$wrapper = array_merge($wrapper,$wrapArr);

		if ($showResultCount != 2) { //show pagebrowser
			if ($pagefloat > -1) {
				$lastPage = min($totalPages,max($pointer + 1 + $pagefloat, $maxPages));
				$firstPage = max(0, $lastPage - $maxPages);
			} else {
				$firstPage = 0;
				$lastPage = tx_div2007_core::intInRange($totalPages, 1, $maxPages);
			}
			$links = array();

				// Make browse-table/links:
			if ($showFirstLast) { // Link to first page
				if ($pointer > 0)	{
					$links[] = $pObject->cObj->wrap($pObject->pi_linkTP_keepPIvars(htmlspecialchars($pObject->pi_getLL('pi_list_browseresults_first', '<< First', $hscText)), array($pointerName => null),$pi_isOnlyFields), $wrapper['inactiveLinkWrap']);
				} else {
					$links[] = $pObject->cObj->wrap(htmlspecialchars($pObject->pi_getLL('pi_list_browseresults_first', '<< First', $hscText)), $wrapper['disabledLinkWrap']);
				}
			}
			if ($alwaysPrev >= 0) { // Link to previous page
				$previousText = $pObject->pi_getLL('pi_list_browseresults_prev', '< Previous', $hscText);
				if ($pointer > 0) {
					$links[] = $pObject->cObj->wrap($pObject->pi_linkTP_keepPIvars($previousText, array($pointerName => ($pointer - 1 ? $pointer - 1 : '')), $pi_isOnlyFields), $wrapper['inactiveLinkWrap']);
				} elseif ($alwaysPrev) {
					$links[] = $pObject->cObj->wrap($previousText, $wrapper['disabledLinkWrap']);
				}
			}
			for($a = $firstPage; $a < $lastPage; $a++)	{ // Links to pages
				if ($pObject->internal['showRange']) {
					$pageText = (($a * $results_at_a_time) + 1) . '-' . min($count, (($a + 1) * $results_at_a_time));
				} else if ($totalPages > 1)	{
					if ($wrapper['browseTextWrap'])	{
						if ($pointer == $a) { // current page
							$pageText = $pObject->cObj->wrap(($a + 1), $wrapper['activeBrowseTextWrap']);
						} else {
							$pageText = $pObject->cObj->wrap(($a + 1), $wrapper['browseTextWrap']);
						}
					} else {
						$pageText = trim($pObject->pi_getLL('pi_list_browseresults_page', 'Page', $hscText)) . ' ' . ($a + 1);
					}
				}
				if ($pointer == $a) { // current page
					if ($pObject->internal['dontLinkActivePage']) {
						$links[] = $pObject->cObj->wrap($pageText, $wrapper['activeLinkWrap']);
					} else {
						$linkArray = array($pointerName  => ($a ? $a : ''));
						$link = $pObject->pi_linkTP_keepPIvars($pageText, $linkArray, $pi_isOnlyFields);
						$links[] = $pObject->cObj->wrap($link, $wrapper['activeLinkWrap']);
					}
				} else {
					$links[] = $pObject->cObj->wrap($pObject->pi_linkTP_keepPIvars($pageText, array($pointerName => ($a ? $a : '')), $pi_isOnlyFields), $wrapper['inactiveLinkWrap']);
				}
			}
			if ($pointer < $totalPages - 1 || $showFirstLast)	{
				$nextText = $pObject->pi_getLL('pi_list_browseresults_next', 'Next >', $hscText);
				if ($pointer == $totalPages-1) { // Link to next page
					$links[] = $pObject->cObj->wrap($nextText, $wrapper['disabledLinkWrap']);
				} else {
					$links[]=$pObject->cObj->wrap($pObject->pi_linkTP_keepPIvars($nextText, array($pointerName => $pointer + 1), $pi_isOnlyFields), $wrapper['inactiveLinkWrap']);
				}
			}
			if ($showFirstLast) { // Link to last page
				if ($pointer < $totalPages-1) {
					$links[] = $pObject->cObj->wrap($pObject->pi_linkTP_keepPIvars($pObject->pi_getLL('pi_list_browseresults_last', 'Last >>', $hscText),array($pointerName => $totalPages - 1), $pi_isOnlyFields), $wrapper['inactiveLinkWrap']);
				} else {
					$links[] = $pObject->cObj->wrap($pObject->pi_getLL('pi_list_browseresults_last', 'Last >>', $hscText), $wrapper['disabledLinkWrap']);
				}
			}
			$theLinks = $pObject->cObj->wrap(implode(chr(10),$links),$wrapper['browseLinksWrap']);
		} else {
			$theLinks = '';
		}

		$pR1 = $pointer * $results_at_a_time + 1;
		$pR2 = $pointer * $results_at_a_time + $results_at_a_time;

		if ($showResultCount) {
			if (isset($wrapper['showResultsNumbersWrap'])) {
				// this will render the resultcount in a more flexible way using markers (new in TYPO3 3.8.0).
				// the formatting string is expected to hold template markers (see function header). Example: 'Displaying results ###FROM### to ###TO### out of ###OUT_OF###'

				$markerArray['###FROM###'] = $pObject->cObj->wrap($pObject->internal['res_count'] > 0 ? $pR1 : 0,$wrapper['showResultsNumbersWrap']);
				$markerArray['###TO###'] = $pObject->cObj->wrap(min($pObject->internal['res_count'], $pR2), $wrapper['showResultsNumbersWrap']);
				$markerArray['###OUT_OF###'] = $pObject->cObj->wrap($pObject->internal['res_count'],$wrapper['showResultsNumbersWrap']);
				$markerArray['###FROM_TO###'] = $pObject->cObj->wrap(($pObject->internal['res_count'] > 0 ? $pR1 : 0) . ' ' . $pObject->pi_getLL('pi_list_browseresults_to', 'to') . ' ' . min($pObject->internal['res_count'], $pR2), $wrapper['showResultsNumbersWrap']);
				$markerArray['###CURRENT_PAGE###'] = $pObject->cObj->wrap($pointer + 1, $wrapper['showResultsNumbersWrap']);
				$markerArray['###TOTAL_PAGES###'] = $pObject->cObj->wrap($totalPages, $wrapper['showResultsNumbersWrap']);
				$pi_list_browseresults_displays = $pObject->pi_getLL('pi_list_browseresults_displays', 'Displaying results ###FROM### to ###TO### out of ###OUT_OF###');
				// substitute markers
				$resultCountMsg = $pObject->cObj->substituteMarkerArray($pi_list_browseresults_displays, $markerArray);
			} else {
				// render the resultcount in the "traditional" way using sprintf
				$resultCountMsg = sprintf(
					str_replace('###SPAN_BEGIN###', '<span' . $pObject->pi_classParam('browsebox-strong') . '>', $pObject->pi_getLL('pi_list_browseresults_displays', 'Displaying results ###SPAN_BEGIN###%s to %s</span> out of ###SPAN_BEGIN###%s</span>')),
					$count > 0 ? $pR1 : 0,
					min($count, $pR2),
					$count);
			}
			$resultCountMsg = $pObject->cObj->wrap($resultCountMsg, $wrapper['showResultsWrap']);
		} else {
			$resultCountMsg = '';
		}

		$sTables = $pObject->cObj->wrap($resultCountMsg . $theLinks, $wrapper['browseBoxWrap']);
		return $sTables;
	}


	public function initFE () {
		global $TT, $TSFE, $BE_USER;

		if (version_compare(TYPO3_version, '6.2.0', '>=')) {
			$callingClassName = '\\JambageCom\\Div2007\\Utility\\FrontendUtility';
			call_user_func($callingClassName . '::init');
		} else {
			// ***********************************
			// Create $TSFE object (TSFE = TypoScript Front End)
			// Connecting to database
			// ***********************************
			$TSFE = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tslib_fe',
				$TYPO3_CONF_VARS,
				\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('id'),
				\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('type'),
				\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('no_cache'),
				\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('cHash'),
				\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('jumpurl'),
				\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('MP'),
				\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('RDCT')
			);

			if($TYPO3_CONF_VARS['FE']['pageUnavailable_force'] &&
				!\TYPO3\CMS\Core\Utility\GeneralUtility::cmpIP(\TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('REMOTE_ADDR'), $TYPO3_CONF_VARS['SYS']['devIPmask'])) {
				$TSFE->pageUnavailableAndExit('This page is temporarily unavailable.');
			}

			$TSFE->connectToDB();

			if ($TSFE->RDCT) {
				$TSFE->sendRedirect();
			}

			// *********
			// FE_USER
			// *********
			$TT->push('Front End user initialized', '');
			$TSFE->initFEuser();
			$TT->pull();

			// *****************************************
			// Proces the ID, type and other parameters
			// After this point we have an array, $page in TSFE, which is the
			// page-record of the current page, $id
			// *****************************************
			$TT->push('Process ID', '');
			// not needed and doesnot work with realurl //
			$TSFE->checkAlternativeIdMethods();
			$TSFE->clear_preview();
			$TSFE->determineId();

				// Now, if there is a backend user logged in and he has NO access to
				// this page, then re-evaluate the id shown!
			if ($TSFE->beUserLogin && !$BE_USER->extPageReadAccess($TSFE->page)) {

				// Remove user
				unset($BE_USER);
				$TSFE->beUserLogin = 0;

					// Re-evaluate the page-id.
				$TSFE->checkAlternativeIdMethods();
				$TSFE->clear_preview();
				$TSFE->determineId();
			}
			$TSFE->makeCacheHash();
			$TT->pull();

			// *******************************************
			// Get compressed $TCA-Array();
			// After this, we should now have a valid $TCA, though minimized
			// *******************************************
			$TSFE->getCompressedTCarray();

			// ********************************
			// Starts the template
			// *******************************
			$TT->push('Start Template','');
			$TSFE->initTemplate();
			$TSFE->tmpl->getFileName_backPath = PATH_site;
			$TT->pull();

			// ******************************************************
			// Get config if not already gotten
			// After this, we should have a valid config-array ready
			// ******************************************************
			$TSFE->getConfigArray();
		}
	}


	/**
	 * This is will calculate your setup as a PHP function
	 * This function is called in your stdWrap preUserFunc function.
	 * 		preUserFunc = tx_div2007_alpha->phpFunc
	 *		preUserFunc {
	 *			php = round($value,12);
	 *		}
	 * The $value in the PHP string will be replaced by your value and the function
	 * will be evaluated.
	 *
	 * @param	string		value
	 * @param	array		the configuration. only the 'php' part is used.
	 * @return	string		The processed string
	 * @see TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::parseFunc()
	 */
	function phpFunc (
		$content,
		$conf
	) {
		if ($conf['php'] != '') {
			$evalStr = str_replace('$value', $content, $conf['php']);
			$rc = eval('return ' . $evalStr);
		}
		return $rc;
	}


	/**
	 * Returns a class-name prefixed with $this->prefixId and with all underscores substituted to dashes (-)
	 * this is an initial state, not yet finished! Therefore the debug lines have been left.
	 *
	 * @param	string		The class name (or the END of it since it will be prefixed by $prefixId.'-')
	 * @param	string		$prefixId
	 * @return	string		The combined class name (with the correct prefix)
	 */
	function unserialize_fh001 (
		$str,
		$bErrorCheck = TRUE
	) {
		$rc = FALSE;

		$codeArray = array('a', 's');
		$len = strlen($str);
		$depth = 0;
		$mode = 'c';
		$i = 0;
		$errorOffset = -1;
		$controlArray = array();
		$controlCount = array();
		$controlData = array();
		$controlIndex = 0;
		while ($i < $len) {
			$ch = $str{$i};
			$i++;
			$next = $str{$i};
			if ($next == ':') {
				$i++;
				$paramPos = strpos($str, ':', $i);
				$param1 = substr($str, $i, $paramPos - $i);
				if ($param1 != '') {
					$i = $paramPos + 1;
					switch ($ch) {
						case 'a':
							if (isset($var)) {
							} else {
								$var = array();
							}
							if ($str{$i} == '{') {
								$i++;
								$controlIndex++;
								$controlArray[$controlIndex] = $ch;
								$controlData[$controlIndex] = array('param' => $param1);
								$controlCount[$controlIndex] = 0;
							} else {
								$errorOffset = $i;
							}
						break;
						case 's':
							if (isset($var)) {
								if ($str{$i} == '"') {
									$i++;
									$param2 = substr($str, $i, $param1);
									$fixPos = strpos($param2, '";');
									if ($fixPos !== FALSE && in_array($param2{$fixPos + 2}, $codeArray)) {
										$i += $fixPos; // fix wrong string length if it is really shorter now
										$param2 = substr($param2, 0, $fixPos);
									} else {
										$i += $param1;
									}

									if ($str{$i} == '"' && $str{$i + 1} == ';') {
										$i += 2;
										if ($controlArray[$controlIndex] == 'a' && $controlData[$controlIndex]['k']=='' && $controlCount[$controlIndex] < $controlData[$controlIndex]['param'])	{
											$controlData[$controlIndex]['k'] = $param2;
											continue;
										}
									}

									if ($controlArray[$controlIndex] == 'a' && $controlCount[$controlIndex] < $controlData[$controlIndex]['param'] && isset($controlData[$controlIndex]['k']))	{
										$controlCount[$controlIndex]++;
										$var[$controlData[$controlIndex]['k']] = $param2;
										$controlData[$controlIndex]['k']='';
									}
								}
							} else {
								$var = '';
							}

						break;
						default:
							$errorOffset = $i;
						break;
					}
				} else {
					$errorOffset = $i;
				}
			} else {
				$errorOffset = $i;
			}
			if ($errorOffset >= 0) {
					if ($bErrorCheck) {
						trigger_error('unserialize_fh001(): Error at offset ' . $errorOffset . ' of ' . $len . ' bytes \'' . substr($str, $errorOffset, 12) . '\'', E_USER_NOTICE);
						$rc = FALSE;
					}
				break;
			}
		}
		if (isset($var) && (!$bErrorCheck || $errorOffset == 0))	{
			$rc = $var;
		}
		return $rc;
	}
}


?>
