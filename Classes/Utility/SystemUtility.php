<?php

namespace JambageCom\Div2007\Utility;

/***************************************************************
*  Copyright notice
*
*  (c) 2019 Franz Holzinger (franz@ttproducts.de)
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
 * TYPO3 system functions
 *
 * @author  Franz Holzinger <franz@ttproducts.de>
 * @maintainer	Franz Holzinger <franz@ttproducts.de>
 * @package TYPO3
 * @subpackage div2007
 *
 *
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;

use JambageCom\Div2007\Utility\TableUtility;


class SystemUtility {
    /**
     * @return string
     */
    static public function getRecursivePids ($storagePid, $recursionDepth, $whereClause = '') {
        if ($recursionDepth <= 0) {
            return $storagePid;
        }

        $cObj = GeneralUtility::makeInstance(\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::class);
        $recursiveStoragePids = '';
        $storagePids = GeneralUtility::intExplode(',', $storagePid);
        if ($whereClause == '') {
            $whereClause = TableUtility::enableFields('pages');
        }

        foreach ($storagePids as $startPid) {
            $pids = $cObj->getTreeList($startPid, $recursionDepth, 0, $whereClause);
            if ((string)$pids !== '') {
                $recursiveStoragePids .= $pids . ',';
            }
        }
        $recursiveStoragePids = rtrim($recursiveStoragePids, ',');
        $pids = explode(',', $recursiveStoragePids);
        $pids = array_unique($pids);
        $result = implode(',', $pids);

        return $result;
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
    static public function userProcess (
        $pObject,
        $conf,
        $mConfKey,
        $passVar
    ) {
        return \JambageCom\Div2007\Utility\ObsoleteUtility::userProcess(
            $pObject,
            $conf,
            $mConfKey,
            $passVar
        );
    }

    /**
    * Fetches the FE user groups (fe_groups) of the logged in FE user
    *
    * @return array of the FE groups
    */
    static public function fetchFeGroups ()
    {
        $result = array();

        if (
            isset($GLOBALS['TSFE']->fe_user) &&
            isset($GLOBALS['TSFE']->fe_user->user) &&
            isset($GLOBALS['TSFE']->fe_user->user['usergroup'])
        ) {
           $result = explode(',', $GLOBALS['TSFE']->fe_user->user['usergroup']); 
        }
        return $result;
    }

    /**
    * Fetches the FE user groups (fe_groups) of the logged in FE user as an array of record
    *
    * @return array of the records of all FE groups
    */
    static public function readFeGroupsRecords ()
    {
        $result = false;
        $feGroups = static::fetchFeGroups();

        if (!empty($feGroups)) {
            $feGroupList = implode(',', $feGroups);
            $where_clause = 'uid IN (' . $feGroupList . ')';
            $result = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
                '*',
                'fe_groups',
                $where_clause
            );
        }
        return $result;
    }

    /**
    * Adds the time zone to the given unix time parameter
    *
    * @param string $time: incoming and outgoing UNIX time
    * @return array of the records of all FE groups
    */
    static public function addTimeZone (&$time) {
        if (!empty($GLOBALS['TYPO3_CONF_VARS']['SYS']['serverTimeZone'])) {
            $time += ($GLOBALS['TYPO3_CONF_VARS']['SYS']['serverTimeZone'] * 3600);
        }
    }

    /**
    * Reads the current time and considers the time zone
    *
    * @return array of the records of all FE groups
    */
    static public function createTime ()
    {
        $result = time();
        static::addTimeZone($result);
        return $result;
    }

    /**
    * Returns a class-name prefixed with $this->prefixId and with all underscores substituted to dashes (-)
    * this is an initial state, not yet finished! Therefore the debug lines have been left.
    *
    * @param	string		$str Input 
    * @param	string		$prefixId
    * @return	string		The combined class name (with the correct prefix)
    */
    static public function unserialize (
        $str,
        $errorCheck = true
    ) {
        $result = false;

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
                                    if ($fixPos !== false && in_array($param2{$fixPos + 2}, $codeArray)) {
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
                    if ($errorCheck) {
                        trigger_error('unserialize_fh002(): Error at offset ' . $errorOffset . ' of ' . $len . ' bytes \'' . substr($str, $errorOffset, 12) . '\'', E_USER_NOTICE);
                        $result = false;
                    }
                break;
            }
        }
        if (isset($var) && (!$errorCheck || $errorOffset == 0)) {
            $result = $var;
        }
        return $result;
    }

    /**
    * This is will calculate your setup as a PHP function
    * This function is called in your stdWrap preUserFunc function.
    * 		preUserFunc = \JambageCom\Div2007\UtilitySystemUtility->phpFunc
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
    static public function phpFunc (
        $content,
        $conf
    ) {
        $result = '';

        if ($conf['php'] != '') {
            $evalStr = str_replace('$value', $content, $conf['php']);
            $result = eval('return ' . $evalStr);
        }
        return $result;
    }
}


