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
 * @subpackage div2007
 */
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory;
use TYPO3\CMS\Saltedpasswords\Salt\SaltFactory;
use TYPO3\CMS\Rsaauth\Backend\BackendFactory;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class StorageSecurity implements SingletonInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    // Extension key
    protected $extensionKey = DIV2007_EXT;

    /**
     * Gets the storage security level.
     *
     * @return	string	the storage security level
     */
    protected function getStorageSecurityLevel()
    {
        $result = 'salted';

        return $result;
    }

    /**
     * Encrypts the password for secure storage.
     *
     * @return	string	encrypted password
     *           boolean false in case of an error
     */
    public function encryptPasswordForStorage($password)
    {
        $encryptedPassword = $password;
        if ($password != '') {
            switch ($this->getStorageSecurityLevel()) {
                case 'salted':
                    $objHash = null;

                    if (class_exists(PasswordHashFactory::class)) {
                        $objHash = GeneralUtility::makeInstance(PasswordHashFactory::class)->getDefaultHashInstance('FE');
                    } elseif (class_exists(SaltFactory::class)) {
                        $objHash = SaltFactory::getSaltingInstance(null);
                    }

                    if (is_object($objHash)) {
                        $encryptedPassword = $objHash->getHashedPassword($password);
                    } else {
                        $encryptedPassword = false;
                        // Could not get a salting instance from saltedpasswords
                        // This must not happen: It has been checked on the beginning in the method checkRequirements that a object to generate a hash must be available. The hash generation must never fail.

                        // Failed to decrypt auto login password
                        $errorMessage =
                            $GLOBALS['TSFE']->sL(
                                'LLL:EXT:' . DIV2007_EXT . DIV2007_LANGUAGE_SUBPATH . 'locallang.xlf:security.internal_hashed_password_error'
                            );
                        $this->logger->critical($errorMessage);
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
     * Encrypts the password for auto-login on confirmation.
     *
     * @return	bool  true if the crypted password and auto-login key are filled in
     */
    public function encryptPasswordForAutoLogin(
        $password,
        &$cryptedPassword,
        &$autoLoginKey
    ) {
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
     * Decrypts the password for auto-login on confirmation or invitation acceptation.
     *
     * @return	bool  true if decryption is successfull or no rsaauth is used
     */
    public function decryptPasswordForAutoLogin(
        &$password,
        &$errorCode,
        &$errorMessage,
        $autoLoginKey
    ) {
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
                                    'LLL:EXT:' . DIV2007_EXT . DIV2007_LANGUAGE_SUBPATH . 'locallang.xlf:security.internal_decrypt_auto_login_failed'
                                );
                            $this->logger->critical($errorMessage);
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
