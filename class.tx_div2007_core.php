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
 * class tslib_cObj	All main TypoScript features, rendering of content objects (cObjects). This class is the backbone of TypoScript Template rendering.
 *
 * @package    TYPO3
 * @subpackage div2007
 * @author	Franz Holzinger <franz@ttproducts.de>
 */



class tx_div2007_core {

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
	public static function testInt ($var) {
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

	public static function intInRange ($theInt, $min, $max = 2000000000, $zeroValue = 0) {
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

	public static function intval_positive ($theInt) {
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
	public function newHtmlParser () {
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
	public function newTsParser () {
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
	public function newMailMessage () {

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
		$callingClassName = '\\TYPO3\\CMS\\Core\\Utility\\DebugUtility';

		if (
			class_exists($callingClassName) &&
			method_exists($callingClassName, 'debugTrail')
		) {
			call_user_func($callingClassName . '::debugTrail');
		} else if (
			class_exists('t3lib_utility_Debug') &&
			method_exists('t3lib_utility_Debug', 'debugTrail')
		) {
			t3lib_utility_Debug::debugTrail($var, $header, $group);
		} else if (
			class_exists('t3lib_div') &&
			method_exists('t3lib_div', 'debugTrail')
		) {
			t3lib_div::debugTrail();
		}
	}

	### BACKEND

	### Backend Utility

	static public function getTCAtypes ($table, $rec, $useFieldNameAsKey = 0) {
		$useClassName = '';
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

			call_user_func($useClassName . '::getTCAtypes', $table, $rec, $useFieldNameAsKey);
		}
	}

	static public function getRecord ($table, $uid, $fields = '*', $where = '', $useDeleteClause = TRUE) {
		$useClassName = '';
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

			call_user_func($useClassName . '::getRecord', $table, $uid, $fields, $where, $useDeleteClause);
		}
	}

	static public function deleteClause ($table, $tableAlias = '') {
		$useClassName = '';
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

			call_user_func($useClassName . '::deleteClause', $table, $tableAlias);
		}
	}

	static public function getTCEFORM_TSconfig ($table, $row) {
		$useClassName = '';
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

			call_user_func($useClassName . '::getTCEFORM_TSconfig', $table, $row);
		}
	}
}

?>