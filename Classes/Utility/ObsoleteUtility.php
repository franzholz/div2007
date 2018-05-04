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
 * obsolete functions. These are here because it takes time for extensions to get rid of them.
 *
 * @author	Franz Holzinger <franz@ttproducts.de>
 * @maintainer Franz Holzinger <franz@ttproducts.de>
 * @package TYPO3
 * @subpackage div2007
 */

class ObsoleteUtility {

    /**
    * Get External CObjects
    * @param	object		tx_div2007_alpha_language_base object
    * @param	string		Configuration Key
    */
    static public function getExternalCObject (
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

}


