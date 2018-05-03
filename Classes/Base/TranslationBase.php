<?php

namespace JambageCom\Div2007\Base;

/***************************************************************
*  Copyright notice
*
*  (c) 2018 Franz Holzinger (franz@ttproducts.de)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License or
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
* Part of the div2007 (Collection of static functions) extension.
*
* Base class for the language translation object of your extension.
*
* @author  Kasper Skaarhoj <kasperYYYY@typo3.com>
* @maintainer	Franz Holzinger <franz@ttproducts.de>
* @package TYPO3
* @subpackage div2007
*
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
    public $scriptRelPath;          // Path to the plugin class script relative to extension directory, eg. 'pi1/class.tx_newfaq_pi1.php'
    public $extensionKey = '';	// extension key must be overridden
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
        $confLocalLang = array(), // only the $conf['_LOCAL_LANG.'] part of the setup
        $scriptRelPath = '',
        $lookupFilename = '',
        $useDiv2007Language = true
    ) {
        if (
            isset($GLOBALS['TSFE']->config['config']) &&
            isset($GLOBALS['TSFE']->config['config']['language'])
        ) {
            $this->LocalLangKey = $GLOBALS['TSFE']->config['config']['language'];
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
     * Attention: only for TYPO3 versions above 4.6
     * Returns the localized label of the LOCAL_LANG key, $key used since TYPO3 4.6
     * Notice that for debugging purposes prefixes for the output values can be set with the internal vars ->localLangTestPrefixAlt and ->localLangTestPrefix
     *
     * @param   string      The key from the LOCAL_LANG array for which to return the value.
     * @param   string      input: if set then this language is used if possible. output: the used language
     * @param   string      Alternative string to return IF no value is found set for the key, neither for the local language nor the default.
     * @param   boolean     If true, the output label is passed through htmlspecialchars()
     * @return  string      The value from LOCAL_LANG. false in error case
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
            $this->LOCAL_LANG[$usedLang][$key][0]['target'] != ''
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
            $this->LOCAL_LANG[$this->getLocalLangKey()][$key][0]['target'] != ''
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
            $this->LOCAL_LANG[$this->altLocalLangKey][$key][0]['target'] != ''
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
            $this->LOCAL_LANG['default'][$key][0]['target'] != ''
        ) {
            $usedLang = 'default';
                // Get default translation (without charset conversion, english)
            $word = $this->LOCAL_LANG[$usedLang][$key][0]['target'];
        } else {
                // Return alternative string or empty
            $word = (isset($this->localLangTestPrefixAlt)) ? $this->localLangTestPrefixAlt . $alternativeLabel : $alternativeLabel;
        }

        $output = (isset($this->localLangTestPrefix)) ? $this->localLangTestPrefix . $word : $word;

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
     * @param   boolean     If true, then former language items can be overwritten from the new file
     * @return  boolean
     */
    public function loadLocalLang (
        $langFileParam = '',
        $overwrite = true
    ) {
        $langFile = ($langFileParam ? $langFileParam : 'locallang.xml');
        $extensionKey = $this->getExtensionKey();

        if (
            substr($langFile, 0, 4) === 'EXT:' ||
            substr($langFile, 0, 5) === 'typo3' ||
            substr($langFile, 0, 9) === 'fileadmin'
        ) {
            $basePath = $langFile;
        } else if ($extensionKey != '') {
            $basePath = ExtensionManagementUtility::extPath($extensionKey) .
                ($this->scriptRelPath ? dirname($this->scriptRelPath) . '/' : '') . $langFile;
        } else {
            return false;
        }

        if (version_compare(TYPO3_version, '7.4.0', '>')) {
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
                    if (!$charset) {
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
}

