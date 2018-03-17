<?php
namespace JambageCom\Div2007\Captcha;

/*
 *  Copyright notice
 *
 *  (c) 2009 Sonja Scholz <ss@cabag.ch>
 *  (c) 2017 Stanislas Rolland <typo3(arobas)sjbr.ca>
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

use JambageCom\Div2007\Captcha\CaptchaBase;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Hook for captcha image marker when extension 'captcha' is used
 */
class Captcha extends CaptchaBase
{
    protected $extensionKey = 'captcha';
    protected $name = 'captcha';
    protected $sessionName = 'tx_captcha_string';

    /**
    * Sets the value of captcha markers
    * return boolean
    */
    public function addGlobalMarkers (array &$markerArray, $enable = true)
    {
        $result = false;
        $markerPrefix = $this->getMarkerPrefix();
        if (
            $enable &&
            $this->isLoaded()
        ) {
            $markerArray['###' . $markerPrefix . '_IMAGE###'] =
                '<img src="' . ExtensionManagementUtility::siteRelPath('captcha') . 'captcha/captcha.php" alt="" />';
            $result = true;
        } else {
            $markerArray['###' . $markerPrefix . '_IMAGE###'] = '';
        }

        return $result;
    }

    /**
    * Evaluates the captcha word
    *
    * @param array $captchaWord: captcha word which is to be checked
    * @param string $name: qualifier name of the captcha
    * @return boolean true if the evaluation is successful, false in error case
    */
    public function evalValues ($captchaWord, $name)
    {
        $result = true;
        if (
            $name == $this->getName() &&
            $this->isLoaded()
        ) {
            if ($captchaWord == '') {
                $result = false;
            } else {
                $captchaString = '';
                $started = session_start();
                if (isset($_SESSION[$this->sessionName])) {
                    $captchaString = $_SESSION[$this->sessionName];
                    if (
                        empty($captchaString) ||
                        $captchaWord !== $captchaString
                    ) {
                        $result = false;
                    } else {
                        $_SESSION[$this->sessionName] = '';
                    }
                } else {
                    $result = false;
                }
            }
        }
        return $result;
    }
}
