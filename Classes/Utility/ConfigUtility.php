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


class ConfigUtility {

    /**
     * Recursively looks for stdWrap and executes it
     *
     * @param cObject $cObj content object
     * @param array $conf Current section of configuration to work on
     * @param int $level Current level being processed (currently just for tracking; no limit enforced)
     * @return array Current section of configuration after stdWrap applied
     */
    static protected function applyStdWrapRecursive($cObj, array $conf, $level = 0)
    {
        foreach ($conf as $key => $confNextLevel) {
            if (strpos($key, '.') !== false) {
                $key = substr($key, 0, -1);

                // descend into all non-stdWrap-subelements first
                foreach ($confNextLevel as $subKey => $subConfNextLevel) {
                    if (is_array($subConfNextLevel) && strpos($subKey, '.') !== false && $subKey !== 'stdWrap.') {
                        $conf[$key . '.'] = self::applyStdWrapRecursive($confNextLevel, $level + 1);
                    }
                }

                // now for stdWrap
                foreach ($confNextLevel as $subKey => $subConfNextLevel) {
                    if (is_array($subConfNextLevel) && $subKey === 'stdWrap.') {
                        $conf[$key] = $cObj->stdWrap($conf[$key], $conf[$key . '.']['stdWrap.']);
                        unset($conf[$key . '.']['stdWrap.']);
                        if (empty($conf[$key . '.'])) {
                            unset($conf[$key . '.']);
                        }
                    }
                }
            }
        }
        return $conf;
    }

}


