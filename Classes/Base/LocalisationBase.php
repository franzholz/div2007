<?php

namespace JambageCom\Div2007\Base;

/***************************************************************
*  Copyright notice
*
*  (c) 2017 Franz Holzinger (franz@ttproducts.de)
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
* Base class for the language object of your extension.
*
* @author  Kasper Skaarhoj <kasperYYYY@typo3.com>
* @maintainer	Franz Holzinger <franz@ttproducts.de>
* @package TYPO3
* @subpackage div2007
*
*/


class LocalisationBase {
    public $cObj;
    public $LOCAL_LANG = array();   // Local Language content
    public $LOCAL_LANG_charset = array();   // Local Language content charset for individual labels (overriding)
    public $LOCAL_LANG_loaded = 0;  // Flag that tells if the locallang file has been fetch (or tried to be fetched) already.
    public $LLkey = 'default';      // Pointer to the language to use.
    public $altLLkey = '';          // Pointer to alternative fall-back language to use.
    public $LLtestPrefix = '';      // You can set this during development to some value that makes it easy for you to spot all labels that ARe delivered by the getLL function.
    public $LLtestPrefixAlt = '';   // Save as LLtestPrefix, but additional prefix for the alternative value in getLL() function calls
    public $scriptRelPath;          // Path to the plugin class script relative to extension directory, eg. 'pi1/class.tx_newfaq_pi1.php'
    public $extKey;                 // Extension key.
    /**
    * Should normally be set in the main function with the TypoScript content passed to the method.
    *
    * $conf[LOCAL_LANG][_key_] is reserved for Local Language overrides.
    * $conf[userFunc] / $conf[includeLibs]  reserved for setting up the USER / USER_INT object. See TSref
    */
    public $conf = array();
    public $typoVersion;
    private $hasBeenInitialized = false;


    public function init ($cObj, $extKey, $conf, $scriptRelPath) {

        if (
            isset($GLOBALS['TSFE']->config['config']) &&
            isset($GLOBALS['TSFE']->config['config']['language'])
        ) {
            $this->LLkey = $GLOBALS['TSFE']->config['config']['language'];
            if ($GLOBALS['TSFE']->config['config']['language_alt']) {
                $this->altLLkey = $GLOBALS['TSFE']->config['config']['language_alt'];
            }
        }

        $this->cObj = $cObj;
        $this->extKey = $extKey;
        $this->conf = $conf;
        $this->scriptRelPath = $scriptRelPath;

        $this->typoVersion = \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version);

        $this->hasBeenInitialized = true;
    }

    public function setLocallang (&$locallang) {
        $this->LOCAL_LANG = &$locallang;
    }

    public function getLocallang () {
        return $this->LOCAL_LANG;
    }

    public function setLocallangCharset (&$locallang) {
        $this->LOCAL_LANG_charset = &$locallang;
    }

    public function getLocallangCharset () {
        return $this->LOCAL_LANG_charset;
    }

    public function setLocallangLoaded ($loaded = true) {
        $this->LOCAL_LANG_loaded = $loaded;
    }

    public function getLocallangLoaded () {
        return $this->LOCAL_LANG_loaded;
    }

    public function getLLkey () {
        return $this->LLkey;
    }

    public function getCObj () {
        return $this->cObj;
    }

    public function getExtKey () {
        return $this->extKey;
    }

    public function setConf ($conf) {
        $this->conf = $conf;
    }

    public function getConf () {
        return $this->conf;
    }

    public function getTypoVersion () {
        return $this->typoVersion;
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
     * Notice that for debugging purposes prefixes for the output values can be set with the internal vars ->LLtestPrefixAlt and ->LLtestPrefix
     *
     * @param   string      The key from the LOCAL_LANG array for which to return the value.
     * @param   string      input: if set then this language is used if possible. output: the used language
     * @param   string      Alternative string to return IF no value is found set for the key, neither for the local language nor the default.
     * @param   boolean     If true, the output label is passed through htmlspecialchars()
     * @return  string      The value from LOCAL_LANG. false in error case
     */
    public function getLL (
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
            $this->LLkey != '' &&
            is_array($this->LOCAL_LANG[$this->LLkey][$key][0]) &&
            $this->LOCAL_LANG[$this->LLkey][$key][0]['target'] != ''
        ) {
            $usedLang = $this->LLkey;

                // The "from" charset of csConv() is only set for strings from TypoScript via _LOCAL_LANG
            if ($this->LOCAL_LANG_charset[$usedLang][$key] != '') {
                $word = $GLOBALS['TSFE']->csConv(
                    $this->LOCAL_LANG[$usedLang][$key][0]['target'],
                    $this->LOCAL_LANG_charset[$usedLang][$key]
                );
            } else {
                $word = $this->LOCAL_LANG[$this->LLkey][$key][0]['target'];
            }
        } elseif (
            $this->altLLkey &&
            is_array($this->LOCAL_LANG[$this->altLLkey][$key][0]) &&
            $this->LOCAL_LANG[$this->altLLkey][$key][0]['target'] != ''
        ) {
            $usedLang = $this->altLLkey;

                // The "from" charset of csConv() is only set for strings from TypoScript via _LOCAL_LANG
            if (isset($this->LOCAL_LANG_charset[$usedLang][$key])) {
                $word = $GLOBALS['TSFE']->csConv(
                    $this->LOCAL_LANG[$usedLang][$key][0]['target'],
                    $this->LOCAL_LANG_charset[$usedLang][$key]
                );
            } else {
                $word = $this->LOCAL_LANG[$this->altLLkey][$key][0]['target'];
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
            $word = (isset($this->LLtestPrefixAlt)) ? $this->LLtestPrefixAlt . $alternativeLabel : $alternativeLabel;
        }

        $output = (isset($this->LLtestPrefix)) ? $this->LLtestPrefix . $word : $word;

        if ($hsc) {
            $output = htmlspecialchars($output);
        }

        return $output;
    }
}

