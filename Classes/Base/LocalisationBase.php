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

use TYPO3\CMS\Core\Charset\CharsetConverter;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

// DEPRECATED: Use the class TranslationBase instead

class LocalisationBase
{
    public $cObj; // DEPRECATED
    public $LOCAL_LANG = [];   // Local Language content
    public $LOCAL_LANG_charset = [];   // Local Language content charset for individual labels (overriding)
    public $LOCAL_LANG_loaded = 0;  // Flag that tells if the locallang file has been fetch (or tried to be fetched) already.
    public $LLkey = 'default';      // Pointer to the language to use.
    public $altLLkey = '';          // Pointer to alternative fall-back language to use.
    public $LLtestPrefix = '';      // You can set this during development to some value that makes it easy for you to spot all labels that ARe delivered by the getLL function.
    public $LLtestPrefixAlt = '';   // Save as LLtestPrefix, but additional prefix for the alternative value in getLL() function calls
    public $scriptRelPath;          // Path to the plugin class script relative to extension directory, eg. 'pi1/class.tx_newfaq_pi1.php'
    public $extensionKey = '';	// extension key must be overridden
    public $extKey;             // DEPRECATED
    protected $lookupFilename = ''; // filename used for the lookup method
    /**
     * Should normally be set in the main function with the TypoScript content passed to the method.
     *
     * $conf[LOCAL_LANG][_key_] is reserved for Local Language overrides.
     * $conf[userFunc] / $conf[includeLibs]  reserved for setting up the USER / USER_INT object. See TSref
     */
    public $conf = [];
    public $typoVersion; // DEPRECATED
    private $hasBeenInitialized = false;

    public function init(
        $cObj, // DEPRECATED
        $extensionKey,
        $conf,
        $scriptRelPath,
        $lookupFilename = '',
        $useDiv2007Language = true
    ) {
        if (
            isset($GLOBALS['TSFE']->config['config']) &&
            isset($GLOBALS['TSFE']->config['config']['language'])
        ) {
            $this->LLkey = $GLOBALS['TSFE']->config['config']['language'];
            if ($GLOBALS['TSFE']->config['config']['language_alt']) {
                $this->altLLkey = $GLOBALS['TSFE']->config['config']['language_alt'];
            }
        }

        if (is_object($cObj)) {
            $this->cObj = $cObj;
        }
        $this->extensionKey = $extensionKey;
        $this->extKey = $extensionKey; // DEPRECATED
        $internalConf = $GLOBALS['TSFE']->tmpl->setup['lib.'][DIV2007_EXT . '.'];
        if (
            isset($internalConf) &&
            is_array($internalConf) &&
            is_array($conf)
        ) {
            $conf = array_merge_recursive($internalConf, $conf);
        }

        $this->setConf($conf);
        $this->scriptRelPath = $scriptRelPath;
        $this->lookupFilename = $lookupFilename;

        $this->typoVersion = \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version);

        $this->hasBeenInitialized = true;
        if ($useDiv2007Language) {
            $this->loadLL(
                'EXT:' . DIV2007_EXT . DIV2007_LANGUAGE_SUBPATH . 'locallang.xlf'
            );
        }
    }

    public function setLocallang(array &$locallang)
    {
        $this->LOCAL_LANG = &$locallang;
    }

    public function getLocallang()
    {
        return $this->LOCAL_LANG;
    }

    public function setLocallangCharset(&$locallang)
    {
        $this->LOCAL_LANG_charset = &$locallang;
    }

    public function getLocallangCharset()
    {
        return $this->LOCAL_LANG_charset;
    }

    public function setLocallangLoaded($loaded = true)
    {
        $this->LOCAL_LANG_loaded = $loaded;
    }

    public function getLocallangLoaded()
    {
        return $this->LOCAL_LANG_loaded;
    }

    public function setLLkey($llKey)
    {
        $this->LLkey = $llKey;
    }

    public function getLLkey()
    {
        return $this->LLkey;
    }

    public function getCObj() // DEPRECATED
    {return $this->cObj;
    }

    public function getExtensionKey()
    {
        return $this->extensionKey;
    }

    public function getExtKey() // DEPRECATED
    {return $this->extKey;
    }

    public function setConf($conf) // DEPRECATED
    {$this->conf = $conf;
    }

    public function getConf() // DEPRECATED
    {return $this->conf;
    }

    public function getTypoVersion() // DEPRECATED
    {return $this->typoVersion;
    }

    public function setLookupFilename($lookupFilename)
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

    public function getLanguage()
    {
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
     * Attention: only for TYPO3 versions above 4.6
     * Returns the localized label of the LOCAL_LANG key, $key used since TYPO3 4.6
     * Notice that for debugging purposes prefixes for the output values can be set with the internal vars ->LLtestPrefixAlt and ->LLtestPrefix.
     *
     * @param   string      the key from the LOCAL_LANG array for which to return the value
     * @param   string      input: if set then this language is used if possible. output: the used language
     * @param   string      alternative string to return IF no value is found set for the key, neither for the local language nor the default
     * @param   bool     If true, the output label is passed through htmlspecialchars()
     *
     * @return  string      The value from LOCAL_LANG. false in error case
     */
    public function getLL(
        $key,
        &$usedLang = '',
        $alternativeLabel = '',
        $hsc = false
    ) {
        $output = false;
        /** @var CharsetConverter $charsetConverter */
        $charsetConverter = GeneralUtility::makeInstance(CharsetConverter::class);

        if (
            $usedLang != '' &&
            isset($this->LOCAL_LANG[$usedLang][$key][0]) &&
            is_array($this->LOCAL_LANG[$usedLang][$key][0]) &&
            $this->LOCAL_LANG[$usedLang][$key][0]['target'] != ''
        ) {
            // The "from" charset of csConv() is only set for strings from TypoScript via _LOCAL_LANG
            if ($this->LOCAL_LANG_charset[$usedLang][$key] != '') {
                try {
                    $word =
                        $charsetConverter->conv(
                            $this->LOCAL_LANG[$usedLang][$key][0]['target'],
                            $this->LOCAL_LANG_charset[$usedLang][$key],
                            'utf-8'
                        );
                } catch (UnknownCharsetException $e) {
                    throw new \RuntimeException('Invalid charset "' . $this->LOCAL_LANG_charset[$usedLang][$key] . '"  for language "' . $usedLang . '" ' . $e->getMessage(), 1652365326);
                }
            } else {
                $word = $this->LOCAL_LANG[$usedLang][$key][0]['target'];
            }
        } elseif (
            $this->getLLkey() != '' &&
            isset($this->LOCAL_LANG[$this->getLLkey()][$key][0]) &&
            is_array($this->LOCAL_LANG[$this->getLLkey()][$key][0]) &&
            $this->LOCAL_LANG[$this->getLLkey()][$key][0]['target'] != ''
        ) {
            $usedLang = $this->getLLkey();

            // The "from" charset of csConv() is only set for strings from TypoScript via _LOCAL_LANG
            if ($this->LOCAL_LANG_charset[$usedLang][$key] != '') {
                try {
                    $word =
                        $charsetConverter->conv(
                            $this->LOCAL_LANG[$usedLang][$key][0]['target'],
                            $this->LOCAL_LANG_charset[$usedLang][$key],
                            'utf-8'
                        );
                } catch (UnknownCharsetException $e) {
                    throw new \RuntimeException('Invalid charset "' . $this->LOCAL_LANG_charset[$usedLang][$key] . '"  for language "' . $usedLang . '" ' . $e->getMessage(), 1652365370);
                }
            } else {
                $word = $this->LOCAL_LANG[$this->getLLkey()][$key][0]['target'];
            }
        } elseif (
            $this->altLLkey &&
            isset($this->LOCAL_LANG[$this->altLLkey][$key][0]) &&
            is_array($this->LOCAL_LANG[$this->altLLkey][$key][0]) &&
            $this->LOCAL_LANG[$this->altLLkey][$key][0]['target'] != ''
        ) {
            $usedLang = $this->altLLkey;

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
                    throw new \RuntimeException('Invalid charset "' . $this->LOCAL_LANG_charset[$usedLang][$key] . '"  for language "' . $usedLang . '" ' . $e->getMessage(), 1652365408);
                }
            } else {
                $word = $this->LOCAL_LANG[$this->altLLkey][$key][0]['target'];
            }
        } elseif (
            isset($this->LOCAL_LANG['default'][$key][0]) &&
            is_array($this->LOCAL_LANG['default'][$key][0]) &&
            $this->LOCAL_LANG['default'][$key][0]['target'] != ''
        ) {
            $usedLang = 'default';
            // Get default translation (without charset conversion, english)
            $word = $this->LOCAL_LANG[$usedLang][$key][0]['target'];
        } else {
            // Return alternative string or empty
            $word = (isset($this->LLtestPrefixAlt)) ? $this->LLtestPrefixAlt . $alternativeLabel : $alternativeLabel;
        }

        $output = (isset($this->LLtestPrefix)) ? $this->LLtestPrefix . $word : $word;

        if ($hsc) {
            $output = htmlspecialchars($output);
        }

        return $output;
    }

    /**
     * used since TYPO3 4.6
     * Loads local-language values by looking for a "locallang.php" file in the plugin class directory ($langObj->scriptRelPath) and if found includes it.
     * Also locallang values set in the TypoScript property "_LOCAL_LANG" are merged onto the values found in the "locallang.xml" file.
     *
     * @param   string      language file to load
     * @param   bool     If true, then former language items can be overwritten from the new file
     *
     * @return  bool
     */
    public function loadLL(
        $langFileParam = '',
        $overwrite = true
    ) {
        $langFile = ($langFileParam ? $langFileParam : 'locallang.xml');

        if (
            str_starts_with($langFile, 'EXT:') ||
            str_starts_with($langFile, 'typo3') ||
            str_starts_with($langFile, 'fileadmin')
        ) {
            $basePath = $langFile;
        } else {
            $basePath = ExtensionManagementUtility::extPath($this->getExtensionKey()) .
                ($this->scriptRelPath ? dirname($this->scriptRelPath) . '/' : '') . $langFile;
        }

        $callingClassName = \TYPO3\CMS\Core\Localization\LocalizationFactory::class;

        /** @var $languageFactory \TYPO3\CMS\Core\Localization\LocalizationFactory */
        $languageFactory = GeneralUtility::makeInstance($callingClassName);
        $tempLOCAL_LANG = $languageFactory->getParsedData(
            $basePath,
            $this->getLLkey(),
            'UTF-8'
        );

        if (count($this->LOCAL_LANG) && is_array($tempLOCAL_LANG)) {
            foreach ($this->LOCAL_LANG as $langKey => $tempArray) {
                if (
                    isset($tempLOCAL_LANG[$langKey]) &&
                    is_array($tempLOCAL_LANG[$langKey])
                ) {
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

        if ($this->altLLkey) {
            $callingClassName = \TYPO3\CMS\Core\Localization\LocalizationFactory::class;

            /** @var $languageFactory \TYPO3\CMS\Core\Localization\LocalizationFactory */
            $languageFactory = GeneralUtility::makeInstance($callingClassName);
            $tempLOCAL_LANG = $languageFactory->getParsedData(
                $basePath,
                $this->altLLkey,
                'UTF-8'
            );

            if (count($this->LOCAL_LANG) && is_array($tempLOCAL_LANG)) {
                foreach ($this->LOCAL_LANG as $langKey => $tempArray) {
                    if (
                        isset($tempLOCAL_LANG[$langKey]) &&
                        is_array($tempLOCAL_LANG[$langKey])
                    ) {
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
        $conf = $this->getConf();
        $confLL = '';
        if (isset($conf['_LOCAL_LANG.'])) {
            $confLL = $conf['_LOCAL_LANG.'];
        }

        if (is_array($confLL)) {
            foreach ($confLL as $languageKey => $languageArray) {
                if (is_array($languageArray)) {
                    if (!isset($this->LOCAL_LANG[$languageKey])) {
                        $this->LOCAL_LANG[$languageKey] = [];
                    }
                    $languageKey = substr($languageKey, 0, -1);
                    $charset = $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'];

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

    public function translate($key, $extensionKey = '', $filename = '')
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
     * @param	string		Key string. Accepts the "LLL:" prefix.
     *
     * @return	string		label value, if any
     */
    public static function sL($input)
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
}
