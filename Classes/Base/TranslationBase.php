<?php

namespace JambageCom\Div2007\Base;

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

use Psr\Http\Message\ServerRequestInterface;

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Localization\Locales;
use TYPO3\CMS\Core\Localization\LocalizationFactory;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteInterface;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;




class TranslationBase
{
    const DEFAULT_LANGUAGE = 'default';
    protected $LOCAL_LANG = [];   // Local Language content
    protected $LOCAL_LANG_loaded = 0;  // Flag that tells if the locallang file has been fetch (or tried to be fetched) already.
    protected $LocalLangKey = self::DEFAULT_LANGUAGE;      // Pointer to the language to use.
    public $altLocalLangKey = '';          // Pointer to alternative fall-back language to use.
    public $localLangTestPrefix = '';      // You can set this during development to some value that makes it easy for you to spot all labels that are delivered by the getLocalLang function.
    public $localLangTestPrefixAlt = '';   // Save as localLangTestPrefix, but additional prefix for the alternative value in getLocalLang() function calls
    protected $scriptRelPath = '/Resources/Private/Language/';          // relative path to the extension directory where the locallang XLF / XML files are stored. The leading and trailing slashes must be included. E.g. '/Resources/Private/Language/'
    protected $extensionKey = '';	// extension key must be overridden
    protected $lookupFilename = ''; // filename used for the lookup method
    protected $request = null;
    /**
     * @var int
     */
    protected $languageId = 0;


    /**
     * Should normally be set in the main function with the TypoScript content passed to the method.
     *
     * $conf[LOCAL_LANG][_key_] is reserved for Local Language overrides.
     * $conf[userFunc] / $conf[includeLibs]  reserved for setting up the USER / USER_INT object. See TSref
     */
    protected $confLocalLang = [];
    private $hasBeenInitialized = false;

    public function __construct(
        private readonly LanguageServiceFactory $languageServiceFactory,
    ) {
    }

    public function init(
        $extensionKey = '',
        array $confLocalLang = [], // you must pass only the $conf['_LOCAL_LANG.'] part of the setup of the caller
        ?ServerRequestInterface $request = null,
        $lookupFilename = '',
        $useDiv2007Language = true
    ): void {
        $conf = [];
        if (!isset($request)) {
            $request = $GLOBALS['TYPO3_REQUEST'];
        }
        $this->request = $request;
        $currentSite = $this->getCurrentSite();
        $currentSiteLanguage = $this->getCurrentSiteLanguage($request) ?? $currentSite?->getDefaultLanguage();
        $this->languageId = $currentSiteLanguage?->getLanguageId();
        $typo3Language = $this->getLanguage($request);
        $this->setLocalLangKey($typo3Language);

        $isFrontend = (ApplicationType::fromRequest($request)->isFrontend());
        if ($isFrontend) {
            $conf = $this->getTypoScriptSetup($request)['plugin.']['div2007.'] ?? [];
        }

        if ($extensionKey != '') {
            $this->extensionKey = $extensionKey;
        }

        if (
            isset($conf) &&
            is_array($conf) &&
            isset($conf['_LOCAL_LANG.'])
        ) {
            $internalConfLocalLang = $conf['_LOCAL_LANG.'];
        }

        if (
            isset($internalConfLocalLang) &&
            is_array($internalConfLocalLang) &&
            isset($confLocalLang) &&
            is_array($confLocalLang)
        ) {
            $confLocalLang =
                array_merge_recursive(
                    $confLocalLang,
                    $internalConfLocalLang
                );
        }
        $this->setConfLocalLang($confLocalLang);
        $this->lookupFilename = $lookupFilename;

        $this->hasBeenInitialized = true;
        if ($useDiv2007Language) {
            $this->loadLocalLang(
                'EXT:' . DIV2007_EXT . DIV2007_LANGUAGE_SUBPATH . 'locallang.xlf'
            );
        }
    }


    /**
     * Returns full Frontend TypoScript setup array calculated by FE middlewares.
     */
    public function getTypoScriptSetup(?ServerRequestInterface $request): array
    {
        $frontendTypoScript = $request->getAttribute('frontend.typoscript');
        if (!($frontendTypoScript instanceof FrontendTypoScript)) {
            throw new \RuntimeException(
                'Setup array has not been initialized. This happens in middleware which is executed too early before' .
                ' the system reads in the full TypoScript.',
                1779811045
            );
        }
        return $frontendTypoScript->getSetupArray();
    }

    public function getLanguageId(): int
    {
        return $this->languageId;
    }

    public function setLocalLang(array $locallang): void
    {
        $this->LOCAL_LANG = $locallang;
    }

    public function getLocalLang()
    {
        return $this->LOCAL_LANG;
    }

    public function setLocalLangLoaded($loaded = true): void
    {
        $this->LOCAL_LANG_loaded = $loaded;
    }

    public function getLocalLangLoaded()
    {
        return $this->LOCAL_LANG_loaded;
    }

    public function setLocalLangKey($localLangKey): void
    {
        $this->LocalLangKey = $localLangKey;
    }

    // former getLLkey
    public function getLocalLangKey()
    {
        return $this->LocalLangKey;
    }

    public function getExtensionKey()
    {
        return $this->extensionKey;
    }

    public function setConfLocalLang(array $conf): void
    {
        $newConf = [];

        foreach ($conf as $k => $subConf) {
            $key = rtrim($k, '.');
            $newConf[$key] = $subConf;
        }
        $this->confLocalLang = $newConf;
    }

    public function getConfLocalLang()
    {
        return $this->confLocalLang;
    }

    public function setLookupFilename($lookupFilename): void
    {
        $this->lookupFilename = $lookupFilename;
    }

    public function getLookupFilename()
    {
        return $this->lookupFilename;
    }

    public function needsInit()
    {
        return !$this->hasBeenInitialized;
    }

    public function getLanguage(?ServerRequestInterface $request = null)
    {
        $typo3Language = 'en';
        if (!isset($request)) {
            $request = $this->request;
        }
        $isFrontend = (ApplicationType::fromRequest($request)->isFrontend());
        if ($isFrontend) {
            $language = $request->getAttribute('language') ?? $request->getAttribute('site')->getDefaultLanguage();
            if ($language->getTypo3Language() !== '') {
                $locale = GeneralUtility::makeInstance(Locales::class)->createLocale($language->getTypo3Language());
            } else {
                $locale = $language->getLocale();
            }
            $typo3Language = $locale->getLanguageCode();
        } else {
            if (
                ($GLOBALS['BE_USER'] ?? null) instanceof BackendUserAuthentication
            ) {
                $typo3Language = $GLOBALS['BE_USER']->uc['lang'] ?? 'en';
            } else {
                $currentSite = $this->getCurrentSite();
                $currentSiteLanguage = $this->getCurrentSiteLanguage($request) ?? $currentSite?->getDefaultLanguage();
                $typo3Language = $currentSiteLanguage?->getTypo3Language();
            }
        }

        return $typo3Language;
    }

    /**
     * This method has been used under TYPO3 versions above 4.6 as getLL
     * Returns the localized label of the LOCAL_LANG key, $key used since TYPO3 4.6
     * Notice that for debugging purposes prefixes for the output values can be set with the internal vars ->localLangTestPrefixAlt and ->localLangTestPrefix.
     *
     * former getLL method
     *
     * @param   string      the key from the LOCAL_LANG array for which to return the value
     * @param   string      input: if set then this language is used if possible. output: the used language
     * @param   string      alternative string to return IF no value is found set for the key, neither for the local language nor the default
     * @param   bool     If true, the output label is passed through htmlspecialchars()
     *
     * @return  string / boolean The prefixed value from LOCAL_LANG. false, if no entry could be found.
     */
    public function getLabel(
        $key,
        &$usedLang = '',
        $alternativeLabel = '',
        $hsc = false
    ) {
        $output = false;
        $word = '';

        if ($usedLang == '') {
            $usedLang = $this->getLocalLangKey();
        }

        if (
            $usedLang != '' &&
            isset($this->LOCAL_LANG[$usedLang][$key])
        ) {
            $word = $this->LOCAL_LANG[$usedLang][$key];
        } elseif (
            $this->getLocalLangKey() != '' &&
            isset($this->LOCAL_LANG[$this->getLocalLangKey()][$key])
        ) {
            $usedLang = $this->getLocalLangKey();
            $word = $this->LOCAL_LANG[$this->getLocalLangKey()][$key];
        } elseif (
            $this->altLocalLangKey != '' &&
            isset($this->LOCAL_LANG[$this->altLocalLangKey][$key])
        ) {
            $usedLang = $this->altLocalLangKey;
            $word = $this->LOCAL_LANG[$this->altLocalLangKey][$key];
        } elseif (
            isset($this->LOCAL_LANG['default'][$key])
        ) {
            $usedLang = 'default';
            $word = $this->LOCAL_LANG[$usedLang][$key];
        } else {
            // Return alternative string or empty
            $word = (!empty($this->localLangTestPrefixAlt)) ? $this->localLangTestPrefixAlt . $alternativeLabel : $alternativeLabel;
        }

        if (is_string($word)) {
            $output = $word;
        } else if (isset($word[0]['target'])) {
            $text = $word[0]['target'];
            $output = (isset($this->localLangTestPrefix) ? $this->localLangTestPrefix . $text : $text);
        }

        if (
            is_string($output) &&
            $hsc
        ) {
            $output = htmlspecialchars($output);
        }

        return $output;
    }


    /**
     * used since TYPO3 4.6 as loadLL
     * Loads local-language values by looking for a "locallang.xlf" file in the plugin class directory ($langObj->scriptRelPath) and if found includes it.
     * Also locallang values set in the TypoScript property "_LOCAL_LANG" are merged onto the values found in the "locallang.xlf" file.
     *
     * former method loadLL
     *
     * @param   string      language file to load
     * @param   bool     If true, then former language items can be overwritten from the new file
     *
     * @return  bool
     */
    public function loadLocalLang(
        $langFileParam = '',
        $overwrite = true
    ) {
        $langFile = ($langFileParam ?? $this->getLookupFilename());
        $extensionKey = $this->getExtensionKey();

        if (
            str_starts_with($langFile, 'EXT:') ||
            str_starts_with($langFile, 'typo3') ||
            str_starts_with($langFile, 'fileadmin')
        ) {
            $basePath = $langFile;
        } elseif ($extensionKey != '') {
            $basePath = ExtensionManagementUtility::extPath($extensionKey);
            $basePath .= $this->scriptRelPath;
            $basePath .= $langFile;
        } else {
            return false;
        }

        $callingClassName = LocalizationFactory::class;

        /** @var $languageFactory \TYPO3\CMS\Core\Localization\LocalizationFactory */
        $languageFactory = GeneralUtility::makeInstance($callingClassName);
        $filePath = GeneralUtility::getFileAbsFileName($basePath);

        if (!file_exists($filePath)) {
            debug($basePath, 'ERROR: ' . DIV2007_EXT . ' called by "' . $extensionKey . '" - file "' . $basePath . '" cannot be found!'); // keep this
            return false;
        }

        $tempLOCAL_LANG = $languageFactory->getParsedData(
            $basePath,
            $this->getLocalLangKey(),
            'UTF-8'
        );

        if (count($this->LOCAL_LANG) && is_array($tempLOCAL_LANG)) {
            $originalLanguages = $this->LOCAL_LANG;
            foreach ($originalLanguages as $langKey => $tempArray) {
                if (
                    isset($tempLOCAL_LANG) && is_array($tempLOCAL_LANG)
                ) {
                    $locallangHasIndex = isset($tempLOCAL_LANG[$langKey]);
                    $newLocalLang = ($locallangHasIndex ? $tempLOCAL_LANG[$langKey] : $tempLOCAL_LANG);
                    if ($overwrite) {
                        $this->LOCAL_LANG[$langKey] = array_merge($this->LOCAL_LANG[$langKey], $newLocalLang);
                    } else {
                        $this->LOCAL_LANG[$langKey] =
                            array_merge(
                                $newLocalLang,
                                $tempArray
                            );
                    }

                    if ($locallangHasIndex) {
                        unset($tempLOCAL_LANG[$langKey]);
                    }
                }
            }

            if (
                isset($tempLOCAL_LANG[self::DEFAULT_LANGUAGE]) &&
                !isset($originalLanguages[self::DEFAULT_LANGUAGE])
            ) {
                $this->LOCAL_LANG[self::DEFAULT_LANGUAGE] = $tempLOCAL_LANG[self::DEFAULT_LANGUAGE];
            }
        } else if (is_array($tempLOCAL_LANG)) {
            $newLocalLang = $tempLOCAL_LANG[$this->getLocalLangKey()] ?? $tempLOCAL_LANG;
            $this->LOCAL_LANG[$this->getLocalLangKey()] = $newLocalLang;
        }

        if ($this->altLocalLangKey) {
            $tempLOCAL_LANG = $languageFactory->getParsedData(
                $basePath,
                $this->altLocalLangKey,
                'UTF-8'
            );

            if (count($this->LOCAL_LANG) && is_array($tempLOCAL_LANG)) {
                $originalLanguages = $this->LOCAL_LANG;
                foreach ($originalLanguages as $langKey => $tempArray) {
                    if (
                        isset($tempLOCAL_LANG) && is_array($tempLOCAL_LANG)
                    ) {
                        $newLocalLang = $tempLOCAL_LANG[$langKey] ?? $tempLOCAL_LANG;
                        if ($overwrite) {
                            $this->LOCAL_LANG[$langKey] =
                                array_merge($this->LOCAL_LANG[$langKey], $newLocalLang);
                        } else {
                            $this->LOCAL_LANG[$langKey] =
                                array_merge($newLocalLang, $this->LOCAL_LANG[$langKey]);
                        }
                    }
                }
            } else {
                $newLocalLang = $tempLOCAL_LANG[$this->getLocalLangKey()] ?? $tempLOCAL_LANG;
                $this->LOCAL_LANG[$this->getLocalLangKey()] = $newLocalLang;
            }
        }

        // Overlaying labels from TypoScript (including fictious language keys for non-system languages!):
        $confLocalLang = $this->getConfLocalLang();

        if (is_array($confLocalLang)) {
            foreach ($confLocalLang as $languageKey => $languageArray) {
                if (is_array($languageArray)) {
                    if (!isset($this->LOCAL_LANG[$languageKey])) {
                        $this->LOCAL_LANG[$languageKey] = [];
                    }

                    // Remove the dot after the language key
                    foreach ($languageArray as $labelKey => $labelValue) {
                        if (!isset($this->LOCAL_LANG[$languageKey][$labelKey])) {
                            $this->LOCAL_LANG[$languageKey][$labelKey] = '';
                        }

                        if (is_array($labelValue)) {
                            foreach ($labelValue as $labelKey2 => $labelValue2) {
                                if (is_array($labelValue2)) {
                                    foreach ($labelValue2 as $labelKey3 => $labelValue3) {
                                        if (is_array($labelValue3)) {
                                            foreach ($labelValue3 as $labelKey4 => $labelValue4) {
                                                if (is_array($labelValue4)) {
                                                } else {
                                                    $this->LOCAL_LANG[$languageKey][$labelKey . $labelKey2 . $labelKey3 . $labelKey4] = $labelValue4;
                                                }
                                            }
                                        } else {
                                            $this->LOCAL_LANG[$languageKey][$labelKey . $labelKey2 . $labelKey3] = $labelValue3;
                                        }
                                    }
                                } else {
                                    $this->LOCAL_LANG[$languageKey][$labelKey . $labelKey2] = $labelValue2;
                                }
                            }
                        } else {
                            $this->LOCAL_LANG[$languageKey][$labelKey] = $labelValue;
                        }
                    }
                }
            }
        }

        $this->LOCAL_LANG_loaded = 1;
        $result = true;

        return $result;
    }


    // notice: this method will not consider the _LOCAL_LANG setup overwritings
    public function translate($key, $extensionKey = '', $filename = '')
    {
        if ($filename == '') {
            $filename = $this->getLookupFilename();
        }
        if ($extensionKey == '') {
            $extensionKey = $this->getExtensionKey();
        }
        $result = $this->sL('LLL:EXT:' . $extensionKey . $filename . ':' . $key);

        return $result;
    }

    /**
     * Main and most often used method.
     *
     * Resolve strings like these:
     *
     * ```
     * 'LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:labels.depth_0'
     * ```
     * @param string $input Label key/reference
     * @see LocalizationUtility::translate()
     */
    public function sL($input): string
    {
        $output = GeneralUtility::makeInstance(LanguageServiceFactory::class)
        ->createFromSiteLanguage($this->request->getAttribute('language'))->sL($input);
        return $output;
    }


    /**
     * Split Label function for front-end applications.
     *
     * former method sL
     *
     * @param	string		Key string. Accepts the "LLL:" prefix.
     *
     * @return	string		label value, if any
     */
    public static function splitLabel($input)
    {
        $restStr = trim(substr($input, 4));
        $extPrfx = '';
        if (!strcmp(substr($restStr, 0, 4), 'EXT:')) {
            $restStr = trim(substr($restStr, 4));
            $extPrfx = 'EXT:';
        }
        $parts = explode(':', $restStr);

        return $parts[1];
    }

    public function getTypo3LanguageKey(): string
    {
        return $this->getLanguageService()->lang;
    }

    /**
     * Check if we have a site object in the current request. if null, this usually means that
     * this class was called from CLI context.
     */
    protected function getCurrentSite(): ?SiteInterface
    {
        if (
            ($this->request ?? null) instanceof ServerRequestInterface
        ) {
            return $this->request->getAttribute('site', null);
        }
        return null;
    }

    /**
     * If the current request has a site language, this means that the SiteResolver has detected a
     * page with a site configuration and a selected language, so let's choose that one.
     */
    protected function getCurrentSiteLanguage(?ServerRequestInterface $request = null
    ): ?SiteLanguage
    {
        if (($request ?? null) instanceof ServerRequestInterface) {
            $request = $this->request;
        }

        if (($request ?? null) instanceof ServerRequestInterface) {
            return $request->getAttribute('language', null);
        }

        return null;
    }

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'] ?? $this->languageServiceFactory->create('default');
    }
}
