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


class HtmlUtility {
    static protected $xhtmlFix = false;

    static public function useXHTML () {
        $result = false;
        if (is_object($GLOBALS['TSFE'])) {
            $config = $GLOBALS['TSFE']->config['config'];
            if (
                $config['xhtmlDoctype'] != '' ||
                stripos($config['doctype'], 'xthml') !== false
            ) {
                $result = true;
            }
        }
        return $result;
    }

    static public function generateXhtmlFix () {
        self::$xhtmlFix = (self::useXHTML() ? ' /' : '');
        return self::$xhtmlFix;
    }

    static public function getXhtmlFix () {
        return self::$xhtmlFix;
    }
}


