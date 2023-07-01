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
 * marker functions.
 *
 * @author	Franz Holzinger <franz@ttproducts.de>
 * @maintainer Franz Holzinger <franz@ttproducts.de>
 * @package TYPO3
 * @subpackage div2007
 */



class MarkerUtility {
    static public function addMarkers (array &$markerArray, $prefix, $separator, $key, $variable)
    {
        $markerkey = $prefix . $separator . strtoupper($key);
        if (is_string($variable)) {
            $markerArray['###' . $markerkey . '###'] =  strip_tags($variable);
        } else if (is_array($variable)) {
            foreach ($variable as $k => $v) {
                static::addMarkers($markerArray, $markerkey, $separator, $k, $v);
            }
        }
    }

    /*determine all markers 
    */
    static public function getTags ($content)
    {
        $found = [];
        $result = false;

        preg_match_all('/###([\w:-]+)###/', $content, $found);
        if (
            isset($found) &&
            is_array($found) &&
            isset($found['1']) &&
            is_array($found['1'])
        ) {
            $tagArray = array_unique($found['1']);
            $tagArray = array_flip($tagArray);
            $result = $tagArray;
        }

        return $result;
    }
}

