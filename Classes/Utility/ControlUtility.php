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
}


