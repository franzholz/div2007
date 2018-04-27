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

use JambageCom\Div2007\Constants\ErrorCode;
use JambageCom\Div2007\Utility\HtmlUtility;


class TransmissionSecurity implements \TYPO3\CMS\Core\SingletonInterface {
        // Extension key
    protected $extensionKey = DIV2007_EXT;
        // The storage security level: normal or rsa
    protected $transmissionSecurityLevel = 'normal';
    public    $encryptionMarker = '###ENCRYPTION###';
    public    $hiddenMarker     = '###HIDDENFIELDS###';
    protected $encryptionAttribute = 'data-rsa-encryption=""';
    public    $requiredExtensions = array('rsa' => array('rsaauth'));
    public    $allowSyslog = true;

    /**
    * Constructor
    *
    * @param string $extensionKey: empty or extension key
    * @return	void
    */
    public function __construct (
        $extensionKey = '',
        $allowSyslog = true
    )
    {
        if ($extensionKey != '') {
            $this->extensionKey = $extensionKey;
        }
        $this->setTransmissionSecurityLevel();
        $this->allowSyslog = $allowSyslog;
    }

    /**
    * Sets the encryption marker for which the replacement is used by the RSA encryption extension
    *
    * @param	string
    * @return	void
    */
    public function setEncryptionMarker ($encryptionMarker)
    {
        $this->encryptionMarker = $encryptionMarker;
    }

    /**
    * Returns the encryption marker for which the replacement is used by the RSA encryption extension
    *
    * @return	string
    */
    public function getEncryptionMarker () {
        return $this->encryptionMarker;
    }

    /**
    * Sets the hidden fields marker for which the RSA encryption extension itself or its hooks might add entries
    *
    * @param	string
    * @return	void
    */
    public function setHiddenMarker ($hiddenMarker)
    {
        $this->hiddenMarker = $hiddenMarker;
    }

    /**
    * Returns the hidden fields marker for which the RSA encryption extension itself or its hooks might add entries
    *
    * @return	string
    */
    public function getHiddenMarker ()
    {
        return $this->hiddenMarker;
    }

    /**
    * Returns the encryption attribute used by the RSA encryption extension
    *
    * @return	string
    */
    public function getEncryptionAttribute ()
    {
        return $this->encryptionAttribute;
    }

    /**
    * Sets the transmission security level
    *
    * @param string $level: empty or loginSecurityLevel
    * @return	void
    */
    protected function setTransmissionSecurityLevel ($level = '')
    {
        if ($level == '') {
            $level = $GLOBALS['TYPO3_CONF_VARS']['FE']['loginSecurityLevel'];
        }
        $this->transmissionSecurityLevel = $level;
    }

    /**
    * Gets the transmission security level
    *
    * @return	string	the transmission security level
    */
    public function getTransmissionSecurityLevel ()
    {
        return $this->transmissionSecurityLevel;
    }
    
    /**
    * Gets the required extensions for the given transmission security level
    *
    * @param string $level: 
    * @return	array	the required extensions for the given transmission security level
    */
    public function getRequiredExtensions ($level)
    {
        $result = '';

        if (isset($this->requiredExtensions[$level])) {
            $result = $this->requiredExtensions[$level];
        }

        return $result;
    }

    /**
    * Decrypts fields that were encrypted for transmission
    *
    * @param array $row: incoming and outgoing array that may contain encrypted fields which will be decrypted
    * @param string $errorCode: outgoing text with an error code
    * @param string $errorMessage: outgoing text with an error message
    * @return boolean  true if any entry inside of the $row array has been decrypted.
    */
    public function decryptIncomingFields (
        array &$row,
        &$errorCode,
        &$errorMessage
    )
    {
        $decrypted = false;

        if (count($row)) {
            switch ($this->getTransmissionSecurityLevel()) {
                case 'rsa':
                    $needsDecryption = false;
                    foreach ($row as $field => $value) {
                        if (isset($value) && $value != '') {
                            if (is_array($value)) {
                                $row2 = $value;
                                foreach ($row2 as $field2 => $value2) {
                                    if (
                                        is_string($value2) &&
                                        substr($value2, 0, 4) == 'rsa:'
                                    ) {
                                        $needsDecryption = true;
                                    }
                                }
                            } else if (
                                is_string($value) &&
                                substr($value, 0, 4) == 'rsa:'
                            ) {
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
                    $errorDecryptField = '';

                    if (is_object($backend) && is_object($storage)) {
                        $key = $storage->get();
                        if ($key != null) {
                            foreach ($row as $field => $value) {
                                if (isset($value) && $value != '') {
                                    if (is_array($value)) {
                                        $row2 = $value;
                                        foreach ($row2 as $field2 => $value2) {
                                            if (
                                                is_string($value2) &&
                                                substr($value2, 0, 4) == 'rsa:'
                                            ) {
                                                    // Decode password
                                                $result =
                                                    $backend->decrypt(
                                                        $key,
                                                        substr($value2, 4)
                                                    );
                                                if ($result !== null) {
                                                    $row2[$field2] = $result;
                                                    $decrypted = true;
                                                } else {
                                                    $errorDecryptField = $field . '|' . $field2;
                                                }
                                            }
                                        } // foreach
                                        $row[$field] = $row2;
                                    } else if (
                                        is_string($value) &&
                                        substr($value, 0, 4) == 'rsa:'
                                    ) {
                                            // Decode password
                                        $result = $backend->decrypt($key, substr($value, 4));
                                        if ($result !== null) {
                                            $row[$field] = $result;
                                            $decrypted = true;
                                        } else {
                                            $errorDecryptField = $field;
                                        }
                                    }
                                }
                            } // foreach

                            if ($errorDecryptField != '') {
                                    // RSA auth service failed to process incoming password
                                    // May happen if the key is wrong
                                    // May happen if multiple instances of rsaauth are on same page
                                    // May happen if the entered password has been empty
                                $errorCode = SECURITY_RSA_AUTH_FAILED_INCOMING;
                                $errorMessage =
                                    $GLOBALS['TSFE']->sL(
                                    'LLL:EXT:' . $this->extensionKey . DIV2007_LANGUAGE_SUBPATH . 'locallang.xlf:security.internal_rsaauth_process_incoming_field_failed');
                                $errorMessage = sprintf($errorMessage, $errorDecryptField);

                                if ($this->allowSyslog) {
                                    GeneralUtility::sysLog(
                                        $errorMessage, 
                                        $this->extensionKey,
                                        GeneralUtility::SYSLOG_SEVERITY_ERROR
                                    );
                                }                                
                            }

                            if ($decrypted) {
                                    // Remove the key
                                $storage->put(null);
                            }
                        } else {
                            $errorCode = SECURITY_RSA_AUTH_FAILED_PRIVATE_KEY;
                                // RSA auth service failed to retrieve private key
                                // May happen if the key was already removed
                            $errorMessage =
                                $GLOBALS['TSFE']->sL(
                                    'LLL:EXT:' . $this->extensionKey . DIV2007_LANGUAGE_SUBPATH . 'locallang.xlf:security.internal_rsaauth_retrieve_private_key_failed');
                            if ($this->allowSyslog) {
                                GeneralUtility::sysLog(
                                    $errorMessage,
                                    $this->extensionKey,
                                    GeneralUtility::SYSLOG_SEVERITY_ERROR
                                );
                            }
                        }
                    } else {
                        $errorCode = SECURITY_RSA_AUTH_BACKEND_NOT_AVAILABLE;
                            // Required RSA auth backend not available
                            // Should not happen. It should have been checked before the call of this function
                        $errorMessage =
                            $GLOBALS['TSFE']->sL(
                                'LLL:EXT:' . $this->extensionKey . DIV2007_LANGUAGE_SUBPATH . 'locallang.xlf:security.internal_rsaauth_backend_not_available');
                        if ($this->allowSyslog) {
                            GeneralUtility::sysLog(
                                $errorMessage,
                                $this->extensionKey,
                                GeneralUtility::SYSLOG_SEVERITY_ERROR
                            );
                        }
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
    * @param string $formId: the form HTML id
    * @param boolean $checkPasswordAgain: if the password again field is present
    * @return void
    */
    public function getJavaScript (
        &$javaScript,
        $extensionKey,
        $formId,
        $checkPasswordAgain = false
    )
    {
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
    * Adds values to the ###HIDDENFIELDS### and ###ENCRYPTION### markers as empty values. Call this method if the RSA encryption is inactive.
    *
    * @param array $markerArray: incoming and outgoing marker array
    * @param string $extensionKey: extension key. This is only needed if the password again check is used.
    * @param boolean $checkPasswordAgain: if the password again field is present
    * @param boolean $loginForm: if it is a login form where some login hooks shall be performed
    * @return void
    */
    public function getEmptyMarkers (
        array &$markerArray
    )
    {
        $markerArray[$this->getEncryptionMarker()] = '';
        $markerArray[$this->getHiddenMarker()]     = '';    
    }

    /**
    * Adds values to the ###HIDDENFIELDS### and ###ENCRYPTION### markers which are needed for RSA encryption
    *
    * @param array $markerArray: incoming and outgoing marker array
    * @param string $extensionKey: extension key. This is only needed if the password again check is used.
    * @param boolean $checkPasswordAgain: if the password again field is present
    * @param boolean $loginForm: if it is a login form where some login hooks shall be performed
    * @return void
    */
    public function getMarkers (
        array &$markerArray,
        $extensionKey = '',
        $checkPasswordAgain = false,
        $loginForm = false
    )
    {
        $markerArray[$this->getEncryptionMarker()] = '';
        $xhtmlFix = HtmlUtility::determineXhtmlFix();
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
                if (
                    $checkPasswordAgain &&
                    $extensionKey != ''
                ) {
                    $extraHiddenFieldsArray[] = '<input type="hidden" name="password_again_failure" value="0"' . $xhtmlFix . '>' . LF . '<input type="hidden" name="' . $extensionKey . '[submit-security]" value="0"' . $xhtmlFix . '>';
                }

                $markerArray[$this->getEncryptionMarker()] = ' ' . $this->getEncryptionAttribute();
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
            $markerArray[$this->getHiddenMarker()] .= $extraHiddenFields . LF;
        }
        
        return true;
    }
}

