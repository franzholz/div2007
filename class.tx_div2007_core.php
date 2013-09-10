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

	static public function requireOnce ($requireFile) {
		$callingClassName = '\\TYPO3\\CMS\\Core\\Utility\\GeneralUtility';
		if (
			class_exists($callingClassName) &&
			method_exists($callingClassName, 'requireOnce')
		) {
			call_user_func($callingClassName . '::requireOnce', $requireFile);
		} else if (
			class_exists('t3lib_div') &&
			method_exists('t3lib_div', 'requireOnce')
		) {
			t3lib_div::requireOnce($requireFile);
		}
	}

	static public function makeInstance ($className) {
		$result = FALSE;
		$callingClassName = '\\TYPO3\\CMS\\Core\\Utility\\GeneralUtility';
		if (
			class_exists($callingClassName) &&
			method_exists($callingClassName, 'makeInstance')
		) {
			$result = call_user_func($callingClassName . '::makeInstance', $className);
		} else if (
			class_exists('t3lib_div') &&
			method_exists('t3lib_div', 'makeInstance')
		) {
			$result = t3lib_div::makeInstance($className);
		}

		return $result;
	}

	static public function getUserObj ($classRef, $checkPrefix = '', $silent = FALSE) {

		$result = FALSE;
		$callingClassName = '\\TYPO3\\CMS\\Core\\Utility\\GeneralUtility';
		if (
			class_exists($callingClassName) &&
			method_exists($callingClassName, 'getUserObj')
		) {
			$result = call_user_func($callingClassName . '::getUserObj', $classRef, $checkPrefix, $silent);
		} else if (
			class_exists('t3lib_div') &&
			method_exists('t3lib_div', 'getUserObj')
		) {
			$result = t3lib_div::getUserObj($classRef, $checkPrefix, $silent);
		}

		return $result;
	}

	static public function intExplode ($delimiter, $string, $onlyNonEmptyValues = FALSE, $limit = 0) {
		$result = FALSE;
		$callingClassName = '\\TYPO3\\CMS\\Core\\Utility\\GeneralUtility';
		if (
			class_exists($callingClassName) &&
			method_exists($callingClassName, 'intExplode')
		) {
			$result = call_user_func($callingClassName . '::intExplode', $delimiter, $string, $onlyNonEmptyValues, $limit);
		} else if (
			class_exists('t3lib_div') &&
			method_exists('t3lib_div', 'intExplode')
		) {
			$result = t3lib_div::intExplode($delimiter, $string, $onlyNonEmptyValues, $limit);
		}

		return $result;
	}

	static public function trimExplode ($delim, $string, $removeEmptyValues = FALSE, $limit = 0) {
		$result = FALSE;
		$callingClassName = '\\TYPO3\\CMS\\Core\\Utility\\GeneralUtility';
		if (
			class_exists($callingClassName) &&
			method_exists($callingClassName, 'trimExplode')
		) {
			$result = call_user_func($callingClassName . '::trimExplode', $delim, $string, $removeEmptyValues, $limit);
		} else if (
			class_exists('t3lib_div') &&
			method_exists('t3lib_div', 'trimExplode')
		) {
			$result = t3lib_div::trimExplode($delim, $string, $removeEmptyValues, $limit);
		}

		return $result;
	}

	static public function _GPmerged ($parameter) {
		$result = FALSE;
		$callingClassName = '\\TYPO3\\CMS\\Core\\Utility\\GeneralUtility';
		if (
			class_exists($callingClassName) &&
			method_exists($callingClassName, '_GPmerged')
		) {
			$result = call_user_func($callingClassName . '::_GPmerged', $parameter);
		} else if (
			class_exists('t3lib_div') &&
			method_exists('t3lib_div', '_GPmerged')
		) {
			$result = t3lib_div::_GPmerged($parameter);
		}

		return $result;
	}

	static public function _GET ($var = NULL) {
		$result = FALSE;
		$callingClassName = '\\TYPO3\\CMS\\Core\\Utility\\GeneralUtility';
		if (
			class_exists($callingClassName) &&
			method_exists($callingClassName, '_GET')
		) {
			$result = call_user_func($callingClassName . '::_GET', $var);
		} else if (
			class_exists('t3lib_div') &&
			method_exists('t3lib_div', '_GET')
		) {
			$result = t3lib_div::_GET($var);
		}

		return $result;
	}

	static public function _POST ($var = NULL) {
		$result = FALSE;
		$callingClassName = '\\TYPO3\\CMS\\Core\\Utility\\GeneralUtility';
		if (
			class_exists($callingClassName) &&
			method_exists($callingClassName, '_POST')
		) {
			$result = call_user_func($callingClassName . '::_POST', $var);
		} else if (
			class_exists('t3lib_div') &&
			method_exists('t3lib_div', '_POST')
		) {
			$result = t3lib_div::_POST($var);
		}

		return $result;
	}

	static public function _GP ($var) {
		$result = FALSE;
		$callingClassName = '\\TYPO3\\CMS\\Core\\Utility\\GeneralUtility';
		if (
			class_exists($callingClassName) &&
			method_exists($callingClassName, '_GP')
		) {
			$result = call_user_func($callingClassName . '::_GP', $var);
		} else if (
			class_exists('t3lib_div') &&
			method_exists('t3lib_div', '_GP')
		) {
			$result = t3lib_div::_GP($var);
		}

		return $result;
	}

	static public function inList ($list, $item) {
		$result = FALSE;
		$callingClassName = '\\TYPO3\\CMS\\Core\\Utility\\GeneralUtility';
		if (
			class_exists($callingClassName) &&
			method_exists($callingClassName, 'inList')
		) {
			$result = call_user_func($callingClassName . '::inList', $list, $item);
		} else if (
			class_exists('t3lib_div') &&
			method_exists('t3lib_div', 'inList')
		) {
			$result = t3lib_div::inList($list, $item);
		}

		return $result;
	}

	static public function rmFromList ($element, $list) {
		$result = FALSE;
		$callingClassName = '\\TYPO3\\CMS\\Core\\Utility\\GeneralUtility';
		if (
			class_exists($callingClassName) &&
			method_exists($callingClassName, 'rmFromList')
		) {
			$result = call_user_func($callingClassName . '::rmFromList', $element, $list);
		} else if (
			class_exists('t3lib_div') &&
			method_exists('t3lib_div', 'rmFromList')
		) {
			$result = t3lib_div::rmFromList($element, $list);
		}

		return $result;
	}

	static public function getFileAbsFileName ($filename, $onlyRelative = TRUE, $relToTYPO3_mainDir = FALSE) {
		$result = FALSE;
		$callingClassName = '\\TYPO3\\CMS\\Core\\Utility\\GeneralUtility';
		if (
			class_exists($callingClassName) &&
			method_exists($callingClassName, 'getFileAbsFileName')
		) {
			$result = call_user_func($callingClassName . '::getFileAbsFileName', $filename, $onlyRelative, $relToTYPO3_mainDir);
		} else if (
			class_exists('t3lib_div') &&
			method_exists('t3lib_div', 'getFileAbsFileName')
		) {
			$result = t3lib_div::getFileAbsFileName($filename, $onlyRelative, $relToTYPO3_mainDir);
		}

		return $result;
	}

	static public function readLLfile ($fileRef, $langKey, $charset = '', $errorMode = 0) {
		$result = FALSE;
		$callingClassName = '\\TYPO3\\CMS\\Core\\Utility\\GeneralUtility';
		if (
			class_exists($callingClassName) &&
			method_exists($callingClassName, 'readLLfile')
		) {
			$result = call_user_func($callingClassName . '::readLLfile', $fileRef, $langKey, $charset, $errorMode);
		} else if (
			class_exists('t3lib_div') &&
			method_exists('t3lib_div', 'readLLfile')
		) {
			$result = t3lib_div::readLLfile($fileRef, $langKey, $charset, $errorMode);
		}

		return $result;
	}

	static public function array_merge_recursive_overrule (array $arr0, array $arr1, $notAddKeys = FALSE, $includeEmptyValues = TRUE, $enableUnsetFeature = TRUE) {
		$result = FALSE;
		$callingClassName = '\\TYPO3\\CMS\\Core\\Utility\\GeneralUtility';
		if (
			class_exists($callingClassName) &&
			method_exists($callingClassName, 'array_merge_recursive_overrule')
		) {
			$result = call_user_func($callingClassName . '::array_merge_recursive_overrule', $arr0, $arr1, $notAddKeys, $includeEmptyValues, $enableUnsetFeature);
		} else if (
			class_exists('t3lib_div') &&
			method_exists('t3lib_div', 'array_merge_recursive_overrule')
		) {
			$result = t3lib_div::array_merge_recursive_overrule($arr0, $arr1, $notAddKeys, $includeEmptyValues, $enableUnsetFeature);
		}

		return $result;
	}

	static public function xml2array ($string, $NSprefix = '', $reportDocTag = FALSE) {
		$result = FALSE;
		$callingClassName = '\\TYPO3\\CMS\\Core\\Utility\\GeneralUtility';
		if (
			class_exists($callingClassName) &&
			method_exists($callingClassName, 'xml2array')
		) {
			$result = call_user_func($callingClassName . '::xml2array', $string, $NSprefix, $reportDocTag);
		} else if (
			class_exists('t3lib_div') &&
			method_exists('t3lib_div', 'xml2array')
		) {
			$result = t3lib_div::xml2array($string, $NSprefix, $reportDocTag);
		}

		return $result;
	}

	static public function implodeArrayForUrl ($name, array $theArray, $str = '', $skipBlank = FALSE, $rawurlencodeParamName = FALSE) {
		$result = FALSE;
		$callingClassName = '\\TYPO3\\CMS\\Core\\Utility\\GeneralUtility';
		if (
			class_exists($callingClassName) &&
			method_exists($callingClassName, 'implodeArrayForUrl')
		) {
			$result = call_user_func($callingClassName . '::implodeArrayForUrl', $name, $theArray, $str, $skipBlank, $rawurlencodeParamName);
		} else if (
			class_exists('t3lib_div') &&
			method_exists('t3lib_div', 'implodeArrayForUrl')
		) {
			$result = t3lib_div::implodeArrayForUrl($name, $theArray, $str, $skipBlank, $rawurlencodeParamName);
		}

		return $result;
	}

	static public function getIndpEnv ($getEnvName) {
		$result = FALSE;
		$callingClassName = '\\TYPO3\\CMS\\Core\\Utility\\GeneralUtility';
		if (
			class_exists($callingClassName) &&
			method_exists($callingClassName, 'getIndpEnv')
		) {
			$result = call_user_func($callingClassName . '::getIndpEnv', $getEnvName);
		} else if (
			class_exists('t3lib_div') &&
			method_exists('t3lib_div', 'getIndpEnv')
		) {
			$result = t3lib_div::getIndpEnv($getEnvName);
		}

		return $result;
	}

	static public function isFirstPartOfStr ($str, $partStr) {
		$result = FALSE;
		$callingClassName = '\\TYPO3\\CMS\\Core\\Utility\\GeneralUtility';
		if (
			class_exists($callingClassName) &&
			method_exists($callingClassName, 'isFirstPartOfStr')
		) {
			$result = call_user_func($callingClassName . '::isFirstPartOfStr', $str, $partStr);
		} else if (
			class_exists('t3lib_div') &&
			method_exists('t3lib_div', 'isFirstPartOfStr')
		) {
			$result = t3lib_div::isFirstPartOfStr($str, $partStr);
		}

		return $result;
	}

	static public function get_tag_attributes ($tag) {
		$result = FALSE;
		$callingClassName = '\\TYPO3\\CMS\\Core\\Utility\\GeneralUtility';
		if (
			class_exists($callingClassName) &&
			method_exists($callingClassName, 'get_tag_attributes')
		) {
			$result = call_user_func($callingClassName . '::get_tag_attributes', $tag);
		} else if (
			class_exists('t3lib_div') &&
			method_exists('t3lib_div', 'get_tag_attributes')
		) {
			$result = t3lib_div::get_tag_attributes($tag);
		}

		return $result;
	}

	static public function callUserFunction($funcName, &$params, &$ref, $checkPrefix = '', $errorMode = 0) {
		$result = FALSE;
		$callingClassName = '\\TYPO3\\CMS\\Core\\Utility\\GeneralUtility';
		if (
			class_exists($callingClassName) &&
			method_exists($callingClassName, 'callUserFunction')
		) {
			$result = call_user_func($callingClassName . '::callUserFunction', $funcName, $params, $ref, $checkPrefix, $errorMode);
		} else if (
			class_exists('t3lib_div') &&
			method_exists('t3lib_div', 'callUserFunction')
		) {
			$result = t3lib_div::callUserFunction($funcName, $params, $ref, $checkPrefix, $errorMode);
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

	public static function loadTCA ($table) {
		$result = FALSE;
		$callingClassName = '\\TYPO3\\CMS\\Core\\Utility\\GeneralUtility';
		$typoVersion = self::getTypoVersion();

		if (
			$typoVersion >= '6001000'
		) {
			// do nothing
		} else if (
			class_exists($callingClassName) &&
			method_exists($callingClassName, 'loadTCA')
		) {
			$result = call_user_func($callingClassName . '::loadTCA', $table);
		} else if (
			class_exists('t3lib_div') &&
			method_exists('t3lib_div', 'loadTCA')
		) {
			$result = t3lib_div::loadTCA($table);
		}

		return $result;
	}

	static public function slashJS ($string, $extended = FALSE, $char = '\'') {
		$result = FALSE;
		$callingClassName = '\\TYPO3\\CMS\\Core\\Utility\\GeneralUtility';

		if (
			class_exists($callingClassName) &&
			method_exists($callingClassName, 'slashJS')
		) {
			$result = call_user_func($callingClassName . '::slashJS', $string, $extended, $char);
		} else if (
			class_exists('t3lib_div') &&
			method_exists('t3lib_div', 'slashJS', $string, $extended, $char)
		) {
			$result = t3lib_div::slashJS($theInt);
		}

		return $result;
	}

	static public function linkThisScript (array $getParams = array()) {
		$result = FALSE;
		$callingClassName = '\\TYPO3\\CMS\\Core\\Utility\\GeneralUtility';

		if (
			class_exists($callingClassName) &&
			method_exists($callingClassName, 'linkThisScript')
		) {
			$result = call_user_func($callingClassName . '::linkThisScript', $getParams);
		} else if (
			class_exists('t3lib_div') &&
			method_exists('t3lib_div', 'linkThisScript')
		) {
			$result = t3lib_div::linkThisScript($getParams);
		}

		return $result;
	}

	static public function validPathStr ($theFile) {
		$result = FALSE;
		$callingClassName = '\\TYPO3\\CMS\\Core\\Utility\\GeneralUtility';

		if (
			class_exists($callingClassName) &&
			method_exists($callingClassName, 'validPathStr')
		) {
			$result = call_user_func($callingClassName . '::validPathStr', $theFile);
		} else if (
			class_exists('t3lib_div') &&
			method_exists('t3lib_div', 'validPathStr')
		) {
			$result = t3lib_div::validPathStr($theFile);
		}

		return $result;
	}


	### Extension Manager functions:
	static public function extPath ($key, $script = '') {
		$result = FALSE;
		$callingClassName = '\\TYPO3\\CMS\\Core\\Utility\\ExtensionManagementUtility';
		if (
			class_exists($callingClassName) &&
			method_exists($callingClassName, 'extPath')
		) {
			$result = call_user_func($callingClassName . '::extPath', $key, $script);
		} else if (
			class_exists('t3lib_extMgm') &&
			method_exists('t3lib_extMgm', 'extPath')
		) {
			$result = t3lib_extMgm::extPath($key, $script);
		}

		return $result;
	}

	static public function extRelPath($key) {
		$result = FALSE;
		$callingClassName = '\\TYPO3\\CMS\\Core\\Utility\\ExtensionManagementUtility';
		if (
			class_exists($callingClassName) &&
			method_exists($callingClassName, 'extRelPath')
		) {
			$result = call_user_func($callingClassName . '::extRelPath', $key, $script);
		} else if (
			class_exists('t3lib_extMgm') &&
			method_exists('t3lib_extMgm', 'extRelPath')
		) {
			$result = t3lib_extMgm::extRelPath($key, $script);
		}

		return $result;
	}

	static public function isLoaded ($key, $exitOnError = FALSE) {
		$result = FALSE;
		$callingClassName = '\\TYPO3\\CMS\\Core\\Utility\\ExtensionManagementUtility';
		if (
			class_exists($callingClassName) &&
			method_exists($callingClassName, 'isLoaded')
		) {
			$result = call_user_func($callingClassName . '::isLoaded', $key, $exitOnError);
		} else if (
			class_exists('t3lib_extMgm') &&
			method_exists('t3lib_extMgm', 'isLoaded')
		) {
			$result = t3lib_extMgm::isLoaded($key, $exitOnError);
		}

		return $result;
	}

	static public function addTCAcolumns ($table, $columnArray, $addTofeInterface = 0) {
		$callingClassName = '\\TYPO3\\CMS\\Core\\Utility\\ExtensionManagementUtility';
		if (
			class_exists($callingClassName) &&
			method_exists($callingClassName, 'addTCAcolumns')
		) {
			call_user_func($callingClassName . '::addTCAcolumns', $table, $columnArray, $addTofeInterface);
		} else if (
			class_exists('t3lib_extMgm') &&
			method_exists('t3lib_extMgm', 'addTCAcolumns')
		) {
			t3lib_extMgm::addTCAcolumns($table, $columnArray, $addTofeInterface);
		}
	}

	static public function addToAllTCAtypes ($table, $str, $specificTypesList = '', $position = '') {
		$callingClassName = '\\TYPO3\\CMS\\Core\\Utility\\ExtensionManagementUtility';
		if (
			class_exists($callingClassName) &&
			method_exists($callingClassName, 'addToAllTCAtypes')
		) {
			call_user_func($callingClassName . '::addToAllTCAtypes', $table, $str, $specificTypesList, $position);
		} else if (
			class_exists('t3lib_extMgm') &&
			method_exists('t3lib_extMgm', 'addToAllTCAtypes')
		) {
			t3lib_extMgm::addToAllTCAtypes($table, $str, $specificTypesList, $position);
		}
	}

	static public function siteRelPath ($key) {
		$result = FALSE;
		$callingClassName = '\\TYPO3\\CMS\\Core\\Utility\\ExtensionManagementUtility';
		if (
			class_exists($callingClassName) &&
			method_exists($callingClassName, 'siteRelPath')
		) {
			$result = call_user_func($callingClassName . '::siteRelPath', $key);
		} else if (
			class_exists('t3lib_extMgm') &&
			method_exists('t3lib_extMgm', 'siteRelPath')
		) {
			$result = t3lib_extMgm::siteRelPath($key);
		}

		return $result;
	}

	static public function addLLrefForTCAdescr ($tca_descr_key, $file_ref) {
		$callingClassName = '\\TYPO3\\CMS\\Core\\Utility\\ExtensionManagementUtility';
		if (
			class_exists($callingClassName) &&
			method_exists($callingClassName, 'addLLrefForTCAdescr')
		) {
			call_user_func($callingClassName . '::addLLrefForTCAdescr', $tca_descr_key, $file_ref);
		} else if (
			class_exists('t3lib_extMgm') &&
			method_exists('t3lib_extMgm', 'addLLrefForTCAdescr')
		) {
			t3lib_extMgm::addLLrefForTCAdescr($tca_descr_key, $file_ref);
		}
	}

	static public function allowTableOnStandardPages ($table) {
		$callingClassName = '\\TYPO3\\CMS\\Core\\Utility\\ExtensionManagementUtility';
		if (
			class_exists($callingClassName) &&
			method_exists($callingClassName, 'allowTableOnStandardPages')
		) {
			call_user_func($callingClassName . '::allowTableOnStandardPages', $table);
		} else if (
			class_exists('t3lib_extMgm') &&
			method_exists('t3lib_extMgm', 'allowTableOnStandardPages')
		) {
			t3lib_extMgm::allowTableOnStandardPages($table);
		}
	}

	static public function addUserTSConfig ($content) {
		$callingClassName = '\\TYPO3\\CMS\\Core\\Utility\\ExtensionManagementUtility';
		if (
			class_exists($callingClassName) &&
			method_exists($callingClassName, 'addUserTSConfig')
		) {
			call_user_func($callingClassName . '::addUserTSConfig', $content);
		} else if (
			class_exists('t3lib_extMgm') &&
			method_exists('t3lib_extMgm', 'addUserTSConfig')
		) {
			t3lib_extMgm::addUserTSConfig($content);
		}
	}

	static public function addToInsertRecords ($table, $content_table = 'tt_content', $content_field = 'records') {
		$callingClassName = '\\TYPO3\\CMS\\Core\\Utility\\ExtensionManagementUtility';
		if (
			class_exists($callingClassName) &&
			method_exists($callingClassName, 'addToInsertRecords')
		) {
			call_user_func($callingClassName . '::addToInsertRecords', $table, $content_table, $content_field);
		} else if (
			class_exists('t3lib_extMgm') &&
			method_exists('t3lib_extMgm', 'addToInsertRecords')
		) {
			t3lib_extMgm::addToInsertRecords($table, $content_table, $content_field);
		}
	}

	static public function addPlugin ($itemArray, $type = 'list_type') {
		$callingClassName = '\\TYPO3\\CMS\\Core\\Utility\\ExtensionManagementUtility';
		if (
			class_exists($callingClassName) &&
			method_exists($callingClassName, 'addPlugin')
		) {
			call_user_func($callingClassName . '::addPlugin', $itemArray, $type);
		} else if (
			class_exists('t3lib_extMgm') &&
			method_exists('t3lib_extMgm', 'addPlugin')
		) {
			t3lib_extMgm::addPlugin($itemArray, $type);
		}
	}

	static public function addPItoST43 ($key, $classFile = '', $prefix = '', $type = 'list_type', $cached = 0) {
		$callingClassName = '\\TYPO3\\CMS\\Core\\Utility\\ExtensionManagementUtility';
		if (
			class_exists($callingClassName) &&
			method_exists($callingClassName, 'addPItoST43')
		) {
			call_user_func($callingClassName . '::addPItoST43', $key, $classFile, $prefix, $type, $cached);
		} else if (
			class_exists('t3lib_extMgm') &&
			method_exists('t3lib_extMgm', 'addPItoST43')
		) {
			t3lib_extMgm::addPItoST43($key, $classFile, $prefix, $type, $cached);
		}
	}

	static public function addTypoScript ($key, $type, $content, $afterStaticUid = 0) {
		$callingClassName = '\\TYPO3\\CMS\\Core\\Utility\\ExtensionManagementUtility';
		if (
			class_exists($callingClassName) &&
			method_exists($callingClassName, 'addTypoScript')
		) {
			call_user_func($callingClassName . '::addTypoScript', $key, $type, $content, $afterStaticUid);
		} else if (
			class_exists('t3lib_extMgm') &&
			method_exists('t3lib_extMgm', 'addTypoScript')
		) {
			t3lib_extMgm::addTypoScript($key, $type, $content, $afterStaticUid);
		}
	}

	static public function addPiFlexFormValue ($piKeyToMatch, $value, $CTypeToMatch = 'list') {
		$callingClassName = '\\TYPO3\\CMS\\Core\\Utility\\ExtensionManagementUtility';
		if (
			class_exists($callingClassName) &&
			method_exists($callingClassName, 'addPiFlexFormValue')
		) {
			call_user_func($callingClassName . '::addPiFlexFormValue', $piKeyToMatch, $value, $CTypeToMatch);
		} else if (
			class_exists('t3lib_extMgm') &&
			method_exists('t3lib_extMgm', 'addPiFlexFormValue')
		) {
			t3lib_extMgm::addPiFlexFormValue($piKeyToMatch, $value, $CTypeToMatch);
		}
	}

	static public function addStaticFile ($extKey, $path, $title) {
		$callingClassName = '\\TYPO3\\CMS\\Core\\Utility\\ExtensionManagementUtility';
		if (
			class_exists($callingClassName) &&
			method_exists($callingClassName, 'addStaticFile')
		) {
			call_user_func($callingClassName . '::addStaticFile', $extKey, $path, $title);
		} else if (
			class_exists('t3lib_extMgm') &&
			method_exists('t3lib_extMgm', 'addStaticFile')
		) {
			t3lib_extMgm::addStaticFile($extKey, $path, $title);
		}
	}

	static public function insertModuleFunction ($modname, $className, $classPath, $title, $MM_key = 'function', $WS = '') {
		$callingClassName = '\\TYPO3\\CMS\\Core\\Utility\\ExtensionManagementUtility';
		if (
			class_exists($callingClassName) &&
			method_exists($callingClassName, 'insertModuleFunction')
		) {
			call_user_func($callingClassName . '::insertModuleFunction', $modname, $className, $classPath, $title, $MM_key, $WS);
		} else if (
			class_exists('t3lib_extMgm') &&
			method_exists('t3lib_extMgm', 'insertModuleFunction')
		) {
			t3lib_extMgm::insertModuleFunction($modname, $className, $classPath, $title, $MM_key, $WS);
		}
	}


	### HTML parser object
	public function newHtmlParser () {
		$useClassName = '';
		$callingClassName = '\\TYPO3\\CMS\\Core\\Html\\HtmlParser';

		if (
			class_exists($callingClassName)
		) {
			$useClassName = $callingClassName;
		} else if (
			class_exists('t3lib_parsehtml')
		) {
			$useClassName = 't3lib_parsehtml';
		}

		$result = self::makeInstance($useClassName);
		return $result;
	}


	### Mail object
	public function newMailMessage () {

		$useClassName = '';
		$callingClassName = '\\TYPO3\\CMS\\Mail\\MailMessage';

		if (
			class_exists($callingClassName)
		) {
			$useClassName = $callingClassName;
		} else if (
			class_exists('t3lib_mail_Message')
		) {
			$useClassName = 't3lib_mail_Message';
		}

		$result = self::makeInstance($useClassName);
		return $result;
	}


	### Caching Framework
	static public function initializeCachingFramework () {
		$useClassName = '';
		$callingClassName = '\\TYPO3\\CMS\\Core\\Cache\\Cache';

		if (
			class_exists($callingClassName)
		) {
			$useClassName = $callingClassName;
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

}

?>