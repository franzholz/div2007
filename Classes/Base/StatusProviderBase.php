<?php

namespace JambageCom\Div2007\Base;

/*
*  Copyright notice
*
*  (c) 2018 Stanislas Rolland <typo3(arobas)sjbr.ca>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
*/

use JambageCom\Div2007\Utility\StatusUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Reports\Status;
use TYPO3\CMS\Reports\StatusProviderInterface;

/**
 * checking of the required or conflicting configurations.
 */
class StatusProviderBase implements StatusProviderInterface
{
    /**
     * @var string Extension key: must be overridden
     */
    protected $extensionKey = '';

    /**
     * @var string Extension name: must be overridden
     */
    protected $extensionName = '';

    public function getExtensionKey()
    {
        return $this->extensionKey;
    }

    public function getExtensionName()
    {
        return $this->extensionName;
    }

    public function getGlobalVariables()
    {
        return null;
    }

    /**
     * Compiles a collection of system status checks as a status report.
     *
     * @return array List of status
     */
    public function getStatus()
    {
        $result = [
            'requiredExtensionsAreInstalled' => $this->checkIfRequiredExtensionsAreInstalled(),
            'noConflictingExtensionIsInstalled' => $this->checkIfNoConflictingExtensionIsInstalled(),
            'frontEndLoginSecurityLevelIsCorrectlySet' => $this->checkIfFrontEndLoginSecurityLevelIsCorrectlySet(),
            'saltedPasswordsAreEnabledInFrontEnd' => $this->checkIfSaltedPasswordsAreEnabledInFrontEnd(),
            'globalVariablesAreSet' => StatusUtility::checkIfGlobalVariablesAreSet($this->getExtensionName(), $this->getGlobalVariables()),
        ];

        return $result;
    }

    /**
     * Check whether any required extension is not installed.
     *
     * @return	Status
     */
    protected function checkIfRequiredExtensionsAreInstalled()
    {
        $title = LocalizationUtility::translate('LLL:EXT:' . DIV2007_EXT . '/Resources/Private/Language/locallang_statusreport.xlf:Required_extensions_not_installed', $this->getExtensionName());
        $value = null;
        $message = null;
        $status = Status::OK;
        $missingExtensions = [];

        if (
            isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->getExtensionKey()]['constraints']['depends']) &&
            is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->getExtensionKey()]['constraints']['depends'])
        ) {
            $requiredExtensions = array_diff(array_keys($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->getExtensionKey()]['constraints']['depends']), ['php', 'typo3']);
            foreach ($requiredExtensions as $extensionKey) {
                if (!ExtensionManagementUtility::isLoaded($extensionKey)) {
                    $missingExtensions[] = $extensionKey;
                }
            }
        }

        if (count($missingExtensions)) {
            $value = LocalizationUtility::translate('LLL:EXT:' . DIV2007_EXT . '/Resources/Private/Language/locallang_statusreport.xlf:keys', $this->getExtensionName()) . ' ' . implode(', ', $missingExtensions);
            $message = LocalizationUtility::translate('LLL:EXT:' . DIV2007_EXT . '/Resources/Private/Language/locallang_statusreport.xlf:install', $this->getExtensionName());
            $status = Status::ERROR;
        } else {
            $value = LocalizationUtility::translate('LLL:EXT:' . DIV2007_EXT . '/Resources/Private/Language/locallang_statusreport.xlf:none', $this->getExtensionName());
            $message = '';
            $status = Status::OK;
        }

        $result =
            GeneralUtility::makeInstance(
                Status::class,
                $title,
                $value,
                $message,
                $status
            );

        return $result;
    }

    /**
     * Check whether any conflicting extension has been installed.
     *
     * @return	Status
     */
    protected function checkIfNoConflictingExtensionIsInstalled()
    {
        $title = LocalizationUtility::translate('LLL:EXT:' . DIV2007_EXT . '/Resources/Private/Language/locallang_statusreport.xlf:Conflicting_extensions_installed', $this->getExtensionName());
        $value = null;
        $message = null;
        $status = Status::OK;
        $conflictingExtensions = [];

        if (
            isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->getExtensionKey()]['constraints']['conflicts']) &&
            is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->getExtensionKey()]['constraints']['conflicts'])) {
            foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->getExtensionKey()]['constraints']['conflicts'] as $extensionKey => $version) {
                if (ExtensionManagementUtility::isLoaded($extensionKey)) {
                    $conflictingExtensions[] = $extensionKey;
                }
            }
        }

        if (count($conflictingExtensions)) {
            $value = LocalizationUtility::translate('LLL:EXT:' . DIV2007_EXT . '/Resources/Private/Language/locallang_statusreport.xlf:keys', $this->getExtensionName()) . ' ' . implode(', ', $conflictingExtensions);
            $message = LocalizationUtility::translate('LLL:EXT:' . DIV2007_EXT . '/Resources/Private/Language/locallang_statusreport.xlf:uninstall', $this->getExtensionName());
            $status = Status::ERROR;
        } else {
            $value = LocalizationUtility::translate('LLL:EXT:' . DIV2007_EXT . '/Resources/Private/Language/locallang_statusreport.xlf:none', $this->getExtensionName());
            $message = '';
            $status = Status::OK;
        }
        $result = GeneralUtility::makeInstance(Status::class, $title, $value, $message, $status);

        return $result;
    }

    /**
     * Check whether frontend login security level is correctly set.
     *
     * @return	Status
     */
    protected function checkIfFrontEndLoginSecurityLevelIsCorrectlySet()
    {
        $title = LocalizationUtility::translate('LLL:EXT:' . DIV2007_EXT . '/Resources/Private/Language/locallang_statusreport.xlf:Front_end_login_security_level', $this->getExtensionName());
        $value = null;
        $message = null;
        $status = Status::OK;
        $supportedTransmissionSecurityLevels = ['', 'normal', 'rsa'];

        if (
            in_array(
                $GLOBALS['TYPO3_CONF_VARS']['FE']['loginSecurityLevel'],
                $supportedTransmissionSecurityLevels
            )
        ) {
            $value = $GLOBALS['TYPO3_CONF_VARS']['FE']['loginSecurityLevel'];
            $message = '';
            $status = Status::OK;
        } else {
            $value = $GLOBALS['TYPO3_CONF_VARS']['FE']['loginSecurityLevel'];
            $message = LocalizationUtility::translate('LLL:EXT:' . DIV2007_EXT . '/Resources/Private/Language/locallang_statusreport.xlf:must_be_normal_or_rsa', $this->getExtensionName());
            $status = Status::ERROR;
        }
        $result = GeneralUtility::makeInstance(Status::class, $title, $value, $message, $status);

        return $result;
    }

    /**
     * Check whether salted passwords are enabled in front end.
     *
     * @return	Status
     */
    protected function checkIfSaltedPasswordsAreEnabledInFrontEnd()
    {
        $title = LocalizationUtility::translate('LLL:EXT:' . DIV2007_EXT . '/Resources/Private/Language/locallang_statusreport.xlf:Salted_passwords_in_front_end', $this->getExtensionName());

        $value = LocalizationUtility::translate('LLL:EXT:' . DIV2007_EXT . '/Resources/Private/Language/locallang_statusreport.xlf:enabled', $this->getExtensionName());
        $message = '';
        $status = Status::OK;
        $result = GeneralUtility::makeInstance(Status::class, $title, $value, $message, $status);

        return $result;
    }
}
