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
use SJBR\SrFreecap\PiBaseApi;
use SJBR\SrFreecap\Domain\Session\SessionStorage;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;


/**
 * Hook for captcha image marker when extension 'sr_freecap' is used
 */
class Freecap extends CaptchaBase
{
    protected $extensionKey = 'sr_freecap';
    protected $name         = 'freecap';
    protected $markerPrefix = 'SR_FREECAP';

    /**
    * SrFreecap object
    *
    * @var SJBR\SrFreecap\PiBaseApi
    */
    protected $srFreecap = null;

    /**
    * Session object
    *
    * @var SJBR\SrFreecap\Domain\Session
    */
    protected $session = null;
    

    public function getApi () {
        return $this->srFreecap;
    }

    public function getSession () {
        return $this->session;
    }

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
            $this->initialize() == true
        ) {
            $freecapMarkerArray = $this->getApi()->makeCaptcha();
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
            ($result = $this->initialize())
        ) {
            if ($captchaWord == '') {
                $result = false;
            } else {
                // Save the sr_freecap word_hash
                // sr_freecap will invalidate the word_hash after calling checkWord
                $sessionData = $this->getSession()->restoreFromSession();

                if ($this->getApi()->checkWord($captchaWord)) {
                    // Restore sr_freecap word_hash
                    $this->getSession()->writeToSession($sessionData);
                    $this->getFrontendUser()->storeSessionData();
                } else {
                    $result = false;
                }
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
            $this->getApi() == null &&
            $this->isLoaded()
        ) {
            $this->srFreecap =
                GeneralUtility::makeInstance(PiBaseApi::class);
            $this->session =
                GeneralUtility::makeInstance(SessionStorage::class);
        }

        if (
            is_object($this->getApi()) &&
            is_object($this->getSession())
        ) {
            $result = true;
        }

        return $result;
    }

    /**
    * Gets a frontend user from TSFE->fe_user
    *
    * @return	\TYPO3\CMS\Frontend\Authentication\FrontendUserAuthtenication	The current frontend user object
    * @throws	SessionNotFoundException
    */
    protected function getFrontendUser()
    {
        if ($GLOBALS ['TSFE']->fe_user) {
            return $GLOBALS ['TSFE']->fe_user;
        }
        throw new SessionNotFoundException('No frontend user found in session!');
    }
}

