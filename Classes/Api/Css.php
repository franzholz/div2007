<?php

namespace JambageCom\Div2007\Api;

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
 * HTML functions
 *
 * @author  Franz Holzinger <franz@ttproducts.de>
 * @maintainer	Franz Holzinger <franz@ttproducts.de>
 * @package TYPO3
 * @subpackage div2007
 *
 *
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;


class Css implements \TYPO3\CMS\Core\SingletonInterface {

    /**
     * Returns a class-name
     *
     * @param   string      The class name
     * @param   string      type (HTML tag or combination) You can use '-' as the inside separator sign
     * @return  string      The combined class name
     * @see pi_getClassName()
     */
    public function getClassName ($class, $type = '')
    {
        $result = '';
        if ($type != '') {
            $result = $type;
        }
        $separators = ' -_';
        $result .= ucwords($class, $separators);
        
        $result = preg_replace('/[^A-Za-z0-9]/', '', $result);

        return $result;
    }
}

