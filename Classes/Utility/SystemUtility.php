<?php

namespace JambageCom\Div2007\Utility;

/***************************************************************
*  Copyright notice
*
*  (c) 2018 Franz Holzinger (franz@ttproducts.de)
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



class SystemUtility {

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
        $feGroups = self::fetchFeGroups();

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
}

