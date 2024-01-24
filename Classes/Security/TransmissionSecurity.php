<?php

namespace JambageCom\Div2007\Security;

/***************************************************************
*  Copyright notice
*
*  (c) 2022 Stanislas Rolland <typo3(arobas)sjbr.ca>
*  All rights reserved
*
*  This script is part of the Typo3 project. The Typo3 project is
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
 * Part of the div2007 (Static Methods for Extensions since 2007) extension.
 *
 * Storage security functions
 *
 * @author	Stanislas Rolland <typo3(arobas)sjbr.ca>
 *
 * @package TYPO3
 * @subpackage agency
 */
use TYPO3\CMS\Core\SingletonInterface;
use JambageCom\Div2007\Utility\HtmlUtility;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TransmissionSecurity implements SingletonInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    // Extension key
    protected $extensionKey = DIV2007_EXT;
    // The storage security level: normal
    protected $transmissionSecurityLevel = 'normal';
    public $encryptionMarker = '###ENCRYPTION###';
    public $hiddenMarker = '###HIDDENFIELDS###';
    protected $encryptionAttribute = '';
    public $requiredExtensions = [];
    public $allowLog = true;

    /**
     * Constructor.
     */
    public function __construct(
        $extensionKey = '',
        $allowLog = true
    ) {
        if ($extensionKey != '') {
            $this->extensionKey = $extensionKey;
        }
        $this->setTransmissionSecurityLevel();
        $this->allowLog = $allowLog;
    }

    /**
     * No functionality any more.
     *
     * @param	string
     */
    public function setEncryptionMarker($encryptionMarker): void
    {
        $this->encryptionMarker = $encryptionMarker;
    }

    /**
     * No functionality any more.
     *
     * @return	string
     */
    public function getEncryptionMarker()
    {
        return $this->encryptionMarker;
    }

    /**
     * No functionality any more.
     *
     * @param	string
     */
    public function setHiddenMarker($hiddenMarker): void
    {
        $this->hiddenMarker = $hiddenMarker;
    }

    /**
     * No functionality any more.
     *
     * @return	string
     */
    public function getHiddenMarker()
    {
        return $this->hiddenMarker;
    }

    /**
     * No functionality any more.
     *
     * @return	string
     */
    public function getEncryptionAttribute()
    {
        return $this->encryptionAttribute;
    }

    /**
     * Sets the transmission security level.
     */
    protected function setTransmissionSecurityLevel($level = '')
    {
        if ($level == '') {
            $level = 'normal';
        }
        $this->transmissionSecurityLevel = $level;
    }

    /**
     * Gets the transmission security level.
     *
     * @return	string	the transmission security level
     */
    public function getTransmissionSecurityLevel()
    {
        return $this->transmissionSecurityLevel;
    }

    /**
     * Gets the required extensions for the given transmission security level.
     *
     * @return	array	the required extensions for the given transmission security level
     */
    public function getRequiredExtensions($level)
    {
        $result = '';

        if (isset($this->requiredExtensions[$level])) {
            $result = $this->requiredExtensions[$level];
        }

        return $result;
    }

    /**
     * No functionality any more!
     *
     * @return bool  false
     */
    public function decryptIncomingFields(
        array &$row,
        &$errorCode,
        &$errorMessage
    ) {
        $decrypted = false;

        if (count($row)) {
            switch ($this->getTransmissionSecurityLevel()) {
                case 'normal':
                default:
                    // Nothing to decrypt
                    break;
            }
        }

        return $decrypted;
    }

    /**
     * No functionality any more.
     */
    public function getJavaScript(
        &$javaScript,
        $extensionKey,
        $formId,
        $checkPasswordAgain = false
    ): void {

    }

    /**
     * No functionality any more.
     */
    public function getEmptyMarkers(
        array &$markerArray
    ): void {
        $markerArray[$this->getEncryptionMarker()] = '';
        $markerArray[$this->getHiddenMarker()] = '';
    }

    /**
     * No functionality any more!
     */
    public function getMarkers(
        array &$markerArray,
        $extensionKey = '',
        $checkPasswordAgain = false,
        $loginForm = false
    ): bool {
        $markerArray[$this->getEncryptionMarker()] = '';
        $xhtmlFix = HtmlUtility::determineXhtmlFix();
        $extraHiddenFieldsArray = [];

        switch ($this->getTransmissionSecurityLevel()) {
            case 'normal':
            default:
                break;
        }

        $extraHiddenFields = '';
        if (count($extraHiddenFieldsArray)) {
            $extraHiddenFields = LF . implode(LF, $extraHiddenFieldsArray);
        }

        if ($extraHiddenFields != '') {
            $markerArray[$this->getHiddenMarker()] .= $extraHiddenFields . LF;
        }

        return true;
    }
}
