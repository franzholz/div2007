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

use JambageCom\Div2007\Captcha\CaptchaInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Hook for captcha image marker when extension 'captcha' is used
 */
abstract class CaptchaBase implements CaptchaInterface
{
    protected $extensionKey; // override the extension key
    protected $name;         // override the qualifier name
    protected $markerPrefix = 'CAPTCHA';
    private   $defaultMarkerPrefix = 'CAPTCHA';

    public function getExtensionKey ()
    {
        return $this->extensionKey;
    }

    /**
    * Returns the qualifier name for this captcha
    *
    * @return string the type name for this captcha
    */
    public function getName ()
    {
        return $this->name;
    }

    /**
    * Returns the prefix name for the marker key
    *
    * @return string prefix name
    */
    public function getMarkerPrefix ()
    {
        return $this->markerPrefix;
    }

    /**
    * Returns the default prefix name for the marker key
    *
    * @return string prefix name
    */
    public function getDefaultMarkerPrefix ()
    {
        return $this->defaultMarkerPrefix;
    }

    /**
    * Determines whether the required captcha extension is loaded
    *
    * @return boolean true if the required captcha extension is loaded
    */
    public function isLoaded ()
    {
        return ExtensionManagementUtility::isLoaded($this->getExtensionKey());
    }

}

