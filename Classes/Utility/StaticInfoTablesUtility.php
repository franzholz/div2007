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
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
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
 *
 * functions for the TYPO3 extension static_info_tables
 *
 * @author Franz Holzinger <franz@ttproducts.de>
 * @maintainer Franz Holzinger <franz@ttproducts.de>
 * @package TYPO3
 * @subpackage div2007
 *
 * attention: This class must also work under TYPO3 4.5
 */

use JambageCom\Div2007\Utility\ExtensionUtility;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

class StaticInfoTablesUtility {

    static private $staticInfo = false;

    /**
    * Getting all tt_products_cat categories into internal array
    */
    static public function init () {
        $result = false;

        if (
            !is_object(self::$staticInfo) &&
            ExtensionManagementUtility::isLoaded('static_info_tables')
        ) {
            $path = ExtensionManagementUtility::extPath('static_info_tables');
            $eInfo = ExtensionUtility::getExtensionInfo('static_info_tables', $path);

            if (is_array($eInfo)) {
                $sitVersion = $eInfo['version'];
                $class = '';
                if (version_compare($sitVersion, '6.0.0', '>=')) {
                    $class = 'SJBR\\StaticInfoTables\\PiBaseApi';
                } else {
                    return false;
                }

                // Initialise static info library
                self::$staticInfo = GeneralUtility::makeInstance($class);
                if (
                    !method_exists(self::$staticInfo, 'needsInit') ||
                    self::$staticInfo->needsInit()
                ) {
                    self::$staticInfo->init();
                }

                if (is_object(self::$staticInfo)) {
                    $result = true;
                } else {
                    self::$staticInfo = false;
                }
            }
        }

        return $result;
    } // init

    static public function getStaticInfo () {
        return self::$staticInfo;
    }

    /**
     * Get a list of countries by specific parameters or parts of names of countries
     * in different languages. Parameters might be left empty.
     *
     * @param   string      a name of the country or a part of it in any language
     * @param   string      ISO alpha-2 code of the country
     * @param   string      ISO alpha-3 code of the country
     * @param   array       Database row.
     * @return  array       Array of rows of country records
     */
    static public function fetchCountries ($country, $iso2 = '', $iso3 = '', $isonr = '') {

        $resultArray = array();
        $where = '';

        $table = 'static_countries';
        if ($country != '') {
            $value = $GLOBALS['TYPO3_DB']->fullQuoteStr(trim('%' . $country . '%'), $table);
            $where = 'cn_official_name_local LIKE '. $value . ' OR cn_official_name_en LIKE ' . $value;

            foreach ($GLOBALS['TCA'][$table]['columns'] as $fieldname => $fieldArray) {
                if (strpos($fieldname, 'cn_short_') === 0) {
                    $where .= ' OR ' . $fieldname . ' LIKE ' . $value;
                }
            }
        }

        if ($isonr != '') {
            $where = 'cn_iso_nr=' . $GLOBALS['TYPO3_DB']->fullQuoteStr(trim($isonr), $table);
        }

        if ($iso2 != '') {
            $where = 'cn_iso_2=' . $GLOBALS['TYPO3_DB']->fullQuoteStr(trim($iso2), $table);
        }

        if ($iso3 !='') {
            $where = 'cn_iso_3=' . $GLOBALS['TYPO3_DB']->fullQuoteStr(trim($iso3), $table);
        }

        if ($where != '') {
            $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $table, $where);

            if ($res)   {
                while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
                    $resultArray[] = $row;
                }
            }
            $GLOBALS['TYPO3_DB']->sql_free_result($res);
        }
        return $resultArray;
    }

}

