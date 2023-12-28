<?php

namespace JambageCom\Div2007\Utility;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
/**
 * deprecated. Use the class \JambageCom\Div2007\Api\StaticInfoTablesApi instead.
 * functions for the TYPO3 extension static_info_tables
 * It will be removed in 2025.
 *
 * attention: This class must also work under TYPO3 6.2
 */
use SJBR\StaticInfoTables\PiBaseApi;
use TYPO3\CMS\Core\Localization\Locales;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

class StaticInfoTablesUtility
{
    private static $staticInfo = false;
    private static $cache = [];
    private static $versionNumber;

    public static function getStaticInfo()
    {
        return static::$staticInfo;
    }

    public static function getVersionNumer()
    {
        return static::$versionNumber;
    }

    /**
     * Getting all tt_products_cat categories into internal array.
     */
    public static function init()
    {
        $result = false;

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
                    $class = PiBaseApi::class;
                } else {
                    return false;
                }
                static::$versionNumber = $sitVersion;

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
     * Returns the current language as iso-2-alpha code.
     *
     * @return	string		'DE', 'EN', 'DK', ...
     */
    public static function getCurrentLanguage()
    {
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

        $lang = $lang ?: strtoupper($langCodeT3);

        // Initialize cache array
        if (
            !isset(self::$cache['getCurrentLanguage']) ||
            !is_array(self::$cache['getCurrentLanguage'])
        ) {
            self::$cache['getCurrentLanguage'] = [];
        }
        // Cache retrieved value
        self::$cache['getCurrentLanguage'][$langCodeT3] = $lang;

        return $lang;
    }

    /**
     * Returns a label field for the current language.
     *
     * @param	string		table name
     * @param	bool		DEPRECATED
     * @param	string		language to be used
     * @param	bool		If set, we are looking for the "local" title field
     *
     * @return	string		field name
     */
    public static function getTCAlabelField($table, $bLoadTCA = true, $lang = '', $local = false)
    {
        $labelFields = [];
        if (
            $table &&
            isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['static_info_tables']['tables'][$table]['label_fields']) &&
            is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['static_info_tables']['tables'][$table]['label_fields'])
        ) {
            $locales = GeneralUtility::makeInstance(Locales::class);
            $isoArray = (array)$locales->getIsoMapping();

            $lang = $lang ?: static::getCurrentLanguage();
            $lang = $isoArray[$lang] ?? $lang;

            foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['static_info_tables']['tables'][$table]['label_fields'] as $field) {
                if ($local) {
                    $labelField = str_replace('##', 'local', $field);
                } else {
                    $labelField = str_replace('##', strtolower($lang), $field);
                }
                if (
                    isset($GLOBALS['TCA'][$table]['columns'][$labelField]) &&
                    is_array($GLOBALS['TCA'][$table]['columns'][$labelField])
                ) {
                    $labelFields[] = $labelField;
                }
            }
        }

        return $labelFields;
    }

    /**
     * Returns the type of an iso code: nr, 2, 3.
     *
     * @param	string		iso code
     *
     * @return	string		iso code type
     */
    public static function isoCodeType($isoCode)
    {
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
     * Returns a iso code field for the passed table and iso code.
     *
     *  $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['static_info_tables']['tables']
     *
     * @param	string		table name
     * @param	string		iso code
     * @param	bool		If set (default) the TCA definition of the table should be loaded with tx_div2007_core::loadTCA(). It will be needed to set it to false if you call this function from inside of tca.php
     * @param	int		index in the table's isocode_field array in the global variable
     *
     * @return	string		field name
     */
    public static function getIsoCodeField($table, $isoCode, $bLoadTCA = false, $index = 0)
    {
        $result = false;

        if (
            $isoCode &&
            $table
        ) {
            $isoCodeField = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['static_info_tables']['tables'][$table]['isocode_field'][$index];

            if ($isoCodeField != '') {
                $type = static::isoCodeType($isoCode);
                $isoCodeField = str_replace('##', $type, $isoCodeField);

                if (
                    isset($GLOBALS['TCA'][$table]['columns'][$isoCodeField]) &&
                    is_array($GLOBALS['TCA'][$table]['columns'][$isoCodeField])
                ) {
                    $result = $isoCodeField;
                }
            }
        }

        return $result;
    }

    /**
     * Fetches short title from an iso code.
     *
     * @param	string		table name
     * @param	string		iso code
     * @param	string		language code - if not set current default language is used
     * @param	bool		local name only - if set local title is returned
     *
     * @return	string		short title
     */
    public static function getTitleFromIsoCode($table, $isoCode, $lang = '', $local = false)
    {
        $title = '';
        $titleFields = static::getTCAlabelField($table, true, $lang, $local);
        if (count($titleFields)) {
            $prefixedTitleFields = [];

            if (version_compare(static::$versionNumber, '11.5.0', '>=')) {
                foreach ($titleFields as $titleField => $titleFieldProperty) {
                    $prefixedTitleFields[] = $table . '.' . $titleField;
                }
            } else {
                foreach ($titleFields as $titleField) {
                    $prefixedTitleFields[] = $table . '.' . $titleField;
                }
            }

            $fields = implode(',', $prefixedTitleFields);
            $whereClause = '1=1';
            if (!is_array($isoCode)) {
                $isoCode = [$isoCode];
            }
            $index = 0;
            foreach ($isoCode as $index => $code) {
                if ($code != '') {
                    $tmpField = static::getIsoCodeField($table, $code, true, $index);
                    $tmpValue = $GLOBALS['TYPO3_DB']->fullQuoteStr($code, $table);
                    if ($tmpField && $tmpValue) {
                        $whereClause .= ' AND ' . $table . '.' . $tmpField . ' = ' . $tmpValue;
                    }
                }
            }
            if (is_object($GLOBALS['TSFE'])) {
                $enableFields = $GLOBALS['TSFE']->sys_page->enableFields($table);
            } else {
                $enableFields = TableUtility::deleteClause($table);
            }

            $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                $fields,
                $table,
                $whereClause . $enableFields
            );

            if ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
                if (version_compare(static::$versionNumber, '11.5.0', '>=')) {
                    foreach ($titleFields as $titleField => $titleFieldProperty) {
                        if ($row[$titleField]) {
                            $title = $row[$titleField];
                            break;
                        }
                    }
                } else {
                    foreach ($titleFields as $titleField) {
                        if ($row[$titleField]) {
                            $title = $row[$titleField];
                            break;
                        }
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
     * @param   array       database row
     *
     * @return  array       Array of rows of country records
     */
    public static function fetchCountries($country, $iso2 = '', $iso3 = '', $isonr = '')
    {
        $resultArray = [];
        $where = '';

        $table = 'static_countries';
        if ($country != '') {
            $value = $GLOBALS['TYPO3_DB']->fullQuoteStr(trim('%' . $country . '%'), $table);
            $where = 'cn_official_name_local LIKE ' . $value . ' OR cn_official_name_en LIKE ' . $value;

            foreach ($GLOBALS['TCA'][$table]['columns'] as $fieldname => $fieldArray) {
                if (str_starts_with($fieldname, 'cn_short_')) {
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

        if ($iso3 != '') {
            $where = 'cn_iso_3=' . $GLOBALS['TYPO3_DB']->fullQuoteStr(trim($iso3), $table);
        }

        if ($where != '') {
            $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $table, $where);

            if ($res) {
                while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
                    $resultArray[] = $row;
                }
            }
            $GLOBALS['TYPO3_DB']->sql_free_result($res);
        }

        return $resultArray;
    }
}
