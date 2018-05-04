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

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Core\Localization\Locales;

class StaticInfoTablesUtility {

    static private $staticInfo = false;
    static private $cache = array();


    static public function getStaticInfo () {
        return static::$staticInfo;
    }

    /**
    * Getting all tt_products_cat categories into internal array
    */
    static public function init () {
        $result = false;
		Locales::initialize();

        if (
            !is_object(static::$staticInfo) &&
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
                static::$staticInfo = GeneralUtility::makeInstance($class);
                if (
                    !method_exists(static::$staticInfo, 'needsInit') ||
                    static::$staticInfo->needsInit()
                ) {
                    static::$staticInfo->init();
                }

                if (is_object(static::$staticInfo)) {
                    $result = true;
                } else {
                    static::$staticInfo = false;
                }
            }
        }

        return $result;
    } // init

    /**
    * Returns the current language as iso-2-alpha code
    *
    * @return	string		'DE', 'EN', 'DK', ...
    */
    static public function getCurrentLanguage () {

        if (is_object($GLOBALS['TSFE'])) {
            $langCodeT3 = $GLOBALS['TSFE']->lang;
        } elseif (is_object($GLOBALS['LANG'])) {
            $langCodeT3 = $GLOBALS['LANG']->lang;
        } else {
            return 'EN';
        }
        if ($langCodeT3 == 'default') {
            return 'EN';
        }
            // Return cached value if any
        if (isset(self::$cache['getCurrentLanguage'][$langCodeT3])) {
            return self::$cache['getCurrentLanguage'][$langCodeT3];
        }

        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
            'lg_iso_2,lg_country_iso_2',
            'static_languages',
            'lg_typo3=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($langCodeT3, 'static_languages')
        );
        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
            $lang = $row['lg_iso_2'] . ($row['lg_country_iso_2'] ? '_' . $row['lg_country_iso_2'] : '');
        }
        $GLOBALS['TYPO3_DB']->sql_free_result($res);

        $lang = $lang ? $lang : strtoupper($langCodeT3);

            // Initialize cache array
        if (!is_array(self::$cache['getCurrentLanguage'])) {
            self::$cache['getCurrentLanguage'] = array();
        }
            // Cache retrieved value
        self::$cache['getCurrentLanguage'][$langCodeT3] = $lang;

        return $lang;
    }

    /**
    * Returns a label field for the current language
    *
    * @param	string		table name
    * @param	boolean		DEPRECATED
    * @param	string		language to be used
    * @param	boolean		If set, we are looking for the "local" title field
    * @return	string		field name
    */
    static public function getTCAlabelField ($table, $bLoadTCA = true, $lang = '', $local = false) {

        $labelFields = array();
        if(
            $table &&
            is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][STATIC_INFO_TABLES_EXT]['tables'][$table]['label_fields'])
        ) {
            $locales = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Localization\Locales::class);
            $isoArray = (array) $locales->getIsoMapping();

            $lang = $lang ? $lang : static::getCurrentLanguage();
            $lang = isset($isoArray[$lang]) ? $isoArray[$lang] : $lang;
            
            foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][STATIC_INFO_TABLES_EXT]['tables'][$table]['label_fields'] as $field) {
                if ($local) {
                    $labelField = str_replace ('##', 'local', $field);
                } else {
                    $labelField = str_replace ('##',  strtolower($lang), $field);
                }
                if (is_array($GLOBALS['TCA'][$table]['columns'][$labelField])) {
                    $labelFields[] = $labelField;
                }
            }
        }
        return $labelFields;
    }

    /**
    * Returns the type of an iso code: nr, 2, 3
    *
    * @param	string		iso code
    * @return	string		iso code type
    */
    static public function isoCodeType ($isoCode) {
        $type = '';
        $isoCodeAsInteger = 
            MathUtility::canBeInterpretedAsInteger($isoCode);
        if ($isoCodeAsInteger) {
            $type = 'nr';
        } elseif (strlen($isoCode) == 2) {
            $type = '2';
        } elseif (strlen($isoCode) == 3) {
            $type = '3';
        }
        return $type;
    }

    /**
    * Returns a iso code field for the passed table and iso code
    *
    *  $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][STATIC_INFO_TABLES_EXT]['tables']
    *
    * @param	string		table name
    * @param	string		iso code
    * @param	boolean		If set (default) the TCA definition of the table should be loaded with tx_div2007_core::loadTCA(). It will be needed to set it to false if you call this function from inside of tca.php
    * @param	integer		index in the table's isocode_field array in the global variable
    * @return	string		field name
    */
    static public function getIsoCodeField ($table, $isoCode, $bLoadTCA = false, $index = 0) {
        $result = false;

        if (
            $isoCode &&
            $table
        ) {
            $isoCodeField = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][STATIC_INFO_TABLES_EXT]['tables'][$table]['isocode_field'][$index];

            if ($isoCodeField != '') {
                $type = static::isoCodeType($isoCode);
                $isoCodeField = str_replace ('##', $type, $isoCodeField);

                if (is_array($GLOBALS['TCA'][$table]['columns'][$isoCodeField])) {
                    $result = $isoCodeField;
                }
            }
        }
        return $result;
    }

    /**
    * Fetches short title from an iso code
    *
    * @param	string		table name
    * @param	string		iso code
    * @param	string		language code - if not set current default language is used
    * @param	boolean		local name only - if set local title is returned
    * @return	string		short title
    */
    static public function getTitleFromIsoCode ($table, $isoCode, $lang = '', $local = false) {

        $title = '';
        $titleFields = static::getTCAlabelField($table, true, $lang, $local);
        if (count ($titleFields)) {
            $prefixedTitleFields = array();
            foreach ($titleFields as $titleField) {
                $prefixedTitleFields[] = $table . '.' . $titleField;
            }
            $fields = implode(',', $prefixedTitleFields);
            $whereClause = '1=1';
            if (!is_array($isoCode)) {
                $isoCode = array($isoCode);
            }
            $index = 0;
            foreach ($isoCode as $index => $code) {
                if ($code != '') {
                    $tmpField = static::getIsoCodeField($table, $code, true, $index);
                    $tmpValue = $GLOBALS['TYPO3_DB']->fullQuoteStr($code, $table);
                    if ($tmpField && $tmpValue)	{
                        $whereClause .= ' AND ' . $table . '.' . $tmpField . ' = ' . $tmpValue;
                    }
                }
            }
            if (is_object($GLOBALS['TSFE'])) {
                $enableFields = $GLOBALS['TSFE']->sys_page->enableFields($table);
            } else {
                $enableFields = tx_div2007_core::deleteClause($table);
            }

            $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                $fields,
                $table,
                $whereClause . $enableFields
            );

            if ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
                foreach ($titleFields as $titleField) {
                    if ($row[$titleField]) {
                        $title = $row[$titleField];
                        break;
                    }
                }
            }
            $GLOBALS['TYPO3_DB']->sql_free_result($res);
        }

        return $title;
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

