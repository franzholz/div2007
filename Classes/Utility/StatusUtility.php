<?php

namespace JambageCom\Div2007\Utility;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Part of the div2007 (Static Methods for Extensions since 2007) extension.
 *
 * Methods for the status provider reports module
 *
 * @author  Franz Holzinger <franz@ttproducts.de>
 *
 * @maintainer	Franz Holzinger <franz@ttproducts.de>
 *
 * @package TYPO3
 * @subpackage div2007
 */

use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Reports\Status;

class StatusUtility
{
    /**
     * Check whether salted passwords are enabled in front end.
     *
     * @return	Status
     */
    public static function checkIfGlobalVariablesAreSet($extensionName, $globalVariables)
    {
        $title = LocalizationUtility::translate('LLL:EXT:' . DIV2007_EXT . '/Resources/Private/Language/locallang_statusreport.xlf:Global_variables_in_front_end', $extensionName);
        $value = null;
        $message = null;
        $status = ContextualFeedbackSeverity::OK;

        if (
            isset($globalVariables) &&
            is_array($globalVariables)
        ) {
            foreach ($globalVariables as $subkey => $subkeyVariables) {
                if (
                    isset($subkeyVariables) &&
                    is_array($subkeyVariables)
                ) {
                    foreach ($subkeyVariables as $key => $expression) {
                        if (is_scalar($expression)) {
                            if ($GLOBALS['TYPO3_CONF_VARS'][$subkey][$key] != $expression) {
                                $value = LocalizationUtility::translate('LLL:EXT:' . DIV2007_EXT . '/Resources/Private/Language/locallang_statusreport.xlf:' . ($expression ? 'disabled' : 'enabled'), $extensionName);
                                $message = LocalizationUtility::translate('LLL:EXT:' . DIV2007_EXT . '/Resources/Private/Language/locallang_statusreport.xlf:global_variable_must_be_set', $extensionName);
                                $message = sprintf($message, $extensionName, '$GLOBALS[\'TYPO3_CONF_VARS\'][\'' . $subkey . '\'][\'' . $key . '\']', htmlspecialchars($expression));
                                $status = ContextualFeedbackSeverity::ERROR;
                                break;
                            }
                        }
                    }
                }
            }
        }
        $result = GeneralUtility::makeInstance(Status::class, $title, $value, $message, $status);

        return $result;
    }
}
