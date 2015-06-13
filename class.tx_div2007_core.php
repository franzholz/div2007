<?php
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
 * adapter for the call of TYPO3 core functions
 * It takes care of the differences between the TYPO3 versions 4.5 and 6.2.
 * See the TYPO3 core files for the descriptions of these functions.
 *
 * $Id$
 *
 * class tslib_cObj All main TypoScript features, rendering of content objects (cObjects). This class is the backbone of TypoScript Template rendering.
 *
 * @package    TYPO3
 * @subpackage div2007
 * @author	Franz Holzinger <franz@ttproducts.de>
 */


class tx_div2007_core {
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

	static public function getTypoVersion () {
		$result = FALSE;
		$callingClassName = '\\TYPO3\\CMS\\Core\\Utility\\VersionNumberUtility';
		if (
			class_exists($callingClassName) &&
			method_exists($callingClassName, 'convertVersionNumberToInteger')
		) {
			$result = call_user_func($callingClassName . '::convertVersionNumberToInteger', TYPO3_version);
		} else if (
			class_exists('t3lib_utility_VersionNumber') &&
			method_exists('t3lib_utility_VersionNumber', 'convertVersionNumberToInteger')
		) {
			$result = t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version);
		} else if (
			class_exists('t3lib_div') &&
			method_exists('t3lib_div', 'int_from_ver')
		) {
			$result = t3lib_div::int_from_ver(TYPO3_version);
		}

		return $result;
	}


	### Mathematical functions
	static public function testInt ($var) {
		$result = FALSE;
		$callingClassName = '\\TYPO3\\CMS\\Core\\Utility\\MathUtility';

		if (
			class_exists($callingClassName) &&
			method_exists($callingClassName, 'canBeInterpretedAsInteger')
		) {
			$result = call_user_func($callingClassName . '::canBeInterpretedAsInteger', $var);
		} else if (
			class_exists('t3lib_utility_Math') &&
			method_exists('t3lib_utility_Math', 'canBeInterpretedAsInteger')
		) {
			$result = t3lib_utility_Math::canBeInterpretedAsInteger($var);
		} else if (
			class_exists('t3lib_div') &&
			method_exists('t3lib_div', 'testInt')
		) {
			$result = t3lib_div::testInt($var);
		}

		return $result;
	}

	static public function intInRange ($theInt, $min, $max = 2000000000, $zeroValue = 0) {
		$result = FALSE;
		$callingClassName = '\\TYPO3\\CMS\\Core\\Utility\\MathUtility';

		if (
			class_exists($callingClassName) &&
			method_exists($callingClassName, 'forceIntegerInRange')
		) {
			$result = call_user_func($callingClassName . '::forceIntegerInRange', $theInt, $min, $max, $zeroValue);
		} else if (
			class_exists('t3lib_utility_Math') &&
			method_exists('t3lib_utility_Math', 'forceIntegerInRange')
		) {
			$result = t3lib_utility_Math::forceIntegerInRange($theInt, $min, $max, $zeroValue);
		} else if (
			class_exists('t3lib_div') &&
			method_exists('t3lib_div', 'intInRange')
		) {
			$result = t3lib_div::intInRange($theInt, $min, $max, $zeroValue);
		}
		return $result;
	}

	static public function intval_positive ($theInt) {
		$result = FALSE;
		$callingClassName = '\\TYPO3\\CMS\\Core\\Utility\\MathUtility';

		if (
			class_exists($callingClassName) &&
			method_exists($callingClassName, 'convertToPositiveInteger')
		) {
			$result = call_user_func($callingClassName . '::convertToPositiveInteger', $theInt);
		} else if (
			class_exists('t3lib_utility_Math') &&
			method_exists('t3lib_utility_Math', 'convertToPositiveInteger')
		) {
			$result = t3lib_utility_Math::convertToPositiveInteger($theInt);
		} else if (
			class_exists('t3lib_div') &&
			method_exists('t3lib_div', 'intval_positive')
		) {
			$result = t3lib_div::intval_positive($theInt);
		}

		return $result;
	}


	### HTML parser object
	static public function newHtmlParser () {
		$useClassName = '';
		$callingClassName = '\\TYPO3\\CMS\\Core\\Html\\HtmlParser';

		if (
			class_exists($callingClassName)
		) {
			$useClassName = substr($callingClassName, 1);
		} else if (
			class_exists('t3lib_parsehtml')
		) {
			$useClassName = 't3lib_parsehtml';
		}

		$result = t3lib_div::makeInstance($useClassName);
		return $result;
	}


	### TS parser object
	static public function newTsParser () {
		$useClassName = '';
		$callingClassName = '\\TYPO3\\CMS\\Core\\TypoScript\\Parser\\TypoScriptParser';

		if (
			class_exists($callingClassName)
		) {
			$useClassName = substr($callingClassName, 1);
		} else if (
			class_exists('t3lib_tsparser')
		) {
			$useClassName = 't3lib_tsparser';
		}

		$result = t3lib_div::makeInstance($useClassName);
		return $result;
	}


	### Mail object
	static public function newMailMessage () {

		$useClassName = '';
		$callingClassName = '\\TYPO3\\CMS\\Mail\\MailMessage';

		if (
			class_exists($callingClassName)
		) {
			$useClassName = substr($callingClassName, 1);
		} else if (
			class_exists('t3lib_mail_Message')
		) {
			$useClassName = 't3lib_mail_Message';
		}

		$result = t3lib_div::makeInstance($useClassName);
		return $result;
	}


	### Caching Framework
	static public function initializeCachingFramework () {
		$useClassName = '';
		$callingClassName = '\\TYPO3\\CMS\\Core\\Cache\\Cache';

		if (
			class_exists($callingClassName)
		) {
			$useClassName = substr($callingClassName, 1);
		} else if (
			class_exists('t3lib_cache')
		) {
			$useClassName = 't3lib_cache';
		}

		if (method_exists($useClassName, 'initializeCachingFramework')) {

			call_user_func($useClassName . '::initializeCachingFramework');
		}
	}


	### Debug Utility
	static public function debug ($var = '', $header = '', $group = 'Debug') {
		$callingClassName = '\\TYPO3\\CMS\\Core\\Utility\\DebugUtility';

		if (
			class_exists($callingClassName) &&
			method_exists($callingClassName, 'debug')
		) {
			call_user_func($callingClassName . '::debug', $var, $header, $group);
		} else if (
			class_exists('t3lib_utility_Debug') &&
			method_exists('t3lib_utility_Debug', 'debug')
		) {
			t3lib_utility_Debug::debug($var, $header, $group);
		} else if (
			class_exists('t3lib_div') &&
			method_exists('t3lib_div', 'debug')
		) {
			t3lib_div::debug($var, $header, $group);
		}
	}

	static public function debugTrail () {
		$result = FALSE;
		$callingClassName = '\\TYPO3\\CMS\\Core\\Utility\\DebugUtility';

		if (
			class_exists($callingClassName) &&
			method_exists($callingClassName, 'debugTrail')
		) {
			$result = call_user_func($callingClassName . '::debugTrail');
		} else if (
			class_exists('t3lib_utility_Debug') &&
			method_exists('t3lib_utility_Debug', 'debugTrail')
		) {
			$result = t3lib_utility_Debug::debugTrail();
		} else if (
			class_exists('t3lib_div') &&
			method_exists('t3lib_div', 'debugTrail')
		) {
			$result = t3lib_div::debugTrail();
		}

		return $result;
	}


	### BACKEND

	### Backend Utility
	static public function getTCAtypes ($table, $rec, $useFieldNameAsKey = 0) {
		$useClassName = '';
		$result = FALSE;
		$callingClassName = '\\TYPO3\\CMS\\Backend\\Utility\\BackendUtility';

		if (
			class_exists($callingClassName)
		) {
			$useClassName = substr($callingClassName, 1);
		} else if (
			class_exists('t3lib_BEfunc')
		) {
			$useClassName = 't3lib_BEfunc';
		}

		if (method_exists($useClassName, 'getTCAtypes')) {

			$result = call_user_func($useClassName . '::getTCAtypes', $table, $rec, $useFieldNameAsKey);
		}

		return $result;
	}

	static public function getRecord ($table, $uid, $fields = '*', $where = '', $useDeleteClause = TRUE) {
		$useClassName = '';
		$result = FALSE;
		$callingClassName = '\\TYPO3\\CMS\\Backend\\Utility\\BackendUtility';

		if (
			class_exists($callingClassName)
		) {
			$useClassName = substr($callingClassName, 1);
		} else if (
			class_exists('t3lib_BEfunc')
		) {
			$useClassName = 't3lib_BEfunc';
		}

		if (method_exists($useClassName, 'getRecord')) {

			$result = call_user_func($useClassName . '::getRecord', $table, $uid, $fields, $where, $useDeleteClause);
		}

		return $result;
	}

	static public function deleteClause ($table, $tableAlias = '') {
		$useClassName = '';
		$result = FALSE;
		$callingClassName = '\\TYPO3\\CMS\\Backend\\Utility\\BackendUtility';

		if (
			class_exists($callingClassName)
		) {
			$useClassName = substr($callingClassName, 1);
		} else if (
			class_exists('t3lib_BEfunc')
		) {
			$useClassName = 't3lib_BEfunc';
		}

		if (method_exists($useClassName, 'deleteClause')) {

			$result = call_user_func($useClassName . '::deleteClause', $table, $tableAlias);
		}

		return $result;
	}

	static public function getTCEFORM_TSconfig ($table, $row) {
		$useClassName = '';
		$result = FALSE;
		$callingClassName = '\\TYPO3\\CMS\\Backend\\Utility\\BackendUtility';

		if (
			class_exists($callingClassName)
		) {
			$useClassName = substr($callingClassName, 1);
		} else if (
			class_exists('t3lib_BEfunc')
		) {
			$useClassName = 't3lib_BEfunc';
		}

		if (method_exists($useClassName, 'getTCEFORM_TSconfig')) {

			$result = call_user_func($useClassName . '::getTCEFORM_TSconfig', $table, $row);
		}

		return $result;
	}


	### TYPO3 SPECIFIC FUNCTIONS

	static public function calculateCacheHash (array $params) {
		$useClassName = '';
		$result = FALSE;
		$callingClassName = '\\TYPO3\\CMS\\Frontend\\Page\\CacheHashCalculator';
		if (
			class_exists($callingClassName)
		) {
			$useClassName = substr($callingClassName, 1);
		} else if (
			class_exists('t3lib_cacheHash')
		) {
			$useClassName = 't3lib_cacheHash';
		}

		if (method_exists($useClassName, 'calculateCacheHash')) {

			$result = call_user_func($useClassName . '::calculateCacheHash', $params);
		}

		return $result;
	}

	/**
	* generates a hash value out of a string array.
	*
	* Checks the configuration and substitutes defaults for missing values.
	*
	* @param array $params parameter strings
	* @return bool/string hash string if initialization was successful, FALSE otherwise
	* @see tx_myext_class:anotherFunc()
	*/

	static public function generateHash (array $params, $limit = 20) {
		$result = FALSE;
		$typoVersion = self::getTypoVersion();

		if ($typoVersion < 4007000) {
			$regHash_array = t3lib_div::cHashParams(t3lib_div::implodeArrayForUrl('', $params));
			$result = t3lib_div::shortMD5(serialize($regHash_array), $limit);
		} else {
			$regHash_calc = self::calculateCacheHash($params);
			$result = substr($regHash_calc, 0, $limit);
		}
		return $result;
	}


	### SQL

	/**
	 * @return array
	 */
	static public function getSystemFields () {
		return self::$systemFields;
	}

	/**
	 * Returns an array containing the regular field names.
	 *
	 * @return array
	 */
	static public function getFields ($table) {
		$result = FALSE;

		if (is_array($GLOBALS['TCA'][$table]['columns'])) {
			$tcaFields = array_keys($GLOBALS['TCA'][$table]['columns']);
			$systemFields = self::getSystemFields();
			$result = array_diff($tcaFields, $systemFields);
		}

		return $result;
	}
}

?>