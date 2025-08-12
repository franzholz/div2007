<?php

namespace JambageCom\Div2007\Api;

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
 * functions for the TYPO3 extension static_info_tables.
 *
 * Alternative: see TYPO3 12 TYPO3\CMS\Core\Country\CountryProvider class
 * https://docs.typo3.org/m/typo3/reference-coreapi/main/en-us/ApiOverview/Country/Index.html
 */
use TYPO3\CMS\Core\SingletonInterface;
use JambageCom\Div2007\Utility\ExtensionUtility;
use JambageCom\Div2007\Utility\TableUtility;
use SJBR\StaticInfoTables\Domain\Repository\CurrencyRepository;
use SJBR\StaticInfoTables\Utility\HtmlElementUtility;
use SJBR\StaticInfoTables\Utility\LocalizationUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\QueryHelper;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Localization\Locales;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

class StaticInfoTablesApi implements SingletonInterface
{
    private $hasBeenInitialized = false;
    private $cache = [];
    protected $types = ['TERRITORIES', 'COUNTRIES', 'SUBDIVISIONS', 'CURRENCIES', 'LANGUAGES'];
    private $tables = [
        'TERRITORIES' => 'static_territories',
        'COUNTRIES' => 'static_countries',
        'SUBDIVISIONS' => 'static_country_zones',
        'CURRENCIES' => 'static_currencies',
        'LANGUAGES' => 'static_languages',
    ];
    // Default currency
    public $currency;
    public $currencyInfo = [];
    public $defaultCountry;
    public $defaultCountryZone;
    public $defaultLanguage;
    public $versionNumber; // extension static_info_tables version number
    public $version; // TYPO3 version number
    public $countriesAllowed;

    /**
     * @var CurrencyRepository
     */
    protected $currencyRepository;

    /**
     * Initialization of the extension static_info_tables.
     */
    public function init($conf = [])
    {
        $result = true;
        if (!ExtensionManagementUtility::isLoaded('static_info_tables')) {
            $result = false;
        } elseif (!$this->hasBeenInitialized) {
            $typo3Version = GeneralUtility::makeInstance(Typo3Version::class);
            $this->version = $typo3Version->getVersion();

            if (empty($conf) && isset($GLOBALS['TSFE']) && is_object($GLOBALS['TSFE']) && isset($GLOBALS['TSFE']->tmpl->setup['plugin.']['static_info_tables.'])) {
                $conf = $GLOBALS['TSFE']->tmpl->setup['plugin.']['static_info_tables.'];
            }
            $extensionInfo = ExtensionUtility::getExtensionInfo('static_info_tables');
            $this->versionNumber = $extensionInfo['version'];
            $this->initCountries('ALL');

            // Get the default currency and make sure it does exist in table static_currencies
            $this->currency = $conf['currencyCode'] ?? '';
            if (!$this->currency) {
                $this->currency = (!empty($conf['currencyCode'])) ? trim($conf['currencyCode']) : 'EUR';
            }
            // If nothing is set, we use the Euro because TYPO3 is spread more in this area
            if (!$this->getStaticInfoName($this->currency, 'CURRENCIES')) {
                $this->currency = 'EUR';
            }
            $this->currencyInfo = $this->loadCurrencyInfo($this->currency);
            $this->defaultCountry = $conf['countryCode'] ?? '';

            if (!$this->defaultCountry && isset($conf['countryCode'])) {
                $this->defaultCountry = trim($conf['countryCode']);
            }

            if (!$this->getStaticInfoName($this->defaultCountry, 'COUNTRIES')) {
                $this->defaultCountry = 'DEU';
            }
            $this->initCountrySubdivisions($this->defaultCountry);
            $this->defaultCountryZone = $conf['countryZoneCode'] ?? '';
            if (!$this->defaultCountryZone && isset($conf['countryZoneCode'])) {
                $this->defaultCountryZone = trim($conf['countryZoneCode']);
            }
            if (!$this->getStaticInfoName($this->defaultCountryZone, 'SUBDIVISIONS', $this->defaultCountry)) {
                if ($this->defaultCountry == 'DEU') {
                    $this->defaultCountryZone = 'NW';
                } else {
                    $this->defaultCountryZone = '';
                }
            }

            $this->defaultLanguage = $conf['languageCode'] ?? '';
            if (!$this->defaultLanguage && isset($conf['languageCode'])) {
                $this->defaultLanguage = trim($conf['languageCode']);
            }
            if (!$this->getStaticInfoName($this->defaultLanguage, 'LANGUAGES')) {
                $this->defaultLanguage = 'EN';
            }

            $this->countriesAllowed = $conf['countriesAllowed'] ?? '';
            $this->hasBeenInitialized = true;
        }

        return $result;
    } // init

    public function isActive()
    {
        return $this->hasBeenInitialized;
    }

    /**
     * Getting the name of a country, country subdivision, currency, language, tax.
     *
     * @param string The ISO alpha-3 code of a territory, country or currency, or the ISO alpha-2 code of a language or the code of a country subdivision, can be a comma ',' separated string, then all the single items are looked up and returned
     * @param string Defines the type of entry of the requested name: 'TERRITORIES', 'COUNTRIES', 'SUBDIVISIONS', 'CURRENCIES', 'LANGUAGES'
     * @param string The value of the country code (cn_iso_3) for which a name of type 'SUBDIVISIONS' is requested (meaningful only in this case)
     * @param string Not used
     * @param bool local name only - if set local title is returned
     *
     * @return string|bool The name of the object in the current language or false
     */
    public function getStaticInfoName($code, $type = 'COUNTRIES', $country = '', $countrySubdivision = '', $local = false)
    {
        $names = false;
        if (in_array($type, $this->types) && trim($code)) {
            $codeArray = GeneralUtility::trimExplode(',', $code);
            $tableName = $this->tables[$type];
            if (!$tableName) {
                return false;
            }
            $nameArray = [];
            foreach ($codeArray as $item) {
                $isoCodeArray = [];
                $isoCodeArray[] = $item;
                switch ($type) {
                    case 'SUBDIVISIONS':
                        $isoCodeArray[] = trim($country) ?: $this->defaultCountry;
                        break;
                    case 'LANGUAGES':
                        $isoCodeArray = GeneralUtility::trimExplode('_', $code, 1);
                        break;
                }
                $nameArray[] = LocalizationUtility::translate(['iso' => $isoCodeArray], $tableName, $local);
            }
            $names = implode(',', $nameArray);
        }

        return $names;
    }

    /**
     * Buils a HTML drop-down selector of countries, country subdivisions, currencies or languages.
     *
     * @param boolean/string $submit: If set to 1, an onchange attribute will be added to the <select> tag for immediate submit of the changed value; if set to other than 1, overrides the onchange script
     *
     * @return string A set of HTML <select> and <option> tags
     */
    public function buildStaticInfoSelector($type = 'COUNTRIES', $name = '', $class = '', $selectedArray = [], $country = '', $submit = 0, $id = '', $title = '', $addWhere = '', $lang = '', $local = false, $mergeArray = [], $size = 1, &$outSelectedArray = [])
    {
        if (!$this->isActive()) {
            return false;
        }
        $selector = '';

        if (isset($selectedArray) && !is_array($selectedArray)) {
            $selectedArray = GeneralUtility::trimExplode(',', $selectedArray);
        }

        $country = trim($country);
        $onChange = '';
        if ($submit) {
            if ($submit == 1) {
                $onChange = $this->conf['onChangeAttribute'];
            } else {
                $onChange = $submit;
            }
        }

        switch ($type) {
            case 'COUNTRIES':
                $nameArray = $this->initCountries('ALL', $lang, $local, $addWhere);
                $defaultSelectedArray = [$this->defaultCountry];
                break;
            case 'SUBDIVISIONS':
                $param = (trim($country) ?: $this->defaultCountry);
                $nameArray = $this->initCountrySubdivisions($param, $addWhere);
                if ($param == $this->defaultCountry) {
                    $defaultSelectedArray = [$this->defaultCountryZone];
                }
                break;
            case 'CURRENCIES':
                $nameArray = $this->initCurrencies($addWhere);
                $defaultSelectedArray = [$this->currency];
                break;
            case 'LANGUAGES':
                $nameArray = $this->initLanguages($addWhere);
                $defaultSelectedArray = [$this->defaultLanguage];
                break;
        }

        if (!$defaultSelectedArray) {
            $defaultSelectedArray = [array_key_first($nameArray)];
        }
        $bEmptySelected = (empty($selectedArray) || ((count($selectedArray) == 1) && empty($selectedArray[0])));
        $selectedArray = ((!$bEmptySelected || count($mergeArray)) ? $selectedArray : $defaultSelectedArray);

        if (count($mergeArray)) {
            $nameArray = array_merge($nameArray, $mergeArray);
            uasort($nameArray, 'strcoll');
        }

        if (count($nameArray) > 0) {
            $items = [];
            foreach ($nameArray as $itemKey => $itemName) {
                $items[] = ['name' => $itemName, 'value' => $itemKey];
            }
            $selector = HtmlElementUtility::selectConstructor($items, $selectedArray, $outSelectedArray, $name, $class, $id, $title, $onChange, $size);
        }

        return $selector;
    }

    /**
     * Getting all countries into an array
     * where the key is the ISO alpha-3 code of the country
     * and where the value is the name of the country in the current language.
     *
     * @return array An array of names of countries
     */
    public function initCountries($param = 'UN', $lang = '', $local = false, $addWhere = '')
    {
        $nameArray = [];
        $table = $this->tables['COUNTRIES'];
        if (!$lang) {
            $lang = LocalizationUtility::getCurrentLanguage();
            $lang = LocalizationUtility::getIsoLanguageKey($lang);
        }
        $titleFields = LocalizationUtility::getLabelFields($table, $lang, $local);
        $prefixedTitleFields = [];
        $prefixedTitleFields[] = $table . '.cn_iso_3';
        if (version_compare($this->versionNumber, '11.5.0', '>=')) {
            foreach ($titleFields as $titleField => $titleFieldProperty) {
                $prefixedTitleFields[] = $table . '.' . $titleField;
            }
        } else {
            foreach ($titleFields as $titleField) {
                $prefixedTitleFields[] = $table . '.' . $titleField;
            }
        }
        array_unique($prefixedTitleFields);

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable($table)
        ;
        $queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));
        $queryBuilder
            ->select($prefixedTitleFields[0])
            ->from($table)
        ;
        array_shift($prefixedTitleFields);
        foreach ($prefixedTitleFields as $titleField) {
            $queryBuilder->addSelect($titleField);
        }

        if ($param === 'UN') {
            $queryBuilder->where($queryBuilder->expr()->eq('cn_uno_member', $queryBuilder->createNamedParameter(1, \PDO::PARAM_INT)));
        } elseif ($param === 'EU') {
            $queryBuilder->where($queryBuilder->expr()->eq('cn_eu_member', $queryBuilder->createNamedParameter(1, \PDO::PARAM_INT)));
        }

        if ($addWhere) {
            $addWhere = QueryHelper::stripLogicalOperatorPrefix($addWhere);
            if (empty($queryBuilder->getQueryPart('where'))) {
                $queryBuilder->where($addWhere);
            } else {
                $queryBuilder->andWhere($addWhere);
            }
        }
        $statement = $queryBuilder->execute();
        $version12 = version_compare($this->version, '12.0.0', '>=');
        while ($row = ($version12 ? $statement->fetchAssociative() : $statement->fetch())) {
            if (version_compare($this->versionNumber, '11.5.0', '>=')) {
                foreach ($titleFields as $titleField => $titleFieldProperty) {
                    if ($row[$titleField]) {
                        $nameArray[$row['cn_iso_3']] = $row[$titleField];
                        break;
                    }
                }
            } else {
                foreach ($titleFields as $titleField) {
                    if ($row[$titleField]) {
                        $nameArray[$row['cn_iso_3']] = $row[$titleField];
                        break;
                    }
                }
            }
        }

        if ($this->countriesAllowed != '') {
            $countriesAllowedArray = GeneralUtility::trimExplode(',', $this->countriesAllowed);
            $newNameArray = [];
            foreach ($countriesAllowedArray as $iso3) {
                if (isset($nameArray[$iso3])) {
                    $newNameArray[$iso3] = $nameArray[$iso3];
                }
            }
            $nameArray = $newNameArray;
        } else {
            uasort($nameArray, 'strcoll');
        }

        return $nameArray;
    }

    /**
     * Getting all country subdivisions of a given country into an array
     * 	where the key is the code of the subdivision
     * 	and where the value is the name of the country subdivision in the current language
     * You can leave the ISO code empty and use the additional WHERE clause instead of it.
     *
     * @param string The ISO alpha-3 code of a country
     * @param string additional WHERE clause
     *
     * @return array An array of names of country subdivisions
     */
    public function initCountrySubdivisions($param, $addWhere = '')
    {
        $nameArray = [];
        $table = $this->tables['SUBDIVISIONS'];
        $lang = LocalizationUtility::getCurrentLanguage();
        $lang = LocalizationUtility::getIsoLanguageKey($lang);
        $titleFields = LocalizationUtility::getLabelFields($table, $lang);
        $prefixedTitleFields = [];
        if (version_compare($this->versionNumber, '11.5.0', '>=')) {
            foreach ($titleFields as $titleField => $titleFieldProperty) {
                $prefixedTitleFields[] = $table . '.' . $titleField;
            }
        } else {
            foreach ($titleFields as $titleField) {
                $prefixedTitleFields[] = $table . '.' . $titleField;
            }
        }
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable($table)
        ;
        $queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));
        $queryBuilder
            ->select($table . '.zn_code')
            ->from($table)
        ;
        foreach ($prefixedTitleFields as $titleField) {
            $queryBuilder->addSelect($titleField);
        }
        if (strlen($param) == 3) {
            $queryBuilder->where($queryBuilder->expr()->eq('zn_country_iso_3', $queryBuilder->createNamedParameter($param, \PDO::PARAM_STR)));
        }
        if ($addWhere) {
            $addWhere = QueryHelper::stripLogicalOperatorPrefix($addWhere);
            if (empty($queryBuilder->getQueryPart('where'))) {
                $queryBuilder->where($addWhere);
            } else {
                $queryBuilder->andWhere($addWhere);
            }
        }
        $statement = $queryBuilder->execute();
        $version12 = version_compare($this->version, '12.0.0', '>=');
        while ($row = ($version12 ? $statement->fetchAssociative() : $statement->fetch())) {
            if (version_compare($this->versionNumber, '11.5.0', '>=')) {
                foreach ($titleFields as $titleField => $titleFieldProperty) {
                    if ($row[$titleField]) {
                        $nameArray[$row['zn_code']] = $row[$titleField];
                        break;
                    }
                }
            } else {
                foreach ($titleFields as $titleField) {
                    if ($row[$titleField]) {
                        $nameArray[$row['zn_code']] = $row[$titleField];
                        break;
                    }
                }
            }
        }
        uasort($nameArray, 'strcoll');

        return $nameArray;
    }

    /**
     * Getting all currencies into an array
     * 	where the key is the ISO alpha-3 code of the currency
     * 	and where the value are the name of the currency in the current language.
     *
     * @param string additional WHERE clause
     *
     * @return array An array of names of currencies
     */
    public function initCurrencies($addWhere = '')
    {
        if (!$this->isActive()) {
            return false;
        }
        $nameArray = [];
        $table = $this->tables['CURRENCIES'];
        $lang = LocalizationUtility::getCurrentLanguage();
        $lang = LocalizationUtility::getIsoLanguageKey($lang);
        $titleFields = LocalizationUtility::getLabelFields($table, $lang);
        $prefixedTitleFields = [];
        if (version_compare($this->versionNumber, '11.5.0', '>=')) {
            foreach ($titleFields as $titleField => $titleFieldProperty) {
                $prefixedTitleFields[] = $table . '.' . $titleField;
            }
        } else {
            foreach ($titleFields as $titleField) {
                $prefixedTitleFields[] = $table . '.' . $titleField;
            }
        }
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable($table)
        ;
        $queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));
        $queryBuilder
            ->select($table . '.cu_iso_3')
            ->from($table)
        ;
        foreach ($prefixedTitleFields as $titleField) {
            $queryBuilder->addSelect($titleField);
        }
        if ($addWhere) {
            $addWhere = QueryHelper::stripLogicalOperatorPrefix($addWhere);
            $queryBuilder->where($addWhere);
        }
        $statement = $queryBuilder->execute();
        $version12 = version_compare($this->version, '12.0.0', '>=');
        while ($row = ($version12 ? $statement->fetchAssociative() : $statement->fetch())) {
            if (version_compare($this->versionNumber, '11.5.0', '>=')) {
                foreach ($titleFields as $titleField => $titleFieldProperty) {
                    if ($row[$titleField]) {
                        $nameArray[$row['cu_iso_3']] = $row[$titleField];
                        break;
                    }
                }
            } else {
                foreach ($titleFields as $titleField) {
                    if ($row[$titleField]) {
                        $nameArray[$row['cu_iso_3']] = $row[$titleField];
                        break;
                    }
                }
            }
        }
        uasort($nameArray, 'strcoll');

        return $nameArray;
    }

    /**
     * Returns the current language as iso-2-alpha code.
     *
     * @return	string		'DE', 'EN', 'DK', ...
     */
    public static function getCurrentLanguage()
    {
        if (!$this->isActive()) {
            return false;
        }
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
        if (isset($this->cache['getCurrentLanguage'][$langCodeT3])) {
            return $this->cache['getCurrentLanguage'][$langCodeT3];
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
            !isset($this->cache['getCurrentLanguage']) ||
            !is_array($this->cache['getCurrentLanguage'])
        ) {
            $this->cache['getCurrentLanguage'] = [];
        }
        // Cache retrieved value
        $this->cache['getCurrentLanguage'][$langCodeT3] = $lang;

        return $lang;
    }

    /**
     * Loading currency display parameters from Static Info Tables.
     *
     * @return array An array of information regarding the currrency
     */
    public function loadCurrencyInfo($currencyCode)
    {
        if (!$this->isActive()) {
            return false;
        }
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->currencyRepository = $objectManager->get(CurrencyRepository::class);

        // Fetching the currency record
        $this->currencyInfo['cu_iso_3'] = trim($currencyCode);
        $this->currencyInfo['cu_iso_3'] = $this->currencyInfo['cu_iso_3'] ?: $this->currency;
        $currency = $this->currencyRepository->findOneByIsoCodeA3($this->currencyInfo['cu_iso_3']);
        // If not found we fetch the default currency!
        if (!($currency instanceof Currency)) {
            $this->currencyInfo['cu_iso_3'] = $this->currency;
            $currency = $this->currencyRepository->findOneByIsoCodeA3($this->currencyInfo['cu_iso_3']);
        }
        if ($currency instanceof Currency) {
            $this->currencyInfo['cu_name'] = $this->getStaticInfoName($this->currencyInfo['cu_iso_3'], 'CURRENCIES');
            $this->currencyInfo['cu_symbol_left'] = $currency->getSymbolLeft();
            $this->currencyInfo['cu_symbol_right'] = $currency->getSymbolRight();
            $this->currencyInfo['cu_decimal_digits'] = $currency->getDecimalDigits();
            $this->currencyInfo['cu_decimal_point'] = $currency->getDecimalPoint();
            $this->currencyInfo['cu_thousands_point'] = $currency->getThousandsPoint();
        }

        return $this->currencyInfo;
    }

    /**
     * Formatting an amount in the currency loaded by loadCurrencyInfo($currencyCode).
     *
     * 	 '' - the currency code is not displayed
     * 	 'RIGHT' - the code is displayed at the right of the amount
     * 	 'LEFT' - the code is displayed at the left of the amount
     *
     * @return string The formated amounted
     */
    public function formatAmount($amount, $displayCurrencyCode = '')
    {
        if (!$this->isActive()) {
            return false;
        }
        $formatedAmount = '';
        if ($displayCurrencyCode === 'LEFT') {
            $formatedAmount .= $this->currencyInfo['cu_iso_3'] . chr(32);
        }
        $formatedAmount .= $this->currencyInfo['cu_symbol_left'];
        $formatedAmount .= number_format($amount, (int)$this->currencyInfo['cu_decimal_digits'], $this->currencyInfo['cu_decimal_point'], $this->currencyInfo['cu_thousands_point'] ?: chr(32));
        $formatedAmount .= (($this->currencyInfo['cu_symbol_right']) ? chr(32) : '') . $this->currencyInfo['cu_symbol_right'];
        if ($displayCurrencyCode === 'RIGHT') {
            $formatedAmount .= chr(32) . $this->currencyInfo['cu_iso_3'];
        }

        return $formatedAmount;
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
        if (!$this->isActive()) {
            return false;
        }
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
        if (!$this->isActive()) {
            return false;
        }
        $result = false;

        if (
            $isoCode &&
            $table &&
            isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['static_info_tables']['tables'][$table]['isocode_field'][$index])
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
        if (!$this->isActive()) {
            return false;
        }
        $title = '';
        $titleFields = static::getTCAlabelField($table, true, $lang, $local);
        if (count($titleFields)) {
            $prefixedTitleFields = [];
            if (version_compare($this->versionNumber, '11.5.0', '>=')) {
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
                if (version_compare($this->versionNumber, '11.5.0', '>=')) {
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
        if (!$this->isActive()) {
            return false;
        }
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
