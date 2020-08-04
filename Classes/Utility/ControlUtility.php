<?php

namespace JambageCom\Div2007\Utility;

/***************************************************************
*  Copyright notice
*
*  (c) 2017 Franz Holzinger (franz@ttproducts.de)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.HTMLContent.
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
 * Part of the div2007 (Static Methods for Extensions since 2007) extension.
 *
 * Control functions
 *
 * @author  Franz Holzinger <franz@ttproducts.de>
 * @maintainer	Franz Holzinger <franz@ttproducts.de>
 * @package TYPO3
 * @subpackage div2007
 *
 *
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;


class ControlUtility {

    /**
        * Creates a regular expression out of an array of tags
        *
        * @param	array		$tags: the array of tags
        * @return	string		the regular expression
        */
    static public function readGP ($variable, $prefixId = '' , $htmlSpecialChars = true) {
        $result = null;

        if (
            $variable != ''
        ) {
            if ($prefixId != '') {
                $value = GeneralUtility::_GP($prefixId);
                if (
                    isset($value) &&
                    is_array($value) &&
                    isset($value[$variable])
                ) {
                    $result = $value[$variable];
                }
            } else {
                $result = GeneralUtility::_GP($variable);
            }
        } else if ($prefixId != '') {
            $result = GeneralUtility::_GP($prefixId);
        }

        if ($htmlSpecialChars && isset($result)) {
            if (is_string($result)) {
                $result = htmlSpecialChars($result);
            } else if (is_array($result)) {
                $newResult = array();
                foreach ($result as $key => $value) {
                    $newResult[$key] = htmlSpecialChars($value);
                }
                $result = $newResult;
            }
        }

        return $result;
    }

    /**
    * Recursively looks for stdWrap and executes it
    *
    * @param array $conf Current section of configuration to work on
    * @param integer $level Current level being processed (currently just for tracking; no limit enforced)
    * @return array Current section of configuration after stdWrap applied
    */
    static public function applyStdWrapRecursive (
        \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $cObj,
        array $conf, 
        $level = 0
    )
    {
        foreach ($conf as $key => $confNextLevel) {
            if (strpos($key, '.') !== false) {
                $key = substr($key, 0, -1);

                // descend into all non-stdWrap-subelements first
                foreach ($confNextLevel as $subKey => $subConfNextLevel) {
                    if (is_array($subConfNextLevel) && strpos($subKey, '.') !== false && $subKey !== 'stdWrap.') {
                        $subKey = substr($subKey, 0, -1);
                        $conf[$key . '.'] = static::applyStdWrapRecursive($cObj, $confNextLevel, $level + 1);
                    }
                }

                // now for stdWrap
                foreach ($confNextLevel as $subKey => $subConfNextLevel) {
                    if (is_array($subConfNextLevel) && $subKey === 'stdWrap.') {
                        $conf[$key] = $cObj->stdWrap($conf[$key], $conf[$key . '.']['stdWrap.']);
                        unset($conf[$key . '.']['stdWrap.']);
                        if (!count($conf[$key . '.'])) {
                            unset($conf[$key . '.']);
                        }
                    }
                }
            }
        }
        return $conf;
    }

    /**
    * If internal TypoScript property "_DEFAULT_PI_VARS." is set then it will merge the current $piVars array onto these default values.
    *
    * @return void
    */
    static public function setPiVarDefaults (
        &$piVars,
        \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $cObj,
        array $conf
    )
    {
        if (isset($conf['_DEFAULT_PI_VARS.']) && is_array($conf['_DEFAULT_PI_VARS.'])) {
            $conf['_DEFAULT_PI_VARS.'] = static::applyStdWrapRecursive($cObj, $conf['_DEFAULT_PI_VARS.']);
            $tmp = $conf['_DEFAULT_PI_VARS.'];
            \TYPO3\CMS\Core\Utility\ArrayUtility::mergeRecursiveWithOverrule($tmp, is_array($piVars) ? $piVars : array());
            $piVars = $tmp;
        }
    }
}

