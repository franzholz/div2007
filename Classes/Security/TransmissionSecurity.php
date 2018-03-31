<?php

namespace JambageCom\Div2007\Security;

/***************************************************************
*  Copyright notice
*
*  (c) 2018 Stanislas Rolland <typo3(arobas)sjbr.ca>
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
*
*
*/

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Rsaauth\Backend\BackendFactory;
use TYPO3\CMS\Rsaauth\Storage\StorageFactory;

use JambageCom\Div2007\Utility\HtmlUtility;


class TransmissionSecurity implements \TYPO3\CMS\Core\SingletonInterface {
        // Extension key
    protected $extensionKey = DIV2007_EXT;
        // The storage security level: normal or rsa
    protected $transmissionSecurityLevel = 'normal';

    /**
    * Constructor
    *
    * @param string $extensionKey: empty or extension key
    * @return	void
    */
    public function __construct ($extensionKey = '') {
        if ($extensionKey != '') {
            $this->extensionKey = $extensionKey;
        }
        $this->setTransmissionSecurityLevel();
    }

    /**
    * Sets the transmission security level
    *
    * @param string $level: empty or loginSecurityLevel
    * @return	void
    */
    protected function setTransmissionSecurityLevel ($level = '') {
        if ($level == '') {
            $level = $GLOBALS['TYPO3_CONF_VARS']['FE']['loginSecurityLevel'];
        }
        $this->transmissionSecurityLevel = $level;
    }

    /**
    * Gets the transmission security level
    *
    * @return	string	the storage security level
    */
    public function getTransmissionSecurityLevel () {
        return $this->transmissionSecurityLevel;
    }

    /**
    * Decrypts fields that were encrypted for transmission
    *
    * @param array $row: incoming and outgoing array that may contain encrypted fields which will be decrypted
    * @param string $errorMessage: outgoing text with an error message
    * @return void
    */
    public function decryptIncomingFields (array &$row, &$errorMessage) {
        $decrypted = false;

        if (count($row)) {
            switch ($this->getTransmissionSecurityLevel()) {
                case 'rsa':
                    $needsDecryption = false;
                    foreach ($row as $field => $value) {
                        if (isset($value) && $value != '') {
                            if (substr($value, 0, 4) == 'rsa:') {
                                $needsDecryption = true;
                            }
                        }
                    }
                    
                    if (!$needsDecryption) {
                        return $decrypted;
                    }

                        // Get services from rsaauth
                        // Can't simply use the authentication service because we have two fields to decrypt
                    /** @var $backend \TYPO3\CMS\Rsaauth\Backend\AbstractBackend */
                    $backend = BackendFactory::getBackend();
                    /** @var $storage \TYPO3\CMS\Rsaauth\Storage\AbstractStorage */
                    $storage = StorageFactory::getStorage();
            
                    if (is_object($backend) && is_object($storage)) {
                        $key = $storage->get();
                        if ($key != null) {
                            foreach ($row as $field => $value) {
                                if (isset($value) && $value != '') {
                                    if (substr($value, 0, 4) == 'rsa:') {
                                            // Decode password
                                        $result = $backend->decrypt($key, substr($value, 4));
                                        if ($result) {
                                            $row[$field] = $result;
                                            $decrypted = true;
                                        } else {
                                                // RSA auth service failed to process incoming password
                                                // May happen if the key is wrong
                                                // May happen if multiple instances of rsaauth are on same page
                                            $errorMessage =
                                                $GLOBALS['TSFE']->sL(
                                                'LLL:EXT:' . $this->extensionKey . DIV2007_LANGUAGE_SUBPATH . 'locallang.xlf:security.internal_rsaauth_process_incoming_password_failed');
                                            GeneralUtility::sysLog(
                                                $errorMessage, 
                                                $this->extensionKey,
                                                GeneralUtility::SYSLOG_SEVERITY_ERROR
                                            );
                                        }
                                    }
                                }
                            }
                                // Remove the key
                            $storage->put(null);
                        } else {
                                // RSA auth service failed to retrieve private key
                                // May happen if the key was already removed
                            $errorMessage =
                                $GLOBALS['TSFE']->sL(
                                    'LLL:EXT:' . $this->extensionKey . DIV2007_LANGUAGE_SUBPATH . 'locallang.xlf:security.internal_rsaauth_retrieve_private_key_failed');
                            GeneralUtility::sysLog($errorMessage, $this->extensionKey, GeneralUtility::SYSLOG_SEVERITY_ERROR);
                        }
                    } else {
                            // Required RSA auth backend not available
                            // Should not happen. It should have been checked before the call of this function
                        $errorMessage =
                            $GLOBALS['TSFE']->sL(
                                'LLL:EXT:' . $this->extensionKey . DIV2007_LANGUAGE_SUBPATH . 'locallang.xlf:security.internal_rsaauth_backend_not_available');
                        GeneralUtility::sysLog($errorMessage, $this->extensionKey, GeneralUtility::SYSLOG_SEVERITY_ERROR);
                    }
                    break;
                case 'normal':
                default:
                        // Nothing to decrypt
                    break;
            }
        }
        return $decrypted;
    }

    /**
    * Adds a JavaScript string which contains a workaround to avoid the encryption of a password again field. Both passwords are compared immediately on the JavaScript side before using RSA encryption.
    *
    * @param string $javaScript: outgoing text with the added JavaScript
    * @param string $extensionKey: extension key
    * @param boolean $checkPasswordAgain: if the password again field is present
    * @param string $formId: the form HTML id
    * @return void
    */
    public function getJavaScript (
        &$javaScript,
        $extensionKey,
        $checkPasswordAgain,
        $formId
    ) {
        if (
            $this->getTransmissionSecurityLevel() == 'rsa' &&
            $checkPasswordAgain
        ) {
            $javaScript .=
'<script type="text/javascript">
document.getElementById(\'' . $formId . '\').addEventListener(\'submit\', function(event) {
        var password = document.getElementById(\'' . $extensionKey . '-password\'); 
        var password_again = document.getElementById(\'' . $extensionKey . '-password_again\');

        if (!password.value.trim().length) {
            event.stopImmediatePropagation();
            return false; 
        }
        if (password.value != password_again.value) {
            document.getElementById(\'password_again_failure\').value = 1;
            password.value = \'X\';
            event.stopImmediatePropagation();
        } else {
            document.getElementById(\'' . $extensionKey . '[submit-security]\').value = \'1\'; 
        }
        password_again.value = \'\';
    });
</script>';
        }
    }

    /**
    * Adds values to the ###HIDDENFIELDS### and ###ENCRYPTION### markers which are needed for RSA encryption
    *
    * @param array $markerArray: incoming and outgoing marker array
    * @param string $extensionKey: extension key
    * @param boolean $checkPasswordAgain: if the password again field is present
    * @param boolean $loginForm: if it is a login form where some login hooks shall be performed
    * @return void
    */
    public function getMarkers (
        array &$markerArray,
        $extensionKey,
        $checkPasswordAgain,
        $loginForm = false
    ) {
        $markerArray['###ENCRYPTION###'] = '';
        $xhtmlFix = HtmlUtility::getXhtmlFix();
        $extraHiddenFieldsArray = array();

        if (
            $loginForm &&
            is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['felogin']['loginFormOnSubmitFuncs'])
        ) {
            $_params = array();
            foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['felogin']['loginFormOnSubmitFuncs'] as $funcRef) {
                list($onSubmit, $hiddenFields) = GeneralUtility::callUserFunction($funcRef, $_params, $this);
                $extraHiddenFieldsArray[] = $hiddenFields;
            }
        }

        switch ($this->getTransmissionSecurityLevel()) {
            case 'rsa':
                if ($checkPasswordAgain) {

                    $extraHiddenFieldsArray[] = '<input type="hidden" name="password_again_failure" value="0"' . $xhtmlFix . '>' . LF . '<input type="hidden" name="' . $extensionKey . '[submit-security]" value="0"' . $xhtmlFix . '>';
                }

                $markerArray['###ENCRYPTION###'] = ' data-rsa-encryption=""';
                break;
            case 'normal':
            default:
                break;
        }

        $extraHiddenFields = '';
        if (count($extraHiddenFieldsArray)) {
            $extraHiddenFields = LF . implode(LF, $extraHiddenFieldsArray);
        }

        if ($extraHiddenFields != '') {
            $markerArray['###HIDDENFIELDS###'] .= $extraHiddenFields . LF;
        }
        
        return true;
    }
}

