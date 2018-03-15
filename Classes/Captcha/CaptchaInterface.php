<?php
namespace JambageCom\Div2007\Captcha;

/*
 *  Copyright notice
 *
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


/**
* Inferface for captcha hooks
*/
interface CaptchaInterface extends \TYPO3\CMS\Core\SingletonInterface
{
    /**
    * Determines whether the required captcha extension is loaded
    *
    * @return boolean true if the required captcha extension is loaded
    */
    public function isLoaded ();

    /**
    * Returns the qualifier name of this captcha
    *
    * @return string name type of this captcha
    */
    public function getName ();

    /**
    * Sets the value of captcha markers
    *
    * @param array $markerArray: a marker array
    * @param boolean $enable: true if the markers shall be added,
    *                         false if empty markers are filled
    * @return void
    */
    public function addGlobalMarkers (array &$markerArray, $enable = true);

    /**
    * Evaluates the captcha word
    *
    * @param array $captchaWord: captcha word which is to be checked
    * @param string $evalRule: type of the captcha
    * @return boolean true if the evaluation is successful, false in error case or when the captcha word is empty.
    */
    public function evalValues ($captchaWord, $evalRule);
}

