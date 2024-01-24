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
        $result = 'normal';

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
     * No function because rsaauth has been removed! 
     *
     * @return	bool  true always
     */
    public function decryptPasswordForAutoLogin(
        &$password,
        &$errorCode,
        &$errorMessage,
        $autoLoginKey
    ) {
        $errorMessage = '';

        return true;
    }
}
