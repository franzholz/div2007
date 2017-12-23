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

use JambageCom\Div2007\Captcha\CaptchaBase;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Hook for captcha image marker when extension 'sr_freecap' is used
 */
class Freecap extends CaptchaBase
{
    protected $extensionKey = 'sr_freecap';
    protected $name         = 'freecap';
    protected $sessionName  = 'tx_srfreecap';
    protected $markerPrefix = 'SR_FREECAP';

    /**
    * SrFreecap object
    *
    * @var PiBaseApi
    */
    protected $srFreecap = null;

    /**
    * Sets the value of captcha markers
    * return boolean
    */
    public function addGlobalMarkers (array &$markerArray, $enable = true)
    {
        $result = false;
        $markerPrefix = $this->getMarkerPrefix();
        $defaultMarkerPrefix = $this->getDefaultMarkerPrefix();
        if (
            $enable &&
            $this->initialize() !== null
        ) {
            $freecapMarkerArray = $this->srFreecap->makeCaptcha();
            $captchaMarkerArray = array();
            $prefixLength = strlen($markerPrefix);

            foreach ($freecapMarkerArray as $key => $value) {
                $subKey = substr($key, 3 + $prefixLength, strlen($key) - 6 - $prefixLength);
                $newKey = '###' . $defaultMarkerPrefix . $subKey . '###';
                $captchaMarkerArray[$newKey] = $value;
            }
            $result = true;
        } else {
            $captchaMarkerArray =
                array(
                    '###' . $defaultMarkerPrefix . '_NOTICE###' => '',
                    '###' . $defaultMarkerPrefix . '_CANT_READ###' => '',
                    '###' . $defaultMarkerPrefix . '_IMAGE###' => '',
                    '###' . $defaultMarkerPrefix . '_ACCESSIBLE###' => ''
                );
        }
        $markerArray = array_merge($markerArray, $captchaMarkerArray);
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
            $captchaWord != '' &&
            ($result = $this->initialize())
        ) {
            // Save the sr_freecap word_hash
            // sr_freecap will invalidate the word_hash after calling checkWord
            $sessionData = $GLOBALS['TSFE']->fe_user->getKey('ses', $this->sessionName);

            if (!$this->srFreecap->checkWord($captchaWord)) {
                $result = false;
            } else {
                // Restore sr_freecap word_hash
                $GLOBALS['TSFE']->fe_user->setKey(
                    'ses',
                    $this->sessionName,
                    $sessionData
                );
                $GLOBALS['TSFE']->storeSessionData();
            }
        }

        return $result;
    }

    /**
    * Initializes de SrFreecap object
    */
    protected function initialize ()
    {
        $result = false;

        if (
            $this->srFreecap == null &&
            $this->isLoaded()
        ) {
            $this->srFreecap =
                GeneralUtility::makeInstance(\SJBR\SrFreecap\PiBaseApi::class);
            if (is_object($this->srFreecap)) {
                $result = true;
            }
        }

        return $result;
    }
}

