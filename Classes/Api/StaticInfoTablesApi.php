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

use Psr\Http\Message\ServerRequestInterface;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Database\Connection as Typo3Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\QueryHelper;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Localization\Locales;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

use SJBR\StaticInfoTables\Domain\Model\Currency;
use SJBR\StaticInfoTables\Domain\Repository\CountryRepository;
use SJBR\StaticInfoTables\Domain\Repository\CurrencyRepository;
use SJBR\StaticInfoTables\Utility\HtmlElementUtility;
use SJBR\StaticInfoTables\Utility\LocalizationUtility;

use JambageCom\Div2007\Utility\ExtensionUtility;
use JambageCom\Div2007\Utility\TableUtility;


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
    public $version; // TYPO3 version number
    public $countriesAllowed;

    /**
     * @var SiteLanguage
     */
    protected $siteLanguage = null;

    /**
     * @var CountryRepository
     */
    protected $countryRepository = null;

    /**
     * @var CurrencyRepository
     */
    protected $currencyRepository = null;

    /**
     * Initialization of the extension static_info_tables.
     */
    public function init(
        ServerRequestInterface $request,
        array $conf = [],
    )
    {
        $result = true;

        if (!ExtensionManagementUtility::isLoaded('static_info_tables')) {
            $result = false;
        } elseif (!$this->hasBeenInitialized) {
            $this->countryRepository = GeneralUtility::makeInstance(CountryRepository::class);
            $this->currencyRepository = GeneralUtility::makeInstance(CurrencyRepository::class);

            $typo3Version = GeneralUtility::makeInstance(Typo3Version::class);
            $this->version = $typo3Version->getMajorVersion();
            if (empty($conf)) {
                if (isset($GLOBALS['TSFE']) && is_object($GLOBALS['TSFE']) && isset($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_staticinfotables_pi1.'])) {
                    $conf = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_staticinfotables_pi1.'];
                } else if ($this->version >= 13) {
                    $fullTypoScript =
                        $request->getAttribute('frontend.typoscript')->getSetupArray();
                    $conf = $fullTypoScript['plugin.']['tx_staticinfotables_pi1.'] ?? [];
                }
            }

            $this->siteLanguage = $request->getAttribute('language');
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
        $defaultSelectedArray = null;
        if (!$this->isActive()) {
            return false;
        }
        $selector = '';

        if (isset($selectedArray) && is_string($selectedArray)) {
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
                $defaultSelectedArray = $selectedArray ?? [$this->defaultCountry];
                break;
            case 'SUBDIVISIONS':
                $param = (trim($country) ?: $this->defaultCountry);
                $nameArray = $this->initCountrySubdivisions($param, $addWhere);
                if ($param == $this->defaultCountry) {
                    $defaultSelectedArray = $selectedArray ?? [$this->defaultCountryZone];
                }
                break;
            case 'CURRENCIES':
                $nameArray = $this->initCurrencies($addWhere);
                $defaultSelectedArray = $selectedArray ?? [$this->currency];
                break;
            case 'LANGUAGES':
                $nameArray = $this->initLanguages($addWhere);
                $defaultSelectedArray = $selectedArray ?? [$this->defaultLanguage];
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
        foreach ($titleFields as $titleField => $titleFieldProperty) {
            $prefixedTitleFields[] = $table . '.' . $titleField;
        }

        array_unique($prefixedTitleFields);

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable($table)
        ;
        $expressionBuilder = $queryBuilder->expr();
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
            $queryBuilder->where($expressionBuilder->eq('cn_uno_member', $queryBuilder->createNamedParameter(1, Typo3Connection::PARAM_INT)));
        } elseif ($param === 'EU') {
            $queryBuilder->where($expressionBuilder->eq('cn_eu_member', $queryBuilder->createNamedParameter(1, Connection::PARAM_INT)));
        }

        if ($addWhere) {
            $addWhere = QueryHelper::stripLogicalOperatorPrefix($addWhere);
            if (empty($queryBuilder->getQueryPart('where'))) {
                $queryBuilder->where($addWhere);
            } else {
                $queryBuilder->andWhere($addWhere);
            }
        }
        $statement = $queryBuilder->executeQuery();
        while ($row = $statement->fetchAssociative()) {
            foreach ($titleFields as $titleField => $titleFieldProperty) {
                if ($row[$titleField]) {
                    $nameArray[$row['cn_iso_3']] = $row[$titleField];
                    break;
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
        foreach ($titleFields as $titleField => $titleFieldProperty) {
            $prefixedTitleFields[] = $table . '.' . $titleField;
        }
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable($table)
        ;
        $expressionBuilder = $queryBuilder->expr();
        $queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));
        $queryBuilder
            ->select($table . '.zn_code')
            ->from($table)
        ;
        foreach ($prefixedTitleFields as $titleField) {
            $queryBuilder->addSelect($titleField);
        }
        if (strlen($param) == 3) {
            $queryBuilder->where($expressionBuilder->eq('zn_country_iso_3', $queryBuilder->createNamedParameter($param, Typo3Connection::PARAM_STR)));
        }
        if ($addWhere) {
            $addWhere = QueryHelper::stripLogicalOperatorPrefix($addWhere);
            if (empty($queryBuilder->getQueryPart('where'))) {
                $queryBuilder->where($addWhere);
            } else {
                $queryBuilder->andWhere($addWhere);
            }
        }
        $statement = $queryBuilder->executeQuery();
        while ($row = $statement->fetchAssociative()) {
            foreach ($titleFields as $titleField => $titleFieldProperty) {
                if ($row[$titleField]) {
                    $nameArray[$row['zn_code']] = $row[$titleField];
                    break;
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
        foreach ($titleFields as $titleField => $titleFieldProperty) {
            $prefixedTitleFields[] = $table . '.' . $titleField;
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
        $statement = $queryBuilder->executeQuery();
        while ($row = $statement->fetchAssociative()) {
            foreach ($titleFields as $titleField => $titleFieldProperty) {
                if ($row[$titleField]) {
                    $nameArray[$row['cu_iso_3']] = $row[$titleField];
                    break;
                }
            }
        }
        uasort($nameArray, 'strcoll');

        return $nameArray;
    }

    /**
     * Getting all languages into an array
     * 	where the key is the ISO alpha-2 code of the language
     * 	and where the value are the name of the language in the current language
     * 	Note: we exclude sacred and constructed languages
     *
     * @param string $addWhere: additional WHERE clause
     *
     * @return string|bool Gibt den Sprachcode zurück (z.B. 'EN' oder 'de_DE') oder false
     */
    public function getCurrentLanguage()
    {
        if (!$this->isActive()) {
            return false;
        }

        if ($this->siteLanguage !== null) {
            $langCodeT3 = $this->siteLanguage->getTypo3Language();
        } else {
            $languageAspect = GeneralUtility::makeInstance(Context::class)->getAspect('language');
            $langCodeT3 = $languageAspect->getLegacyLanguageKey();
        }

        if (empty($langCodeT3) || $langCodeT3 === 'default') {
            return 'EN';
        }

        // Cache-Abfrage vorab
        if (isset($this->cache['getCurrentLanguage'][$langCodeT3])) {
            return $this->cache['getCurrentLanguage'][$langCodeT3];
        }

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('static_languages');

        // Optimierung: Nur eine Zeile fetchen statt einer while-Schleife
        $row = $queryBuilder
            ->select('lg_iso_2', 'lg_country_iso_2')
            ->from('static_languages')
            ->where(
                $queryBuilder->expr()->eq(
                    'lg_typo3',
                    $queryBuilder->createNamedParameter($langCodeT3)
                )
            )
            ->executeQuery()
            ->fetchAssociative();

        $lang = '';
        if ($row) {
            $lang = $row['lg_iso_2'] . ($row['lg_country_iso_2'] ? '_' . $row['lg_country_iso_2'] : '');
        }

        $lang = $lang ?: strtoupper($langCodeT3);

        // Cache befüllen (Dank PHP-Array-Autovivification ist keine Vorab-Initialisierung nötig)
        $this->cache['getCurrentLanguage'][$langCodeT3] = $lang;

        return $lang;
    }


    /**
    * Initialisiert die Sprachenliste.
    *
    * @param string $addWhere Optionaler zusätzlicher Query-String (wird sicher angehängt)
    * @return array Sortiertes Array mit [Sprachcode => Name]
    */
    public function initLanguages($addWhere = ''): array
    {
        $nameArray = [];
        $table = $this->tables['LANGUAGES'];

        $lang = LocalizationUtility::getCurrentLanguage();
        $lang = LocalizationUtility::getIsoLanguageKey($lang);
        $titleFields = LocalizationUtility::getLabelFields($table, $lang);

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable($table);

        $queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));

        // Basis-Auswahl mit Aliasen, um Treiber-Inkonsistenzen zu vermeiden
        $queryBuilder
            ->select($table . '.lg_iso_2 AS lg_iso_2')
            ->addSelect($table . '.lg_country_iso_2 AS lg_country_iso_2')
            ->from($table);

        // Dynamische Titelfelder mit klarem Alias hinzufügen
        foreach ($titleFields as $titleField => $map) {
            $queryBuilder->addSelect($table . '.' . $titleField . ' AS ' . $titleField);
        }

        $queryBuilder->where(
            $queryBuilder->expr()->eq('lg_sacred', $queryBuilder->createNamedParameter(0, Connection::PARAM_INT))
        )->andWhere(
            $queryBuilder->expr()->eq('lg_constructed', $queryBuilder->createNamedParameter(0, Connection::PARAM_INT))
        );

        if ($addWhere) {
            $strippedWhere = QueryHelper::stripLogicalOperatorPrefix($addWhere);
            $queryBuilder->andWhere($strippedWhere);
        }

        $query = $queryBuilder->executeQuery();
        while ($row = $query->fetchAssociative()) {
            $code = $row['lg_iso_2'] . ($row['lg_country_iso_2'] ? '_' . $row['lg_country_iso_2'] : '');

            foreach ($titleFields as $titleField => $map) {
                // Prüfung optimiert für leere Strings oder null
                if (isset($row[$titleField]) && $row[$titleField] !== '') {
                    $nameArray[$code] = $row[$titleField];
                    break;
                }
            }
        }

        asort($nameArray);

        return $nameArray;
    }

    /**
     * Loading currency display parameters from Static Info Tables
     *
     * @param string $currencyCode: An ISO alpha-3 currency code
     *
     * @return array An array of information regarding the currrency
     */
    public function loadCurrencyInfo($currencyCode)
    {
        // Fetching the currency record
        $this->currencyInfo['cu_iso_3'] = trim($currencyCode);
        $this->currencyInfo['cu_iso_3'] = $this->currencyInfo['cu_iso_3'] ?? $this->currency;

        $currency = $this->currencyRepository->findOneBy(['isoCodeA3' => $this->currencyInfo['cu_iso_3']]);
        // If not found we fetch the default currency!
        if (!($currency instanceof Currency)) {
            $this->currencyInfo['cu_iso_3'] = $this->currency;
            $currency = $this->currencyRepository->findOneBy(['isoCodeA3' => $this->currencyInfo['cu_iso_3']]);
        }
        if ($currency instanceof Currency) {
            $this->currencyInfo['cu_name'] = $this->getStaticInfoName('CURRENCIES', $this->currencyInfo['cu_iso_3']);
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
     * @param string $table
     * @param bool $bLoadTCA
     * @param string $lang
     * @param bool $local
     * @return array
     */

    public function getTCAlabelField($table, $bLoadTCA = true, $lang = '', $local = false): array
    {
        // Geändert: von !static::isActive() zu $this->isActive()
        if (!$this->isActive()) {
            return [];
        }

        $labelFields = [];
        $extConf = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['static_info_tables']['tables'][$table]['label_fields'] ?? null;

        if ($table && is_array($extConf)) {
            $locales = GeneralUtility::makeInstance(Locales::class);
            $isoArray = (array)$locales->getIsoMapping();
            $lang = $lang ?: $this->getCurrentLanguage();
            $lang = $isoArray[$lang] ?? $lang;

            foreach ($extConf as $field) {
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
    * @param mixed $isoCode
    * @return string 'nr', '2', '3' oder leerer String
    */
    public function isoCodeType($isoCode): string
    {
        if (is_array($isoCode)) {
            $isoCode = reset($isoCode);
        }

        if ($isoCode === null || $isoCode === '') {
            return '';
        }

        if (MathUtility::canBeInterpretedAsInteger($isoCode)) {
            return 'nr';
        }

        $length = strlen((string)$isoCode);
        if ($length === 2) {
            return '2';
        }
        if ($length === 3) {
            return '3';
        }

        return '';
    }

    /**
     * Returns a iso code field for the passed table and iso code.
     *
     *  $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['static_info_tables']['tables']
     *
     *
     * @param string $table
     * @param string|array $isoCode
     * @param bool $bLoadTCA (Optional, obsolete)
     * @param int $index
     * @return string|bool Returns the fieldname as string or false
     */
    public function getIsoCodeField($table, $isoCode, $bLoadTCA = false, $index = 0)
    {
        if (!$this->isActive()) {
            return false;
        }
        $result = false;

        $extConf = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['static_info_tables']['tables'][$table]['isocode_field'][$index] ?? null;

        if ($isoCode && $table && $extConf) {
            $isoCodeField = $extConf;

            if ($isoCodeField !== '') {
                // GEÄNDERT: von static::isoCodeType() zu $this->isoCodeType()
                $type = $this->isoCodeType($isoCode);
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
    public function getTitleFromIsoCode($table, $isoCode, $lang = '', $local = false)
    {
        if (!$this->isActive()) {
            return false;
        }

        $title = '';
        $titleFields = $this->getTCAlabelField($table, true, $lang, $local);

        if (count($titleFields)) {
            // Initialize the QueryBuilder for the dynamic table name
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getQueryBuilderForTable($table);

            // Build fields array for the select statement
            $selectFields = [];
            foreach ($titleFields as $titleField => $titleFieldProperty) {
                $selectFields[] = $table . '.' . $titleField . ' AS ' . $titleField;
            }
            $queryBuilder
                ->select(...$selectFields)
                ->from($table);

            // Dynamically add conditions securely using Named Parameters
            if (!is_array($isoCode)) {
                $isoCode = [$isoCode];
            }

            foreach ($isoCode as $index => $code) {
                if ($code !== '') {
                    $tmpField = static::getIsoCodeField($table, $code, true, $index);
                    if ($tmpField) {
                        $queryBuilder->andWhere(
                            $queryBuilder->expr()->eq(
                                $table . '.' . $tmpField,
                                $queryBuilder->createNamedParameter($code)
                            )
                        );
                    }
                }
            }

            // Execute query and fetch the single matching row
            $row = $queryBuilder->executeQuery()->fetchAssociative();

            if ($row) {
                foreach ($titleFields as $titleField => $titleFieldProperty) {
                    if (!empty($row[$titleField])) {
                        $title = $row[$titleField];
                        break;
                    }
                }
            }
        }

        return $title;
    }

    /**
     * Get a list of countries by specific parameters or parts of names of countries
     * in different languages. Parameters might be left empty.
     *
     * @param string $country  a name of the country or a part of it in any language
     * @param string $iso2     ISO alpha-2 code of the country
     * @param string $iso3     ISO alpha-3 code of the country
     * @param string $isonr
     * @param   array       database row
     *
     * @return  array       Array of rows of found country records
     */
    public function fetchCountries($country = 'Germany', $iso2 = '', $iso3 = '', $isonr = ''): array
    {
        if (!$this->isActive()) {
            return [];
        }

        $resultArray = [];
        $table = 'static_countries';

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable($table);

        $queryBuilder
            ->select('*')
            ->from($table);

        // Flag, um zu prüfen, ob überhaupt eine Bedingung gesetzt wurde
        $hasConstraints = false;

        // Priorität 1: Spezifische ISO-Abfragen (wie im Original überschreiben diese die Namenssuche)
        if ($isonr !== '') {
            $queryBuilder->where(
                $queryBuilder->expr()->eq('cn_iso_nr', $queryBuilder->createNamedParameter(trim($isonr)))
            );
            $hasConstraints = true;
        } elseif ($iso2 !== '') {
            $queryBuilder->where(
                $queryBuilder->expr()->eq('cn_iso_2', $queryBuilder->createNamedParameter(trim($iso2)))
            );
            $hasConstraints = true;
        } elseif ($iso3 !== '') {
            $queryBuilder->where(
                $queryBuilder->expr()->eq('cn_iso_3', $queryBuilder->createNamedParameter(trim($iso3)))
            );
            $hasConstraints = true;
        }
        // Priorität 2: Namenssuche via LIKE (nur wenn kein ISO-Code übergeben wurde)
        elseif ($country !== '') {
            $trimmedCountry = trim($country);
            $likeValue = $queryBuilder->createNamedParameter('%' . $trimmedCountry . '%');

            // Sammle alle OR-Bedingungen für die Namenssuche
            $orConditions = [
                $queryBuilder->expr()->like('cn_official_name_local', $likeValue),
                $queryBuilder->expr()->like('cn_official_name_en', $likeValue)
            ];

            // Dynamische Spalten aus der TCA auslesen (z.B. cn_short_en, cn_short_de)
            if (isset($GLOBALS['TCA'][$table]['columns']) && is_array($GLOBALS['TCA'][$table]['columns'])) {
                foreach ($GLOBALS['TCA'][$table]['columns'] as $fieldname => $fieldArray) {
                    if (str_starts_with($fieldname, 'cn_short_')) {
                        $orConditions[] = $queryBuilder->expr()->like($fieldname, $likeValue);
                    }
                }
            }

            // Alle gesammelten Bedingungen mit OR verknüpfen und in das WHERE setzen
            $queryBuilder->where(
                $queryBuilder->expr()->or(...$orConditions)
            );
            $hasConstraints = true;
        }

        // Nur ausführen, wenn mindestens ein Suchkriterium zutraf
        if ($hasConstraints) {
            $result = $queryBuilder->executeQuery();
            while ($row = $result->fetchAssociative()) {
                $resultArray[] = $row;
            }
        }

        return $resultArray;
    }

}
