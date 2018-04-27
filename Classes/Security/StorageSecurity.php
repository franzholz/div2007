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
* @subpackage div2007
*
*
*/

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Rsaauth\Backend\BackendFactory;
use TYPO3\CMS\Saltedpasswords\Salt\SaltFactory;
use TYPO3\CMS\Saltedpasswords\Utility\SaltedPasswordsUtility;

use JambageCom\Div2007\Constants\ErrorCode;


class StorageSecurity implements \TYPO3\CMS\Core\SingletonInterface {
        // Extension key
    protected $extensionKey = DIV2007_EXT;

    /**
    * Gets the storage security level
    *
    * @return	string	the storage security level
    */
    static protected function getStorageSecurityLevel ()
    {
        $result = 'normal';
        if (
            ExtensionManagementUtility::isLoaded('saltedpasswords') &&
            SaltedPasswordsUtility::isUsageEnabled('FE')
        ) {
            $result = 'salted';
        }
        return $result;
    }

    /**
    * Encrypts the password for secure storage
    *
    * @param	string	$password: password to encrypt
    * @return	string	encrypted password
    *           boolean false in case of an error
    */
    static public function encryptPasswordForStorage ($password)
    {
        $encryptedPassword = $password;
        if ($password != '') {
            switch (self::getStorageSecurityLevel()) {
                case 'salted':
                    $objSalt = SaltFactory::getSaltingInstance(null);
                    if (is_object($objSalt)) {
                        $encryptedPassword = $objSalt->getHashedPassword($password);
                    } else {
                        $encryptedPassword = false;
                        // Could not get a salting instance from saltedpasswords
                        // This should not happen: It has been checked on the beginning in the method checkRequirements.
                    }
                    break;
                case 'normal':
                default:
                        // No encryption!
                    break;
            }
        }

        return $encryptedPassword;
    }

    /**
    * Encrypts the password for auto-login on confirmation
    *
    * @param	string	$password: the password to be encrypted
    * @param	string	$cryptedPassword: returns the encrypted password
    * @param	string	$autoLoginKey: returns the auto-login key
    * @return	boolean  true if the crypted password and auto-login key are filled in
    */
    static public function encryptPasswordForAutoLogin (
        $password,
        &$cryptedPassword,
        &$autoLoginKey
    )
    {
        $result = false;
        $privateKey = '';
        $cryptedPassword = '';

        if ($password != '') {
                // Create the keypair
            $keyPair = openssl_pkey_new();

                // Get private key
            openssl_pkey_export($keyPair, $privateKey);
                // Get public key
            $keyDetails = openssl_pkey_get_details($keyPair);
            $publicKey = $keyDetails['key'];

            if (
                @openssl_public_encrypt(
                    $password,
                    $cryptedPassword,
                    $publicKey
                )
            ) {
                $autoLoginKey = $privateKey;
                $result = true;
            }
        }

        return $result;
    }

    /**
    * Decrypts the password for auto-login on confirmation or invitation acceptation
    *
    * @param string	$password: incoming and outgoing string of the password to be decrypted
    * @param string $errorCode: outgoing text with an error code
    * @param string $errorMessage: outgoing text with an error message
    * @param string	$autoLoginKey: incoming the auto-login private key
    * @return	boolean  true if decryption is successfull or no rsaauth is used
    */
    public function decryptPasswordForAutoLogin (
        &$password,
        &$errorCode,
        &$errorMessage,
        $autoLoginKey
    )
    {
        $result = true;
        $errorMessage = '';

        if ($autoLoginKey != '') {
            $privateKey = $autoLoginKey;
            if ($privateKey != '') {
                if (
                    $password != '' &&
                    ExtensionManagementUtility::isLoaded('rsaauth')
                ) {
                    $backend = BackendFactory::getBackend();
                    if (is_object($backend) && $backend->isAvailable()) {
                        $decryptedPassword = $backend->decrypt($privateKey, $password);
                        if ($decryptedPassword) {
                            $password = $decryptedPassword;
                        } else {
                            $errorCode = SECURITY_RSA_AUTH_DECRYPTION_FAILED;
                                // Failed to decrypt auto login password
                            $errorMessage =
                                $GLOBALS['TSFE']->sL(
                                    'LLL:EXT:' . $this->extensionKey . DIV2007_LANGUAGE_SUBPATH . 'locallang.xlf:security.internal_decrypt_auto_login_failed'
                                );
                            GeneralUtility::sysLog(
                                $errorMessage,
                                $this->extensionKey,
                                GeneralUtility::SYSLOG_SEVERITY_ERROR
                            );
                        }
                    } else {
                        // Required RSA auth backend not available
                        // Should not happen: checked in method checkRequirements
                        $result = false;
                    }
                }
            }
        }

        return $result;
    }
}

