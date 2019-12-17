<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2017 Kasper Skårhøj (kasperYYYY@typo3.com)
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
 * @author     Kasper Skårhøj <kasperYYYY@typo3.com>
 * @author     Franz Holzinger <franz@ttproducts.de>
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @since      0.1
 */


class tx_div2007_alpha5 {

	/**
	 * Returns the values from the setup field or the field of the flexform converted into the value
	 * The default value will be used if no return value would be available.
	 * This can be used fine to get the CODE values or the display mode dependant if flexforms are used or not.
	 * And all others fields of the flexforms can be read.
	 *
	 * example:
	 * 	$config['code'] = tx_div2007_alpha5::getSetupOrFFvalue_fh002(
	 *					$this->cObj,
	 * 					$this->conf['code'],
	 * 					$this->conf['code.'],
	 * 					$this->conf['defaultCode'],
	 * 					$this->cObj->data['pi_flexform'],
	 * 					'display_mode',
	 * 					$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][TT_PRODUCTS_EXTkey]['useFlexforms']);
	 *
	 * You have to call $this->pi_initPIflexForm(); before you call this method!
	 * @param	object		cObject
	 * @param	string		TypoScript configuration
	 * @param	string		extended TypoScript configuration
	 * @param	string		default value to use if the result would be empty
	 * @param	boolean		if flexforms are used or not
	 * @param	string		name of the flexform which has been used in ext_tables.php
	 * 						$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['5']='pi_flexform';
	 * @return	string		name of the field to look for in the flexform
	 * @access	public
	 *
	 */
	static public function getSetupOrFFvalue_fh002 (
		$cObj,
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
		$result = '';
		if (empty($code)) {
			if ($useFlexforms) {
				// Converting flexform data into array:
				$result = tx_div2007_ff::get(
					$T3FlexForm_array,
					$fieldName,
					$sheet,
					$lang,
					$value
				);
			} else {
				$result = strtoupper(trim($cObj->stdWrap($code, $codeExt)));
			}
			if (empty($result)) {
				$result = strtoupper($defaultCode);
			}
		} else {
			$result = $code;
		}
		return $result;
	}



    /**
     * Returns the values from the setup field or the field of the flexform converted into the value
     * The default value will be used if no return value would be available.
     * This can be used fine to get the CODE values or the display mode dependant if flexforms are used or not.
     * And all others fields of the flexforms can be read.
     *
     * example:
     *  $config['code'] = tx_div2007_alpha5::getSetupOrFFvalue_fh004(
     *                  $cObj,
     *                  $this->conf['code'],
     *                  $this->conf['code.'],
     *                  $this->conf['defaultCode'],
     *                  $this->cObj->data['pi_flexform'],
     *                  'display_mode',
     *                  $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][TT_PRODUCTS_EXTkey]['useFlexforms']);
     *
     * You have to call $this->pi_initPIflexForm(); before you call this method!
     * @param   object      tx_div2007_alpha_language_base object
     * @param   string      TypoScript configuration
     * @param   string      extended TypoScript configuration
     * @param   string      default value to use if the result would be empty
     * @param   boolean     if flexforms are used or not
     * @param   string      name of the flexform which has been used in ext_tables.php
     *                      $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['5']='pi_flexform';
     * @return  string      name of the field to look for in the flexform
     * @access  public
     *
     */
    static public function getSetupOrFFvalue_fh004 (
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
            $rc = 'error in call of tx_div2007_alpha5::getSetupOrFFvalue_fh004: parameter $cObj is not an object';
            debug ($rc, '$rc'); // keep this
        }
        return $rc;
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
					mmtable       ... name of the mm table
					foreign_field ... name of the field in the mm table which joins with
					                  the foreign table
	 * @access	public
	 *
	 */
	static public function getForeignTableInfo_fh003 ($tablename, $fieldname) {
		$result = array();
		if (
			$tablename != '' &&
			$fieldname != '' &&
			isset($GLOBALS['TCA'][$tablename]['columns'][$fieldname]) &&
			isset($GLOBALS['TCA'][$tablename]['columns'][$fieldname]['config'])
		) {
			$tableConf = $GLOBALS['TCA'][$tablename]['columns'][$fieldname]['config'];
			$LocalFieldname = '';
			$foreignFieldname = '';
			$foreignTable = '';
			$mmTablename = '';
			$mmTableConf = '';

			$type = $tableConf['type'];
			if ($type == 'group') {
				$type = 'select';
			}

			if ($type == 'inline') {
				$mmTablename = $tableConf['foreign_table'];
				$foreignFieldname = $tableConf['foreign_selector'];
			} else if ($type == 'select' && isset($tableConf['MM'])) {
				$mmTablename = $tableConf['MM'];
				$LocalFieldname = 'uid_local';
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
			$result['mmtable'] = $mmTablename;
			$result['local_field'] = $LocalFieldname;
			$result['foreign_field'] = $foreignFieldname;
		}
		return $result;
	}

	/**
	 * Will select all records from the "category table", $table, and return them in an array.
	 *
	 * @param	object		cObject
	 * @param	string		The name of the category table to select from.
	 * @param	integer		The page from where to select the category records.
	 * @param	string		Optional additional WHERE clauses put in the end of the query. DO NOT PUT IN GROUP BY, ORDER BY or LIMIT!
	 * @param	string		Optional GROUP BY field(s), if none, supply blank string.
	 * @param	string		Optional ORDER BY field(s), if none, supply blank string.
	 * @param	string		Optional LIMIT value ([begin,]max), if none, supply blank string.
	 * @return	array		The array with the category records in.
	 */
	static public function getCategoryTableContents_fh001 (
		$cObj,
		$table,
		$pid,
		$whereClause = '',
		$groupBy = '',
		$orderBy = '',
		$limit = ''
	) {
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'*',
			$table,
			'pid=' . intval($pid).
				$cObj->enableFields($table).' '.
				$whereClause,	// whereClauseMightContainGroupOrderBy
			$groupBy,
			$orderBy,
			$limit
		);
		$outArr = array();
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$outArr[$row['uid']] = $row;
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);

		if (
			count($outArr) &&
			$GLOBALS['TSFE']->config['config']['sys_language_uid'] &&
			$GLOBALS['TCA'][$table]['ctrl']['transForeignTable'] != ''
		) {
			$theTable = $GLOBALS['TCA'][$table]['ctrl']['transForeignTable'];
			$theUidField = $GLOBALS['TCA'][$theTable]['ctrl']['transOrigPointerField'];
			$uids = implode(',', array_keys($outArr));
			$whereUids = ' AND ' . $theUidField . ' IN (' . $uids . ')';

			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'*',
				$theTable,
				'pid=' . intval($pid).
					$cObj->enableFields($theTable) . ' '. $whereUids,
				$groupBy,
				$orderBy,
				$limit
			);

			$newOutArray = array(); // new array to get a new order
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$uidDefaultLanguage = $row[$theUidField];
				$rowDefaultLanguage = $outArr[$uidDefaultLanguage];
				$newOutArray[$uidDefaultLanguage] = array_merge($rowDefaultLanguage, $row);
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($res);
			$outArr = $newOutArray;
		}


		return $outArr;
	}

	/**
	 * Returns TRUE if the array $inArray contains only values allowed to be cached based on the configuration in $this->pi_autoCacheFields
	 * Used by self::linkTP_keepCtrlVars
	 * This is an advanced form of evaluation of whether a URL should be cached or not.
	 *
	 * @param	object		parent object of type tx_div2007_alpha_browse_base
	 * @return	boolean		Returns TRUE (1) if conditions are met.
	 * @see linkTP_keepCtrlVars()
	 */
	static public function autoCache_fh001 ($pObject, $inArray) {
		$bUseCache = TRUE;

		if (is_array($inArray)) {
			foreach($inArray as $fN => $fV) {
				$bIsCachable = FALSE;
				if (!strcmp($inArray[$fN],'')) {
					$bIsCachable = TRUE;
				} elseif (is_array($pObject->autoCacheFields[$fN])) {
					if (is_array($pObject->autoCacheFields[$fN]['range'])
							 && intval($inArray[$fN]) >= intval($pObject->autoCacheFields[$fN]['range'][0])
							 && intval($inArray[$fN]) <= intval($pObject->autoCacheFields[$fN]['range'][1])) {
								$bIsCachable = TRUE;
					}
					if (is_array($this->autoCacheFields[$fN]['list'])
							 && in_array($inArray[$fN], $pObject->autoCacheFields[$fN]['list'])) {
								$bIsCachable = TRUE;
					}
				}
				if (!$bIsCachable) {
					$bUseCache = FALSE;
					break;
				}
			}
		}
		return $bUseCache;
	}

	/**
	 * Returns a class-name prefixed with $prefixId and with all underscores substituted to dashes (-). Copied from pi_getClassName
	 *
	 * @param	string		The class name (or the END of it since it will be prefixed by $prefixId.'-')
	 * @param	string		$prefixId
	 * @return	string		The combined class name (with the correct prefix)
	 * @see pi_getClassName()
	 */
	static public function getClassName_fh001 ($class, $prefixId = '') {
		return str_replace('_', '-', $prefixId) . ($prefixId ? '-' : '') . $class;
	}

	/**
	 * Returns a class-name prefixed with $prefixId and with all underscores substituted to dashes (-). Copied from pi_getClassName
	 *
	 * @param	string		The class name (or the END of it since it will be prefixed by $prefixId.'-')
	 * @param	string		$prefixId
	 * @param	boolean		if set, then the prefix 'tx_' is added to the extension name
	 * @return	string		The combined class name (with the correct prefix)
	 * @see pi_getClassName()
	 */
	static public function getClassName_fh002 ($class, $prefixId = '', $bAddPrefixTx = FALSE) {
		if ($bAddPrefixTx && $prefixId != '' && strpos($prefixId, 'tx_') !== 0) {
			$prefixId = 'tx_' . $prefixId;
		}
		return str_replace('_', '-', $prefixId) . ($prefixId != '' ? '-' : '') . $class;
	}

	/**
	 * Returns the class-attribute with the correctly prefixed classname
	 * Using getClassName_fh001()
	 *
	 * @param	string		The class name(s) (suffix) - separate multiple classes with commas
	 * @param	string		Additional class names which should not be prefixed - separate multiple classes with commas
	 * @param	string		$prefixId
	 * @return	string		A "class" attribute with value and a single space char before it.
	 * @see pi_classParam()
	 */
	static public function classParam_fh001 ($class, $addClasses = '', $prefixId = '') {
		$output = '';
		foreach (\TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $class) as $v) {
			$output .= ' ' . self::getClassName_fh001($v, $prefixId);
		}
		foreach (\TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $addClasses) as $v) {
			$output .= ' ' . $v;
		}
		return ' class="' . trim($output) . '"';
	}

	/**
	 * Returns the class-attribute with the correctly prefixed classname
	 * Using getClassName_fh001()
	 *
	 * @param	string		The class name(s) (suffix) - separate multiple classes with commas
	 * @param	string		Additional class names which should not be prefixed - separate multiple classes with commas
	 * @param	string		$prefixId
	 * @param	boolean		if set, then the prefix 'tx_' is added to the extension name
	 * @return	string		A "class" attribute with value and a single space char before it.
	 * @see pi_classParam()
	 */
	static public function classParam_fh002 ($class, $addClasses = '', $prefixId = '', $bAddPrefixTx = FALSE) {
		$output = '';
		foreach (\TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $class) as $v) {
			$output .= ' ' . self::getClassName_fh002($v, $prefixId, $bAddPrefixTx);
		}
		foreach (\TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $addClasses) as $v) {
			$output .= ' ' . $v;
		}
		return ' class="' . trim($output) . '"';
	}

	/**
	 * Link string to the current page.
	 * Returns the $str wrapped in <a>-tags with a link to the CURRENT page, but with $urlParameters set as extra parameters for the page.
	 *
	 * @param	object		parent object of type tx_div2007_alpha_browse_base
	 * @param	object		cObject
	 * @param	string		The content string to wrap in <a> tags
	 * @param	array		Array with URL parameters as key/value pairs. They will be "imploded" and added to the list of parameters defined in the plugins TypoScript property "parent.addParams" plus $this->pi_moreParams.
	 * @param	boolean		If $cache is set (0/1), the page is asked to be cached by a &cHash value (unless the current plugin using this class is a USER_INT). Otherwise the no_cache-parameter will be a part of the link.
	 * @param	integer		Alternative page ID for the link. (By default this function links to the SAME page!)
	 * @return	string		The input string wrapped in <a> tags
	 * @see pi_linkTP_keepPIvars(), TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::typoLink()
	 */
	static public function linkTP (
		$pObject,
		$cObj,
		$str,
		$urlParameters = array(),
		$cache = 0,
		$altPageId = 0
	) {
		$conf = array();
		$conf['useCacheHash'] = $pObject->bUSER_INT_obj ? 0 : $cache;
		$conf['no_cache'] = $pObject->bUSER_INT_obj ? 0 : !$cache;
		$conf['parameter'] = $altPageId ? $altPageId : ($pObject->tmpPageId ? $pObject->tmpPageId : $GLOBALS['TSFE']->id);
		$conf['additionalParams'] = $pObject->conf['parent.']['addParams'] . \TYPO3\CMS\Core\Utility\GeneralUtility::implodeArrayForUrl('', $urlParameters, '', TRUE) . $pObject->moreParams;
		$result = $cObj->typoLink($str, $conf);
		return $result;
	}


	/**
	 * Link a string to the current page while keeping currently set values in piVars.
	 * Like self::linkTP, but $urlParameters is by default set to $this->piVars with $overruleCtrlVars overlaid.
	 * This means any current entries from this->piVars are passed on (except the key "DATA" which will be unset before!) and entries in $overruleCtrlVars will OVERRULE the current in the link.
	 *
	 * @param	object		parent object of type tx_div2007_alpha_browse_base
	 * @param	object		cObject
	 * @param	string		prefix id
	 * @param	string		The content string to wrap in <a> tags
	 * @param	array		Array of values to override in the current piVars. Contrary to self::linkTP the keys in this array must correspond to the real piVars array and therefore NOT be prefixed with the $this->prefixId string. Further, if a value is a blank string it means the piVar key will not be a part of the link (unset)
	 * @param	boolean		If $cache is set, the page is asked to be cached by a &cHash value (unless the current plugin using this class is a USER_INT). Otherwise the no_cache-parameter will be a part of the link.
	 * @param	boolean		If set, then the current values of piVars will NOT be preserved anyways... Practical if you want an easy way to set piVars without having to worry about the prefix, "tx_xxxxx[]"
	 * @param	integer		Alternative page ID for the link. (By default this function links to the SAME page!)
	 * @return	string		The input string wrapped in <a> tags
	 * @see self::linkTP()
	 */
	static public function linkTP_keepCtrlVars (
		$pObject,
		$cObj,
		$prefixId,
		$str,
		$overruleCtrlVars = array(),
		$cache = 0,
		$clearAnyway = 0,
		$altPageId = 0
	) {
        $overruledCtrlVars = '';

		if (
            is_array($overruleCtrlVars) &&
            !$clearAnyway
        ) {
            $overruledCtrlVars = array();
            if (
                isset($pObject->ctrlVars) &&
                is_array($pObject->ctrlVars)
            ) {
                $ctrlVars = $pObject->ctrlVars;
                unset($ctrlVars['DATA']);
                $overruledCtrlVars = $ctrlVars;
			}
			$merged =
                tx_div2007_core::mergeRecursiveWithOverrule(
                    $overruledCtrlVars,
                    $overruleCtrlVars
                );
			if ($pObject->bAutoCacheEn) {
				$cache = self::autoCache_fh001($pObject, $overruledCtrlVars);
			}
		}

		$result =
			self::linkTP(
				$pObject,
				$cObj,
				$str,
				array(
					$prefixId => $overruledCtrlVars
				),
				$cache,
				$altPageId
			);
		return $result;
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
	static public function getTypoLink_fh003 (
		$cObj,
		$label,
		$params,
		$urlParameters = array(),
		$target = '',
		$conf = array()
	) {
		$result = FALSE;

		if (is_object($cObj)) {
			$conf['parameter'] = $params;

			if ($target) {
				if (!isset($conf['target'])) {
					$conf['target'] = $target;
				}
				if (!isset($conf['extTarget'])) {
					$conf['extTarget'] = $target;
				}
			}

			$paramsOld = '';
			$paramsNew = '';
			if (isset($conf['additionalParams'])) {
                $paramsOld = $conf['additionalParams'];
			}

			if (is_array($urlParameters)) {
				if (count($urlParameters)) {
					$paramsNew = \TYPO3\CMS\Core\Utility\GeneralUtility::implodeArrayForUrl('', $urlParameters);
				}
			} else {
				$paramsNew = $urlParameters;
			}
			$conf['additionalParams'] = $paramsOld . $paramsNew;
			$result = $cObj->typolink($label, $conf);
		} else {
			$out = 'error in call of tx_div2007_alpha5::getTypoLink_fh003: parameter $cObj is not an object';
		}
		return $result;
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
	static public function getTypoLink_URL_fh003 (
		$cObj,
		$params,
		$urlParameters = array(),
		$target = '',
		$conf = array()
	) {
		$result = FALSE;

		if (is_object($cObj)) {
			$result = self::getTypoLink_fh003(
				$cObj,
				'',
				$params,
				$urlParameters,
				$target,
				$conf
			);
			if ($result !== FALSE) {
				$result = $cObj->lastTypoLinkUrl;
			}
		} else {
			$out = 'error in call of tx_div2007_alpha5::getTypoLink_URL_fh003: parameter $cObj is not an object';
			debug($out, '$out'); // keep this
		}

		return $result;
	}


	/**
	 * Get URL to some page.
	 * Returns the URL to page $id with $target and an array of additional url-parameters, $urlParameters
	 * Simple example: $this->pi_getPageLink(123) to get the URL for page-id 123.
	 *
	 * The function basically calls $cObj->getTypoLink_URL()
	 *
	 * @param	object		cObject
	 * @param	integer		Page id
	 * @param	string		Target value to use. Affects the &type-value of the URL, defaults to current.
	 * @param	array		Additional URL parameters to set (key/value pairs)
	 * @param	array		Configuration
	 * @return	string		The resulting URL
	 * @see pi_linkToPage()
	 */
	static public function getPageLink_fh003 (
		$cObj,
		$id,
		$target = '',
		$urlParameters = array(),
		$conf = array()
	) {
		$result = self::getTypoLink_URL_fh003(
			$cObj,
			$id,
			$urlParameters,
			$target,
			$conf
		);
		return $result;
	}


	/**
	 * Link a string to some page.
	 * Like pi_getPageLink() but takes a string as first parameter which will in turn be wrapped with the URL including target attribute
	 * Simple example: $this->pi_linkToPage('My link', 123) to get something like <a href="index.php?id=123&type=1">My link</a> (or <a href="123.1.html">My link</a> if simulateStaticDocuments is set)
	 *
	 * @param	object		cObject
	 * @param	string		The content string to wrap in <a> tags
	 * @param	integer		Page id
	 * @param	string		Target value to use. Affects the &type-value of the URL, defaults to current.
	 * @param	array		Additional URL parameters to set (key/value pairs)
	 * @return	string		The input string wrapped in <a> tags with the URL and target set.
	 * @see pi_getPageLink(), TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::getTypoLink()
	 */
	static public function linkToPage_fh001 (
		$cObj,
		$str,
		$id,
		$target = '',
		$urlParameters = array()
	) {
		return $cObj->getTypoLink($str, $id, $urlParameters, $target);
	}


	/**
	 * Returns the localized label of the LOCAL_LANG key, $key used since TYPO3 4.6
	 * Notice that for debugging purposes prefixes for the output values can be set with the internal vars ->LLtestPrefixAlt and ->LLtestPrefix
	 *
	 * @param	object		tx_div2007_alpha_language_base or a tslib_pibase object
	 * @param	string		The key from the LOCAL_LANG array for which to return the value.
	 * @param	string		output: the used language
	 * @param	string		Alternative string to return IF no value is found set for the key, neither for the local language nor the default.
	 * @param	boolean		If TRUE, the output label is passed through htmlspecialchars()
	 * @return	string		The value from LOCAL_LANG.
	 */
	static public function getLL_fh002 (
		$langObj,
		$key,
		&$usedLang = '',
		$alternativeLabel = '',
		$hsc = FALSE
	) {
		$typoVersion = tx_div2007_core::getTypoVersion();

		if ($typoVersion >= 4006000) {

			if ($langObj->LOCAL_LANG[$langObj->LLkey][$key][0]['target'] != '') {
				$usedLang = $langObj->LLkey;

					// The "from" charset of csConv() is only set for strings from TypoScript via _LOCAL_LANG
				if ($langObj->LOCAL_LANG_charset[$usedLang][$key] != '') {
					$word = $GLOBALS['TSFE']->csConv(
						$langObj->LOCAL_LANG[$usedLang][$key][0]['target'],
						$langObj->LOCAL_LANG_charset[$usedLang][$key]
					);
				} else {
					$word = $langObj->LOCAL_LANG[$langObj->LLkey][$key][0]['target'];
				}
			} elseif ($langObj->altLLkey && $langObj->LOCAL_LANG[$langObj->altLLkey][$key][0]['target'] != '') {
				$usedLang = $langObj->altLLkey;

					// The "from" charset of csConv() is only set for strings from TypoScript via _LOCAL_LANG
				if (isset($langObj->LOCAL_LANG_charset[$usedLang][$key])) {
					$word = $GLOBALS['TSFE']->csConv(
						$langObj->LOCAL_LANG[$usedLang][$key][0]['target'],
						$langObj->LOCAL_LANG_charset[$usedLang][$key]
					);
				} else {
					$word = $langObj->LOCAL_LANG[$langObj->altLLkey][$key][0]['target'];
				}
			} elseif ($langObj->LOCAL_LANG['default'][$key][0]['target'] != '') {
				$usedLang = 'default';
					// Get default translation (without charset conversion, english)
				$word = $langObj->LOCAL_LANG[$usedLang][$key][0]['target'];
			} else {
					// Return alternative string or empty
				$word = (isset($langObj->LLtestPrefixAlt)) ? $langObj->LLtestPrefixAlt . $alternativeLabel : $alternativeLabel;
			}
		} else {
			if ($langObj->LOCAL_LANG[$langObj->LLkey][$key] != '') {
				$usedLang = $langObj->LLkey;
				$word = $GLOBALS['TSFE']->csConv(
					$langObj->LOCAL_LANG[$usedLang][$key],
					$langObj->LOCAL_LANG_charset[$usedLang][$key]
				);	// The "from" charset is normally empty and thus it will convert from the charset of the system language, but if it is set (see ->pi_loadLL()) it will be used.
			} elseif (
				$langObj->altLLkey &&
				$langObj->LOCAL_LANG[$langObj->altLLkey][$key] != ''
			) {
				$usedLang = $langObj->altLLkey;
				$word = $GLOBALS['TSFE']->csConv(
					$langObj->LOCAL_LANG[$usedLang][$key],
					$langObj->LOCAL_LANG_charset[$usedLang][$key]
				);	// The "from" charset is normally empty and thus it will convert from the charset of the system language, but if it is set (see ->pi_loadLL()) it will be used.
			} elseif ($langObj->LOCAL_LANG['default'][$key] != '') {
				$usedLang = 'default';
				$word = $langObj->LOCAL_LANG[$usedLang][$key];	// No charset conversion because default is English and thereby ASCII
			} else {
				$word = $langObj->LLtestPrefixAlt . $alt;
			}
		}
		$output = (isset($langObj->LLtestPrefix)) ? $langObj->LLtestPrefix . $word : $word;

		if ($hsc) {
			$output = htmlspecialchars($output);
		}

		return $output;
	}

	/**
	 * Attention: only for TYPO3 versions above 4.6
	 * Returns the localized label of the LOCAL_LANG key, $key used since TYPO3 4.6
	 * Notice that for debugging purposes prefixes for the output values can be set with the internal vars ->LLtestPrefixAlt and ->LLtestPrefix
	 *
	 * @param	object		\JambageCom\Div2007\Base\LocalisationBase or
	 *                     tx_div2007_alpha_language_base or a tslib_pibase object
	 * @param	string		The key from the LOCAL_LANG array for which to return the value.
	 * @param	string		input: if set then this language is used if possible. output: the used language
	 * @param	string		Alternative string to return IF no value is found set for the key, neither for the local language nor the default.
	 * @param	boolean		If TRUE, the output label is passed through htmlspecialchars()
	 * @return	string		The value from LOCAL_LANG. FALSE in error case
	 */
	static public function getLL_fh003 (
		$langObj,
		$key,
		&$usedLang = '',
		$alternativeLabel = '',
		$hsc = FALSE
	) {
		$output = FALSE;
		$typoVersion = tx_div2007_core::getTypoVersion();

		if ($typoVersion >= 4006000) {

			if (is_object($langObj)) {

				if (
					$usedLang != '' &&
					is_array($langObj->LOCAL_LANG[$usedLang][$key][0]) &&
					$langObj->LOCAL_LANG[$usedLang][$key][0]['target'] != ''
				) {
						// The "from" charset of csConv() is only set for strings from TypoScript via _LOCAL_LANG
					if ($langObj->LOCAL_LANG_charset[$usedLang][$key] != '') {
						$word = $GLOBALS['TSFE']->csConv(
							$langObj->LOCAL_LANG[$usedLang][$key][0]['target'],
							$langObj->LOCAL_LANG_charset[$usedLang][$key]
						);
					} else {
						$word = $langObj->LOCAL_LANG[$usedLang][$key][0]['target'];
					}
				} else if (
					$langObj->LLkey != '' &&
					is_array($langObj->LOCAL_LANG[$langObj->LLkey][$key][0]) &&
					$langObj->LOCAL_LANG[$langObj->LLkey][$key][0]['target'] != ''
				) {
					$usedLang = $langObj->LLkey;

						// The "from" charset of csConv() is only set for strings from TypoScript via _LOCAL_LANG
					if ($langObj->LOCAL_LANG_charset[$usedLang][$key] != '') {
						$word = $GLOBALS['TSFE']->csConv(
							$langObj->LOCAL_LANG[$usedLang][$key][0]['target'],
							$langObj->LOCAL_LANG_charset[$usedLang][$key]
						);
					} else {
						$word = $langObj->LOCAL_LANG[$langObj->LLkey][$key][0]['target'];
					}
				} elseif (
					$langObj->altLLkey &&
					is_array($langObj->LOCAL_LANG[$langObj->altLLkey][$key][0]) &&
					$langObj->LOCAL_LANG[$langObj->altLLkey][$key][0]['target'] != ''
				) {
					$usedLang = $langObj->altLLkey;

						// The "from" charset of csConv() is only set for strings from TypoScript via _LOCAL_LANG
					if (isset($langObj->LOCAL_LANG_charset[$usedLang][$key])) {
						$word = $GLOBALS['TSFE']->csConv(
							$langObj->LOCAL_LANG[$usedLang][$key][0]['target'],
							$langObj->LOCAL_LANG_charset[$usedLang][$key]
						);
					} else {
						$word = $langObj->LOCAL_LANG[$langObj->altLLkey][$key][0]['target'];
					}
				} elseif (
					is_array($langObj->LOCAL_LANG['default'][$key][0]) &&
					$langObj->LOCAL_LANG['default'][$key][0]['target'] != ''
				) {
					$usedLang = 'default';
						// Get default translation (without charset conversion, english)
					$word = $langObj->LOCAL_LANG[$usedLang][$key][0]['target'];
				} else {
						// Return alternative string or empty
					$word = (isset($langObj->LLtestPrefixAlt)) ? $langObj->LLtestPrefixAlt . $alternativeLabel : $alternativeLabel;
				}

				$output = (isset($langObj->LLtestPrefix)) ? $langObj->LLtestPrefix . $word : $word;
			}

			if ($hsc) {
				$output = htmlspecialchars($output);
			}
		}

		return $output;
	}


	/**
     * used since TYPO3 4.6
	 * Loads local-language values by looking for a "locallang.php" file in the plugin class directory ($langObj->scriptRelPath) and if found includes it.
	 * Also locallang values set in the TypoScript property "_LOCAL_LANG" are merged onto the values found in the "locallang.xml" file.
	 *
	 * @param	object		\JambageCom\Div2007\Base\LocalisationBase,
     *                      tx_div2007_alpha_language_base or a tslib_pibase object
	 * @param	string		language file to load
	 * @param	boolean		If TRUE, then former language items can be overwritten from the new file
	 * @return	boolean
	 */
	static public function loadLL_fh002 (
		$langObj,
		$langFileParam = '',
		$overwrite = TRUE
	) {
		$result = FALSE;
		$emClass = '\\TYPO3\\CMS\\Core\\Utility\\ExtensionManagementUtility';

		if (
			class_exists($emClass) &&
			method_exists($emClass, 'extPath')
		) {
			// nothing
		} else {
			$emClass = 't3lib_extMgm';
		}

		if (
			is_object($langObj) &&
			isset($langObj->LOCAL_LANG) &&
			is_array($langObj->LOCAL_LANG)
		) {
			$typoVersion = tx_div2007_core::getTypoVersion();
			$langFile = ($langFileParam ? $langFileParam : 'locallang.xml');

			if (
				substr($langFile, 0, 4) === 'EXT:' ||
				substr($langFile, 0, 5) === 'typo3' ||
				substr($langFile, 0, 9) === 'fileadmin'
			) {
				$basePath = $langFile;
			} else {
				$basePath = call_user_func($emClass . '::extPath', $langObj->extKey) .
					($langObj->scriptRelPath ? dirname($langObj->scriptRelPath) . '/' : '') . $langFile;
			}


            if ($typoVersion >= 7004000) {

                $callingClassName = '\\TYPO3\\CMS\\Core\\Localization\\LocalizationFactory';
                $useClassName = substr($callingClassName, 1);

                /** @var $languageFactory \TYPO3\CMS\Core\Localization\LocalizationFactory */
                $languageFactory = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance($useClassName);
                $tempLOCAL_LANG = $languageFactory->getParsedData(
                    $basePath,
                    $langObj->LLkey,
                    'UTF-8'
                );
            } else {
                    // Read the strings in the required charset (since TYPO3 4.2)
                $tempLOCAL_LANG =
                    \TYPO3\CMS\Core\Utility\GeneralUtility::readLLfile(
                        $basePath,
                        $langObj->LLkey,
                        $GLOBALS['TSFE']->renderCharset
                    );
            }

			if (count($langObj->LOCAL_LANG) && is_array($tempLOCAL_LANG)) {
				foreach ($langObj->LOCAL_LANG as $langKey => $tempArray) {
					if (is_array($tempLOCAL_LANG[$langKey])) {

						if ($overwrite) {
							$langObj->LOCAL_LANG[$langKey] = array_merge($langObj->LOCAL_LANG[$langKey], $tempLOCAL_LANG[$langKey]);
						} else {
							$langObj->LOCAL_LANG[$langKey] = array_merge($tempLOCAL_LANG[$langKey], $langObj->LOCAL_LANG[$langKey]);
						}
					}
				}
			} else {
				$langObj->LOCAL_LANG = $tempLOCAL_LANG;
			}
			$charset = 'UTF-8';
			if ($typoVersion <= 6000000) {
                $charset = $GLOBALS['TSFE']->renderCharset;
			}

			if ($langObj->altLLkey) {
				$tempLOCAL_LANG =
					\TYPO3\CMS\Core\Utility\GeneralUtility::readLLfile(
						$basePath,
						$langObj->altLLkey,
						$charset
					);

				if (count($langObj->LOCAL_LANG) && is_array($tempLOCAL_LANG)) {
					foreach ($langObj->LOCAL_LANG as $langKey => $tempArray) {
						if (is_array($tempLOCAL_LANG[$langKey])) {
							if ($overwrite) {
								$langObj->LOCAL_LANG[$langKey] =
									array_merge($langObj->LOCAL_LANG[$langKey], $tempLOCAL_LANG[$langKey]);
							} else {
								$langObj->LOCAL_LANG[$langKey] =
									array_merge($tempLOCAL_LANG[$langKey], $langObj->LOCAL_LANG[$langKey]);
							}
						}
					}
				} else {
					$langObj->LOCAL_LANG = $tempLOCAL_LANG;
				}
			}

				// Overlaying labels from TypoScript (including fictitious language keys for non-system languages!):

			$confLL = $langObj->conf['_LOCAL_LANG.'];

			if (is_array($confLL)) {
				foreach ($confLL as $languageKey => $languageArray) {
					if (is_array($languageArray)) {
						if (!isset($langObj->LOCAL_LANG[$languageKey])) {
							$langObj->LOCAL_LANG[$languageKey] = array();
						}
						$languageKey = substr($languageKey, 0, -1);
						$charset = $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'];

						// For labels coming from the TypoScript (database) the charset is assumed to be "forceCharset"
						// and if that is not set, assumed to be that of the individual system languages
						if (!$charset) {
							$charset = $GLOBALS['TSFE']->csConvObj->charSetArray[$languageKey];
						}

							// Remove the dot after the language key
						foreach ($languageArray as $labelKey => $labelValue) {
							if (!isset($langObj->LOCAL_LANG[$languageKey][$labelKey])) {
								$langObj->LOCAL_LANG[$languageKey][$labelKey] = array();
							}

							if (is_array($labelValue)) {
								foreach ($labelValue as $labelKey2 => $labelValue2) {
									if (is_array($labelValue2)) {
										foreach ($labelValue2 as $labelKey3 => $labelValue3) {
											if (is_array($labelValue3)) {
												foreach ($labelValue3 as $labelKey4 => $labelValue4) {
													if (is_array($labelValue4)) {
													} else {
														if ($typoVersion >= 4006000) {
															$langObj->LOCAL_LANG[$languageKey][$labelKey . $labelKey2 . $labelKey3 . $labelKey4][0]['target'] = $labelValue4;
														} else {
															$langObj->LOCAL_LANG[$languageKey][$labelKey . $labelKey2 . $labelKey3 . $labelKey4] = $labelValue4;
														}

														if ($languageKey != 'default') {
															$langObj->LOCAL_LANG_charset[$languageKey][$labelKey . $labelKey2 . $labelKey3 . $labelKey4] = $charset;	// For labels coming from the TypoScript (database) the charset is assumed to be "forceCharset" and if that is not set, assumed to be that of the individual system languages (thus no conversion)
														}
													}
												}
											} else {
												if ($typoVersion >= 4006000) {
													$langObj->LOCAL_LANG[$languageKey][$labelKey . $labelKey2 . $labelKey3][0]['target'] = $labelValue3;
												} else {
													$langObj->LOCAL_LANG[$languageKey][$labelKey . $labelKey2 . $labelKey3] = $labelValue3;
												}

												if ($languageKey != 'default') {
													$langObj->LOCAL_LANG_charset[$languageKey][$labelKey . $labelKey2 . $labelKey3] = $charset;	// For labels coming from the TypoScript (database) the charset is assumed to be "forceCharset" and if that is not set, assumed to be that of the individual system languages (thus no conversion)
												}
											}
										}
									} else {
										if ($typoVersion >= 4006000) {
											$langObj->LOCAL_LANG[$languageKey][$labelKey . $labelKey2][0]['target'] = $labelValue2;
										} else {
											$langObj->LOCAL_LANG[$languageKey][$labelKey . $labelKey2] = $labelValue2;
										}

										if ($languageKey != 'default') {
											$langObj->LOCAL_LANG_charset[$languageKey][$labelKey . $labelKey2] = $charset;	// For labels coming from the TypoScript (database) the charset is assumed to be "forceCharset" and if that is not set, assumed to be that of the individual system languages (thus no conversion)
										}
									}
								}
							} else {
								if ($typoVersion >= 4006000) {
									$langObj->LOCAL_LANG[$languageKey][$labelKey][0]['target'] = $labelValue;
								} else {
									$langObj->LOCAL_LANG[$languageKey][$labelKey] = $labelValue;
								}

								if ($languageKey != 'default') {
									$langObj->LOCAL_LANG_charset[$languageKey][$labelKey] = $charset;	// For labels coming from the TypoScript (database) the charset is assumed to be "forceCharset" and if that is not set, assumed to be that of the individual system languages (thus no conversion)
								}
							}
						}
					}
				}
			}

			$langObj->LOCAL_LANG_loaded = 1;
			$result = TRUE;
		} else {
			$output = 'error in call of tx_div2007_alpha::loadLL_fh002: parameter $langObj is not an object';
			debug ($output, '$output'); // keep this
		}

		return $result;
	}


	/**
	 * Returns a results browser. This means a bar of page numbers plus a "previous" and "next" link. For each entry in the bar the ctrlVars "pointer" will be pointing to the "result page" to show.
	 * Using $this->ctrlVars['pointer'] as pointer to the page to display. Can be overwritten with another string ($pointerName) to make it possible to have more than one pagebrowser on a page)
	 * Using $this->internal['resCount'], $this->internal['limit'] and $this->internal['maxPages'] for count number, how many results to show and the max number of pages to include in the browse bar.
	 * Using $this->internal['dontLinkActivePage'] as switch if the active (current) page should be displayed as pure text or as a link to itself
	 * Using $this->internal['bShowFirstLast'] as switch if the two links named "<< First" and "LAST >>" will be shown and point to the first or last page.
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
	 * @param	object		parent object of type tx_div2007_alpha_browse_base
	 * @param	object		language object of type tx_div2007_alpha_language_base
	 * @param	object		cObject
	 * @param	string		prefix id
	 * @param	boolean		if CSS styled content with div tags shall be used
	 * @param	integer		determines how the results of the pagerowser will be shown. See description below
	 * @param	string		Attributes for the table tag which is wrapped around the table cells containing the browse links
	 *						(only used if no CSS style is set)
	 * @param	array		Array with elements to overwrite the default $wrapper-array.
	 * @param	string		varname for the pointer.
	 * @param	boolean		enable htmlspecialchars() for the tx_div2007_alpha::getLL function (set this to FALSE if you want e.g. use images instead of text for links like 'previous' and 'next').
	 * @param	array		Additional query string to be passed as parameters to the links
	 * @return	string		Output HTML-Table, wrapped in <div>-tags with a class attribute (if $wrapArr is not passed,
	 */
	static public function &list_browseresults_fh002 (
		$pObject,
		$langObj,
		$cObj,
		$prefixId,
		$bCSSStyled = TRUE,
		$showResultCount = 1,
		$browseParams = '',
		$wrapArr = array(),
		$pointerName = 'pointer',
		$hscText = TRUE,
		$addQueryString = array()
	) {
		// example $wrapArr-array how it could be traversed from an extension
		/* $wrapArr = array(
			'showResultsNumbersWrap' => '<span class="showResultsNumbersWrap">|</span>'
		); */

		$linkArray = $addQueryString;
			// Initializing variables:
		$pointer = intval($pObject->ctrlVars[$pointerName]);
		$count = intval($pObject->internal['resCount']);
		$limit = tx_div2007_core::intInRange($pObject->internal['limit'], 1, 1000);
		$totalPages = ceil($count / $limit);
		$maxPages = tx_div2007_core::intInRange($pObject->internal['maxPages'], 1, 100);
		$bUseCache = self::autoCache_fh001($pObject, $pObject->ctrlVars);

			// $showResultCount determines how the results of the pagerowser will be shown.
			// If set to 0: only the result-browser will be shown
			//	 		 1: (default) the text "Displaying results..." and the result-browser will be shown.
			//	 		 2: only the text "Displaying results..." will be shown
		$showResultCount = intval($showResultCount);

			// if this is set, two links named "<< First" and "LAST >>" will be shown and point to the very first or last page.
		$bShowFirstLast = $pObject->internal['bShowFirstLast'];

			// if this has a value the "previous" button is always visible (will be forced if "bShowFirstLast" is set)
		$alwaysPrev = ($bShowFirstLast ? TRUE : $pObject->internal['bAlwaysPrev']);

		if (isset($pObject->internal['pagefloat'])) {
			if (strtoupper($pObject->internal['pagefloat']) == 'CENTER') {
				$pagefloat = ceil(($maxPages - 1) / 2);
			} else {
				// pagefloat set as integer. 0 = left, value >= $pObject->internal['maxPages'] = right
				$pagefloat = tx_div2007_core::intInRange($pObject->internal['pagefloat'], -1, $maxPages-1);
			}
		} else {
			$pagefloat = -1; // pagefloat disabled
		}

				// default values for "traditional" wrapping with a table. Can be overwritten by vars from $wrapArr
		if ($bCSSStyled)	{
			$wrapper['disabledLinkWrap'] = '<span class="disabledLinkWrap">|</span>';
			$wrapper['inactiveLinkWrap'] = '<span class="inactiveLinkWrap">|</span>';
			$wrapper['activeLinkWrap'] = '<span class="activeLinkWrap">|</span>';
			$wrapper['browseLinksWrap'] = '<div class="browseLinksWrap">|</div>';
			$wrapper['disabledNextLinkWrap'] = '<span class="pagination-next">|</span>';
			$wrapper['inactiveNextLinkWrap'] = '<span class="pagination-next">|</span>';
			$wrapper['disabledPreviousLinkWrap'] = '<span class="pagination-previous">|</span>';
			$wrapper['inactivePreviousLinkWrap'] = '<span class="pagination-previous">|</span>';
		} else {
			$wrapper['disabledLinkWrap'] = '<td nowrap="nowrap"><p>|</p></td>';
			$wrapper['inactiveLinkWrap'] = '<td nowrap="nowrap"><p>|</p></td>';
			$wrapper['activeLinkWrap'] = '<td' . self::classParam_fh001('browsebox-SCell', '', $prefixId) . ' nowrap="nowrap"><p>|</p></td>';
			$wrapper['browseLinksWrap'] = trim('<table '.$browseParams).'><tr>|</tr></table>';
		}

		if (is_array($pObject->internal['image']) && $pObject->internal['image']['path'])	{
			$onMouseOver = ($pObject->internal['image']['onmouseover'] ? 'onmouseover="'.$pObject->internal['image']['onmouseover'] . '" ': '');
			$onMouseOut = ($pObject->internal['image']['onmouseout'] ? 'onmouseout="' . $pObject->internal['image']['onmouseout'] . '" ': '');
			$onMouseOverActive = ($pObject->internal['imageactive']['onmouseover'] ? 'onmouseover="' . $pObject->internal['imageactive']['onmouseover'] . '" ': '');
			$onMouseOutActive = ($pObject->internal['imageactive']['onmouseout'] ? 'onmouseout="' . $pObject->internal['imageactive']['onmouseout'] . '" ': '');
			$wrapper['browseTextWrap'] = '<img src="' . $pObject->internal['image']['path'] . $pObject->internal['image']['filemask'] . '" ' . $onMouseOver . $onMouseOut . '>';
			$wrapper['activeBrowseTextWrap'] = '<img src="' . $pObject->internal['image']['path'] . $pObject->internal['imageactive']['filemask'] . '" ' . $onMouseOverActive . $onMouseOutActive . '>';
		}

		if ($bCSSStyled)	{
			$wrapper['showResultsWrap'] = '<div class="showResultsWrap">|</div>';
			$wrapper['browseBoxWrap'] = '<div class="browseBoxWrap">|</div>';
		} else {
			$wrapper['showResultsWrap'] = '<p>|</p>';
			$wrapper['browseBoxWrap'] = '
			<!--
				List browsing box:
			-->
			<div ' . self::classParam_fh001('browsebox', '', $prefixId) . '>
				|
			</div>';
		}

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
			$links=array();

				// Make browse-table/links:
			if ($bShowFirstLast) { // Link to first page
				if ($pointer>0)	{
					$linkArray[$pointerName] = null;
					$links[] = $cObj->wrap(self::linkTP_keepCtrlVars($pObject,$cObj, $prefixId, tx_div2007_alpha::getLL($langObj, 'list_browseresults_first', '<< First', $hscText), $linkArray, $bUseCache), $wrapper['inactiveLinkWrap']);
				} else {
					$links[] = $cObj->wrap(tx_div2007_alpha::getLL($langObj, 'list_browseresults_first', '<< First', $hscText), $wrapper['disabledLinkWrap']);
				}
			}
			if ($alwaysPrev>=0)	{ // Link to previous page
				$previousText = tx_div2007_alpha::getLL($langObj, 'list_browseresults_prev', '< Previous', $hscText);
				if ($pointer>0)	{
					$linkArray[$pointerName] = ($pointer - 1 ? $pointer-1 : '');
					$links[] = $cObj->wrap(self::linkTP_keepCtrlVars($pObject, $cObj, $prefixId, $previousText, $linkArray, $bUseCache), $wrapper['inactiveLinkWrap']);
				} elseif ($alwaysPrev)	{
					$links[] = $cObj->wrap($previousText, $wrapper['disabledLinkWrap']);
				}
			}

			for($a = $firstPage; $a < $lastPage; $a++)	{ // Links to pages
				$pageText = '';
				if ($pObject->internal['showRange']) {
					$pageText = (($a * $limit) + 1) . '-' . min($count, (($a + 1) * $limit));
				} else if ($totalPages > 1) {
					if ($wrapper['browseTextWrap'])	{
						if ($pointer == $a) { // current page
							$pageText = $cObj->wrap(($a + 1), $wrapper['activeBrowseTextWrap']);
						} else {
							$pageText = $cObj->wrap(($a + 1), $wrapper['browseTextWrap']);
						}
					} else {
						$pageText = trim(tx_div2007_alpha::getLL($langObj, 'list_browseresults_page', 'Page', $hscText)) . ' ' . ($a+1);
					}
				}
				if ($pointer == $a) { // current page
					if ($pObject->internal['dontLinkActivePage']) {
						$links[] = $cObj->wrap($pageText, $wrapper['activeLinkWrap']);
					} else if ($pageText != ''){
						$linkArray[$pointerName] = ($a ? $a : '');
						$link = self::linkTP_keepCtrlVars($pObject, $cObj, $prefixId, $pageText, $linkArray, $bUseCache);
						$links[] = $cObj->wrap($link, $wrapper['activeLinkWrap']);
					}
				} else if ($pageText != '') {
					$linkArray[$pointerName] = ($a ? $a : '');
					$links[] = $cObj->wrap(self::linkTP_keepCtrlVars($pObject, $cObj, $prefixId, $pageText, $linkArray, $bUseCache), $wrapper['inactiveLinkWrap']);
				}
			}
			if ($pointer < $totalPages - 1 || $bShowFirstLast)	{
				$nextText = tx_div2007_alpha::getLL($langObj, 'list_browseresults_next', 'Next >', $hscText);
				if ($pointer == $totalPages-1) { // Link to next page
					$links[] = $cObj->wrap($nextText, $wrapper['disabledLinkWrap']);
				} else {
					$linkArray[$pointerName] = $pointer + 1;
					$links[]=$cObj->wrap(self::linkTP_keepCtrlVars($pObject, $cObj, $prefixId, $nextText, $linkArray, $bUseCache), $wrapper['inactiveLinkWrap']);
				}
			}
			if ($bShowFirstLast) { // Link to last page
				if ($pointer < $totalPages - 1) {
					$linkArray[$pointerName] = $totalPages-1;
					$links[] = $cObj->wrap(self::linkTP_keepCtrlVars($pObject, $cObj, $prefixId, tx_div2007_alpha::getLL($langObj, 'list_browseresults_last', 'Last >>', $hscText), $linkArray, $bUseCache), $wrapper['inactiveLinkWrap']);
				} else {
					$links[] = $cObj->wrap(tx_div2007_alpha::getLL($langObj, 'list_browseresults_last', 'Last >>', $hscText), $wrapper['disabledLinkWrap']);
				}
			}
			$theLinks = $cObj->wrap(implode(chr(10), $links), $wrapper['browseLinksWrap']);
		} else {
			$theLinks = '';
		}

		$pR1 = $pointer * $limit + 1;
		$pR2 = $pointer * $limit + $limit;

		if ($showResultCount) {
			if (isset($wrapper['showResultsNumbersWrap'])) {
				// this will render the resultcount in a more flexible way using markers (new in TYPO3 3.8.0).
				// the formatting string is expected to hold template markers (see function header). Example: 'Displaying results ###FROM### to ###TO### out of ###OUT_OF###'

				$markerArray['###FROM###'] = $cObj->wrap($count > 0 ? $pR1 : 0,$wrapper['showResultsNumbersWrap']);
				$markerArray['###TO###'] = $cObj->wrap(min($count, $pR2), $wrapper['showResultsNumbersWrap']);
				$markerArray['###OUT_OF###'] = $cObj->wrap($count, $wrapper['showResultsNumbersWrap']);
				$markerArray['###FROM_TO###'] = $cObj->wrap(($count > 0 ? $pR1 : 0) . ' ' . tx_div2007_alpha::getLL($langObj, 'list_browseresults_to', 'to') . ' ' . min($count, $pR2),$wrapper['showResultsNumbersWrap']);
				$markerArray['###CURRENT_PAGE###'] = $cObj->wrap($pointer + 1,$wrapper['showResultsNumbersWrap']);
				$markerArray['###TOTAL_PAGES###'] = $cObj->wrap($totalPages,$wrapper['showResultsNumbersWrap']);
				$list_browseresults_displays = tx_div2007_alpha::getLL($langObj, 'list_browseresults_displays_marker','Displaying results ###FROM### to ###TO### out of ###OUT_OF###');
				// substitute markers
				$resultCountMsg = $cObj->substituteMarkerArray($list_browseresults_displays, $markerArray);
			} else {
				// render the resultcount in the "traditional" way using sprintf
				$resultCountMsg = sprintf(
					str_replace(
						'###SPAN_BEGIN###',
						'<span' . self::classParam_fh001('browsebox-strong', '', $prefixId) . '>',
						tx_div2007_alpha::getLL($langObj, 'list_browseresults_displays', 'Displaying results ###SPAN_BEGIN###%s to %s</span> out of ###SPAN_BEGIN###%s</span>')
					),
					$count > 0 ? $pR1 : 0,
					min($count,$pR2),
					$count
				);
			}
			$resultCountMsg = $cObj->wrap($resultCountMsg, $wrapper['showResultsWrap']);
		} else {
			$resultCountMsg = '';
		}
		$rc = $cObj->wrap($resultCountMsg . $theLinks, $wrapper['browseBoxWrap']);
		return $rc;
	}


	/**
	 * Returns a results browser. This means a bar of page numbers plus a "previous" and "next" link. For each entry in the bar the ctrlVars "pointer" will be pointing to the "result page" to show.
	 * Using $this->ctrlVars['pointer'] as pointer to the page to display. Can be overwritten with another string ($pointerName) to make it possible to have more than one pagebrowser on a page)
	 * Using $this->internal['resCount'], $this->internal['limit'] and $this->internal['maxPages'] for count number, how many results to show and the max number of pages to include in the browse bar.
	 * Using $this->internal['dontLinkActivePage'] as switch if the active (current) page should be displayed as pure text or as a link to itself
	 * Using $this->internal['bShowFirstLast'] as switch if the two links named "<< First" and "LAST >>" will be shown and point to the first or last page.
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
	 * @param	object		parent object of type tx_div2007_alpha_browse_base
	 * @param	object		language object of type tx_div2007_alpha_language_base
	 * @param	object		cObject
	 * @param	string		prefix id
	 * @param	boolean		if CSS styled content with div tags shall be used
	 * @param	integer		determines how the results of the pagerowser will be shown. See description below
	 * @param	string		Attributes for the table tag which is wrapped around the table cells containing the browse links
	 *						(only used if no CSS style is set)
	 * @param	array		Array with elements to overwrite the default $wrapper-array.
	 * @param	string		varname for the pointer.
	 * @param	boolean		enable htmlspecialchars() for the tx_div2007_alpha5::getLL_fh002 function (set this to FALSE if you want e.g. use images instead of text for links like 'previous' and 'next').
	 * @param	array		Additional query string to be passed as parameters to the links
	 * @return	string		Output HTML-Table, wrapped in <div>-tags with a class attribute (if $wrapArr is not passed,
	 */
	static public function &list_browseresults_fh003 (
		$pObject,
		$langObj,
		$cObj,
		$prefixId,
		$bCSSStyled = TRUE,
		$showResultCount = 1,
		$browseParams = '',
		$wrapArr = array(),
		$pointerName = 'pointer',
		$hscText = TRUE,
		$addQueryString = array()
	) {
		$usedLang = '';
		$linkArray = $addQueryString;
			// Initializing variables:
		$pointer = intval($pObject->ctrlVars[$pointerName]);
		$count = intval($pObject->internal['resCount']);
		$limit = tx_div2007_core::intInRange($pObject->internal['limit'], 1, 1000);
		$totalPages = ceil($count/$limit);
		$maxPages = tx_div2007_core::intInRange($pObject->internal['maxPages'], 1, 100);
		$bUseCache = self::autoCache_fh001($pObject, $pObject->ctrlVars);

			// $showResultCount determines how the results of the pagerowser will be shown.
			// If set to 0: only the result-browser will be shown
			//	 		 1: (default) the text "Displaying results..." and the result-browser will be shown.
			//	 		 2: only the text "Displaying results..." will be shown
		$showResultCount = intval($showResultCount);

			// if this is set, two links named "<< First" and "LAST >>" will be shown and point to the very first or last page.
		$bShowFirstLast = $pObject->internal['bShowFirstLast'];

			// if this has a value the "previous" button is always visible (will be forced if "bShowFirstLast" is set)
		$alwaysPrev = ($bShowFirstLast ? TRUE : $pObject->internal['bAlwaysPrev']);

		if (isset($pObject->internal['pagefloat'])) {
			if (strtoupper($pObject->internal['pagefloat']) == 'CENTER') {
				$pagefloat = ceil(($maxPages - 1) / 2);
			} else {
				// pagefloat set as integer. 0 = left, value >= $pObject->internal['maxPages'] = right
				$pagefloat = tx_div2007_core::intInRange($pObject->internal['pagefloat'], -1, $maxPages - 1);
			}
		} else {
			$pagefloat = -1; // pagefloat disabled
		}

				// default values for "traditional" wrapping with a table. Can be overwritten by vars from $wrapArr
		if ($bCSSStyled) {
			$wrapper['disabledLinkWrap'] = '<span class="disabledLinkWrap">|</span>';
			$wrapper['inactiveLinkWrap'] = '<span class="inactiveLinkWrap">|</span>';
			$wrapper['activeLinkWrap'] = '<span class="activeLinkWrap">|</span>';
			$wrapper['browseLinksWrap'] = '<div class="browseLinksWrap">|</div>';
			$wrapper['disabledNextLinkWrap'] = '<span class="pagination-next">|</span>';
			$wrapper['inactiveNextLinkWrap'] = '<span class="pagination-next">|</span>';
			$wrapper['disabledPreviousLinkWrap'] = '<span class="pagination-previous">|</span>';
			$wrapper['inactivePreviousLinkWrap'] = '<span class="pagination-previous">|</span>';
		} else {
			$wrapper['disabledLinkWrap'] = '<td nowrap="nowrap"><p>|</p></td>';
			$wrapper['inactiveLinkWrap'] = '<td nowrap="nowrap"><p>|</p></td>';
			$wrapper['activeLinkWrap'] = '<td' . self::classParam_fh001('browsebox-SCell', '', $prefixId) . ' nowrap="nowrap"><p>|</p></td>';
			$wrapper['browseLinksWrap'] = trim('<table ' . $browseParams) . '><tr>|</tr></table>';
		}

		if (is_array($pObject->internal['image']) && $pObject->internal['image']['path']) {
			$onMouseOver = ($pObject->internal['image']['onmouseover'] ? 'onmouseover="'.$pObject->internal['image']['onmouseover'] . '" ': '');
			$onMouseOut = ($pObject->internal['image']['onmouseout'] ? 'onmouseout="' . $pObject->internal['image']['onmouseout'] . '" ': '');
			$onMouseOverActive = ($pObject->internal['imageactive']['onmouseover'] ? 'onmouseover="' . $pObject->internal['imageactive']['onmouseover'] . '" ': '');
			$onMouseOutActive = ($pObject->internal['imageactive']['onmouseout'] ? 'onmouseout="' . $pObject->internal['imageactive']['onmouseout'] . '" ': '');
			$wrapper['browseTextWrap'] = '<img src="' . $pObject->internal['image']['path'] . $pObject->internal['image']['filemask'] . '" ' . $onMouseOver . $onMouseOut . '>';
			$wrapper['activeBrowseTextWrap'] = '<img src="' . $pObject->internal['image']['path'] . $pObject->internal['imageactive']['filemask'] . '" ' . $onMouseOverActive . $onMouseOutActive . '>';
		}

		if ($bCSSStyled) {
			$wrapper['showResultsWrap'] = '<div class="showResultsWrap">|</div>';
			$wrapper['browseBoxWrap'] = '<div class="browseBoxWrap">|</div>';
		} else {
			$wrapper['showResultsWrap'] = '<p>|</p>';
			$wrapper['browseBoxWrap'] = '
			<!--
				List browsing box:
			-->
			<div ' . self::classParam_fh001('browsebox', '', $prefixId) . '>
				|
			</div>';
		}

			// now overwrite all entries in $wrapper which are also in $wrapArr
		$wrapper = array_merge($wrapper,$wrapArr);

		if ($showResultCount != 2) { //show pagebrowser
			if ($pagefloat > -1) {
				$lastPage = min($totalPages, max($pointer + 1 + $pagefloat, $maxPages));
				$firstPage = max(0, $lastPage - $maxPages);
			} else {
				$firstPage = 0;
				$lastPage = tx_div2007_core::intInRange($totalPages, 1, $maxPages);
			}
			$links = array();

				// Make browse-table/links:
			if ($bShowFirstLast) { // Link to first page
				if ($pointer > 0) {
					$linkArray[$pointerName] = null;
					$links[] = $cObj->wrap(self::linkTP_keepCtrlVars($pObject, $cObj, $prefixId, self::getLL_fh002($langObj, 'list_browseresults_first', $usedLang, '<< First', $hscText), $linkArray, $bUseCache), $wrapper['inactiveLinkWrap']);
				} else {
					$links[] = $cObj->wrap(self::getLL_fh002($langObj, 'list_browseresults_first', $usedLang, '<< First', $hscText), $wrapper['disabledLinkWrap']);
				}
			}
			if ($alwaysPrev >= 0) { // Link to previous page
				$previousText = self::getLL_fh002($langObj, 'list_browseresults_prev', $usedLang, '< Previous', $hscText);
				if ($pointer > 0) {
					$linkArray[$pointerName] = ($pointer - 1 ? $pointer - 1 : '');
					$links[] = $cObj->wrap(self::linkTP_keepCtrlVars($pObject, $cObj, $prefixId, $previousText, $linkArray, $bUseCache), $wrapper['inactiveLinkWrap']);
				} elseif ($alwaysPrev) {
					$links[] = $cObj->wrap($previousText, $wrapper['disabledLinkWrap']);
				}
			}

			for($a = $firstPage; $a < $lastPage; $a++) { // Links to pages
				$pageText = '';
				if ($pObject->internal['showRange']) {
					$pageText = (($a * $limit) + 1) . '-' . min($count, (($a + 1) * $limit));
				} else if ($totalPages > 1) {
					if ($wrapper['browseTextWrap']) {
						if ($pointer == $a) { // current page
							$pageText = $cObj->wrap(($a + 1), $wrapper['activeBrowseTextWrap']);
						} else {
							$pageText = $cObj->wrap(($a + 1), $wrapper['browseTextWrap']);
						}
					} else {
						$pageText = trim(self::getLL_fh002($langObj, 'list_browseresults_page', $usedLang, 'Page', $hscText)) . ' ' . ($a + 1);
					}
				}
				if ($pointer == $a) { // current page
					if ($pObject->internal['dontLinkActivePage']) {
						$links[] = $cObj->wrap($pageText, $wrapper['activeLinkWrap']);
					} else if ($pageText != '') {
						$linkArray[$pointerName] = ($a ? $a : '');
						$link = self::linkTP_keepCtrlVars($pObject, $cObj, $prefixId, $pageText, $linkArray, $bUseCache);
						$links[] = $cObj->wrap($link, $wrapper['activeLinkWrap']);
					}
				} else if ($pageText != '') {
					$linkArray[$pointerName] = ($a ? $a : '');
					$links[] = $cObj->wrap(self::linkTP_keepCtrlVars($pObject, $cObj, $prefixId, $pageText, $linkArray, $bUseCache), $wrapper['inactiveLinkWrap']);
				}
			}
			if ($pointer < $totalPages - 1 || $bShowFirstLast) {
				$nextText = self::getLL_fh002($langObj, 'list_browseresults_next', $usedLang, 'Next >', $hscText);
				if ($pointer == $totalPages - 1) { // Link to next page
					$links[] = $cObj->wrap($nextText, $wrapper['disabledLinkWrap']);
				} else {
					$linkArray[$pointerName] = $pointer + 1;
					$links[] = $cObj->wrap(self::linkTP_keepCtrlVars($pObject, $cObj, $prefixId, $nextText, $linkArray, $bUseCache), $wrapper['inactiveLinkWrap']);
				}
			}
			if ($bShowFirstLast) { // Link to last page
				if ($pointer < $totalPages-1) {
					$linkArray[$pointerName] = $totalPages-1;
					$links[]=$cObj->wrap(self::linkTP_keepCtrlVars($pObject, $cObj, $prefixId, self::getLL_fh002($langObj, 'list_browseresults_last', $usedLang, 'Last >>', $hscText), $linkArray, $bUseCache), $wrapper['inactiveLinkWrap']);
				} else {
					$links[]=$cObj->wrap(self::getLL_fh002($langObj, 'list_browseresults_last', $usedLang, 'Last >>', $hscText), $wrapper['disabledLinkWrap']);
				}
			}
			$theLinks = $cObj->wrap(implode(chr(10), $links), $wrapper['browseLinksWrap']);
		} else {
			$theLinks = '';
		}

		$pR1 = $pointer * $limit + 1;
		$pR2 = $pointer * $limit + $limit;

		if ($showResultCount) {
			if (isset($wrapper['showResultsNumbersWrap'])) {
				// this will render the resultcount in a more flexible way using markers (new in TYPO3 3.8.0).
				// the formatting string is expected to hold template markers (see function header). Example: 'Displaying results ###FROM### to ###TO### out of ###OUT_OF###'

				$markerArray['###FROM###'] = $cObj->wrap($count > 0 ? $pR1 : 0,$wrapper['showResultsNumbersWrap']);
				$markerArray['###TO###'] = $cObj->wrap(min($count, $pR2), $wrapper['showResultsNumbersWrap']);
				$markerArray['###OUT_OF###'] = $cObj->wrap($count, $wrapper['showResultsNumbersWrap']);
				$markerArray['###FROM_TO###'] = $cObj->wrap(($count > 0 ? $pR1 : 0) . ' ' . self::getLL_fh002($langObj, 'list_browseresults_to', $usedLang, 'to') . ' ' . min($count,$pR2),$wrapper['showResultsNumbersWrap']);
				$markerArray['###CURRENT_PAGE###'] = $cObj->wrap($pointer + 1, $wrapper['showResultsNumbersWrap']);
				$markerArray['###TOTAL_PAGES###'] = $cObj->wrap($totalPages, $wrapper['showResultsNumbersWrap']);
				$list_browseresults_displays = self::getLL_fh002($langObj,  'list_browseresults_displays_marker', $usedLang, 'Displaying results ###FROM### to ###TO### out of ###OUT_OF###');
				// substitute markers
				$resultCountMsg = $cObj->substituteMarkerArray($list_browseresults_displays, $markerArray);
			} else {
				// render the resultcount in the "traditional" way using sprintf
				$resultCountMsg = sprintf(
					str_replace(
						'###SPAN_BEGIN###',
						'<span' . self::classParam_fh001('browsebox-strong', '', $prefixId) . '>',
						self::getLL_fh002($langObj, 'list_browseresults_displays', $usedLang, 'Displaying results ###SPAN_BEGIN###%s to %s</span> out of ###SPAN_BEGIN###%s</span>')
					),
					$count > 0 ? $pR1 : 0,
					min($count, $pR2),
					$count
				);
			}
			$resultCountMsg = $cObj->wrap($resultCountMsg, $wrapper['showResultsWrap']);
		} else {
			$resultCountMsg = '';
		}
		$rc = $cObj->wrap($resultCountMsg . $theLinks, $wrapper['browseBoxWrap']);
		return $rc;
	}


    /**
     * Returns a results browser. This means a bar of page numbers plus a "previous" and "next" link. For each entry in the bar the ctrlVars "pointer" will be pointing to the "result page" to show.
     * Using $this->ctrlVars['pointer'] as pointer to the page to display. Can be overwritten with another string ($pointerName) to make it possible to have more than one pagebrowser on a page)
     * Using $this->internal['resCount'], $this->internal['limit'] and $this->internal['maxPages'] for count number, how many results to show and the max number of pages to include in the browse bar.
     * Using $this->internal['dontLinkActivePage'] as switch if the active (current) page should be displayed as pure text or as a link to itself
     * Using $this->internal['bShowFirstLast'] as switch if the two links named "<< First" and "LAST >>" will be shown and point to the first or last page.
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
     * @param   object      parent object of type tx_div2007_alpha_browse_base
     * @param   object      language object of type tx_div2007_alpha_language_base
     * @param   object      cObject
     * @param   string      prefix id
     * @param   boolean     if CSS styled content with div tags shall be used
     * @param   integer     determines how the results of the pagerowser will be shown. See description below
     * @param   string      Attributes for the table tag which is wrapped around the table cells containing the browse links
     *                      (only used if no CSS style is set)
     * @param   array       Array with elements to overwrite the default $wrapper-array.
     * @param   string      varname for the pointer.
     * @param   boolean     enable htmlspecialchars() for the tx_div2007_alpha5::getLL_fh002 function (set this to FALSE if you want e.g. use images instead of text for links like 'previous' and 'next').
     * @param   array       Additional query string to be passed as parameters to the links
     * @return  string      Output HTML-Table, wrapped in <div>-tags with a class attribute (if $wrapArr is not passed,
     */
    static public function list_browseresults_fh004 (
        tx_div2007_alpha_browse_base $pObject,
        tx_div2007_alpha_language_base $langObj,
        $cObj,
        $prefixId,
        $bCSSStyled = TRUE,
        $showResultCount = 1,
        $browseParams = '',
        $wrapArr = array(),
        $pointerName = 'pointer',
        $hscText = TRUE,
        $addQueryString = array()
    ) {
        $usedLang = '';
        $linkArray = $addQueryString;
            // Initializing variables:
        $pointer = intval($pObject->ctrlVars[$pointerName]);
        $count = intval($pObject->internal['resCount']);
        $limit =
            tx_div2007_core::intInRange(
                $pObject->internal['limit'],
                1,
                1000
            );
        $totalPages = ceil($count/$limit);
        $maxPages =
            tx_div2007_core::intInRange(
                $pObject->internal['maxPages'],
                1,
                100
            );
        $bUseCache =
            self::autoCache_fh001(
                $pObject,
                $pObject->ctrlVars
            );

            // $showResultCount determines how the results of the pagerowser will be shown.
            // If set to 0: only the result-browser will be shown
            //           1: (default) the text "Displaying results..." and the result-browser will be shown.
            //           2: only the text "Displaying results..." will be shown
        $showResultCount = intval($showResultCount);

            // if this is set, two links named "<< First" and "LAST >>" will be shown and point to the very first or last page.
        $bShowFirstLast = $pObject->internal['bShowFirstLast'];

            // if this has a value the "previous" button is always visible (will be forced if "bShowFirstLast" is set)
        $alwaysPrev =
            (
                $bShowFirstLast ?
                    TRUE :
                    $pObject->internal['bAlwaysPrev']
            );

        if (isset($pObject->internal['pagefloat'])) {
            if (strtoupper($pObject->internal['pagefloat']) == 'CENTER') {
                $pagefloat = ceil(($maxPages - 1) / 2);
            } else {
                // pagefloat set as integer. 0 = left, value >= $pObject->internal['maxPages'] = right
                $pagefloat =
                    tx_div2007_core::intInRange(
                        $pObject->internal['pagefloat'],
                        -1,
                        $maxPages - 1
                    );
            }
        } else {
            $pagefloat = -1; // pagefloat disabled
        }

                // default values for "traditional" wrapping with a table. Can be overwritten by vars from $wrapArr
        if ($bCSSStyled) {
            $wrapper['disabledLinkWrap'] = '<span class="disabledLinkWrap">|</span>';
            $wrapper['inactiveLinkWrap'] = '<span class="inactiveLinkWrap">|</span>';
            $wrapper['activeLinkWrap'] = '<span class="activeLinkWrap">|</span>';
            $wrapper['browseLinksWrap'] = '<div class="browseLinksWrap">|</div>';
            $wrapper['disabledNextLinkWrap'] = '<span class="pagination-next">|</span>';
            $wrapper['inactiveNextLinkWrap'] = '<span class="pagination-next">|</span>';
            $wrapper['disabledPreviousLinkWrap'] = '<span class="pagination-previous">|</span>';
            $wrapper['inactivePreviousLinkWrap'] = '<span class="pagination-previous">|</span>';
        } else {
            $wrapper['disabledLinkWrap'] = '<td nowrap="nowrap"><p>|</p></td>';
            $wrapper['inactiveLinkWrap'] = '<td nowrap="nowrap"><p>|</p></td>';
            $wrapper['activeLinkWrap'] = '<td' .
                self::classParam_fh001(
                    'browsebox-SCell', '', $prefixId
                ) . ' nowrap="nowrap"><p>|</p></td>';
            $wrapper['browseLinksWrap'] = trim('<table ' . $browseParams) . '><tr>|</tr></table>';
        }

        if (is_array($pObject->internal['image']) && $pObject->internal['image']['path']) {
            $onMouseOver = ($pObject->internal['image']['onmouseover'] ? 'onmouseover="'.$pObject->internal['image']['onmouseover'] . '" ': '');
            $onMouseOut = ($pObject->internal['image']['onmouseout'] ? 'onmouseout="' . $pObject->internal['image']['onmouseout'] . '" ': '');
            $onMouseOverActive = ($pObject->internal['imageactive']['onmouseover'] ? 'onmouseover="' . $pObject->internal['imageactive']['onmouseover'] . '" ': '');
            $onMouseOutActive = (
                $pObject->internal['imageactive']['onmouseout'] ?
                    'onmouseout="' . $pObject->internal['imageactive']['onmouseout'] . '" ':
                    ''
            );
            $wrapper['browseTextWrap'] = '<img src="' . $pObject->internal['image']['path'] . $pObject->internal['image']['filemask'] . '" ' . $onMouseOver . $onMouseOut . '>';
            $wrapper['activeBrowseTextWrap'] = '<img src="' . $pObject->internal['image']['path'] . $pObject->internal['imageactive']['filemask'] . '" ' . $onMouseOverActive . $onMouseOutActive . '>';
        }

        if ($bCSSStyled) {
            $wrapper['showResultsWrap'] = '<div class="showResultsWrap">|</div>';
            $wrapper['browseBoxWrap'] = '<div class="browseBoxWrap">|</div>';
        } else {
            $wrapper['showResultsWrap'] = '<p>|</p>';
            $wrapper['browseBoxWrap'] = '
            <!--
                List browsing box:
            -->
            <div ' . self::classParam_fh001('browsebox', '', $prefixId) . '>
                |
            </div>';
        }

            // now overwrite all entries in $wrapper which are also in $wrapArr
        $wrapper = array_merge($wrapper, $wrapArr);

        if ($showResultCount != 2) { //show pagebrowser
            if ($pagefloat > -1) {
                $lastPage =
                    min(
                        $totalPages,
                        max(
                            $pointer + 1 + $pagefloat,
                            $maxPages
                        )
                    );
                $firstPage = max(0, $lastPage - $maxPages);
            } else {
                $firstPage = 0;
                $lastPage =
                    tx_div2007_core::intInRange(
                        $totalPages,
                        1,
                        $maxPages
                    );
            }
            $links = array();

                // Make browse-table/links:
            if ($bShowFirstLast) { // Link to first page
                if ($pointer > 0) {
                    $linkArray[$pointerName] = null;
                    $links[] =
                        $cObj->wrap(
                            self::linkTP_keepCtrlVars(
                                $pObject,
                                $cObj,
                                $prefixId,
                                self::getLL_fh003(
                                    $langObj,
                                    'list_browseresults_first',
                                    $usedLang,
                                    '<< First',
                                    $hscText
                                ),
                                $linkArray,
                                $bUseCache
                            ),
                            $wrapper['inactiveLinkWrap']
                        );
                } else {
                    $links[] =
                        $cObj->wrap(
                            self::getLL_fh003(
                                $langObj,
                                'list_browseresults_first',
                                $usedLang,
                                '<< First',
                                $hscText
                            ),
                            $wrapper['disabledLinkWrap']
                        );
                }
            }

            if ($alwaysPrev >= 0) { // Link to previous page
                $previousText =
                    self::getLL_fh003(
                        $langObj,
                        'list_browseresults_prev',
                        $usedLang,
                        '< Previous',
                        $hscText
                    );
                if ($pointer > 0) {
                    $linkArray[$pointerName] = ($pointer - 1 ? $pointer - 1 : '');
                    $links[] =
                        $cObj->wrap(
                            self::linkTP_keepCtrlVars(
                                $pObject,
                                $cObj,
                                $prefixId,
                                $previousText,
                                $linkArray,
                                $bUseCache
                            ),
                            $wrapper['inactiveLinkWrap']
                        );
                } elseif ($alwaysPrev) {
                    $links[] =
                        $cObj->wrap(
                            $previousText,
                            $wrapper['disabledLinkWrap']
                        );
                }
            }

            for($a = $firstPage; $a < $lastPage; $a++) { // Links to pages
                $pageText = '';
                if ($pObject->internal['showRange']) {
                    $pageText = (($a * $limit) + 1) . '-' .
                        min(
                            $count,
                            (($a + 1) * $limit)
                        );
                } else if ($totalPages > 1) {
                    if ($wrapper['browseTextWrap']) {
                        if ($pointer == $a) { // current page
                            $pageText = $cObj->wrap(($a + 1), $wrapper['activeBrowseTextWrap']);
                        } else {
                            $pageText =
                                $cObj->wrap(
                                    ($a + 1),
                                    $wrapper['browseTextWrap']
                                );
                        }
                    } else {
                        $pageText =
                            trim(
                                self::getLL_fh003(
                                    $langObj,
                                    'list_browseresults_page',
                                    $usedLang,
                                    'Page',
                                    $hscText
                                )
                            ) . ' ' . ($a + 1);
                    }
                }

                $link = null;
                if ($pointer == $a) { // current page
                    if ($pObject->internal['dontLinkActivePage']) {
                        $link =
                            $cObj->wrap(
                                $pageText,
                                $wrapper['activeLinkWrap']
                            );
                    } else if ($pageText != '') {
                        $linkArray[$pointerName] = ($a ? $a : '');
                        $link =
                            self::linkTP_keepCtrlVars(
                                $pObject,
                                $cObj,
                                $prefixId,
                                $pageText,
                                $linkArray,
                                $bUseCache
                            );
                    }
                } else if ($pageText != '') {
                    $linkArray[$pointerName] = ($a ? $a : '');
                    $link =
                        $cObj->wrap(
                            self::linkTP_keepCtrlVars(
                                $pObject,
                                $cObj,
                                $prefixId,
                                $pageText,
                                $linkArray,
                                $bUseCache
                            ),
                            $wrapper['inactiveLinkWrap']
                        );
                }
                if (!empty($link)) {
                    $links[] = $link;
                }
            }

            if ($pointer < $totalPages - 1 || $bShowFirstLast) {
                $nextText =
                    self::getLL_fh003(
                        $langObj,
                        'list_browseresults_next',
                        $usedLang,
                        'Next >',
                        $hscText
                    );
                if ($pointer == $totalPages - 1) { // Link to next page
                    $links[] = $cObj->wrap($nextText, $wrapper['disabledLinkWrap']);
                } else {
                    $linkArray[$pointerName] = $pointer + 1;
                    $links[] =
                        $cObj->wrap(
                            self::linkTP_keepCtrlVars(
                                $pObject,
                                $cObj,
                                $prefixId,
                                $nextText,
                                $linkArray,
                                $bUseCache
                            ),
                            $wrapper['inactiveLinkWrap']
                        );
                }
            }

            if ($bShowFirstLast) { // Link to last page
                if ($pointer < $totalPages - 1) {
                    $linkArray[$pointerName] = $totalPages - 1;
                    $links[] =
                        $cObj->wrap(
                            self::linkTP_keepCtrlVars(
                                $pObject,
                                $cObj,
                                $prefixId,
                                self::getLL_fh003(
                                    $langObj,
                                    'list_browseresults_last',
                                    $usedLang,
                                    'Last >>',
                                    $hscText
                                ),
                                $linkArray,
                                $bUseCache
                            ),
                            $wrapper['inactiveLinkWrap']
                        );
                } else {
                    $links[] =
                        $cObj->wrap(
                            self::getLL_fh003(
                                $langObj,
                                'list_browseresults_last',
                                $usedLang,
                                'Last >>',
                                $hscText
                            ),
                            $wrapper['disabledLinkWrap']
                        );
                }
            }
            $theLinks = $cObj->wrap(implode(chr(10), $links), $wrapper['browseLinksWrap']);
        } else {
            $theLinks = '';
        }

        $pR1 = $pointer * $limit + 1;
        $pR2 = $pointer * $limit + $limit;

        if ($showResultCount) {
            if (isset($wrapper['showResultsNumbersWrap'])) {
                // this will render the resultcount in a more flexible way using markers (new in TYPO3 3.8.0).
                // the formatting string is expected to hold template markers (see function header). Example: 'Displaying results ###FROM### to ###TO### out of ###OUT_OF###'

                $markerArray['###FROM###'] = $cObj->wrap($count > 0 ? $pR1 : 0,$wrapper['showResultsNumbersWrap']);
                $markerArray['###TO###'] = $cObj->wrap(min($count, $pR2), $wrapper['showResultsNumbersWrap']);
                $markerArray['###OUT_OF###'] = $cObj->wrap($count, $wrapper['showResultsNumbersWrap']);
                $markerArray['###FROM_TO###'] = $cObj->wrap(($count > 0 ? $pR1 : 0) . ' ' . self::getLL_fh003($langObj, 'list_browseresults_to', $usedLang, 'to') . ' ' . min($count,$pR2),$wrapper['showResultsNumbersWrap']);
                $markerArray['###CURRENT_PAGE###'] = $cObj->wrap($pointer + 1, $wrapper['showResultsNumbersWrap']);
                $markerArray['###TOTAL_PAGES###'] = $cObj->wrap($totalPages, $wrapper['showResultsNumbersWrap']);
                $list_browseresults_displays = self::getLL_fh003($langObj,  'list_browseresults_displays_marker', $usedLang, 'Displaying results ###FROM### to ###TO### out of ###OUT_OF###');
                // substitute markers
                $resultCountMsg = $cObj->substituteMarkerArray($list_browseresults_displays, $markerArray);
            } else {
                // render the resultcount in the "traditional" way using sprintf
                $resultCountMsg = sprintf(
                    str_replace(
                        '###SPAN_BEGIN###',
                        '<span' . self::classParam_fh001('browsebox-strong', '', $prefixId) . '>',
                        self::getLL_fh003($langObj, 'list_browseresults_displays', $usedLang, 'Displaying results ###SPAN_BEGIN###%s to %s</span> out of ###SPAN_BEGIN###%s</span>')
                    ),
                    $count > 0 ? $pR1 : 0,
                    min($count, $pR2),
                    $count
                );
            }
            $resultCountMsg = $cObj->wrap($resultCountMsg, $wrapper['showResultsWrap']);
        } else {
            $resultCountMsg = '';
        }
        $rc = $cObj->wrap($resultCountMsg . $theLinks, $wrapper['browseBoxWrap']);
        return $rc;
    }


	static public function slashName ($name, $apostrophe='"') {
		$name = str_replace(',' , ' ', $name);
		$rc = $apostrophe . addcslashes($name, '<>()@;:\\".[]' . chr('\n')) . $apostrophe;
		return $rc;
	}


	/* workaround to get the absolute image link if no absolute reference prefix is used */
	/* $imageCode is the result of a call $this->cObj->IMAGE(...) */
	static public function fixImageCodeAbsRefPrefix (&$imageCode) {
		if ($GLOBALS['TSFE']->absRefPrefix == '') {
			$absRefPrefix = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_SITE_URL');
			$fixImgCode = str_replace('index.php', $absRefPrefix . 'index.php', $imageCode);
			$fixImgCode = str_replace('src="', 'src="' . $absRefPrefix, $fixImgCode);
			$fixImgCode = str_replace('"uploads/', '"' . $absRefPrefix . 'uploads/', $fixImgCode);
			$imageCode = $fixImgCode;
		}
	}


	static public function fixImageCodeAbsRefPrefix_fh001 (&$imageCode, $domain = '') {
		$absRefPrefix = '';
		$absRefPrefixDomain = '';
		$bSetAbsRefPrefix = FALSE;
		if ($GLOBALS['TSFE']->absRefPrefix != '') {
			$absRefPrefix = $GLOBALS['TSFE']->absRefPrefix;
		} else {
			$bSetAbsRefPrefix = TRUE;
			$absRefPrefix = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_SITE_URL');
		}

		if ($domain != '') {
			$absRefPrefixArray = explode('?', $absRefPrefix);
			$protocollArray = explode('//', $absRefPrefixArray['0']);
			$absRefPrefixArray['0'] = $protocollArray['0'] . '//' . $domain;
			$absRefPrefixDomain = implode('?', $absRefPrefixArray);
		}

		if ($bSetAbsRefPrefix) {
			if ($absRefPrefixDomain != '') {
				$absRefPrefix = $absRefPrefixDomain . '/';
			}
			$fixImgCode = str_replace('index.php', $absRefPrefix . 'index.php', $imageCode);
			$fixImgCode = str_replace('src="', 'src="' . $absRefPrefix, $fixImgCode);
			$fixImgCode = str_replace('"uploads/', '"' . $absRefPrefix . 'uploads/', $fixImgCode);
			$imageCode = $fixImgCode;
		} else {
			if ($absRefPrefixDomain != '') {
				$fixImgCode = str_replace($absRefPrefix . 'index.php', $absRefPrefixDomain . 'index.php', $imageCode);
				$fixImgCode = str_replace('src="' . $absRefPrefix, 'src="' . $absRefPrefixDomain, $fixImgCode);
				$fixImgCode = str_replace('"' . $absRefPrefix . 'uploads/', '"' . $absRefPrefixDomain . 'uploads/', $fixImgCode);
				$imageCode = $fixImgCode;
			}
		}
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
	static public function wrapContentCode_fh004 (
		$content,
		$theCode,
		$prefixId,
		$uid
	) {
		$idNumber = str_replace('_', '-', $prefixId . '-' . strtolower($theCode));
		$classname = $idNumber;
		if ($uid != '') {
			$idNumber .= '-' . $uid;
		}

		$result = '<!-- START: ' . $idNumber . ' --><div id="' . $idNumber . '" class="' . $classname . '">' .
			($content != '' ? $content : '') . '</div><!-- END: ' . $idNumber . ' -->';

		return $result;
	}


	/**
	 * Fetches the character set conversion object of class t3lib_cs
	 *
	 * @param	boolean		if TRUE, then the object will be created if no such object is present
	 * @return	object/boolean		Object of class t3lib_cs or FALSE
	 */
	static public function getCsConvObj ($bCreateIfNotFound = FALSE) {
		$csConvObj = FALSE;

		if (version_compare(TYPO3_version, '8.5.0', '>=')) {
            // nothing
		} else if (is_object($GLOBALS['LANG'])) {
			$csConvObj = $GLOBALS['LANG']->csConvObj;
		} elseif (is_object($GLOBALS['TSFE'])) {
			$csConvObj = $GLOBALS['TSFE']->csConvObj;
		} elseif ($bCreateIfNotFound) {
			$csConvObj = self::makeInstance('t3lib_cs');
		}

		return $csConvObj;
	}


	static public function initFE () {
		global $TYPO3_CONF_VARS, $TSFE, $BE_USER, $error;

		$callingClassName = '\\TYPO3\\CMS\\Core\\Utility\\GeneralUtility';
		/** @var $TSFE \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController */
		$TSFE = call_user_func(
			$callingClassName . '::makeInstance',
			'TYPO3\\CMS\\Frontend\\Controller\\TypoScriptFrontendController',
			$TYPO3_CONF_VARS,
			call_user_func($callingClassName . '::_GP', 'id'),
			call_user_func($callingClassName . '::_GP', 'type'),
			call_user_func($callingClassName . '::_GP', 'no_cache'),
			call_user_func($callingClassName . '::_GP', 'cHash'),
			call_user_func($callingClassName . '::_GP', 'jumpurl'),
			call_user_func($callingClassName . '::_GP', 'MP'),
			call_user_func($callingClassName . '::_GP', 'RDCT')
		);

		if ($TYPO3_CONF_VARS['FE']['pageUnavailable_force']
			&& !call_user_func($callingClassName . '::cmpIP',
				call_user_func($callingClassName . '::getIndpEnv', 'REMOTE_ADDR'),
				$TYPO3_CONF_VARS['SYS']['devIPmask'])
		) {
			$TSFE->pageUnavailableAndExit('This page is temporarily unavailable.');
		}

		$TSFE->connectToDB();
		$TSFE->sendRedirect();

		// Output compression
		// Remove any output produced until now
		ob_clean();
		if ($TYPO3_CONF_VARS['FE']['compressionLevel'] && extension_loaded('zlib')) {
			$callingClassName2 = '\\TYPO3\\CMS\\Core\\Utility\\MathUtility';
			if (call_user_func($callingClassName2 . '::canBeInterpretedAsInteger', $TYPO3_CONF_VARS['FE']['compressionLevel'])) {
				// Prevent errors if ini_set() is unavailable (safe mode)
				@ini_set('zlib.output_compression_level', $TYPO3_CONF_VARS['FE']['compressionLevel']);
			}
			ob_start(
				array(call_user_func($callingClassName . '::makeInstance', 'TYPO3\\CMS\\Frontend\\Utility\\CompressionUtility'), 'compressionOutputHandler')
			);
		}

		// FE_USER
		if (is_object($GLOBALS['TT'])) {
            $GLOBALS['TT']->push('Front End user initialized', '');
		}
		/** @var $TSFE \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController */
		$TSFE->initFEuser();
        if (is_object($GLOBALS['TT'])) {
            $GLOBALS['TT']->pull();
        }

		// BE_USER
		/** @var $BE_USER \TYPO3\CMS\Backend\FrontendBackendUserAuthentication */
		$BE_USER = $TSFE->initializeBackendUser();

		// Process the ID, type and other parameters
		// After this point we have an array, $page in TSFE, which is the page-record of the current page, $id
        if (is_object($GLOBALS['TT'])) {
            $GLOBALS['TT']->push('Process ID', '');
        }
		// Initialize admin panel since simulation settings are required here:
		$callingClassNameBootstrap = '\\TYPO3\\CMS\\Core\\Core\\Bootstrap';
		$bootStrap = call_user_func($callingClassNameBootstrap . '::getInstance');
		if ($TSFE->isBackendUserLoggedIn()) {
			$BE_USER->initializeAdminPanel();
			$bootStrap->loadExtensionTables(TRUE);
		} else {
            if (method_exists($callingClassNameBootstrap, 'loadCachedTca')) {
                $bootStrap->loadCachedTca();
			} else {
                $callingClassNameEid = '\\TYPO3\\CMS\\Frontend\\Utility\\EidUtility';
                call_user_func($callingClassNameEid . '::initTCA');
            }
		}
		$TSFE->checkAlternativeIdMethods();
		$TSFE->clear_preview();
		$TSFE->determineId();
		// Now, if there is a backend user logged in and he has NO access to this page, then re-evaluate the id shown!
		if (
			$TSFE->isBackendUserLoggedIn() &&
			(
				!$BE_USER->extPageReadAccess($TSFE->page) ||
				call_user_func($callingClassName . '::_GP', 'ADMCMD_noBeUser')
			)
		) {
			// \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('ADMCMD_noBeUser') is placed here because
			// \TYPO3\CMS\Version\Hook\PreviewHook might need to know if a backend user is logged in!
			// Remove user
			unset($BE_USER);
			$TSFE->beUserLogin = 0;
			// Re-evaluate the page-id.
			$TSFE->checkAlternativeIdMethods();
			$TSFE->clear_preview();
			$TSFE->determineId();
		}
		$TSFE->makeCacheHash();
        if (is_object($GLOBALS['TT'])) {
            $GLOBALS['TT']->pull();
        }
		// Admin Panel & Frontend editing
		if ($TSFE->isBackendUserLoggedIn()) {
			$BE_USER->initializeFrontendEdit();
			$className1 = '\\TYPO3\\CMS\\Frontend\\View\\AdminPanelView';
			if ($BE_USER->adminPanel instanceof $className1) {
				$bootStrap->initializeLanguageObject();
			}
			$className2 = '\\TYPO3\\CMS\\Core\\FrontendEditing\\FrontendEditingController';
			if ($BE_USER->frontendEdit instanceof $className2) {
				$BE_USER->frontendEdit->initConfigOptions();
			}
		}

		// Starts the template
        if (is_object($GLOBALS['TT'])) {
            $GLOBALS['TT']->push('Start Template', '');
		}
		$TSFE->initTemplate();
        if (is_object($GLOBALS['TT'])) {
            $GLOBALS['TT']->pull();
            // Get from cache
            $GLOBALS['TT']->push('Get Page from cache', '');
        }
        $TSFE->getFromCache();
        if (is_object($GLOBALS['TT'])) {
            $GLOBALS['TT']->pull();
        }
		// Get config if not already gotten
		// After this, we should have a valid config-array ready
		$TSFE->getConfigArray();
		// Convert POST data to internal "renderCharset" if different from the metaCharset
		$TSFE->convPOSTCharset();
		// Setting language and locale
        if (is_object($GLOBALS['TT'])) {
            $GLOBALS['TT']->push('Setting language and locale', '');
        }
		$TSFE->settingLanguage();
		$TSFE->settingLocale();
        if (is_object($GLOBALS['TT'])) {
            $GLOBALS['TT']->pull();
        }

		// Hook for end-of-frontend
		$TSFE->hook_eofe();
		// Finish timetracking
        if (is_object($GLOBALS['TT'])) {
            $GLOBALS['TT']->pull();
        }
        // Check memory usage
        $callingClassNameMonitor = '\\TYPO3\\CMS\\Core\\Utility\\MonitorUtility';
        if (class_exists($callingClassNameMonitor)) {
            $useClassName = substr($callingClassNameMonitor, 1);
            call_user_func($useClassName . '::peakMemoryUsage');
        }

		// Debugging Output
		if (isset($error) && is_object($error) && @is_callable(array($error, 'debugOutput'))) {
			$error->debugOutput();
		}
	}


	/**
	 * Gets information for an extension, eg. version and most-recently-edited-script
	 *
	 * @param	string		Extension key
	 * @param	string		predefined path ... needed if you have the extension in another place
	 * @return	array		Information array (unless an error occured)
	 */
	static public function getExtensionInfo_fh003 ($extKey, $path = '') {
		$result = '';

		if (!$path) {
			$emClass = '\\TYPO3\\CMS\\Core\\Utility\\ExtensionManagementUtility';

			if (
				class_exists($emClass) &&
				method_exists($emClass, 'extPath')
			) {
				// nothing
			} else {
				$emClass = 't3lib_extMgm';
			}
			$path = call_user_func($emClass . '::extPath', $extKey);
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
					foreach ($fieldArray as $field) {
						if (isset($extConf[$field])) {
							// Info from emconf:
							$eInfo[$field] = $extConf[$field];
						}
					}

					if (
						is_array($extConf['constraints']) &&
						is_array($EM_CONF[$extKey]['constraints']['depends'])
					) {
						$eInfo['TYPO3_version'] = $extConf['constraints']['depends']['typo3'];
					} else {
						$eInfo['TYPO3_version'] = $extConf['TYPO3_version'];
					}
					$filesHash = unserialize($extConf['_md5_values_when_last_written']);
					$eInfo['manual'] =
						@is_file($path . '/doc/manual.sxw') ||
						@is_file($path . '/Documentation/Index.rst');
					$result = $eInfo;
				} else {
					$result = 'ERROR: The array $EM_CONF is wrong in file: ' . $file;
				}
			} else {
				$result = 'ERROR: No file ext_emconf.php could be found: ' . $file;
			}
		} else {
			$result = 'ERROR: Path not found: ' . $path;
		}

		return $result;
	}


	/**
	 * Get External CObjects
	 * @param	object		tx_div2007_alpha_language_base object
	 * @param	string		Configuration Key
	 */
	static public function getExternalCObject_fh003 (
		$pOb,
		$mConfKey
	) {
		$result = '';

		if (
			$pOb->conf[$mConfKey] &&
			$pOb->conf[$mConfKey . '.']
		) {
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
	 * Wraps the input string in a <div> tag with the class attribute set to the prefixId.
	 * All content returned from your plugins should be returned through this function so all content from your plugin is encapsulated in a <div>-tag nicely identifying the content of your plugin.
	 *
	 * @param	string		HTML content to wrap in the div-tags with the "main class" of the plugin
	 * @return	string		HTML content wrapped, ready to return to the parent object.
	 * @see pi_wrapInBaseClass()
	 */
	static public function wrapInBaseClass_fh002 (
		$str,
		$prefixId,
		$extKey
	) {
		$content = '<div class="' . str_replace('_', '-', $prefixId) . '">
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
	* Invokes a user process
	*
	* @param object $pObject: the name of the parent object
	* @param array  $conf:    the base TypoScript setup
	* @param array  $mConfKey: the configuration array of the user process
	* @param array  $passVar: the array of variables to be passed to the user process
	* @return array the updated array of passed variables
	*/
	static public function userProcess_fh002 (
		$pObject,
		$conf,
		$mConfKey,
		$passVar
	) {
		if (
			isset($conf) &&
			is_array($conf) &&
			$conf[$mConfKey]
		) {
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
	 * run function from external cObject
	 * @param	object		tx_div2007_alpha_language_base object
	 */
	static public function load_noLinkExtCobj_fh002 ($langObj) {
		if (
			$langObj->conf['externalProcessing_final'] ||
			is_array($langObj->conf['externalProcessing_final.'])
		) {	// If there is given another cObject for the final order confirmation template!
			$langObj->externalCObject =
				self::getExternalCObject_fh003(
					$langObj,
					'externalProcessing_final'
				);
		}
	} // load_noLinkExtCobj


	/**
	 * Returns the help page with a mini guide how to setup the extension
	 *
	 * example:
	 * 	$content .= tx_div2007_alpha5::displayHelpPage_fh003($this->cObj->fileResource('EXT:myextension/template/help.tmpl'));
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
	static public function displayHelpPage_fh003 (
		$langObj,
		$cObj,
		$helpTemplate,
		$extKey,
		$errorMessage = '',
		$theCode = ''
	) {
		$emClass = '\\TYPO3\\CMS\\Core\\Utility\\ExtensionManagementUtility';

		if (
			class_exists($emClass) &&
			method_exists($emClass, 'extPath')
		) {
			// nothing
		} else {
			$emClass = 't3lib_extMgm';
		}

			// Get language version
		$helpTemplate_lang='';
		if ($langObj->LLkey) {
			$helpTemplate_lang =
				$cObj->getSubpart(
					$helpTemplate,
					'###TEMPLATE_' . $langObj->LLkey . '###'
				);
		}

		$helpTemplate = $helpTemplate_lang ? $helpTemplate_lang : $cObj->getSubpart($helpTemplate,'###TEMPLATE_DEFAULT###');
			// Markers and substitution:

		$markerArray['###PATH###'] = call_user_func($emClass . '::siteRelPath', $extKey);
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
	static public function loadTcaAdditions_fh002 ($ext_keys) {
		global $_EXTKEY, $TCA, $TYPO3_CONF_VARS;

		$typoVersion = tx_div2007_core::getTypoVersion();

		if ($typoVersion < '6002000') {
			$loadTcaAdditions = TRUE;

			//Merge all ext_keys
			if (is_array($ext_keys)) {

				foreach ($ext_keys as $_EXTKEY) {

					if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded($_EXTKEY)) {
						//Include the ext_table
						require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'ext_tables.php');
					}
				}
			}

				// ext-script
			if (TYPO3_extTableDef_script) {
				require_once(PATH_typo3conf . TYPO3_extTableDef_script);
			}
		}
	}


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
	static public function RTEcssText ($cObj, $str) {
		$parseFunc = $GLOBALS['TSFE']->tmpl->setup['lib.']['parseFunc_RTE.'];
		if (is_array($parseFunc)) {
			$str = $cObj->parseFunc($str, $parseFunc);
		}
		return $str;
	}


	/**
	 * Split Label function for front-end applications.
	 *
	 * @param	string		Key string. Accepts the "LLL:" prefix.
	 * @return	string		Label value, if any.
	 */
	static public function sL_fh002 ($input) {
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
	 * Returns a class-name prefixed with $this->prefixId and with all underscores substituted to dashes (-)
	 * this is an initial state, not yet finished! Therefore the debug lines have been left.
	 *
	 * @param	string		The class name (or the END of it since it will be prefixed by $prefixId.'-')
	 * @param	string		$prefixId
	 * @return	string		The combined class name (with the correct prefix)
	 */
	static public function unserialize_fh002 (
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
										if ($controlArray[$controlIndex] == 'a' && $controlData[$controlIndex]['k'] == '' && $controlCount[$controlIndex] < $controlData[$controlIndex]['param'])	{
											$controlData[$controlIndex]['k'] = $param2;
											continue 2;
										}
									}

									if ($controlArray[$controlIndex] == 'a' && $controlCount[$controlIndex] < $controlData[$controlIndex]['param'] && isset($controlData[$controlIndex]['k']))	{
										$controlCount[$controlIndex]++;
										$var[$controlData[$controlIndex]['k']] = $param2;
										$controlData[$controlIndex]['k'] = '';
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
						trigger_error('unserialize_fh002(): Error at offset ' . $errorOffset . ' of ' . $len . ' bytes \'' . substr($str, $errorOffset, 12) . '\'', E_USER_NOTICE);
						$rc = FALSE;
					}
				break;
			}
		}
		if (isset($var) && (!$bErrorCheck || $errorOffset == 0)) {
			$rc = $var;
		}
		return $rc;
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
		$memberGroups = \TYPO3\CMS\Core\Utility\GeneralUtility::intExplode(',', $GLOBALS['TSFE']->gr_list);
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
	 * @param	integer		If $show_hidden is set (0/1), any hidden-fields in records are ignored. NOTICE: If you call this function, consider what to do with the show_hidden parameter. Maybe it should be set? See TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer->enableFields where it's implemented correctly.
	 * @param	array		Array you can pass where keys can be "disabled", "starttime", "endtime", "fe_group" (keys from "enablefields" in TCA) and if set they will make sure that part of the clause is not added. Thus disables the specific part of the clause. For previewing etc.
	 * @param	boolean		If set, enableFields will be applied regardless of any versioning preview settings which might otherwise disable enableFields
	 * @return	string		The clause starting like " AND ...=... AND ...=..."
	 * @see TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::enableFields(), deleteClause()
	 */
	static public function enableFields ($table, $show_hidden = -1, $ignore_array = array(), $noVersionPreview = FALSE) {
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
					if ($ctrl['enablecolumns']['disabled'] && !$show_hidden && !$ignore_array['disabled']) {
						$field = $table . '.' . $ctrl['enablecolumns']['disabled'];
						$query .= ' AND ' . $field . '=0';
					}
					if ($ctrl['enablecolumns']['starttime'] && !$ignore_array['starttime']) {
						$field = $table . '.' . $ctrl['enablecolumns']['starttime'];
						$query .= ' AND ' . $field . '<=' . $GLOBALS['SIM_ACCESS_TIME'];
					}
					if ($ctrl['enablecolumns']['endtime'] && !$ignore_array['endtime']) {
						$field = $table . '.' . $ctrl['enablecolumns']['endtime'];
						$query .= ' AND (' . $field . '=0 OR ' . $field . '>' . $GLOBALS['SIM_ACCESS_TIME'] . ')';
					}
					if ($ctrl['enablecolumns']['fe_group'] && !$ignore_array['fe_group']) {
						$field = $table . '.' . $ctrl['enablecolumns']['fe_group'];
						$query .= self::getMultipleGroupsWhereClause($field, $table);
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
							$query .= \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($_funcRef, $_params, $tmp = 'tx_div2007_alpha5');
						}
					}
				}
			}
		} else {
			throw new InvalidArgumentException(
				'There is no entry in the $GLOBALS[\'TCA\'] array for the table "' . $table .
				'". This means that the function enableFields() is ' .
				'called with an invalid table name as argument.',
				1283790586
			);
		}

		return $query;
	}
}


if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/div2007/class.tx_div2007_alpha5.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/div2007/class.tx_div2007_alpha5.php']);
}

