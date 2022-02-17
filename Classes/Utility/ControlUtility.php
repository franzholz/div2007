<?php

namespace JambageCom\Div2007\Utility;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
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

 
use Psr\Http\Message\ServerRequestInterface;

use TYPO3\CMS\Core\Utility\ArrayUtility;
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
            if (str_contains($key, '.')) {
                $key = substr($key, 0, -1);

                // descend into all non-stdWrap-subelements first
                foreach ($confNextLevel as $subKey => $subConfNextLevel) {
                    if (is_array($subConfNextLevel) && str_contains($subKey, '.') && $subKey !== 'stdWrap.') {
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
            ArrayUtility::mergeRecursiveWithOverrule($tmp, is_array($piVars) ? $piVars : array());
            $piVars = $tmp;
        }
    }

    /**
     * Writes input value to $_GET.
     *
     * @param mixed $inputGet
     * @param string $key
     */
    public static function _GETset($inputGet, $key = '')
    {
        if ($key != '') {
            if (strpos($key, '|') !== false) {
                $pieces = explode('|', $key);
                $newGet = [];
                $pointer = &$newGet;
                foreach ($pieces as $piece) {
                    $pointer = &$pointer[$piece];
                }
                $pointer = $inputGet;
                $mergedGet = $_GET;
                ArrayUtility::mergeRecursiveWithOverrule($mergedGet, $newGet);
                $_GET = $mergedGet;
                $GLOBALS['HTTP_GET_VARS'] = $mergedGet;
            } else {
                $_GET[$key] = $inputGet;
                $GLOBALS['HTTP_GET_VARS'][$key] = $inputGet;
            }
        } elseif (is_array($inputGet)) {
            $_GET = $inputGet;
            $GLOBALS['HTTP_GET_VARS'] = $inputGet;
            if (isset($GLOBALS['TYPO3_REQUEST']) && $GLOBALS['TYPO3_REQUEST'] instanceof ServerRequestInterface) {
                $GLOBALS['TYPO3_REQUEST'] = $GLOBALS['TYPO3_REQUEST']->withQueryParams($inputGet);
            }
        }
    }
}

