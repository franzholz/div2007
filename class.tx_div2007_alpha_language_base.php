<?php
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
 * Deprecated
 *
 * Part of the div2007 (Collection of static functions) extension.
 *
 * interface for the language object
 * You can use a pibase object for it.
 *
 * @author  Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @maintainer	Franz Holzinger <franz@ttproducts.de>
 * @package TYPO3
 * @subpackage div2007
 *
 */



class tx_div2007_alpha_language_base {
	public $cObj;
	public $LOCAL_LANG = array();		// Local Language content
	public $LOCAL_LANG_charset = array();	// Local Language content charset for individual labels (overriding)
	public $LOCAL_LANG_loaded = 0;		// Flag that tells if the locallang file has been fetch (or tried to be fetched) already.
	public $LLkey = 'default';		// Pointer to the language to use.
	public $altLLkey = '';			// Pointer to alternative fall-back language to use.
	public $LLtestPrefix = '';		// You can set this during development to some value that makes it easy for you to spot all labels that ARe delivered by the getLL function.
	public $LLtestPrefixAlt = '';		// Save as LLtestPrefix, but additional prefix for the alternative value in getLL() function calls
	public $scriptRelPath;	// Path to the plugin class script relative to extension directory, eg. 'pi1/class.tx_newfaq_pi1.php'
	public $extKey;		// Extension key.
	/**
	 * Should normally be set in the main function with the TypoScript content passed to the method.
	 *
	 * $conf[LOCAL_LANG][_key_] is reserved for Local Language overrides.
	 * $conf[userFunc] / $conf[includeLibs]  reserved for setting up the USER / USER_INT object. See TSref
	 */
	public $conf = array();
	public $typoVersion;
	private $hasBeenInitialized = FALSE;


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

		$this->typoVersion = tx_div2007_core::getTypoVersion();
		$this->hasBeenInitialized = TRUE;
	}

	public function getLocallang () {
		return $this->LOCAL_LANG;
	}

	public function getLLkey () {
		return $this->LLkey;
	}

	public function getCObj () {
		return $this->cObj;
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

}


if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/div2007/class.tx_div2007_alpha_language_base.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/div2007/class.tx_div2007_alpha_language_base.php']);
}

