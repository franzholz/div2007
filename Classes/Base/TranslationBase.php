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

use TYPO3\CMS\Core\Charset\CharsetConverter;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Localization\Locales;
use TYPO3\CMS\Core\Localization\LocalizationFactory;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteInterface;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;


class TranslationBase
{
    public $LOCAL_LANG = [];   // Local Language content
    public $LOCAL_LANG_charset = [];   // Local Language content charset for individual labels (overriding)
    public $LOCAL_LANG_loaded = 0;  // Flag that tells if the locallang file has been fetch (or tried to be fetched) already.
    public $LocalLangKey = 'default';      // Pointer to the language to use.
    public $altLocalLangKey = '';          // Pointer to alternative fall-back language to use.
    public $localLangTestPrefix = '';      // You can set this during development to some value that makes it easy for you to spot all labels that ARe delivered by the getLocalLang function.
    public $localLangTestPrefixAlt = '';   // Save as localLangTestPrefix, but additional prefix for the alternative value in getLocalLang() function calls
    public $scriptRelPath = '/Resources/Private/Language/';          // relative path to the extension directory where the locallang XLF / XML files are stored. The leading and trailing slashes must be included. E.g. '/Resources/Private/Language/'
    protected $extensionKey = '';	// extension key must be overridden
    protected $lookupFilename = ''; // filename used for the lookup method
    protected $request = null;

    /**
     * @var TypoScriptFrontendController|null
     */
    protected $typoScriptFrontendController;

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
        $confLocalLang = [], // you must pass only the $conf['_LOCAL_LANG.'] part of the setup of the caller
        ?ServerRequestInterface $request = null,
        $lookupFilename = '',
        $useDiv2007Language = true
    ): void {
        $conf = [];
        if (!isset($request)) {
            $request = $GLOBALS['TYPO3_REQUEST'];
        }
        $this->request = $request;
        $typo3Language = $this->getLanguage($request);
        $this->setLocalLangKey($typo3Language);

        $isFrontend = (ApplicationType::fromRequest($request)->isFrontend());
        if ($isFrontend) {
            $conf = [];
            $tsfe = $request->getAttribute('frontend.typoscript');
            if (
                $tsfe instanceof TypoScriptFrontendController
            ) {
                $conf = $tsfe->getSetupArray()['plugin.']['div2007.'] ?? [];
            }
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

    public function setLocalLang(array $locallang): void
    {
        $this->LOCAL_LANG = $locallang;
    }

    // former getLocallang
    public function getLocalLang()
    {
        return $this->LOCAL_LANG;
    }

    public function setLocalLangCharset($locallang): void
    {
        $this->LOCAL_LANG_charset = $locallang;
    }

    public function getLocalLangCharset()
    {
        return $this->LOCAL_LANG_charset;
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

    public function setConfLocalLang($conf): void
    {
        $this->confLocalLang = $conf;
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
            $request = $GLOBALS['TYPO3_REQUEST'];
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
            $currentSite = $this->getCurrentSite();
            $currentSiteLanguage = $this->getCurrentSiteLanguage($request) ?? $currentSite?->getDefaultLanguage();
            $typo3Language = $currentSiteLanguage?->getTypo3Language();
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
        /** @var CharsetConverter $charsetConverter */
        $charsetConverter = GeneralUtility::makeInstance(CharsetConverter::class);

        if (
            $usedLang != '' &&
            isset($this->LOCAL_LANG[$usedLang][$key][0]) &&
            is_array($this->LOCAL_LANG[$usedLang][$key][0]) &&
            isset($this->LOCAL_LANG[$usedLang][$key][0]['target']) &&
            (
                $this->LOCAL_LANG[$usedLang][$key][0]['target'] != '' ||
                !isset($this->LOCAL_LANG[$usedLang][$key][0]['source'])
            )
        ) {
            // The "from" charset of csConv() is only set for strings from TypoScript via _LOCAL_LANG
            if (!empty($this->LOCAL_LANG_charset[$usedLang][$key])) {
                try {
                    $word =
                        $charsetConverter->conv(
                            $this->LOCAL_LANG[$usedLang][$key][0]['target'],
                            $this->LOCAL_LANG_charset[$usedLang][$key],
                            'utf-8'
                        );
                } catch (UnknownCharsetException $e) {
                    throw new \RuntimeException('Invalid charset "' . $this->LOCAL_LANG_charset[$usedLang][$key] . '"  for language "' . $usedLang . '" ' . $e->getMessage(), 1652354355);
                }
            } else {
                $word = $this->LOCAL_LANG[$usedLang][$key][0]['target'];
            }
        } elseif (
            $this->getLocalLangKey() != '' &&
            isset($this->LOCAL_LANG[$this->getLocalLangKey()][$key][0]) &&
            is_array($this->LOCAL_LANG[$this->getLocalLangKey()][$key][0]) &&
            isset($this->LOCAL_LANG[$this->getLocalLangKey()][$key][0]['target']) &&
            (
                $this->LOCAL_LANG[$this->getLocalLangKey()][$key][0]['target'] != '' ||
                !isset($this->LOCAL_LANG[$this->getLocalLangKey()][$key][0]['source'])
            )
        ) {
            $usedLang = $this->getLocalLangKey();

            // The "from" charset of csConv() is only set for strings from TypoScript via _LOCAL_LANG
            if (!empty($this->LOCAL_LANG_charset[$usedLang][$key])) {
                try {
                    $word =
                        $charsetConverter->conv(
                            $this->LOCAL_LANG[$usedLang][$key][0]['target'],
                            $this->LOCAL_LANG_charset[$usedLang][$key],
                            'utf-8'
                        );
                } catch (UnknownCharsetException $e) {
                    throw new \RuntimeException('Invalid charset "' . $this->LOCAL_LANG_charset[$usedLang][$key] . '"  for language "' . $usedLang . '" ' . $e->getMessage(), 1652359060);
                }
            } else {
                $word = $this->LOCAL_LANG[$this->getLocalLangKey()][$key][0]['target'];
            }
        } elseif (
            $this->altLocalLangKey &&
            isset($this->LOCAL_LANG[$this->altLocalLangKey][$key][0]) &&
            is_array($this->LOCAL_LANG[$this->altLocalLangKey][$key][0]) &&
            (
                !empty($this->LOCAL_LANG[$this->altLocalLangKey][$key][0]['target']) ||
                !isset($this->LOCAL_LANG[$this->altLocalLangKey][$key][0]['source'])
            )
        ) {
            $usedLang = $this->altLocalLangKey;

            // The "from" charset of csConv() is only set for strings from TypoScript via _LOCAL_LANG
            if (isset($this->LOCAL_LANG_charset[$usedLang][$key])) {
                try {
                    $word =
                        $charsetConverter->conv(
                            $this->LOCAL_LANG[$usedLang][$key][0]['target'],
                            $this->LOCAL_LANG_charset[$usedLang][$key],
                            'utf-8'
                        );
                } catch (UnknownCharsetException $e) {
                    throw new \RuntimeException('Invalid charset "' . $this->LOCAL_LANG_charset[$usedLang][$key] . '"  for language "' . $usedLang . '" ' . $e->getMessage(), 1652359097);
                }
            } else {
                $word = $this->LOCAL_LANG[$this->altLocalLangKey][$key][0]['target'];
            }
        } elseif (
            isset($this->LOCAL_LANG['default'][$key][0]) &&
            is_array($this->LOCAL_LANG['default'][$key][0]) &&
            (
                isset($this->LOCAL_LANG['default'][$key][0]['target']) &&
                $this->LOCAL_LANG['default'][$key][0]['target'] != '' ||
                !isset($this->LOCAL_LANG['default'][$key][0]['source'])
            )
        ) {
            $usedLang = 'default';
            // Get default translation (without charset conversion, English)
            $word = $this->LOCAL_LANG[$usedLang][$key][0]['target'];
        } else {
            // Return alternative string or empty
            $word = (isset($this->localLangTestPrefixAlt)) ? $this->localLangTestPrefixAlt . $alternativeLabel : $alternativeLabel;
        }

        if (isset($word)) {
            $output = (isset($this->localLangTestPrefix)) ? $this->localLangTestPrefix . $word : $word;
            if ($hsc) {
                $output = htmlspecialchars($output);
            }
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
            foreach ($this->LOCAL_LANG as $langKey => $tempArray) {
                if (isset($tempLOCAL_LANG[$langKey]) && is_array($tempLOCAL_LANG[$langKey])) {
                    if ($overwrite) {
                        $this->LOCAL_LANG[$langKey] = array_merge($this->LOCAL_LANG[$langKey], $tempLOCAL_LANG[$langKey]);
                    } else {
                        $this->LOCAL_LANG[$langKey] = array_merge($tempLOCAL_LANG[$langKey], $this->LOCAL_LANG[$langKey]);
                    }
                }
            }
        } else {
            $this->LOCAL_LANG = $tempLOCAL_LANG;
        }
        $charset = 'UTF-8';

        if ($this->altLocalLangKey) {
            $tempLOCAL_LANG = $languageFactory->getParsedData(
                $basePath,
                $this->altLocalLangKey,
                'UTF-8'
            );

            if (count($this->LOCAL_LANG) && is_array($tempLOCAL_LANG)) {
                foreach ($this->LOCAL_LANG as $langKey => $tempArray) {
                    if (isset($tempLOCAL_LANG[$langKey]) && is_array($tempLOCAL_LANG[$langKey])) {
                        if ($overwrite) {
                            $this->LOCAL_LANG[$langKey] =
                                array_merge($this->LOCAL_LANG[$langKey], $tempLOCAL_LANG[$langKey]);
                        } else {
                            $this->LOCAL_LANG[$langKey] =
                                array_merge($tempLOCAL_LANG[$langKey], $this->LOCAL_LANG[$langKey]);
                        }
                    }
                }
            } else {
                $this->LOCAL_LANG = $tempLOCAL_LANG;
            }
        }

        // Overlaying labels from TypoScript (including fictitious language keys for non-system languages!):
        $confLocalLang = $this->getConfLocalLang();

        if (is_array($confLocalLang)) {
            foreach ($confLocalLang as $languageKey => $languageArray) {
                if (is_array($languageArray)) {
                    if (!isset($this->LOCAL_LANG[$languageKey])) {
                        $this->LOCAL_LANG[$languageKey] = [];
                    }
                    $languageKey = substr($languageKey, 0, -1);

                    // Remove the dot after the language key
                    foreach ($languageArray as $labelKey => $labelValue) {
                        if (!isset($this->LOCAL_LANG[$languageKey][$labelKey])) {
                            $this->LOCAL_LANG[$languageKey][$labelKey] = [];
                        }

                        if (is_array($labelValue)) {
                            foreach ($labelValue as $labelKey2 => $labelValue2) {
                                if (is_array($labelValue2)) {
                                    foreach ($labelValue2 as $labelKey3 => $labelValue3) {
                                        if (is_array($labelValue3)) {
                                            foreach ($labelValue3 as $labelKey4 => $labelValue4) {
                                                if (is_array($labelValue4)) {
                                                } else {
                                                    $this->LOCAL_LANG[$languageKey][$labelKey . $labelKey2 . $labelKey3 . $labelKey4][0]['target'] = $labelValue4;

                                                    if ($languageKey != 'default') {
                                                        $this->LOCAL_LANG_charset[$languageKey][$labelKey . $labelKey2 . $labelKey3 . $labelKey4] = $charset;    // For labels coming from the TypoScript (database) the charset is assumed to be "forceCharset" and if that is not set, assumed to be that of the individual system languages (thus no conversion)
                                                    }
                                                }
                                            }
                                        } else {
                                            $this->LOCAL_LANG[$languageKey][$labelKey . $labelKey2 . $labelKey3][0]['target'] = $labelValue3;

                                            if ($languageKey != 'default') {
                                                $this->LOCAL_LANG_charset[$languageKey][$labelKey . $labelKey2 . $labelKey3] = $charset; // For labels coming from the TypoScript (database) the charset is assumed to be "forceCharset" and if that is not set, assumed to be that of the individual system languages (thus no conversion)
                                            }
                                        }
                                    }
                                } else {
                                    $this->LOCAL_LANG[$languageKey][$labelKey . $labelKey2][0]['target'] = $labelValue2;

                                    if ($languageKey != 'default') {
                                        $this->LOCAL_LANG_charset[$languageKey][$labelKey . $labelKey2] = $charset;  // For labels coming from the TypoScript (database) the charset is assumed to be "forceCharset" and if that is not set, assumed to be that of the individual system languages (thus no conversion)
                                    }
                                }
                            }
                        } else {
                            $this->LOCAL_LANG[$languageKey][$labelKey][0]['target'] = $labelValue;

                            if ($languageKey != 'default') {
                                $this->LOCAL_LANG_charset[$languageKey][$labelKey] = $charset;   // For labels coming from the TypoScript (database) the charset is assumed to be "forceCharset" and if that is not set, assumed to be that of the individual system languages (thus no conversion)
                            }
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
        $tsfe = $this->getTypoScriptFrontendController();
        $result = $tsfe->sL('LLL:EXT:' . $extensionKey . $filename . ':' . $key);

        return $result;
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
     * @return TypoScriptFrontendController|null
     * @internal for reducing usage of global TSFE objects and to avoid conflicts when different frontend environments are used
     */
    public function getTypoScriptFrontendController()
    {
        return $this->typoScriptFrontendController ?: $GLOBALS['TSFE'] ?? throw new RuntimeException('No TypoScriptFontendController found in div2007 TranslationBase.', 1710013027);
    }

    /**
     * Check if we have a site object in the current request. if null, this usually means that
     * this class was called from CLI context.
     */
    protected function getCurrentSite(): ?SiteInterface
    {
        if ($this->typoScriptFrontendController instanceof TypoScriptFrontendController) {
            return $this->typoScriptFrontendController->getSite();
        }
        if (isset($this->request) && $this->request instanceof ServerRequestInterface) {
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
        if (!isset($request)) {
            $request = $GLOBALS['TYPO3_REQUEST'];
        }
        if ($this->typoScriptFrontendController instanceof TypoScriptFrontendController) {
            return $this->typoScriptFrontendController->getLanguage();
        }
        if (isset($request) && $request instanceof ServerRequestInterface) {
            return $request->getAttribute('language', null);
        }
        return null;
    }

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'] ?? $this->languageServiceFactory->create('default');
    }

}
