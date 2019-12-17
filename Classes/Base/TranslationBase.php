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


use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;


class TranslationBase {
    public $LOCAL_LANG = array();   // Local Language content
    public $LOCAL_LANG_charset = array();   // Local Language content charset for individual labels (overriding)
    public $LOCAL_LANG_loaded = 0;  // Flag that tells if the locallang file has been fetch (or tried to be fetched) already.
    public $LocalLangKey = 'default';      // Pointer to the language to use.
    public $altLocalLangKey = '';          // Pointer to alternative fall-back language to use.
    public $localLangTestPrefix = '';      // You can set this during development to some value that makes it easy for you to spot all labels that ARe delivered by the getLocalLang function.
    public $localLangTestPrefixAlt = '';   // Save as localLangTestPrefix, but additional prefix for the alternative value in getLocalLang() function calls
    public $scriptRelPath;          // relative path to the extension directory where the locallang XLF / XML files are stored. The leading and trailing slashes must be included. E.g. '/Resources/Private/Language/'
    protected $extensionKey = '';	// extension key must be overridden
    protected $lookupFilename = ''; // filename used for the lookup method

    /**
    * Should normally be set in the main function with the TypoScript content passed to the method.
    *
    * $conf[LOCAL_LANG][_key_] is reserved for Local Language overrides.
    * $conf[userFunc] / $conf[includeLibs]  reserved for setting up the USER / USER_INT object. See TSref
    */
    protected $confLocalLang = array();
    private $hasBeenInitialized = false;


    public function init (
        $extensionKey = '',
        $confLocalLang = array(), // you must pass only the $conf['_LOCAL_LANG.'] part of the setup of the caller
        $scriptRelPath = '',
        $lookupFilename = '',
        $useDiv2007Language = true
    ) {
        if (
            isset($GLOBALS['TSFE']->config['config']) &&
            isset($GLOBALS['TSFE']->config['config']['language'])
        ) {
            $this->setLocalLangKey($GLOBALS['TSFE']->config['config']['language']);
            if ($GLOBALS['TSFE']->config['config']['language_alt']) {
                $this->altLocalLangKey = $GLOBALS['TSFE']->config['config']['language_alt'];
            }
        }

        if ($extensionKey != '') {
            $this->extensionKey = $extensionKey;
        }
        $conf = $GLOBALS['TSFE']->tmpl->setup['lib.'][DIV2007_EXT . '.'];
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
        $this->scriptRelPath = $scriptRelPath;
        $this->lookupFilename = $lookupFilename;

        $this->hasBeenInitialized = true;
        if ($useDiv2007Language) {
            $this->loadLocalLang(
                'EXT:' . DIV2007_EXT . DIV2007_LANGUAGE_SUBPATH . 'locallang.xlf'
            );
        }
    }

    public function setLocalLang (array $locallang) {
        $this->LOCAL_LANG = $locallang;
    }

    /* former getLocallang */
    public function getLocalLang () {
        return $this->LOCAL_LANG;
    }

    public function setLocalLangCharset ($locallang) {
        $this->LOCAL_LANG_charset = $locallang;
    }

    public function getLocalLangCharset () {
        return $this->LOCAL_LANG_charset;
    }

    public function setLocalLangLoaded ($loaded = true) {
        $this->LOCAL_LANG_loaded = $loaded;
    }

    public function getLocalLangLoaded () {
        return $this->LOCAL_LANG_loaded;
    }

    public function setLocalLangKey ($localLangKey) {
        $this->LocalLangKey = $localLangKey;
    }

    /* former getLLkey */
    public function getLocalLangKey () {
        return $this->LocalLangKey;
    }

    public function getExtensionKey () {
        return $this->extensionKey;
    }

    public function setConfLocalLang ($conf) {
        $this->confLocalLang = $conf;
    }

    public function getConfLocalLang () {
        return $this->confLocalLang;
    }

    public function setLookupFilename ($lookupFilename) {
        $this->lookupFilename = $lookupFilename;
    }

    public function getLookupFilename () {
        return $this->lookupFilename;
    }
    
    public function needsInit () {
        return !$this->hasBeenInitialized;
    }

    public function getLanguage () {

        $result = 'default';

        if (
            isset($GLOBALS['TSFE']->config) &&
            is_array($GLOBALS['TSFE']->config) &&
            isset($GLOBALS['TSFE']->config['config']) &&
            is_array($GLOBALS['TSFE']->config['config'])
        ) {
            $result = $GLOBALS['TSFE']->config['config']['language'];
        }

        return $result;
    }

    /**
     * This method has been used under TYPO3 versions above 4.6 as getLL
     * Returns the localized label of the LOCAL_LANG key, $key used since TYPO3 4.6
     * Notice that for debugging purposes prefixes for the output values can be set with the internal vars ->localLangTestPrefixAlt and ->localLangTestPrefix
     * 
     * former getLL method
     *
     * @param   string      The key from the LOCAL_LANG array for which to return the value.
     * @param   string      input: if set then this language is used if possible. output: the used language
     * @param   string      Alternative string to return IF no value is found set for the key, neither for the local language nor the default.
     * @param   boolean     If true, the output label is passed through htmlspecialchars()
     * @return  string / boolean The prefixed value from LOCAL_LANG. false, if no entry could be found.
     */
    public function getLabel (
        $key,
        &$usedLang = '',
        $alternativeLabel = '',
        $hsc = false
    ) {
        $output = false;

        if (
            $usedLang != '' &&
            is_array($this->LOCAL_LANG[$usedLang][$key][0]) &&
            isset($this->LOCAL_LANG[$usedLang][$key][0]['target']) &&
            (
                $this->LOCAL_LANG[$usedLang][$key][0]['target'] != '' ||
                !isset($this->LOCAL_LANG[$usedLang][$key][0]['source']) // neu FHO
            )
        ) {
                // The "from" charset of csConv() is only set for strings from TypoScript via _LOCAL_LANG
            if ($this->LOCAL_LANG_charset[$usedLang][$key] != '') {
                $word = $GLOBALS['TSFE']->csConv(
                    $this->LOCAL_LANG[$usedLang][$key][0]['target'],
                    $this->LOCAL_LANG_charset[$usedLang][$key]
                );
            } else {
                $word = $this->LOCAL_LANG[$usedLang][$key][0]['target'];
            }
        } else if (
            $this->getLocalLangKey() != '' &&
            is_array($this->LOCAL_LANG[$this->getLocalLangKey()][$key][0]) &&
            isset($this->LOCAL_LANG[$this->getLocalLangKey()][$key][0]['target']) &&
            (
                $this->LOCAL_LANG[$this->getLocalLangKey()][$key][0]['target'] != '' ||
                !isset($this->LOCAL_LANG[$this->getLocalLangKey()][$key][0]['source']) // neu FHO
            )
        ) {
            $usedLang = $this->getLocalLangKey();

                // The "from" charset of csConv() is only set for strings from TypoScript via _LOCAL_LANG
            if ($this->LOCAL_LANG_charset[$usedLang][$key] != '') {
                $word = $GLOBALS['TSFE']->csConv(
                    $this->LOCAL_LANG[$usedLang][$key][0]['target'],
                    $this->LOCAL_LANG_charset[$usedLang][$key]
                );
            } else {
                $word = $this->LOCAL_LANG[$this->getLocalLangKey()][$key][0]['target'];
            }
        } elseif (
            $this->altLocalLangKey &&
            is_array($this->LOCAL_LANG[$this->altLocalLangKey][$key][0]) &&
            (
                $this->LOCAL_LANG[$this->altLocalLangKey][$key][0]['target'] != '' ||
                !isset($this->LOCAL_LANG[$this->altLocalLangKey][$key][0]['source']) // neu FHO
            )
        ) {
            $usedLang = $this->altLocalLangKey;

                // The "from" charset of csConv() is only set for strings from TypoScript via _LOCAL_LANG
            if (isset($this->LOCAL_LANG_charset[$usedLang][$key])) {
                $word = $GLOBALS['TSFE']->csConv(
                    $this->LOCAL_LANG[$usedLang][$key][0]['target'],
                    $this->LOCAL_LANG_charset[$usedLang][$key]
                );
            } else {
                $word = $this->LOCAL_LANG[$this->altLocalLangKey][$key][0]['target'];
            }
        } elseif (
            is_array($this->LOCAL_LANG['default'][$key][0]) &&
            (
                $this->LOCAL_LANG['default'][$key][0]['target'] != '' ||
                !isset($this->LOCAL_LANG['default'][$key][0]['source']) // neu FHO
            )
        ) {
            $usedLang = 'default';
                // Get default translation (without charset conversion, english)
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
     * Loads local-language values by looking for a "locallang.php" file in the plugin class directory ($langObj->scriptRelPath) and if found includes it.
     * Also locallang values set in the TypoScript property "_LOCAL_LANG" are merged onto the values found in the "locallang.xml" file.
     *
     * former method loadLL
     *
     * @param   string      language file to load
     * @param   boolean     If true, then former language items can be overwritten from the new file
     * @return  boolean
     */
    public function loadLocalLang (
        $langFileParam = '',
        $overwrite = true
    ) {
        $langFile = ($langFileParam ? $langFileParam : $this->getLookupFilename());
        $extensionKey = $this->getExtensionKey();

        if (
            substr($langFile, 0, 4) === 'EXT:' ||
            substr($langFile, 0, 5) === 'typo3' ||
            substr($langFile, 0, 9) === 'fileadmin'
        ) {
            $basePath = $langFile;
        } else if ($extensionKey != '') {
            $basePath = ExtensionManagementUtility::extPath($extensionKey);
            if ($this->scriptRelPath != '') {
                if (strpos($this->scriptRelPath, '.php')) {
                    $basePath .= dirname($this->scriptRelPath) . '/';
                } else {
                    $basePath .= $this->scriptRelPath;
                }

                if (substr($basePath, -1) != '/') {
                    $basePath .= '/';
                }
            }
            $basePath .= $langFile;
        } else {
            return false;
        }
        $ext = pathinfo($basePath, PATHINFO_EXTENSION);

        if (
            version_compare(TYPO3_version, '7.4.0', '>=')
        ) {
            $callingClassName = '\\TYPO3\\CMS\\Core\\Localization\\LocalizationFactory';
            $useClassName = substr($callingClassName, 1);

            /** @var $languageFactory \TYPO3\CMS\Core\Localization\LocalizationFactory */
            $languageFactory = GeneralUtility::makeInstance($useClassName);
            $tempLOCAL_LANG = $languageFactory->getParsedData(
                $basePath,
                $this->getLocalLangKey(),
                'UTF-8'
            );
        } else {
                // Read the strings in the required charset (since TYPO3 4.2)
            $tempLOCAL_LANG =
                GeneralUtility::readLLfile(
                    $basePath,
                    $this->getLocalLangKey(),
                    $GLOBALS['TSFE']->renderCharset
                );
        }

        if (count($this->LOCAL_LANG) && is_array($tempLOCAL_LANG)) {
            foreach ($this->LOCAL_LANG as $langKey => $tempArray) {
                if (is_array($tempLOCAL_LANG[$langKey])) {

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
            $tempLOCAL_LANG =
                GeneralUtility::readLLfile(
                    $basePath,
                    $this->altLocalLangKey,
                    $charset
                );

            if (count($this->LOCAL_LANG) && is_array($tempLOCAL_LANG)) {
                foreach ($this->LOCAL_LANG as $langKey => $tempArray) {
                    if (is_array($tempLOCAL_LANG[$langKey])) {
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
                        $this->LOCAL_LANG[$languageKey] = array();
                    }
                    $languageKey = substr($languageKey, 0, -1);
                    $charset = $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'];

                    // For labels coming from the TypoScript (database) the charset is assumed to be "forceCharset"
                    // and if that is not set, assumed to be that of the individual system languages
                    if (!$charset && version_compare(TYPO3_version, '8.5.0', '<')) {
                        $charset = $GLOBALS['TSFE']->csConvObj->charSetArray[$languageKey];
                    }

                        // Remove the dot after the language key
                    foreach ($languageArray as $labelKey => $labelValue) {
                        if (!isset($this->LOCAL_LANG[$languageKey][$labelKey])) {
                            $this->LOCAL_LANG[$languageKey][$labelKey] = array();
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
    public function translate ($key, $extensionKey = '', $filename = '')
    {
        if ($filename == '') {
            $filename = $this->getLookupFilename();
        }
        if ($extensionKey == '') {
            $extensionKey = $this->getExtensionKey();
        }
        $result = $GLOBALS['TSFE']->sL('LLL:EXT:' . $extensionKey . $filename . ':' . $key);    
        return $result;
    }

    /**
    * Split Label function for front-end applications.
    *
    * former method sL 
    *
    * @param	string		Key string. Accepts the "LLL:" prefix.
    * @return	string		Label value, if any.
    */
    static public function splitLabel ($input) {
        $restStr = trim(substr($input, 4));
        $extPrfx = '';
        if (!strcmp(substr($restStr, 0, 4), 'EXT:')) {
            $restStr = trim(substr($restStr, 4));
            $extPrfx = 'EXT:';
        }
        $parts = explode(':', $restStr);
        return ($parts[1]);
    }
}

