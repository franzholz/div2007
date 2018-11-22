<?php
namespace JambageCom\Div2007\Captcha;

/*
 *  Copyright notice
 *
 *  (c) 2012-2017 Stanislas Rolland <typo3(arobas)sjbr.ca>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 */

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
* Determines the use of captcha
*/
class CaptchaManager
{
    /**
    * Determines captcha object for the specified qualifier name
    *
    * @param string $extensionKey: the key of the requesting extension
    * @param string $name: qualifier name of the captcha
    * @return object, if the use of captcha is enabled, null otherwise
    */
    static public function getCaptcha ($extensionKey, $name)
    {
        $result = null;
        $captchaArray = array(
            \JambageCom\Div2007\Captcha\Captcha::class,
            \JambageCom\Div2007\Captcha\Freecap::class
        );
        if (
            isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extensionKey]['captcha']) &&
            is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extensionKey]['captcha'])
        ) {
            $captchaArray = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extensionKey]['captcha'];
        }

        if (
            $name != ''
        ) {
            foreach ($captchaArray as $classRef) {
                $captchaObj = GeneralUtility::makeInstance($classRef);
                if (
                    is_object($captchaObj) &&
                    $name == $captchaObj->getName()
                ) {
                    $result = $captchaObj;
                    break;
                }
            }
        }
        return $result;
    }


    /**
    * Determines whether at least one captcha extension is available
    *
    * @return boolean true if at least one captcha extension is available
    */
    static public function isLoaded ($extensionKey)
    {
        $isLoaded = false;
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extensionKey]['captcha'])) {
            foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extensionKey]['captcha'] as $classRef) {
                $captchaObj = GeneralUtility::makeInstance($classRef);
                if (is_object($captchaObj)) {
                    $isLoaded = $captchaObj->isLoaded();
                    if ($isLoaded) {
                        break;
                    }
                }
            }
        }
        return $isLoaded;
    }
}

